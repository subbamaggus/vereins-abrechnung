import os

data = os.popen("curl http://localhost/v-a/www/api.php?method=get_balance'&'apikey=345678").read()

print(data)