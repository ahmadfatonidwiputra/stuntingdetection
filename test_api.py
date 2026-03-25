import requests
import json
import urllib.request
import os

url = 'https://raw.githubusercontent.com/google/mediapipe/master/docs/images/mobile/pose_tracking_full_body_landmarks.png'
urllib.request.urlretrieve(url, 'test_person.png')

url_api = 'http://localhost:5001/predict'
files = {'image': open('test_person.png', 'rb')}
try:
    response = requests.post(url_api, files=files)
    print("Status:", response.status_code)
    try:
        data = response.json()
        if 'pose_image_base64' in data and data['pose_image_base64'] is not None:
            data['pose_image_base64'] = '<base64_string_present>'
        print("JSON:", json.dumps(data, indent=2))
    except Exception as e:
        print("Raw text:", response.text)
except Exception as e:
    print(e)
