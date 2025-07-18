#!/usr/bin/env python3
"""
Healsync HMS Setup Script
This script helps set up the Healsync Hospital Management System.
"""

import os
import sys
import subprocess
import sqlite3
from pathlib import Path

def print_banner():
    """Print the setup banner"""
    print("=" * 60)
    print("🏥 Healsync Hospital Management System Setup")
    print("=" * 60)
    print()

def check_python_version():
    """Check if Python version is compatible"""
    if sys.version_info < (3, 8):
        print("❌ Error: Python 3.8 or higher is required")
        print(f"Current version: {sys.version}")
        sys.exit(1)
    print(f"✅ Python version: {sys.version.split()[0]}")

def create_virtual_environment():
    """Create virtual environment if it doesn't exist"""
    venv_path = Path("venv")
    if not venv_path.exists():
        print("📦 Creating virtual environment...")
        subprocess.run([sys.executable, "-m", "venv", "venv"], check=True)
        print("✅ Virtual environment created")
    else:
        print("✅ Virtual environment already exists")

def install_dependencies():
    """Install Python dependencies"""
    print("📦 Installing dependencies...")
    
    # Determine the pip command based on OS
    if os.name == 'nt':  # Windows
        pip_cmd = "venv\\Scripts\\pip"
    else:  # Unix/Linux/macOS
        pip_cmd = "venv/bin/pip"
    
    try:
        subprocess.run([pip_cmd, "install", "-r", "backend/requirements.txt"], check=True)
        print("✅ Dependencies installed successfully")
    except subprocess.CalledProcessError as e:
        print(f"❌ Error installing dependencies: {e}")
        sys.exit(1)

def create_env_file():
    """Create .env file if it doesn't exist"""
    env_path = Path("backend/.env")
    if not env_path.exists():
        print("🔧 Creating .env file...")
        
        env_content = """# Healsync HMS Environment Configuration
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=yourpassword
DB_NAME=healsync
SECRET_KEY=healsync-secret-key-2024-super-secure
FLASK_ENV=development
FLASK_DEBUG=1
"""
        
        with open(env_path, 'w') as f:
            f.write(env_content)
        
        print("✅ .env file created")
        print("⚠️  Please update the database credentials in backend/.env")
    else:
        print("✅ .env file already exists")

def create_sqlite_database():
    """Create SQLite database for development"""
    print("🗄️  Creating SQLite database for development...")
    
    db_path = Path("backend/healsync.db")
    if not db_path.exists():
        # Create empty database file
        db_path.touch()
        print("✅ SQLite database created")
    else:
        print("✅ SQLite database already exists")

def print_next_steps():
    """Print next steps for the user"""
    print("\n" + "=" * 60)
    print("🎉 Setup completed successfully!")
    print("=" * 60)
    print("\nNext steps:")
    print("1. Update database credentials in backend/.env")
    print("2. Activate virtual environment:")
    if os.name == 'nt':  # Windows
        print("   venv\\Scripts\\activate")
    else:  # Unix/Linux/macOS
        print("   source venv/bin/activate")
    print("3. Run the application:")
    print("   cd backend")
    print("   python app.py")
    print("4. Open your browser and go to: http://localhost:5000")
    print("5. Login with default admin credentials:")
    print("   Username: admin")
    print("   Password: admin123")
    print("\nFor MySQL setup, see the README.md file for detailed instructions.")

def main():
    """Main setup function"""
    print_banner()
    
    try:
        check_python_version()
        create_virtual_environment()
        install_dependencies()
        create_env_file()
        create_sqlite_database()
        print_next_steps()
        
    except KeyboardInterrupt:
        print("\n\n❌ Setup interrupted by user")
        sys.exit(1)
    except Exception as e:
        print(f"\n❌ Setup failed: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main() 