import joblib
import pandas as pd
import warnings
warnings.filterwarnings('ignore')

model = joblib.load('ml_engine/models/height_estimator_model.pkl')
print(f"Model type: {type(model)}")
try:
    print(f"Model feature names: {getattr(model, 'feature_names_in_', 'Not found')}")
except Exception as e:
    print(e)
    
features = {
    'torso_length': 0.3,
    'upper_leg_length': 0.2,
    'lower_leg_length': 0.2,
    'leg_length': 0.4,
    'total_visible_height': 0.9,
    'torso_leg_ratio': 0.3/0.4,
    'upper_lower_leg_ratio': 0.2/0.2
}

try:
    df = pd.DataFrame([features])
    pred = model.predict(df)
    print(f"Prediction: {pred[0]}")
except Exception as e:
    import traceback
    traceback.print_exc()

