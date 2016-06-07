from client import *
from conf import *
import os
import bs4

printer_ip = '10.0.10.28'

server_url = 'http://' + domoticz_ip +  '/json.htm?type=command&param=udevice&idx='

server_domoticz = Server(server_url)

# maps of id's of ink cartridges from printer to domoticz
ids_map = {}
ids_map[0] = '13' # Colors
ids_map[1] = '14' # Black


def send_values(id_sensor, value):
	server_domoticz.query(str(id_sensor) + '&nvalue=0&svalue=' + str(value))


def get_ink_levels():
	server = Server('http://' + printer_ip)

	xmlData = server.query('/DevMgmt/ConsumableConfigDyn.xml')

	soup = bs4.BeautifulSoup(xmlData, 'xml')

	data = soup.find_all('ConsumablePercentageLevelRemaining')
	for color in data:
		d_device_id = color.parent.find('ConsumableStation').get_text()
		d_device_id = int(d_device_id)
		send_values(ids_map[d_device_id], color.get_text())


# ping printer to check if it's on
is_up = os.system('ping -c 1 ' + printer_ip)

if is_up == 0:
	get_ink_levels()



