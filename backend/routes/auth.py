from flask import Blueprint, request, jsonify, render_template, redirect, url_for, flash
from flask_login import login_user, logout_user, login_required, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from models.db_models import User, db
from utils.security import generate_token, log_action
from datetime import datetime
import re

auth = Blueprint('auth', __name__)

@auth.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'GET':
        if current_user.is_authenticated:
            return redirect(url_for(f'{current_user.role}.dashboard'))
        return render_template('common/login.html')
    
    if request.method == 'POST':
        data = request.get_json() if request.is_json else request.form
        
        username = data.get('username')
        password = data.get('password')
        
        if not username or not password:
            return jsonify({'error': 'Username and password are required'}), 400
        
        user = User.query.filter_by(username=username).first()
        
        if user and user.check_password(password):
            if not user.is_active:
                return jsonify({'error': 'Account is deactivated'}), 403
            
            login_user(user)
            log_action(user.id, 'login', f'User logged in from {request.remote_addr}')
            
            # Generate JWT token
            token = generate_token(user.id, user.role)
            
            if request.is_json:
                return jsonify({
                    'message': 'Login successful',
                    'token': token,
                    'user': {
                        'id': user.id,
                        'username': user.username,
                        'role': user.role,
                        'name': user.get_full_name()
                    },
                    'redirect': url_for(f'{user.role}.dashboard')
                })
            else:
                flash('Login successful!', 'success')
                return redirect(url_for(f'{user.role}.dashboard'))
        else:
            if request.is_json:
                return jsonify({'error': 'Invalid username or password'}), 401
            else:
                flash('Invalid username or password', 'error')
                return render_template('common/login.html')

@auth.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'GET':
        if current_user.is_authenticated:
            return redirect(url_for(f'{current_user.role}.dashboard'))
        return render_template('common/register.html')
    
    if request.method == 'POST':
        data = request.get_json() if request.is_json else request.form
        
        # Validate required fields
        required_fields = ['username', 'email', 'password', 'first_name', 'last_name', 'role']
        for field in required_fields:
            if not data.get(field):
                return jsonify({'error': f'{field.replace("_", " ").title()} is required'}), 400
        
        # Validate email format
        email_pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
        if not re.match(email_pattern, data['email']):
            return jsonify({'error': 'Invalid email format'}), 400
        
        # Validate password strength
        if len(data['password']) < 6:
            return jsonify({'error': 'Password must be at least 6 characters long'}), 400
        
        # Check if username or email already exists
        if User.query.filter_by(username=data['username']).first():
            return jsonify({'error': 'Username already exists'}), 409
        
        if User.query.filter_by(email=data['email']).first():
            return jsonify({'error': 'Email already exists'}), 409
        
        # Create new user
        try:
            user = User(
                username=data['username'],
                email=data['email'],
                first_name=data['first_name'],
                last_name=data['last_name'],
                role=data['role'],
                phone=data.get('phone'),
                address=data.get('address')
            )
            
            # Set role-specific fields
            if data['role'] == 'doctor':
                user.specialization = data.get('specialization')
                user.license_number = data.get('license_number')
            elif data['role'] == 'patient':
                if data.get('date_of_birth'):
                    user.date_of_birth = datetime.strptime(data['date_of_birth'], '%Y-%m-%d').date()
                user.blood_group = data.get('blood_group')
                user.emergency_contact = data.get('emergency_contact')
            
            user.set_password(data['password'])
            
            db.session.add(user)
            db.session.commit()
            
            log_action(user.id, 'register', f'New user registered: {user.role}')
            
            if request.is_json:
                return jsonify({
                    'message': 'Registration successful',
                    'user_id': user.id
                }), 201
            else:
                flash('Registration successful! Please login.', 'success')
                return redirect(url_for('auth.login'))
                
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': f'Registration failed: {str(e)}'}), 500

@auth.route('/logout')
@login_required
def logout():
    if current_user.is_authenticated:
        log_action(current_user.id, 'logout', f'User logged out from {request.remote_addr}')
        logout_user()
    
    flash('You have been logged out successfully.', 'info')
    return redirect(url_for('auth.login'))

@auth.route('/profile', methods=['GET', 'PUT'])
@login_required
def profile():
    if request.method == 'GET':
        return render_template('common/profile.html', user=current_user)
    
    if request.method == 'PUT':
        data = request.get_json()
        
        try:
            # Update basic info
            if data.get('first_name'):
                current_user.first_name = data['first_name']
            if data.get('last_name'):
                current_user.last_name = data['last_name']
            if data.get('phone'):
                current_user.phone = data['phone']
            if data.get('address'):
                current_user.address = data['address']
            
            # Update role-specific fields
            if current_user.role == 'doctor':
                if data.get('specialization'):
                    current_user.specialization = data['specialization']
                if data.get('license_number'):
                    current_user.license_number = data['license_number']
            elif current_user.role == 'patient':
                if data.get('date_of_birth'):
                    current_user.date_of_birth = datetime.strptime(data['date_of_birth'], '%Y-%m-%d').date()
                if data.get('blood_group'):
                    current_user.blood_group = data['blood_group']
                if data.get('emergency_contact'):
                    current_user.emergency_contact = data['emergency_contact']
            
            # Update password if provided
            if data.get('new_password'):
                if not current_user.check_password(data.get('current_password', '')):
                    return jsonify({'error': 'Current password is incorrect'}), 400
                current_user.set_password(data['new_password'])
            
            db.session.commit()
            log_action(current_user.id, 'profile_update', 'Profile updated successfully')
            
            return jsonify({
                'message': 'Profile updated successfully',
                'user': {
                    'id': current_user.id,
                    'username': current_user.username,
                    'role': current_user.role,
                    'name': current_user.get_full_name()
                }
            })
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': f'Profile update failed: {str(e)}'}), 500

@auth.route('/unauthorized')
def unauthorized():
    return render_template('common/unauthorized.html'), 403 