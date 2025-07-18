from flask import Flask, render_template, redirect, url_for, flash, request
from flask_login import LoginManager, current_user
from config import Config
from models.db_models import db, User
from utils.db import init_db
from routes.auth import auth
from routes.admin import admin
from routes.doctor import doctor
from routes.patient import patient
from routes.predict import predict
import logging

def create_app():
    app = Flask(__name__, 
                template_folder='../frontend',
                static_folder='../frontend')
    
    # Configure app
    app.config.from_object(Config)
    
    # Initialize extensions
    db.init_app(app)
    
    # Initialize login manager
    login_manager = LoginManager()
    login_manager.init_app(app)
    login_manager.login_view = 'auth.login'
    login_manager.login_message = 'Please log in to access this page.'
    login_manager.login_message_category = 'info'
    
    @login_manager.user_loader
    def load_user(user_id):
        return User.query.get(int(user_id))
    
    # Register blueprints
    app.register_blueprint(auth)
    app.register_blueprint(admin)
    app.register_blueprint(doctor)
    app.register_blueprint(patient)
    app.register_blueprint(predict)
    
    # Error handlers
    @app.errorhandler(404)
    def not_found_error(error):
        return render_template('common/404.html'), 404
    
    @app.errorhandler(500)
    def internal_error(error):
        db.session.rollback()
        return render_template('common/500.html'), 500
    
    @app.errorhandler(403)
    def forbidden_error(error):
        return render_template('common/unauthorized.html'), 403
    
    # Main routes
    @app.route('/')
    def index():
        if current_user.is_authenticated:
            return redirect(url_for(f'{current_user.role}.dashboard'))
        return redirect(url_for('auth.login'))
    
    @app.route('/health')
    def health_check():
        return {'status': 'healthy', 'message': 'Healsync HMS is running'}
    
    # Initialize database
    with app.app_context():
        try:
            db.create_all()
            logging.info("Database tables created successfully")
            
            # Create admin user if not exists
            admin_user = User.query.filter_by(role='admin').first()
            if not admin_user:
                admin_user = User(
                    username='admin',
                    email='admin@healsync.com',
                    first_name='System',
                    last_name='Administrator',
                    role='admin'
                )
                admin_user.set_password('admin123')
                db.session.add(admin_user)
                db.session.commit()
                logging.info("Default admin user created")
                
        except Exception as e:
            logging.error(f"Error initializing database: {e}")
    
    return app

if __name__ == '__main__':
    app = create_app()
    app.run(debug=True, host='0.0.0.0', port=5000) 