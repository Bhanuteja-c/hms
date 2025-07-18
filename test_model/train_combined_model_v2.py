import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.impute import SimpleImputer
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
from imblearn.over_sampling import SMOTE
import joblib

# Step 1: Load all datasets
framingham = pd.read_csv("framingham.csv")
diabetes = pd.read_csv("diabetes.csv")
hypertension = pd.read_csv("hypertension.csv")

# Step 2: Rename to unified schema
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
diabetes["gender"] = np.nan
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

# Step 3: Keep only selected columns
common_cols = ["age", "gender", "bp_sys", "bp_dia", "cholesterol", "glucose", "bmi", "heart_rate", "has_disease"]
framingham = framingham[common_cols]
diabetes = diabetes[common_cols]
hypertension = hypertension[common_cols]

# Step 4: Combine datasets
combined_df = pd.concat([framingham, diabetes, hypertension], ignore_index=True)
combined_df = combined_df.dropna(subset=["has_disease"])  # drop missing labels

# Step 5: Prepare features
X = combined_df.drop("has_disease", axis=1)
y = combined_df["has_disease"]

# Step 6: Impute missing values
imputer = SimpleImputer(strategy="mean")
X_imputed = imputer.fit_transform(X)

# Step 7: SMOTE oversampling
sm = SMOTE(random_state=42)
X_resampled, y_resampled = sm.fit_resample(X_imputed, y)

# Step 8: Train-test split
X_train, X_test, y_train, y_test = train_test_split(X_resampled, y_resampled, test_size=0.2, random_state=42)

# Step 9: Train Random Forest with class_weight balanced
model = RandomForestClassifier(n_estimators=100, class_weight='balanced', random_state=42)
model.fit(X_train, y_train)

# Step 10: Evaluate model at different thresholds
y_probs = model.predict_proba(X_test)[:, 1]  # probabilities for class 1
threshold = 0.4  # adjustable threshold
y_pred = (y_probs >= threshold).astype(int)

# Step 11: Metrics
print("✅ Accuracy:", accuracy_score(y_test, y_pred))
print("📊 Classification Report:\n", classification_report(y_test, y_pred))
print("🧾 Confusion Matrix:\n", confusion_matrix(y_test, y_pred))

# Step 12: Save model and imputer
joblib.dump(model, "combined_disease_model.pkl")
joblib.dump(imputer, "combined_disease_imputer.pkl")
print("✅ Model and imputer saved.")
