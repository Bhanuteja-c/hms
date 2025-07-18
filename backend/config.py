import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'healsync-secret-key-2024-super-secure'
    SQLALCHEMY_DATABASE_URI = f"mysql://{os.environ.get('DB_USER', 'root')}:{os.environ.get('DB_PASSWORD', 'yourpassword')}@{os.environ.get('DB_HOST', 'localhost')}:{os.environ.get('DB_PORT', '3306')}/{os.environ.get('DB_NAME', 'healsync')}"
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    JWT_SECRET_KEY = os.environ.get('SECRET_KEY') or 'healsync-jwt-secret-2024'
    JWT_ACCESS_TOKEN_EXPIRES = 3600  # 1 hour 