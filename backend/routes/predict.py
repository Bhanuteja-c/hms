from flask import Blueprint, request, jsonify, render_template
from flask_login import login_required, current_user
from utils.ml_model import predictor
from models.db_models import DiseasePrediction, db
from utils.security import log_user_action

predict = Blueprint('predict', __name__, url_prefix='/predict')

@predict.route('/diabetes', methods=['GET', 'POST'])
@login_required
@log_user_action('diabetes_prediction')
def diabetes():
    if request.method == 'GET':
        return render_template('predict/diabetes.html')
    
    elif request.method == 'POST':
        data = request.get_json()
        
        try:
            # Make prediction
            prediction_result = predictor.predict_diabetes(data)
            
            if 'error' in prediction_result:
                return jsonify({'error': prediction_result['error']}), 500
            
            # Save prediction if user is logged in
            if current_user.is_authenticated:
                prediction_record = DiseasePrediction(
                    patient_id=current_user.id,
                    disease_type='diabetes',
                    prediction_result=prediction_result['prediction'].lower().replace(' ', '_'),
                    confidence_score=prediction_result['confidence'],
                    input_data=data
                )
                
                db.session.add(prediction_record)
                db.session.commit()
                
                log_user_action(current_user.id, 'diabetes_prediction', f'Diabetes prediction: {prediction_result["prediction"]}')
            
            return jsonify({
                'message': 'Prediction completed successfully',
                'prediction': prediction_result
            })
            
        except Exception as e:
            if current_user.is_authenticated:
                db.session.rollback()
            return jsonify({'error': str(e)}), 500

@predict.route('/heart', methods=['GET', 'POST'])
@login_required
@log_user_action('heart_prediction')
def heart():
    if request.method == 'GET':
        return render_template('predict/heart.html')
    
    elif request.method == 'POST':
        data = request.get_json()
        
        try:
            # Make prediction
            prediction_result = predictor.predict_heart_disease(data)
            
            if 'error' in prediction_result:
                return jsonify({'error': prediction_result['error']}), 500
            
            # Save prediction if user is logged in
            if current_user.is_authenticated:
                prediction_record = DiseasePrediction(
                    patient_id=current_user.id,
                    disease_type='heart',
                    prediction_result=prediction_result['prediction'].lower().replace(' ', '_'),
                    confidence_score=prediction_result['confidence'],
                    input_data=data
                )
                
                db.session.add(prediction_record)
                db.session.commit()
                
                log_user_action(current_user.id, 'heart_prediction', f'Heart disease prediction: {prediction_result["prediction"]}')
            
            return jsonify({
                'message': 'Prediction completed successfully',
                'prediction': prediction_result
            })
            
        except Exception as e:
            if current_user.is_authenticated:
                db.session.rollback()
            return jsonify({'error': str(e)}), 500

@predict.route('/hypertension', methods=['GET', 'POST'])
@login_required
@log_user_action('hypertension_prediction')
def hypertension():
    if request.method == 'GET':
        return render_template('predict/hypertension.html')
    
    elif request.method == 'POST':
        data = request.get_json()
        
        try:
            # Make prediction
            prediction_result = predictor.predict_hypertension(data)
            
            if 'error' in prediction_result:
                return jsonify({'error': prediction_result['error']}), 500
            
            # Save prediction if user is logged in
            if current_user.is_authenticated:
                prediction_record = DiseasePrediction(
                    patient_id=current_user.id,
                    disease_type='hypertension',
                    prediction_result=prediction_result['prediction'].lower().replace(' ', '_'),
                    confidence_score=prediction_result['confidence'],
                    input_data=data
                )
                
                db.session.add(prediction_record)
                db.session.commit()
                
                log_user_action(current_user.id, 'hypertension_prediction', f'Hypertension prediction: {prediction_result["prediction"]}')
            
            return jsonify({
                'message': 'Prediction completed successfully',
                'prediction': prediction_result
            })
            
        except Exception as e:
            if current_user.is_authenticated:
                db.session.rollback()
            return jsonify({'error': str(e)}), 500

@predict.route('/api/history')
@login_required
def prediction_history():
    """Get user's prediction history"""
    predictions = DiseasePrediction.query.filter_by(patient_id=current_user.id).order_by(DiseasePrediction.created_at.desc()).all()
    
    return jsonify([{
        'id': p.id,
        'disease_type': p.disease_type,
        'prediction_result': p.prediction_result,
        'confidence_score': p.confidence_score,
        'created_at': p.created_at.isoformat()
    } for p in predictions]) 