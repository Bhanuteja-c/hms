import pandas as pd
import numpy as np
import joblib
import logging
import matplotlib.pyplot as plt
from sklearn.impute import SimpleImputer
from sklearn.model_selection import train_test_split, GridSearchCV
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
from xgboost import XGBClassifier, plot_importance
from imblearn.over_sampling import SMOTE

# Setup logging
logging.basicConfig(level=logging.INFO, format="%(levelname)s: %(message)s")

# Load datasets
df_framingham = pd.read_csv("framingham.csv")
df_diabetes = pd.read_csv("diabetes.csv")
df_hypertension = pd.read_csv("hypertension.csv")

# --- Normalize column names ---
df_framingham = df_framingham.rename(columns={
    "male": "gender",
    "sysBP": "bp_sys",
    "diaBP": "bp_dia",
    "totChol": "cholesterol",
    "glucose": "glucose",
    "BMI": "bmi",
    "heartRate": "heart_rate",
    "TenYearCHD": "has_disease"
})

df_diabetes = df_diabetes.rename(columns={
    "Age": "age",
    "Glucose": "glucose",
    "BloodPressure": "bp_dia",
    "BMI": "bmi",
    "Outcome": "has_disease"
})
df_diabetes["gender"] = np.nan
df_diabetes["bp_sys"] = np.nan
df_diabetes["cholesterol"] = np.nan
df_diabetes["heart_rate"] = np.nan

df_hypertension = df_hypertension.rename(columns={
    "sex": "gender",
    "trestbps": "bp_sys",
    "chol": "cholesterol",
    "thalach": "heart_rate",
    "target": "has_disease"
})
df_hypertension["bp_dia"] = np.nan
df_hypertension["glucose"] = np.nan
df_hypertension["bmi"] = np.nan

# --- Merge datasets ---
features = ["age", "gender", "bp_sys", "bp_dia", "cholesterol", "glucose", "bmi", "heart_rate", "has_disease"]
df_combined = pd.concat([
    df_framingham[features],
    df_diabetes[features],
    df_hypertension[features]
], ignore_index=True)

df_combined.dropna(subset=["has_disease"], inplace=True)

# --- Preprocessing ---
X = df_combined.drop("has_disease", axis=1)
y = df_combined["has_disease"]

imputer = SimpleImputer(strategy="mean")
X_filled = imputer.fit_transform(X)

smote = SMOTE(random_state=42)
X_resampled, y_resampled = smote.fit_resample(X_filled, y)

X_train, X_test, y_train, y_test = train_test_split(
    X_resampled, y_resampled, test_size=0.2, random_state=42
)

# --- Model and hyperparameter tuning ---
xgb = XGBClassifier(use_label_encoder=False, eval_metric='logloss', random_state=42)

param_grid = {
    "n_estimators": [100, 150],
    "max_depth": [3, 5, 7],
    "learning_rate": [0.05, 0.1],
    "subsample": [0.8, 1.0],
}

grid_search = GridSearchCV(xgb, param_grid, scoring="f1", cv=3, n_jobs=-1, verbose=1)
grid_search.fit(X_train, y_train)

best_model = grid_search.best_estimator_

# --- Evaluation ---
y_pred = best_model.predict(X_test)
logging.info(f"Accuracy: {accuracy_score(y_test, y_pred):.4f}")
logging.info("Classification Report:\n%s", classification_report(y_test, y_pred))
logging.info("Confusion Matrix:\n%s", confusion_matrix(y_test, y_pred))

# --- Plot Feature Importance ---
plt.figure(figsize=(10, 6))
plot_importance(best_model)
plt.title("Feature Importance (XGBoost)")
plt.tight_layout()
plt.show()

# --- Save model & imputer ---
joblib.dump(best_model, "xgb_disease_model.pkl")
joblib.dump(imputer, "xgb_disease_imputer.pkl")
logging.info("Model and imputer saved successfully.")