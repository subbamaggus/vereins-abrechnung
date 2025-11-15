import os
import subprocess

directory = '.'
print('starting tests...')
for filename in os.listdir(directory):
    if filename.startswith('test_'):
        if filename.endswith('.py'):
            print(os.path.join(directory, filename))
            subprocess.Popen("python3 " + filename, shell=True)
            
print("done")

exit
