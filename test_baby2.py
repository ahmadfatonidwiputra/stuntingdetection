import requests

url_api = 'http://localhost:5001/predict'
files = {'image': open('baby.jpg', 'rb')}
resp = requests.post(url_api, files=files)
data = resp.json()
if 'pose_image_base64' in data and data['pose_image_base64'] is not None:
    data['pose_image_base64'] = '<base64...>'
print(data)
