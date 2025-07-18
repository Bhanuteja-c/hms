from flask import Flask, request, jsonify
from flask_cors import CORS
import numpy as np
import joblib  # for model saving/loading

app = Flask(__name__)
CORS(app)  # Allow requests from frontend

# Load your pre-trained model
model = joblib.load("heart_model.pkl")  # Save model after training

@app.route("/predict", methods=["POST"])
def predict():
    data = request.json
    features = np.array([
        data["age"],
        data["gender"],
        data["chest_pain"],
        data["bp"],
        data["cholesterol"],
        data["fasting_sugar"],
        data["max_hr"]
    ]).reshape(1, -1)
    
    result = model.predict(features)[0]
    return jsonify({"risk": "High" if result == 1 else "Low"})

if __name__ == "__main__":
    app.run(debug=True)
