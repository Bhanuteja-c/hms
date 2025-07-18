import pandas as pd
import numpy as np
import joblib
import matplotlib.pyplot as plt
from sklearn.impute import SimpleImputer
from sklearn.model_selection import train_test_split, GridSearchCV
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
from xgboost import XGBClassifier, plot_importance
from imblearn.over_sampling import SMOTE

# Load datasets
framingham = pd.read_csv("framingham.csv")
diabetes = pd.read_csv("diabetes.csv")
hypertension = pd.read_csv("hypertension.csv")

# Rename columns to match schema
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

# Common features
features = ["age", "gender", "bp_sys", "bp_dia", "cholesterol", "glucose", "bmi", "heart_rate", "has_disease"]
df = pd.concat([
    framingham[features],
    diabetes[features],
    hypertension[features]
], ignore_index=True)

# Drop rows with missing target
df = df.dropna(subset=["has_disease"])

# Separate features and labels
X = df.drop("has_disease", axis=1)
y = df["has_disease"]

# Impute missing values
imputer = SimpleImputer(strategy="mean")
X_imputed = imputer.fit_transform(X)

# Apply SMOTE
sm = SMOTE(random_state=42)
X_resampled, y_resampled = sm.fit_resample(X_imputed, y)

# Train-test split
X_train, X_test, y_train, y_test = train_test_split(X_resampled, y_resampled, test_size=0.2, random_state=42)

# XGBoost classifier
model = XGBClassifier(use_label_encoder=False, eval_metric='logloss', random_state=42)

# Hyperparameter grid
param_grid = {
    'n_estimators': [100, 150],
    'max_depth': [3, 5, 7],
    'learning_rate': [0.05, 0.1],
    'subsample': [0.8, 1],
}

# Grid search
grid = GridSearchCV(model, param_grid, scoring='f1', cv=3, verbose=1, n_jobs=-1)
grid.fit(X_train, y_train)

best_model = grid.best_estimator_

# Evaluate
y_pred = best_model.predict(X_test)
print("✅ Accuracy:", accuracy_score(y_test, y_pred))
print("📊 Classification Report:\n", classification_report(y_test, y_pred))
print("🧾 Confusion Matrix:\n", confusion_matrix(y_test, y_pred))

# Plot feature importance
plt.figure(figsize=(10, 6))
plot_importance(best_model)
plt.title("Feature Importance (XGBoost)")
plt.tight_layout()
plt.show()

# Save model and imputer
joblib.dump(best_model, "xgb_combined_disease_model.pkl")
joblib.dump(imputer, "xgb_combined_disease_imputer.pkl")
print("✅ XGBoost model and imputer saved.")
