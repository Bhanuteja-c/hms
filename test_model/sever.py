from flask import Flask, request, jsonify
from flask_cors import CORS
import numpy as np
import joblib
import logging

app = Flask(__name__)
CORS(app)

# Setup logging
logging.basicConfig(level=logging.INFO)

# Load the pre-trained model
try:
    model = joblib.load("heart_model.pkl")
    logging.info("✅ Model loaded successfully.")
except Exception as e:
    logging.error("❌ Failed to load model: %s", e)
    model = None

@app.route("/health", methods=["GET"])
def health_check():
    return jsonify({"status": "ok", "model_loaded": model is not None})

@app.route("/predict", methods=["POST"])
def predict():
    if not model:
        return jsonify({"error": "Model not loaded"}), 500

    data = request.get_json()
    
    required_fields = ["age", "gender", "chest_pain", "bp", "cholesterol", "fasting_sugar", "max_hr"]
    missing = [field for field in required_fields if field not in data]
    if missing:
        return jsonify({"error": f"Missing fields: {', '.join(missing)}"}), 400

    try:
        features = np.array([
            data["age"],
            data["gender"],
            data["chest_pain"],
            data["bp"],
            data["cholesterol"],
            data["fasting_sugar"],
            data["max_hr"]
        ]).reshape(1, -1)

        prediction = model.predict(features)[0]
        return jsonify({"risk": "High" if prediction == 1 else "Low"})

    except Exception as e:
        logging.exception("Prediction failed:")
        return jsonify({"error": "Prediction failed", "details": str(e)}), 500

if __name__ == "__main__":
    app.run(debug=True)