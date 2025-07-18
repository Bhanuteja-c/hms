from flask import Blueprint, request, jsonify, render_template
from flask_login import login_required, current_user
from models.db_models import User, Appointment, MedicalRecord, SystemLog, db
from utils.security import admin_required, log_user_action
from datetime import datetime, timedelta
from sqlalchemy import func

admin = Blueprint('admin', __name__, url_prefix='/admin')

@admin.route('/dashboard')
@login_required
@admin_required
@log_user_action('admin_dashboard_access')
def dashboard():
    # Get statistics
    total_doctors = User.query.filter_by(role='doctor', is_active=True).count()
    total_patients = User.query.filter_by(role='patient', is_active=True).count()
    total_appointments = Appointment.query.count()
    today_appointments = Appointment.query.filter(
        func.date(Appointment.appointment_date) == datetime.now().date()
    ).count()
    
    # Recent activities
    recent_logs = SystemLog.query.order_by(SystemLog.created_at.desc()).limit(10).all()
    
    # Monthly appointment trends
    monthly_appointments = db.session.query(
        func.date_format(Appointment.appointment_date, '%Y-%m').label('month'),
        func.count(Appointment.id).label('count')
    ).group_by('month').order_by('month').limit(6).all()
    
    return render_template('admin/dashboard.html',
                         total_doctors=total_doctors,
                         total_patients=total_patients,
                         total_appointments=total_appointments,
                         today_appointments=today_appointments,
                         recent_logs=recent_logs,
                         monthly_appointments=monthly_appointments)

@admin.route('/manage-doctors')
@login_required
@admin_required
@log_user_action('admin_manage_doctors')
def manage_doctors():
    doctors = User.query.filter_by(role='doctor').order_by(User.created_at.desc()).all()
    return render_template('admin/manage-doctors.html', doctors=doctors)

@admin.route('/api/doctors', methods=['GET', 'POST', 'PUT', 'DELETE'])
@login_required
@admin_required
def api_doctors():
    if request.method == 'GET':
        doctors = User.query.filter_by(role='doctor').all()
        return jsonify([{
            'id': d.id,
            'username': d.username,
            'email': d.email,
            'first_name': d.first_name,
            'last_name': d.last_name,
            'specialization': d.specialization,
            'license_number': d.license_number,
            'phone': d.phone,
            'is_active': d.is_active,
            'created_at': d.created_at.isoformat()
        } for d in doctors])
    
    elif request.method == 'POST':
        data = request.get_json()
        
        try:
            doctor = User(
                username=data['username'],
                email=data['email'],
                first_name=data['first_name'],
                last_name=data['last_name'],
                role='doctor',
                specialization=data.get('specialization'),
                license_number=data.get('license_number'),
                phone=data.get('phone'),
                address=data.get('address')
            )
            doctor.set_password(data['password'])
            
            db.session.add(doctor)
            db.session.commit()
            
            log_user_action(current_user.id, 'create_doctor', f'Created doctor: {doctor.username}')
            
            return jsonify({'message': 'Doctor created successfully', 'id': doctor.id}), 201
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500
    
    elif request.method == 'PUT':
        data = request.get_json()
        doctor_id = data.get('id')
        
        try:
            doctor = User.query.get(doctor_id)
            if not doctor or doctor.role != 'doctor':
                return jsonify({'error': 'Doctor not found'}), 404
            
            doctor.first_name = data.get('first_name', doctor.first_name)
            doctor.last_name = data.get('last_name', doctor.last_name)
            doctor.specialization = data.get('specialization', doctor.specialization)
            doctor.license_number = data.get('license_number', doctor.license_number)
            doctor.phone = data.get('phone', doctor.phone)
            doctor.address = data.get('address', doctor.address)
            doctor.is_active = data.get('is_active', doctor.is_active)
            
            if data.get('password'):
                doctor.set_password(data['password'])
            
            db.session.commit()
            log_user_action(current_user.id, 'update_doctor', f'Updated doctor: {doctor.username}')
            
            return jsonify({'message': 'Doctor updated successfully'})
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500
    
    elif request.method == 'DELETE':
        doctor_id = request.args.get('id')
        
        try:
            doctor = User.query.get(doctor_id)
            if not doctor or doctor.role != 'doctor':
                return jsonify({'error': 'Doctor not found'}), 404
            
            # Soft delete - deactivate instead of hard delete
            doctor.is_active = False
            db.session.commit()
            
            log_user_action(current_user.id, 'deactivate_doctor', f'Deactivated doctor: {doctor.username}')
            
            return jsonify({'message': 'Doctor deactivated successfully'})
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500

@admin.route('/manage-patients')
@login_required
@admin_required
@log_user_action('admin_manage_patients')
def manage_patients():
    patients = User.query.filter_by(role='patient').order_by(User.created_at.desc()).all()
    return render_template('admin/manage-patients.html', patients=patients)

