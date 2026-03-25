import cv2
import urllib.request
import mediapipe as mp
from mediapipe.tasks.python.vision import PoseLandmarker, PoseLandmarkerOptions
from mediapipe.tasks.python import BaseOptions

url = 'https://thumbs.dreamstime.com/b/baby-boy-lying-down-floor-30238053.jpg'
urllib.request.urlretrieve(url, 'baby.jpg')

pose_options = PoseLandmarkerOptions(
    base_options=BaseOptions(model_asset_path='ml_engine/assets/pose_landmarker_heavy.task'),
    num_poses=1,
    min_pose_detection_confidence=0.1,  
    min_pose_presence_confidence=0.1,
    min_tracking_confidence=0.1
)
try:
    landmarker = PoseLandmarker.create_from_options(pose_options)

    image_bgr = cv2.imread('baby.jpg')
    rgb = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2RGB)
    mp_image = mp.Image(image_format=mp.ImageFormat.SRGB, data=rgb)
    result = landmarker.detect(mp_image)

    if not result.pose_landmarks or len(result.pose_landmarks) == 0:
        print("FAILED: No pose detected even with 0.1 confidence thresholds.")
    else:
        print(f"SUCCESS: Detected pose with {len(result.pose_landmarks[0])} landmarks!")
except Exception as e:
    print(e)
