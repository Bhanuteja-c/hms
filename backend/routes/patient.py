from flask import Blueprint, request, jsonify, render_template
from flask_login import login_required, current_user
from models.db_models import User, Appointment, MedicalRecord, DiseasePrediction, db
from utils.security import patient_required, log_user_action
from datetime import datetime, timedelta
from sqlalchemy import func

patient = Blueprint('patient', __name__, url_prefix='/patient')

@patient.route('/dashboard')
@login_required
@patient_required
@log_user_action('patient_dashboard_access')
def dashboard():
    # Get patient's statistics
    total_appointments = Appointment.query.filter_by(patient_id=current_user.id).count()
    upcoming_appointments = Appointment.query.filter(
        Appointment.patient_id == current_user.id,
        Appointment.appointment_date >= datetime.now(),
        Appointment.status == 'scheduled'
    ).order_by(Appointment.appointment_date).limit(5).all()
    
    recent_medical_records = MedicalRecord.query.filter_by(
        patient_id=current_user.id
    ).order_by(MedicalRecord.created_at.desc()).limit(3).all()
    
    recent_predictions = DiseasePrediction.query.filter_by(
        patient_id=current_user.id
    ).order_by(DiseasePrediction.created_at.desc()).limit(3).all()
    
    return render_template('patient/dashboard.html',
                         total_appointments=total_appointments,
                         upcoming_appointments=upcoming_appointments,
                         recent_medical_records=recent_medical_records,
                         recent_predictions=recent_predictions)

@patient.route('/book-appointment', methods=['GET', 'POST'])
@login_required
@patient_required
@log_user_action('patient_book_appointment')
def book_appointment():
    if request.method == 'GET':
        doctors = User.query.filter_by(role='doctor', is_active=True).order_by(User.first_name).all()
        return render_template('patient/book-appointment.html', doctors=doctors)
    
    elif request.method == 'POST':
        data = request.get_json()
        
        try:
            # Validate required fields
            if not data.get('doctor_id') or not data.get('appointment_date') or not data.get('reason'):
                return jsonify({'error': 'Doctor, appointment date, and reason are required'}), 400
            
            # Parse appointment date
            appointment_date = datetime.fromisoformat(data['appointment_date'].replace('Z', '+00:00'))
            
            # Check if doctor exists and is active
            doctor = User.query.filter_by(id=data['doctor_id'], role='doctor', is_active=True).first()
            if not doctor:
                return jsonify({'error': 'Doctor not found or inactive'}), 404
            
            # Check if appointment time is in the future
            if appointment_date <= datetime.now():
                return jsonify({'error': 'Appointment date must be in the future'}), 400
            
            # Check for conflicting appointments
            conflicting_appointment = Appointment.query.filter(
                Appointment.doctor_id == data['doctor_id'],
                Appointment.appointment_date == appointment_date,
                Appointment.status.in_(['scheduled', 'confirmed'])
            ).first()
            
            if conflicting_appointment:
                return jsonify({'error': 'Doctor is not available at this time'}), 409
            
            # Create appointment
            appointment = Appointment(
                patient_id=current_user.id,
                doctor_id=data['doctor_id'],
                appointment_date=appointment_date,
                reason=data['reason'],
                status='scheduled'
            )
            
            db.session.add(appointment)
            db.session.commit()
            
            log_user_action(current_user.id, 'book_appointment', f'Booked appointment with Dr. {doctor.get_full_name()}')
            
            return jsonify({
                'message': 'Appointment booked successfully',
                'appointment_id': appointment.id
            }), 201
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500

@patient.route('/api/appointments')
@login_required
@patient_required
def api_appointments():
    appointments = Appointment.query.filter_by(patient_id=current_user.id).order_by(Appointment.appointment_date.desc()).all()
    
    return jsonify([{
        'id': a.id,
        'doctor_name': a.doctor.get_full_name(),
        'doctor_specialization': a.doctor.specialization,
        'appointment_date': a.appointment_date.isoformat(),
        'status': a.status,
        'reason': a.reason,
        'notes': a.notes,
        'created_at': a.created_at.isoformat()
    } for a in appointments])

@patient.route('/view-reports')
@login_required
@patient_required
@log_user_action('patient_view_reports')
def view_reports():
    medical_records = MedicalRecord.query.filter_by(patient_id=current_user.id).order_by(MedicalRecord.created_at.desc()).all()
    return render_template('patient/view-reports.html', medical_records=medical_records)

@patient.route('/api/medical-records')
@login_required
@patient_required
def api_medical_records():
    medical_records = MedicalRecord.query.filter_by(patient_id=current_user.id).order_by(MedicalRecord.created_at.desc()).all()
    
    return jsonify([{
        'id': m.id,
        'doctor_name': m.doctor.get_full_name(),
        'appointment_date': m.appointment.appointment_date.isoformat() if m.appointment else None,
        'diagnosis': m.diagnosis,
        'prescription': m.prescription,
        'symptoms': m.symptoms,
        'treatment_plan': m.treatment_plan,
        'created_at': m.created_at.isoformat()
    } for m in medical_records])

@patient.route('/my-prescriptions')
@login_required
@patient_required
@log_user_action('patient_prescriptions')
def my_prescriptions():
    medical_records = MedicalRecord.query.filter_by(patient_id=current_user.id).order_by(MedicalRecord.created_at.desc()).all()
    return render_template('patient/my-prescriptions.html', medical_records=medical_records)

@patient.route('/predict-risk', methods=['GET', 'POST'])
@login_required
@patient_required
@log_user_action('patient_predict_risk')
def predict_risk():
    if request.method == 'GET':
        return render_template('patient/predict-risk.html')
    
    elif request.method == 'POST':
        data = request.get_json()
        disease_type = data.get('disease_type')
        
        if not disease_type:
            return jsonify({'error': 'Disease type is required'}), 400
        
        try:
            from utils.ml_model import predictor
            from models.db_models import DiseasePrediction
            
            # Make prediction
            prediction_result = predictor.predict(disease_type, data)
            
            if 'error' in prediction_result:
                return jsonify({'error': prediction_result['error']}), 500
            
            # Save prediction to database
            prediction_record = DiseasePrediction(
                patient_id=current_user.id,
                disease_type=disease_type,
                prediction_result=prediction_result['prediction'].lower().replace(' ', '_'),
                confidence_score=prediction_result['confidence'],
                input_data=data
            )
            
            db.session.add(prediction_record)
            db.session.commit()
            
            log_user_action(current_user.id, 'self_prediction', f'Self-predicted {disease_type} risk')
            
            return jsonify({
                'message': 'Prediction completed successfully',
                'prediction': prediction_result
            })
            
        except Exception as e:
            db.session.rollback()
            return jsonify({'error': str(e)}), 500

@patient.route('/api/predictions')
@login_required
@patient_required
def api_predictions():
    predictions = DiseasePrediction.query.filter_by(patient_id=current_user.id).order_by(DiseasePrediction.created_at.desc()).all()
    
    return jsonify([{
        'id': p.id,
        'disease_type': p.disease_type,
        'prediction_result': p.prediction_result,
        'confidence_score': p.confidence_score,
        'created_at': p.created_at.isoformat()
    } for p in predictions])

@patient.route('/profile')
@login_required
@patient_required
@log_user_action('patient_profile')
def profile():
    return render_template('patient/profile.html', user=current_user) 