#!/usr/bin/env python3.4

from client import *
from conf import *
from xml.dom import minidom
import os
import sys
import requests
import json

serverDomoticz = Server('http://'+domoticz_ip)
serverSoundtouch = Server('http://'+soundtouch_ip)

################# Fn

def send_values(id_sensor, value):
	serverDomoticz.query('/json.htm?type=command&param=switchlight&idx='+ str(id_sensor) + '&switchcmd=' + str(value))

def get_values(id_sensor):
	 return serverDomoticz.query('/json.htm?type=devices&rid='+ str(id_sensor))

def get_soudtouch_state():
	#On recupere l'état actuel de la soundtouch
	xmlFile = serverSoundtouch.query('/now_playing')
	xmldoc = minidom.parseString(xmlFile)
	nowPlaying = xmldoc.getElementsByTagName('nowPlaying')[0].attributes["source"].value
	if (nowPlaying == "STANDBY"):
		soudtouch_device_stat = "Off"
	else:
		soudtouch_device_stat = "On"
	return soudtouch_device_stat

def get_soundtouch_domoticz_state():
	#On recupere l'ancienne valeur dans domoticz 
	jsonFile = get_values(soundtouch_id)
	data = json.loads(jsonFile)
	soudtouch_domoticz_stat = data['result'][0]['Status']
	return soudtouch_domoticz_stat

def update_soundtouch_state():
	soudtouch_device_stat = get_soudtouch_state()
	if (soudtouch_device_stat == "Off"):
		print("Soundtouch éteinte")
		send_values(soundtouch_id,'Off')
	else:
		print("Soundtouch run !")
		send_values(soundtouch_id,'On')

def send_touch(button):
	if(button == 'POWER_ON' and get_soudtouch_state() == 'On'):
		return
	if(button == 'POWER_OFF' and get_soudtouch_state() == 'Off'):
		return
	if(button == 'POWER_OFF' or button == 'POWER_ON'):
		button = 'POWER'

	url = 'http://'+soundtouch_ip+'/key'
	#appui
	dataXml = '<?xml version="1.0" encoding="UTF-8" ?><key state="press" sender="Gabbo">'+button+'</key>'
	requests.post(url, data=dataXml)

################# Call

if(len(sys.argv) > 1 and sys.argv[1] == "send_touch"):
	send_touch(sys.argv[2])
elif(len(sys.argv) == 1):
	update_soundtouch_state()
