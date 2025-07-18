from models.db_models import db
from flask import current_app
import logging

def init_db(app):
    """Initialize database with Flask app"""
    db.init_app(app)
    
    with app.app_context():
        try:
            db.create_all()
            logging.info("Database tables created successfully")
        except Exception as e:
            logging.error(f"Error creating database tables: {e}")

def get_db():
    """Get database instance"""
    return db

def log_action(user_id, action, details, ip_address=None):
    """Log user actions to system_logs table"""
    from models.db_models import SystemLog
    
    try:
        log_entry = SystemLog(
            user_id=user_id,
            action=action,
            details=details,
            ip_address=ip_address
        )
        db.session.add(log_entry)
        db.session.commit()
    except Exception as e:
        logging.error(f"Error logging action: {e}")
        db.session.rollback() 