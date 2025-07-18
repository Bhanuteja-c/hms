from functools import wraps
from flask import request, jsonify, current_app
from flask_login import current_user, login_required
import jwt
from datetime import datetime, timedelta
from models.db_models import User
from utils.db import log_action

def generate_token(user_id, role):
    """Generate JWT token for user"""
    payload = {
        'user_id': user_id,
        'role': role,
        'exp': datetime.utcnow() + timedelta(hours=1),
        'iat': datetime.utcnow()
    }
    return jwt.encode(payload, current_app.config['JWT_SECRET_KEY'], algorithm='HS256')

def verify_token(token):
    """Verify JWT token and return user data"""
    try:
        payload = jwt.decode(token, current_app.config['JWT_SECRET_KEY'], algorithms=['HS256'])
        return payload
    except jwt.ExpiredSignatureError:
        return None
    except jwt.InvalidTokenError:
        return None

def token_required(f):
    """Decorator to require JWT token for API endpoints"""
    @wraps(f)
    def decorated(*args, **kwargs):
        token = request.headers.get('Authorization')
        
        if not token:
            return jsonify({'message': 'Token is missing'}), 401
        
        if token.startswith('Bearer '):
            token = token[7:]
        
        payload = verify_token(token)
        if not payload:
            return jsonify({'message': 'Invalid or expired token'}), 401
        
        user = User.query.get(payload['user_id'])
        if not user or not user.is_active:
            return jsonify({'message': 'User not found or inactive'}), 401
        
        request.current_user = user
        return f(*args, **kwargs)
    
    return decorated

def role_required(allowed_roles):
    """Decorator to require specific user roles"""
    def decorator(f):
        @wraps(f)
        def decorated(*args, **kwargs):
            if not current_user.is_authenticated:
                return jsonify({'message': 'Authentication required'}), 401
            
            if current_user.role not in allowed_roles:
                log_action(current_user.id, 'unauthorized_access', f'Attempted to access {request.endpoint}')
                return jsonify({'message': 'Insufficient permissions'}), 403
            
            return f(*args, **kwargs)
        return decorated
    return decorator

def admin_required(f):
    """Decorator to require admin role"""
    return role_required(['admin'])(f)

def doctor_required(f):
    """Decorator to require doctor role"""
    return role_required(['doctor'])(f)

def patient_required(f):
    """Decorator to require patient role"""
    return role_required(['patient'])(f)

def log_user_action(action, details=None):
    """Decorator to log user actions"""
    def decorator(f):
        @wraps(f)
        def decorated(*args, **kwargs):
            result = f(*args, **kwargs)
            
            if current_user.is_authenticated:
                log_action(
                    current_user.id,
                    action,
                    details or f'Accessed {request.endpoint}',
                    request.remote_addr
                )
            
            return result
        return decorated
    return decorator 