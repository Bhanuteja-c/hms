from flask import Blueprint, request, jsonify, render_template
from flask_login import login_required, current_user
from models.db_models import User, Appointment, MedicalRecord, db
from utils.security import doctor_required, log_user_action
from datetime import datetime, timedelta
from sqlalchemy import func

doctor = Blueprint('doctor', __name__, url_prefix='/doctor')

@doctor.route('/dashboard')
@login_required
@doctor_required
@log_user_action('doctor_dashboard_access')
def dashboard():
    # Get doctor's statistics
    total_appointments = Appointment.query.filter_by(doctor_id=current_user.id).count()
    today_appointments = Appointment.query.filter(
        Appointment.doctor_id == current_user.id,
        func.date(Appointment.appointment_date) == datetime.now().date()
    ).count()
    
    upcoming_appointments = Appointment.query.filter(
        Appointment.doctor_id == current_user.id,
        Appointment.appointment_date >= datetime.now(),
        Appointment.status == 'scheduled'
    ).order_by(Appointment.appointment_date).limit(5).all()
    
    recent_patients = db.session.query(User).join(Appointment).filter(
        Appointment.doctor_id == current_user.id
    ).distinct().order_by(Appointment.created_at.desc()).limit(5).all()
    
    return render_template('doctor/dashboard.html',
                         total_appointments=total_appointments,
                         today_appointments=today_appointments,
                         upcoming_appointments=upcoming_appointments,
                         recent_patients=recent_patients)

@doctor.route('/appointments')
@login_required
@doctor_required
@log_user_action('doctor_appointments')
def appointments():
    appointments = Appointment.query.filter_by(doctor_id=current_user.id).order_by(Appointment.appointment_date.desc()).all()
    return render_template('doctor/appointments.html', appointments=appointments)

@doctor.route('/api/appointments', methods=['GET', 'PUT'])
@login_required
@doctor_required
def api_appointments():
    if request.method == 'GET':
        appointments = Appointment.query.filter_by(doctor_id=current_user.id).order_by(Appointment.appointment_date.desc()).all()
        return jsonify([{
            'id': a.id,
            'patient_name': a.patient.get_full_name(),
            'patient_id': a.patient_id,
            'appointment_date': a.appointment_date.isoformat(),
            'status': a.status,
            'reason': a.reason,
            'notes': a.notes,
            'created_at': a.created_at.isoformat()
        } for a in appointments])
    
    elif request.method == 'PUT':
        data = request.get_json()
        appointment_id = data.get('id')
        
        try:
            appointment = Appointment.query.filter_by(
                id=appointment_id, 
                doctor_id=current_user.id
            ).first()
            
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

@doctor.route('/view-patients')
@login_required
@doctor_required
@log_user_action('doctor_view_patients')
def view_patients():
    patients = db.session.query(User).join(Appointment).filter(
        Appointment.doctor_id == current_user.id
    ).distinct().order_by(User.first_name).all()
    return render_template('doctor/view-patients.html', patients=patients)

@doctor.route('/api/patients')
@login_required
@doctor_required
def api_patients():
    patients = db.session.query(User).join(Appointment).filter(
        Appointment.doctor_id == current_user.id
    ).distinct().order_by(User.first_name).all()
    
    return jsonify([{
        'id': p.id,
        'name': p.get_full_name(),
        'email': p.email,
        'phone': p.phone,
        'date_of_birth': p.date_of_birth.isoformat() if p.date_of_birth else None,
        'blood_group': p.blood_group,
        'emergency_contact': p.emergency_contact,
        'last_appointment': Appointment.query.filter_by(
            doctor_id=current_user.id, 
            patient_id=p.id
        ).order_by(Appointment.appointment_date.desc()).first().appointment_date.isoformat() if Appointment.query.filter_by(doctor_id=current_user.id, patient_id=p.id).first() else None
    } for p in patients])

@doctor.route('/diagnose/<int:appointment_id>', methods=['GET', 'POST'])
@login_required
@doctor_required
@log_user_action('doctor_diagnose')
def diagnose(appointment_id):
    appointment = Appointment.query.filter_by(
        id=appointment_id, 
        doctor_id=current_user.id
    ).first()
    
    if not appointment:
        return jsonify({'error': 'Appointment not found'}), 404
    
    if request.method == 'GET':
        medical_records = MedicalRecord.query.filter_by(
            patient_id=appointment.patient_id
        ).order_by(MedicalRecord.created_at.desc()).all()
        
        return render_template('doctor/diagnose.html', 
                             appointment=appointment, 
                             medical_records=medical_records)
    
    elif request.method == 'POST':
        data = request.get_json()
        
        try:
            # Create medical record
            medical_record = MedicalRecord(
                patient_id=appointment.patient_id,
                doctor_id=current_user.id,
                appointment_id=appointment.id,
                diagnosis=data.get('diagnosis'),
                prescription=data.get('prescription'),
                symptoms=data.get('symptoms'),
                treatment_plan=data.get('treatment_plan')
            )
            
            db.session.add(medical_record)
            
            # Update appointment status
            appointment.status = 'completed'
            appointment.notes = data.get('notes', appointment.notes)
            
            db.session.commit()
            log_user_action(current_user.id, 'create_diagnosis', f'Created diagnosis for patient: {appointment.patient.get_full_name()}')
            
            return jsonify({'message': 'Diagnosis recorded successfully'})
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500

@doctor.route('/prediction-form')
@login_required
@doctor_required
@log_user_action('doctor_prediction_form')
def prediction_form():
    return render_template('doctor/prediction-form.html')

@doctor.route('/api/predict', methods=['POST'])
@login_required
@doctor_required
def predict_disease():
    data = request.get_json()
    disease_type = data.get('disease_type')
    patient_id = data.get('patient_id')
    
    if not disease_type or not patient_id:
        return jsonify({'error': 'Disease type and patient ID are required'}), 400
    
    try:
        from utils.ml_model import predictor
        from models.db_models import DiseasePrediction
        
        # Make prediction
        prediction_result = predictor.predict(disease_type, data)
        
        if 'error' in prediction_result:
            return jsonify({'error': prediction_result['error']}), 500
        
        # Save prediction to database
        prediction_record = DiseasePrediction(
            patient_id=patient_id,
            disease_type=disease_type,
            prediction_result=prediction_result['prediction'].lower().replace(' ', '_'),
            confidence_score=prediction_result['confidence'],
            input_data=data
        )
        
        db.session.add(prediction_record)
        db.session.commit()
        
        log_user_action(current_user.id, 'disease_prediction', f'Predicted {disease_type} for patient: {patient_id}')
        
        return jsonify({
            'message': 'Prediction completed successfully',
            'prediction': prediction_result
        })
        
    except Exception as e:
        db.session.rollback()
        return jsonify({'error': str(e)}), 500

@doctor.route('/profile')
@login_required
@doctor_required
@log_user_action('doctor_profile')
def profile():
    return render_template('doctor/profile.html', user=current_user) 