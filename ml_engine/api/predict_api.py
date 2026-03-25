"""
Flask API server for ML-based height and weight estimation from photos.
Uses MediaPipe Pose for feature extraction,
    height_estimator_model.pkl (RandomForest) for height prediction,
    final_model.h5 (Keras) for weight prediction.
"""

import os
import io
import sys
import traceback
import numpy as np
import cv2
import joblib
from flask import Flask, request, jsonify

# ── Suppress TF/MediaPipe noise ────────────────────────────────────
os.environ["TF_CPP_MIN_LOG_LEVEL"] = "3"

app = Flask(__name__)

# ── Paths ───────────────────────────────────────────────────────────
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__))) # points to ml_engine/
MODEL_DIR = os.path.join(BASE_DIR, "models")
HEIGHT_MODEL_PATH = os.path.join(MODEL_DIR, "height_estimator_model.pkl")
WEIGHT_MODEL_PATH = os.path.join(MODEL_DIR, "final_model.h5")
POSE_MODEL_PATH = os.path.join(BASE_DIR, "assets", "pose_landmarker_heavy.task")

# ── Download MediaPipe pose model if needed ─────────────────────────
if not os.path.exists(POSE_MODEL_PATH):
    import urllib.request
    print("Downloading pose_landmarker_heavy.task ...")
    url = (
        "https://storage.googleapis.com/mediapipe-models/"
        "pose_landmarker/pose_landmarker_heavy/float16/latest/"
        "pose_landmarker_heavy.task"
    )
    urllib.request.urlretrieve(url, POSE_MODEL_PATH)
    print("Download complete.")

# ── Load models at startup ──────────────────────────────────────────
print("Loading height model …")
height_model = joblib.load(HEIGHT_MODEL_PATH)
print("Height model loaded ✓")

print("Loading weight model …")
import tensorflow as tf
import h5py
tf.get_logger().setLevel("ERROR")


def load_weight_model_from_h5(h5_path):
    """
    Reconstruct the Sequential(MobileNetV2 → GAP → Dense → Dropout → Dense)
    architecture and manually load weights from the legacy Keras-2 .h5 file.
    Keras 3 changed variable naming, so we match weights by shape per layer.
    """
    # 1. Rebuild architecture (must match the saved model exactly)
    base = tf.keras.applications.MobileNetV2(
        input_shape=(128, 128, 3), include_top=False, weights=None
    )
    model = tf.keras.Sequential([
        base,
        tf.keras.layers.GlobalAveragePooling2D(),
        tf.keras.layers.Dense(128, activation="relu", name="dense_58"),
        tf.keras.layers.Dropout(0.5),
        tf.keras.layers.Dense(1, activation="sigmoid", name="dense_59"),
    ])
    model.build((None, 128, 128, 3))

    # 2. Flatten all sub-layers (including MobileNetV2 internal layers)
    all_layers = []
    for layer in model.layers:
        if hasattr(layer, "layers"):  # Functional sub-model
            all_layers.extend(layer.layers)
        else:
            all_layers.append(layer)

    # 3. Load weights layer-by-layer, matching by shape
    with h5py.File(h5_path, "r") as f:
        wg = f["model_weights"]
        mn_group = wg.get("mobilenetv2_1.00_128")

        for layer in all_layers:
            expected_weights = layer.get_weights()
            if not expected_weights:
                continue

            lname = layer.name
            h5_datasets = []

            def collect_ds(name, obj):
                if isinstance(obj, h5py.Dataset):
                    h5_datasets.append((name, np.array(obj)))

            # Look in mobilenetv2 group first
            if mn_group is not None and lname in mn_group:
                mn_group[lname].visititems(collect_ds)

            # Look in top-level groups (dense_58, dense_59)
            if not h5_datasets and lname in wg:
                wg[lname].visititems(collect_ds)

            if not h5_datasets:
                continue

            # Match h5 datasets to expected weights by shape
            matched_weights = []
            remaining = list(h5_datasets)
            for ew in expected_weights:
                for name, arr in remaining:
                    if arr.shape == ew.shape:
                        matched_weights.append(arr)
                        remaining.remove((name, arr))
                        break

            if len(matched_weights) == len(expected_weights):
                layer.set_weights(matched_weights)

    return model


weight_model = load_weight_model_from_h5(WEIGHT_MODEL_PATH)
print("Weight model loaded ✓")

# ── MediaPipe Pose Landmarker ───────────────────────────────────────
import mediapipe as mp
from mediapipe.tasks.python.vision import PoseLandmarker, PoseLandmarkerOptions
from mediapipe.tasks.python import BaseOptions
import base64

pose_options = PoseLandmarkerOptions(
    base_options=BaseOptions(model_asset_path=POSE_MODEL_PATH),
    num_poses=1,
    min_pose_detection_confidence=0.1,
    min_pose_presence_confidence=0.1,
    min_tracking_confidence=0.1
)
landmarker = PoseLandmarker.create_from_options(pose_options)
print("MediaPipe PoseLandmarker ready ✓")

# ── Landmark indices ────────────────────────────────────────────────
NOSE = 0
LEFT_SHOULDER = 11
RIGHT_SHOULDER = 12
LEFT_HIP = 23
RIGHT_HIP = 24
LEFT_KNEE = 25
RIGHT_KNEE = 26
LEFT_ANKLE = 27
RIGHT_ANKLE = 28


