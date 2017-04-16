#!/usr/bin/python

#Script to locate Broadlink devices on local network by Graeme Brown Dec 23 2016
#These must be set up using the Broadlink app first!

import broadlink
import time

print "************************************************"
print "Using python library created by Matthew Garrett"
print "https://github.com/mjg59/python-broadlink"
print "************************************************"
print "Scanning network for Broadlink devices...."

mydevices = broadlink.discover(timeout=5)
print "Found " + str(len(mydevices )) + " broadlink devices"
time.sleep(1)
print "..............."

for index, item in enumerate(mydevices):

  mydevices[index].auth()

  ipadd = mydevices[index].host
  ipadd = str(ipadd)
  print "Device " + str(index + 1) +" Host address = " + ipadd[1:19]
  macadd = ''.join(format(x, '02x') for x in mydevices[index].mac[::-1])
  macadd = str(macadd)
 
  mymacadd = macadd[:2] + " " + macadd[2:4] + " " + macadd[4:6] + " " + macadd[6:8] + " " + macadd[8:10] + " " + macadd[10:12]
  print "Device " + str(index + 1) +" MAC address = " + mymacadd
  print "..............."
