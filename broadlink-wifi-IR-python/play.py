#!/usr/bin/python

import broadlink
import time
import sys
import json
import os

with open('config.json') as data_file:
    config = json.load(data_file)

__location__ = os.path.realpath(os.path.join(os.getcwd(), os.path.dirname(__file__)))

print "Device IP: " + config['str_ip'] +" Mac : " + config['str_mac'] +" !"

try:
    fileName = sys.argv[1]
except IndexError:
    fileName = 'null'

if fileName == 'null':
   print "Error - no file name parameter suffixed"
   sys.exit()
else:
   device = broadlink.rm(host=(config['str_ip'],80), mac=bytearray.fromhex(config['str_mac']))

print "Connecting to Broadlink device...."
device.auth()
time.sleep(1)
print "Connected...."
time.sleep(1)
device.host

file = open(os.path.join(__location__,fileName), 'r')

myhex = file.read()

device.send_data(myhex.decode('hex'))
print "Code Sent...."
