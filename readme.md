# Healsync - Hospital Management System 🏥

Healsync is a comprehensive **role-based hospital management system** built using **PHP, MySQL, and TailwindCSS**.  
It provides secure authentication, billing, treatments, prescriptions, and receipt generation for hospitals and clinics.

---

## 🚀 Features

### 👤 Patient Portal
- **Appointment Management**: Book and manage appointments with doctors
- **Treatment History**: View and download treatment records (PDF)
- **Prescription Access**: View prescriptions and download PDF copies
- **Billing & Payments**: Manage bills and receipts (online/offline payments)
- **Notifications**: Real-time notifications for prescriptions, treatments, and payments

### 👨‍⚕️ Doctor Portal
- **Appointment Management**: View approved/pending appointments
- **Treatment Records**: Add treatments with automatic billing
- **Prescription Management**: Add prescriptions with multiple medicines
- **Patient History**: Comprehensive view of patient records
- **PDF Generation**: Download treatment and prescription records

### 🏥 Reception Portal
- **Offline Payment Processing**: Manage cash/offline payments
- **Bill Management**: View and process patient bills
- **Receipt Generation**: Generate payment receipts (PDF)
- **Payment Confirmation**: Mark payments as received

### 👨‍💼 Admin Portal
- **User Management**: Manage doctors, patients, and reception staff
- **System Analytics**: Overview of system usage and statistics
- **Audit Logs**: Track all system activities for accountability
- **Role Management**: Control access permissions

---

## 🛠️ Tech Stack

- **Backend**: PHP 8+, MySQL, PDO
- **Frontend**: TailwindCSS, Lucide Icons, Vanilla JavaScript
- **Security**: CSRF Protection, Password Hashing, Session Management
- **PDF**: FPDF library for document generation

---

## ⚙️ Installation

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd healsync
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   ```bash
   mysql -u root -p
   CREATE DATABASE healsync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   mysql -u root -p healsync < sql/schema.sql
   ```

4. **Configure Database**
   Update `includes/db.php` with your credentials

5. **Access System**
   ```
   http://localhost/healsync
   ```

---

## 🔒 Security Features

- **Multi-role System**: Admin, Doctor, Patient, Receptionist
- **Session Management**: Secure session handling with timeout
- **Password Security**: Bcrypt hashing with salt
- **CSRF Protection**: All forms protected against CSRF attacks
- **SQL Injection Prevention**: PDO prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **Audit Logging**: Comprehensive activity tracking

---

## 📄 PDF Generation

- **Treatment Records** - Individual treatment details
- **Treatment History** - Complete treatment timeline
- **Prescription Copies** - Medicine prescriptions
- **Payment Receipts** - Payment confirmations

---

## 🎨 UI/UX Features

- **TailwindCSS**: Utility-first styling
- **Responsive Layout**: Mobile-first design
- **Intuitive Navigation**: Role-based menus
- **Real-time Updates**: Live notifications
- **Form Validation**: Client and server-side validation
- **Interactive Elements**: Modals, tooltips, progress indicators

---

## 📊 System Capabilities

- **Appointment Management**: Online booking and scheduling
- **Medical Records**: Treatment tracking and prescription management
- **Billing & Payments**: Automatic billing with multiple payment methods
- **Reporting & Analytics**: Dashboard analytics and audit reports

---

## 🚀 Deployment

### Production Checklist
- [ ] Update database credentials
- [ ] Configure email settings
- [ ] Set up SSL certificate
- [ ] Enable error logging
- [ ] Configure backup system
- [ ] Update default passwords
- [ ] Test all user roles
- [ ] Verify PDF generation
- [ ] Check mobile responsiveness

---

## 🐛 Troubleshooting

**Database Connection Error**
```bash
sudo systemctl status mysql
# Verify credentials in includes/db.php
```

**PDF Generation Issues**
```bash
composer show setasign/fpdf
chmod 755 vendor/fpdf/
```

**Session Problems**
```bash
ls -la /tmp/
# Clear browser cookies and cache
```

---

## 📈 Future Enhancements

- [ ] **Telemedicine Integration**: Video consultations
- [ ] **Mobile App**: Native iOS/Android apps
- [ ] **Advanced Analytics**: Business intelligence
- [ ] **API Development**: RESTful API for integrations
- [ ] **Multi-language Support**: Internationalization
- [ ] **Inventory Management**: Medicine and equipment tracking

---

## 🤝 Contributing

1. **Code Style**: Follow PSR-12 standards
2. **Documentation**: Comment all functions
3. **Testing**: Test all user roles
4. **Security**: Validate all inputs
5. **Performance**: Optimize database queries

---

## 📞 Support

- **GitHub Issues**: Bug reports and feature requests
- **Discussions**: Community support forum
- **Email Support**: Direct technical support

---

## 📄 License

This project is licensed under the **MIT License**.

---

**💡 Maintained by the Healsync Development Team**
