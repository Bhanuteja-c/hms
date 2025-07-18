from flask import Flask, request, jsonify, render_template
import joblib
import numpy as np

app = Flask(__name__)

# Load model and imputer
model = joblib.load("xgb_combined_disease_model.pkl")
imputer = joblib.load("xgb_combined_disease_imputer.pkl")

@app.route("/")
def home():
    return render_template("index.html")

@app.route("/predict", methods=["POST"])
def predict():
    data = request.get_json()

    # Extract values in correct order
    input_data = [
        data.get("age"),
        data.get("gender"),
        data.get("bp_sys"),
        data.get("bp_dia"),
        data.get("cholesterol"),
        data.get("glucose"),
        data.get("bmi"),
        data.get("heart_rate")
    ]

    # Impute and reshape
    X = imputer.transform([input_data])
    prediction = model.predict(X)[0]
    prob = model.predict_proba(X)[0][1]  # Probability of class 1

    return jsonify({
    "risk": "High" if prediction == 1 else "Low",
    "confidence": f"{prob:.2f}",
    "class0": float(model.predict_proba(X)[0][0]),  # No disease
    "class1": float(model.predict_proba(X)[0][1])   # Disease
})


if __name__ == "__main__":
    app.run(debug=True)
