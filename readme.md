# Healsync Hospital Management System

A comprehensive Hospital Management System built with Flask, featuring disease prediction using machine learning models.

## 🏥 Features

### 🔐 Authentication & Authorization

- Role-based access control (Admin, Doctor, Patient)
- Secure login/logout with session management
- JWT token support for API endpoints
- Password hashing with bcrypt

### 👨‍⚕️ Doctor Module

- Dashboard with appointment statistics
- View and manage patient appointments
- Patient diagnosis and medical record creation
- Disease prediction using ML models
- Patient history and medical reports

### 👤 Patient Module

- Personal dashboard with health statistics
- Book appointments with available doctors
- View medical reports and prescriptions
- Self-disease risk prediction
- Appointment history and status tracking

### 🧑‍💼 Admin Module

- Comprehensive system overview dashboard
- Manage doctors and patients
- View all appointments and system logs
- User account management
- System statistics and analytics

### 🤖 Machine Learning Features

- Diabetes risk prediction (Pima dataset)
- Heart disease risk prediction (Framingham dataset)
- Hypertension risk prediction
- Real-time prediction with confidence scores
- Prediction history tracking

## 🛠️ Tech Stack

### Backend

- **Python 3.8+**
- **Flask** - Web framework
- **SQLAlchemy** - ORM
- **MySQL** - Database
- **Flask-Login** - Authentication
- **JWT** - Token-based auth
- **Scikit-learn** - Machine Learning
- **Pandas** - Data processing
- **Joblib** - Model persistence

### Frontend

- **HTML5** - Structure
- **Tailwind CSS** - Styling
- **Lucide Icons** - Icons
- **JavaScript** - Interactivity

### Database

- **MySQL Workbench** - Database management
- **SQLAlchemy** - Database ORM

## 📁 Project Structure

```
healsync/
├── backend/
│   ├── app.py                 # Main Flask application
│   ├── config.py              # Configuration settings
│   ├── requirements.txt       # Python dependencies
│   ├── routes/                # Route blueprints
│   │   ├── auth.py           # Authentication routes
│   │   ├── admin.py          # Admin routes
│   │   ├── doctor.py         # Doctor routes
│   │   ├── patient.py        # Patient routes
│   │   └── predict.py        # Prediction routes
│   ├── models/
│   │   └── db_models.py      # Database models
│   ├── utils/
│   │   ├── db.py             # Database utilities
│   │   ├── security.py       # Security utilities
│   │   └── ml_model.py       # ML model utilities
│   └── ml/                   # ML datasets
│       ├── diabetes.csv
│       ├── heart.csv
│       └── hypertension.csv
├── frontend/
│   ├── common/               # Common templates
│   │   ├── base.html
│   │   ├── login.html
│   │   └── register.html
│   ├── admin/                # Admin templates
│   ├── doctor/               # Doctor templates
│   ├── patient/              # Patient templates
│   └── predict/              # Prediction templates
└── README.md
```

## 🚀 Installation & Setup

### Prerequisites

- Python 3.8 or higher
- MySQL Server
- MySQL Workbench (optional)

### 1. Clone the Repository

```bash
git clone <repository-url>
cd healsync
```

### 2. Set Up Virtual Environment

```bash
# Create virtual environment
python -m venv venv

# Activate virtual environment
# On Windows:
venv\Scripts\activate
# On macOS/Linux:
source venv/bin/activate
```

### 3. Install Dependencies

```bash
cd backend
pip install -r requirements.txt
```

### 4. Database Setup

#### Create MySQL Database

```sql
CREATE DATABASE healsync;
CREATE USER 'healsync_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON healsync.* TO 'healsync_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Configure Environment Variables

Create a `.env` file in the `backend/` directory:

```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=healsync_user
DB_PASSWORD=your_password
DB_NAME=healsync
SECRET_KEY=your-secret-key-here
FLASK_ENV=development
FLASK_DEBUG=1
```

### 5. Initialize the Application

```bash
cd backend
python app.py
```

The application will:

- Create database tables automatically
- Train ML models on first run
- Create default admin user (username: `admin`, password: `admin123`)

### 6. Access the Application

- Open your browser and go to `http://localhost:5000`
- Login with default admin credentials or register new users

## 👥 Default Users

### Admin Account

