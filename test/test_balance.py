import os
import json

data = os.popen("curl http://localhost/v-a/www/api.php?method=get_balance'&'apikey=345678").read()

json_object = json.loads(data)

print(json_object["items"])

sum = 0

for element in json_object["items"]:
    sum = sum + float (element["value"])

mydiff = 0
for element in json_object["depots"]:
    diff = float(element['end'] - element['start'])
    mydiff = mydiff + diff

if mydiff == sum:
    print("yes")