def extract_features(image_bgr):
    """Extract the 7 pose features used by height_estimator_model.
    Also draws landmarks on a copy of the image and returns a base64 string.
    Returns: (features_dict, pose_image_base64) or (None, None)
    """
    rgb = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2RGB)
    mp_image = mp.Image(image_format=mp.ImageFormat.SRGB, data=rgb)
    result = landmarker.detect(mp_image)

    if not result.pose_landmarks or len(result.pose_landmarks) == 0:
        return None, None

    # Draw custom landmarks using cv2
    annotated_image = np.copy(image_bgr)
    h, w, _ = annotated_image.shape
    pose_landmarks_list = result.pose_landmarks
    lm = pose_landmarks_list[0]
    
    # Hardcoded pose connections instead of mp.solutions which sometimes missing in mediapipe-python
    POSE_CONNECTIONS = [(0, 1), (1, 2), (2, 3), (3, 7), (0, 4), (4, 5), (5, 6), (6, 8), (9, 10), 
                        (11, 12), (11, 13), (13, 15), (15, 17), (15, 19), (15, 21), (17, 19), 
                        (12, 14), (14, 16), (16, 18), (16, 20), (16, 22), (18, 20), (11, 23), 
                        (12, 24), (23, 24), (23, 25), (24, 26), (25, 27), (26, 28), (27, 29), 
                        (28, 30), (29, 31), (30, 32), (27, 31), (28, 32)]

    for connection in POSE_CONNECTIONS:
        start_idx, end_idx = connection
        if start_idx < len(lm) and end_idx < len(lm):
            pt1 = (int(lm[start_idx].x * w), int(lm[start_idx].y * h))
            pt2 = (int(lm[end_idx].x * w), int(lm[end_idx].y * h))
            cv2.line(annotated_image, pt1, pt2, (0, 255, 0), 2)
            
    for landmark in lm:
        center = (int(landmark.x * w), int(landmark.y * h))
        cv2.circle(annotated_image, center, 4, (0, 0, 255), -1)

    # Encode annotated image to base64
    _, buffer = cv2.imencode('.jpg', annotated_image)
    pose_base64 = base64.b64encode(buffer).decode('utf-8')
    pose_img_data_uri = "data:image/jpeg;base64," + pose_base64

    nose_y = lm[NOSE].y
    shoulder_y = (lm[LEFT_SHOULDER].y + lm[RIGHT_SHOULDER].y) / 2
    hip_y = (lm[LEFT_HIP].y + lm[RIGHT_HIP].y) / 2
    knee_y = (lm[LEFT_KNEE].y + lm[RIGHT_KNEE].y) / 2
    ankle_y = (lm[LEFT_ANKLE].y + lm[RIGHT_ANKLE].y) / 2

    features = {
        "torso_length": abs(hip_y - shoulder_y),
        "upper_leg_length": abs(knee_y - hip_y),
        "lower_leg_length": abs(ankle_y - knee_y),
        "leg_length": abs(ankle_y - hip_y),
        "total_visible_height": abs(ankle_y - nose_y),
        "torso_leg_ratio": abs(hip_y - shoulder_y) / (abs(ankle_y - hip_y) + 1e-6),
        "upper_lower_leg_ratio": abs(knee_y - hip_y) / (abs(ankle_y - knee_y) + 1e-6),
    }

    return features, pose_img_data_uri


def prepare_image_for_weight_model(image_bgr, target_size=(128, 128)):
    """Resize & normalise image for the Keras weight model."""
    img = cv2.resize(image_bgr, target_size)
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    img = img.astype("float32") / 255.0
    return np.expand_dims(img, axis=0)


# ── Prediction endpoint ────────────────────────────────────────────
@app.route("/predict", methods=["POST"])
def predict():
    if "image" not in request.files:
        return jsonify({"error": "No image file provided"}), 400

    file = request.files["image"]
    file_bytes = file.read()
    np_arr = np.frombuffer(file_bytes, np.uint8)
    image_bgr = cv2.imdecode(np_arr, cv2.IMREAD_COLOR)

    if image_bgr is None:
        return jsonify({"error": "Could not decode image"}), 400

    result = {}

    # ── Height estimation (pkl) ─────────────────────────────────
    try:
        features, pose_base64 = extract_features(image_bgr)
        if features is not None:
            import pandas as pd
            df = pd.DataFrame([features])
            predicted_height = float(height_model.predict(df)[0])
            result["height_cm"] = round(predicted_height, 1)
            result["pose_image_base64"] = pose_base64
        else:
            result["height_cm"] = None
            result["height_error"] = "Pose tidak terdeteksi. Pastikan seluruh tubuh terlihat."
            result["pose_image_base64"] = None
    except Exception as e:
        import traceback
        traceback.print_exc()
        result["height_cm"] = None
        result["height_error"] = f"Kesalahan internal: {str(e)}"
        result["pose_image_base64"] = None

    # ── Weight estimation (h5) ──────────────────────────────────
    try:
        img_input = prepare_image_for_weight_model(image_bgr)
        predicted_weight = float(weight_model.predict(img_input, verbose=0)[0][0])
        result["weight_kg"] = round(predicted_weight, 1)
    except Exception as e:
        import traceback
        traceback.print_exc()
        result["weight_kg"] = None
        result["weight_error"] = f"Kesalahan internal: {str(e)}"

    return jsonify(result)


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"})


if __name__ == "__main__":
    print("\n🚀  Prediction API running on http://localhost:5001")
    app.run(host="0.0.0.0", port=5001, debug=False)