- **Username:** admin
- **Password:** admin123
- **Email:** admin@healsync.com

## 🔧 Configuration

### Database Configuration

Edit `backend/config.py` to modify database settings:

```python
class Config:
    SQLALCHEMY_DATABASE_URI = "mysql://user:password@localhost/healsync"
    SECRET_KEY = "your-secret-key"
```

### ML Model Configuration

Models are automatically trained on first run using datasets in `backend/ml/`:

- `diabetes.csv` - Pima Indian Diabetes dataset
- `heart.csv` - Framingham Heart Study dataset
- `hypertension.csv` - Heart disease dataset

## 📊 API Endpoints

### Authentication

- `POST /login` - User login
- `POST /register` - User registration
- `GET /logout` - User logout

### Admin Routes

- `GET /admin/dashboard` - Admin dashboard
- `GET /admin/manage-doctors` - Manage doctors
- `GET /admin/manage-patients` - Manage patients
- `GET /admin/appointments` - View appointments
- `GET /admin/view-logs` - System logs

### Doctor Routes

- `GET /doctor/dashboard` - Doctor dashboard
- `GET /doctor/appointments` - View appointments
- `GET /doctor/view-patients` - View patients
- `POST /doctor/diagnose/<id>` - Create diagnosis
- `GET /doctor/prediction-form` - Disease prediction

### Patient Routes

- `GET /patient/dashboard` - Patient dashboard
- `GET /patient/book-appointment` - Book appointment
- `GET /patient/view-reports` - Medical reports
- `GET /patient/my-prescriptions` - Prescriptions
- `GET /patient/predict-risk` - Risk prediction

### Prediction Routes

- `POST /predict/diabetes` - Diabetes prediction
- `POST /predict/heart` - Heart disease prediction
- `POST /predict/hypertension` - Hypertension prediction

## 🔒 Security Features

- **Password Hashing:** bcrypt for secure password storage
- **Session Management:** Flask-Login for user sessions
- **JWT Tokens:** For API authentication
- **Role-based Access:** Different permissions for each user role
- **Input Validation:** Server-side validation for all inputs
- **SQL Injection Protection:** SQLAlchemy ORM prevents SQL injection
- **XSS Protection:** Template escaping and input sanitization

## 🤖 Machine Learning Models

### Diabetes Prediction

- **Dataset:** Pima Indian Diabetes dataset
- **Features:** Pregnancies, Glucose, Blood Pressure, Skin Thickness, Insulin, BMI, Diabetes Pedigree Function, Age
- **Model:** Random Forest Classifier
- **Accuracy:** ~75-80%

### Heart Disease Prediction

- **Dataset:** Framingham Heart Study dataset
- **Features:** Age, Sex, Education, Smoking, Blood Pressure, Cholesterol, BMI, etc.
- **Model:** Random Forest Classifier
- **Accuracy:** ~85-90%

### Hypertension Prediction

- **Dataset:** Heart disease dataset
- **Features:** Age, Sex, Chest Pain, Blood Pressure, Cholesterol, etc.
- **Model:** Random Forest Classifier
- **Accuracy:** ~80-85%

## 🚀 Deployment

### Local Development

```bash
cd backend
python app.py
```

### Production Deployment

1. Set `FLASK_ENV=production` in environment variables
2. Use a production WSGI server (Gunicorn, uWSGI)
3. Set up a reverse proxy (Nginx, Apache)
4. Configure SSL certificates
5. Set up database backups

### Docker Deployment (Optional)

```dockerfile
FROM python:3.9-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt
COPY . .
EXPOSE 5000
CMD ["python", "app.py"]
```

## 🧪 Testing

### Manual Testing

1. Test user registration for all roles
2. Test login/logout functionality
3. Test appointment booking and management
4. Test disease prediction features
5. Test admin management functions

### API Testing

Use tools like Postman or curl to test API endpoints:

```bash
# Test login
curl -X POST http://localhost:5000/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'
```

## 📝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:

- Create an issue in the repository
- Contact the development team
- Check the documentation

## 🔄 Updates

### Version 1.0.0

- Initial release
- Basic HMS functionality
- ML disease prediction
- Role-based access control
- MySQL database integration

---

**Healsync HMS** - Modern Hospital Management with AI-Powered Disease Prediction
