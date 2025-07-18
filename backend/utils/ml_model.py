import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import StandardScaler
from sklearn.metrics import accuracy_score, classification_report
import joblib
import os
from datetime import datetime

class DiseasePredictor:
    def __init__(self):
        self.models = {}
        self.scalers = {}
        self.model_path = 'ml/models/'
        self.data_path = 'ml/'
        
        # Create models directory if it doesn't exist
        os.makedirs(self.model_path, exist_ok=True)
        
        # Initialize models
        self._load_or_train_models()
    
    def _load_or_train_models(self):
        """Load existing models or train new ones"""
        model_files = {
            'diabetes': 'diabetes_model.pkl',
            'heart': 'heart_model.pkl',
            'hypertension': 'hypertension_model.pkl'
        }
        
        scaler_files = {
            'diabetes': 'diabetes_scaler.pkl',
            'heart': 'heart_scaler.pkl',
            'hypertension': 'hypertension_scaler.pkl'
        }
        
        for disease_type in model_files.keys():
            model_file = os.path.join(self.model_path, model_files[disease_type])
            scaler_file = os.path.join(self.model_path, scaler_files[disease_type])
            
            if os.path.exists(model_file) and os.path.exists(scaler_file):
                # Load existing model
                self.models[disease_type] = joblib.load(model_file)
                self.scalers[disease_type] = joblib.load(scaler_file)
                print(f"Loaded existing {disease_type} model")
            else:
                # Train new model
                self._train_model(disease_type)
    
    def _train_model(self, disease_type):
        """Train model for specific disease type"""
        try:
            # Load dataset
            data_file = os.path.join(self.data_path, f'{disease_type}.csv')
            df = pd.read_csv(data_file)
            
            if disease_type == 'diabetes':
                # Pima Diabetes dataset
                X = df.drop('Outcome', axis=1)
                y = df['Outcome']
                feature_names = ['Pregnancies', 'Glucose', 'BloodPressure', 'SkinThickness', 
                               'Insulin', 'BMI', 'DiabetesPedigreeFunction', 'Age']
                
            elif disease_type == 'heart':
                # Framingham Heart dataset
                X = df.drop('TenYearCHD', axis=1)
                y = df['TenYearCHD']
                feature_names = ['male', 'age', 'education', 'currentSmoker', 'cigsPerDay',
                               'BPMeds', 'prevalentStroke', 'prevalentHyp', 'diabetes',
                               'totChol', 'sysBP', 'diaBP', 'BMI', 'heartRate', 'glucose']
                
            elif disease_type == 'hypertension':
                # Hypertension dataset
                X = df.drop('target', axis=1)
                y = df['target']
                feature_names = ['age', 'sex', 'cp', 'trestbps', 'chol', 'fbs', 'restecg',
                               'thalach', 'exang', 'oldpeak', 'slope', 'ca', 'thal']
            
            # Handle missing values
            X = X.fillna(X.mean())
            
            # Split data
            X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
            
            # Scale features
            scaler = StandardScaler()
            X_train_scaled = scaler.fit_transform(X_train)
            X_test_scaled = scaler.transform(X_test)
            
            # Train model
            model = RandomForestClassifier(n_estimators=100, random_state=42)
            model.fit(X_train_scaled, y_train)
            
            # Evaluate model
            y_pred = model.predict(X_test_scaled)
            accuracy = accuracy_score(y_test, y_pred)
            print(f"{disease_type.capitalize()} model accuracy: {accuracy:.3f}")
            
            # Save model and scaler
            model_file = os.path.join(self.model_path, f'{disease_type}_model.pkl')
            scaler_file = os.path.join(self.model_path, f'{disease_type}_scaler.pkl')
            
            joblib.dump(model, model_file)
            joblib.dump(scaler, scaler_file)
            
            self.models[disease_type] = model
            self.scalers[disease_type] = scaler
            
        except Exception as e:
            print(f"Error training {disease_type} model: {e}")
    
    def predict_diabetes(self, data):
        """Predict diabetes risk"""
        try:
            features = np.array([
                data.get('pregnancies', 0),
                data.get('glucose', 0),
                data.get('blood_pressure', 0),
                data.get('skin_thickness', 0),
                data.get('insulin', 0),
                data.get('bmi', 0),
                data.get('diabetes_pedigree', 0),
                data.get('age', 0)
            ]).reshape(1, -1)
            
            if 'diabetes' not in self.models:
                return {'error': 'Diabetes model not available'}
            
            # Scale features
            features_scaled = self.scalers['diabetes'].transform(features)
            
            # Make prediction
            prediction = self.models['diabetes'].predict(features_scaled)[0]
            probability = self.models['diabetes'].predict_proba(features_scaled)[0]
            
            return {
                'prediction': 'High Risk' if prediction == 1 else 'Low Risk',
                'probability': float(probability[1] if prediction == 1 else probability[0]),
                'confidence': float(max(probability))
            }
            
        except Exception as e:
            return {'error': f'Prediction error: {str(e)}'}
    
    def predict_heart_disease(self, data):
        """Predict heart disease risk"""
        try:
            features = np.array([
                data.get('male', 0),
                data.get('age', 0),
                data.get('education', 0),
                data.get('current_smoker', 0),
                data.get('cigs_per_day', 0),
                data.get('bp_meds', 0),
                data.get('prevalent_stroke', 0),
                data.get('prevalent_hyp', 0),
                data.get('diabetes', 0),
                data.get('tot_chol', 0),
                data.get('sys_bp', 0),
                data.get('dia_bp', 0),
                data.get('bmi', 0),
                data.get('heart_rate', 0),
                data.get('glucose', 0)
            ]).reshape(1, -1)
            
            if 'heart' not in self.models:
                return {'error': 'Heart disease model not available'}
            
            # Scale features
            features_scaled = self.scalers['heart'].transform(features)
            
            # Make prediction
            prediction = self.models['heart'].predict(features_scaled)[0]
            probability = self.models['heart'].predict_proba(features_scaled)[0]
            
            return {
                'prediction': 'High Risk' if prediction == 1 else 'Low Risk',
                'probability': float(probability[1] if prediction == 1 else probability[0]),
                'confidence': float(max(probability))
            }
            
        except Exception as e:
            return {'error': f'Prediction error: {str(e)}'}
    
    def predict_hypertension(self, data):
        """Predict hypertension risk"""
        try:
            features = np.array([
                data.get('age', 0),
                data.get('sex', 0),
                data.get('cp', 0),
                data.get('trestbps', 0),
                data.get('chol', 0),
                data.get('fbs', 0),
                data.get('restecg', 0),
                data.get('thalach', 0),
                data.get('exang', 0),
                data.get('oldpeak', 0),
                data.get('slope', 0),
                data.get('ca', 0),
                data.get('thal', 0)
            ]).reshape(1, -1)
            
            if 'hypertension' not in self.models:
                return {'error': 'Hypertension model not available'}
            
            # Scale features
            features_scaled = self.scalers['hypertension'].transform(features)
            
            # Make prediction
            prediction = self.models['hypertension'].predict(features_scaled)[0]
            probability = self.models['hypertension'].predict_proba(features_scaled)[0]
            
            return {
                'prediction': 'High Risk' if prediction == 1 else 'Low Risk',
                'probability': float(probability[1] if prediction == 1 else probability[0]),
                'confidence': float(max(probability))
            }
            
        except Exception as e:
            return {'error': f'Prediction error: {str(e)}'}
    
    def predict(self, disease_type, data):
        """Generic prediction method"""
        if disease_type == 'diabetes':
            return self.predict_diabetes(data)
        elif disease_type == 'heart':
            return self.predict_heart_disease(data)
        elif disease_type == 'hypertension':
            return self.predict_hypertension(data)
        else:
            return {'error': f'Unknown disease type: {disease_type}'}

# Global instance
predictor = DiseasePredictor() 