@admin.route('/api/patients', methods=['GET', 'POST', 'PUT', 'DELETE'])
@login_required
@admin_required
def api_patients():
    if request.method == 'GET':
        patients = User.query.filter_by(role='patient').all()
        return jsonify([{
            'id': p.id,
            'username': p.username,
            'email': p.email,
            'first_name': p.first_name,
            'last_name': p.last_name,
            'date_of_birth': p.date_of_birth.isoformat() if p.date_of_birth else None,
            'blood_group': p.blood_group,
            'emergency_contact': p.emergency_contact,
            'phone': p.phone,
            'is_active': p.is_active,
            'created_at': p.created_at.isoformat()
        } for p in patients])
    
    elif request.method == 'POST':
        data = request.get_json()
        
        try:
            patient = User(
                username=data['username'],
                email=data['email'],
                first_name=data['first_name'],
                last_name=data['last_name'],
                role='patient',
                phone=data.get('phone'),
                address=data.get('address'),
                blood_group=data.get('blood_group'),
                emergency_contact=data.get('emergency_contact')
            )
            
            if data.get('date_of_birth'):
                patient.date_of_birth = datetime.strptime(data['date_of_birth'], '%Y-%m-%d').date()
            
            patient.set_password(data['password'])
            
            db.session.add(patient)
            db.session.commit()
            
            log_user_action(current_user.id, 'create_patient', f'Created patient: {patient.username}')
            
            return jsonify({'message': 'Patient created successfully', 'id': patient.id}), 201
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500
    
    elif request.method == 'PUT':
        data = request.get_json()
        patient_id = data.get('id')
        
        try:
            patient = User.query.get(patient_id)
            if not patient or patient.role != 'patient':
                return jsonify({'error': 'Patient not found'}), 404
            
            patient.first_name = data.get('first_name', patient.first_name)
            patient.last_name = data.get('last_name', patient.last_name)
            patient.blood_group = data.get('blood_group', patient.blood_group)
            patient.emergency_contact = data.get('emergency_contact', patient.emergency_contact)
            patient.phone = data.get('phone', patient.phone)
            patient.address = data.get('address', patient.address)
            patient.is_active = data.get('is_active', patient.is_active)
            
            if data.get('date_of_birth'):
                patient.date_of_birth = datetime.strptime(data['date_of_birth'], '%Y-%m-%d').date()
            
            if data.get('password'):
                patient.set_password(data['password'])
            
            db.session.commit()
            log_user_action(current_user.id, 'update_patient', f'Updated patient: {patient.username}')
            
            return jsonify({'message': 'Patient updated successfully'})
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500
    
    elif request.method == 'DELETE':
        patient_id = request.args.get('id')
        
        try:
            patient = User.query.get(patient_id)
            if not patient or patient.role != 'patient':
                return jsonify({'error': 'Patient not found'}), 404
            
            patient.is_active = False
            db.session.commit()
            
            log_user_action(current_user.id, 'deactivate_patient', f'Deactivated patient: {patient.username}')
            
            return jsonify({'message': 'Patient deactivated successfully'})
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500

@admin.route('/appointments')
@login_required
@admin_required
@log_user_action('admin_appointments')
def appointments():
    appointments = Appointment.query.order_by(Appointment.appointment_date.desc()).all()
    return render_template('admin/appointments.html', appointments=appointments)

@admin.route('/api/appointments', methods=['GET', 'PUT'])
@login_required
@admin_required
def api_appointments():
    if request.method == 'GET':
        appointments = Appointment.query.order_by(Appointment.appointment_date.desc()).all()
        return jsonify([{
            'id': a.id,
            'patient_name': a.patient.get_full_name(),
            'doctor_name': a.doctor.get_full_name(),
            'appointment_date': a.appointment_date.isoformat(),
            'status': a.status,
            'reason': a.reason,
            'created_at': a.created_at.isoformat()
        } for a in appointments])
    
    elif request.method == 'PUT':
        data = request.get_json()
        appointment_id = data.get('id')
        
        try:
            appointment = Appointment.query.get(appointment_id)
            if not appointment:
                return jsonify({'error': 'Appointment not found'}), 404
            
            appointment.status = data.get('status', appointment.status)
            appointment.notes = data.get('notes', appointment.notes)
            
            db.session.commit()
            log_user_action(current_user.id, 'update_appointment', f'Updated appointment: {appointment_id}')
            
            return jsonify({'message': 'Appointment updated successfully'})
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500

@admin.route('/view-logs')
@login_required
@admin_required
@log_user_action('admin_view_logs')
def view_logs():
    page = request.args.get('page', 1, type=int)
    per_page = 50
    
    logs = SystemLog.query.order_by(SystemLog.created_at.desc()).paginate(
        page=page, per_page=per_page, error_out=False
    )
    
    return render_template('admin/view-logs.html', logs=logs)

@admin.route('/api/logs')
@login_required
@admin_required
def api_logs():
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 50, type=int)
    
    logs = SystemLog.query.order_by(SystemLog.created_at.desc()).paginate(
        page=page, per_page=per_page, error_out=False
    )
    
    return jsonify({
        'logs': [{
            'id': log.id,
            'user_name': log.user.get_full_name() if log.user else 'System',
            'action': log.action,
            'details': log.details,
            'ip_address': log.ip_address,
            'created_at': log.created_at.isoformat()
        } for log in logs.items],
        'total': logs.total,
        'pages': logs.pages,
        'current_page': logs.page
    })

@admin.route('/profile')
@login_required
@admin_required
@log_user_action('admin_profile')
def profile():
    return render_template('admin/profile.html', user=current_user) 