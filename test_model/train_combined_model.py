import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn.impute import SimpleImputer
from sklearn.metrics import classification_report, accuracy_score
import math
import joblib

# Load datasets
framingham = pd.read_csv("framingham.csv")
diabetes = pd.read_csv("diabetes.csv")
hypertension = pd.read_csv("hypertension.csv")

# Clean and rename columns to unified schema
framingham = framingham.rename(columns={
    "male": "gender",
    "sysBP": "bp_sys",
    "diaBP": "bp_dia",
    "totChol": "cholesterol",
    "glucose": "glucose",
    "BMI": "bmi",
    "heartRate": "heart_rate"
})
framingham["has_disease"] = framingham["TenYearCHD"]

diabetes = diabetes.rename(columns={
    "Age": "age",
    "Glucose": "glucose",
    "BloodPressure": "bp_dia",
    "BMI": "bmi"
})
diabetes["gender"] = np.nan  # gender not in dataset
diabetes["bp_sys"] = np.nan
diabetes["cholesterol"] = np.nan
diabetes["heart_rate"] = np.nan
diabetes["has_disease"] = diabetes["Outcome"]

hypertension = hypertension.rename(columns={
    "sex": "gender",
    "trestbps": "bp_sys",
    "chol": "cholesterol",
    "thalach": "heart_rate"
})
hypertension["bp_dia"] = np.nan
hypertension["glucose"] = np.nan
hypertension["bmi"] = np.nan
hypertension["has_disease"] = hypertension["target"]

# Keep only common + important columns
common_cols = ["age", "gender", "bp_sys", "bp_dia", "cholesterol", "glucose", "bmi", "heart_rate", "has_disease"]
framingham = framingham[common_cols]
diabetes = diabetes[common_cols]
hypertension = hypertension[common_cols]

# Combine all into one dataset
combined_df = pd.concat([framingham, diabetes, hypertension], ignore_index=True)

# Drop rows with missing target
combined_df = combined_df.dropna(subset=["has_disease"])

# Separate features and label
X = combined_df.drop("has_disease", axis=1)
y = combined_df["has_disease"]

# Impute missing values
imputer = SimpleImputer(strategy="mean")
X_imputed = imputer.fit_transform(X)

# Train-test split
X_train, X_test, y_train, y_test = train_test_split(X_imputed, y, test_size=0.2, random_state=42)

# Train model
model = LogisticRegression(max_iter=1000)
model.fit(X_train, y_train)

# Evaluate
y_pred = model.predict(X_test)
print("✅ Accuracy:", accuracy_score(y_test, y_pred))
print("📊 Classification Report:\n", classification_report(y_test, y_pred))

# Save model and imputer
joblib.dump(model, "combined_disease_model.pkl")
joblib.dump(imputer, "combined_disease_imputer.pkl")
print("✅ Model & imputer saved.")

