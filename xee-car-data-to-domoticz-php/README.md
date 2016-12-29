# Xee car data to Domoticz in Php :)

Get all car informations with Xee et send all on Domoticz !

# Aperçu

![Preview img](screen/cap_domoticz.png)

![Preview img](screen/cap_domoticz_plan.png)

# Configuration

- Create a dev Xee account on https://developer.xee.com/
- Create a Xee application

![Preview app conf](screen/cap_xee_app_conf)

- Note all application informations in a file xee_conf.json like this :

```json
{
  "Client_Id" : "clientid",
  "Client_secret" : "clientsecret",
  "domoticz_url" : "127.0.0.1:8080",
  "garage_lat" : 32.6242,
  "garage_lng" : 17.032489,
  "garage_radis_size" : 0.7
}

```

- Edit the script with yours informations
	- Client id
	- Client secret
	- Domoticz Url
	- ...

# Getting Started 

## Token ?

- First time ? You need to launch the script from a web server for getting xee token one time
- The token is register in the file token.txt
- The script use this token or ask a new token if is expired automaticali with the refresh_token

## Get access token

/!\ Make sure have all prerequisites of [Domoticz Scripts](https://github.com/T3kstiil3/Domoticz_Scripts/#prerequisites)

Create a vhost on your rpi

````
#creation of vhost
sudo nano /etc/apache2/sites-available/xee.local.conf
````

Edit like this :

````vhost
<VirtualHost *:80>
    ServerName xee.local

    ServerAdmin webmaster@localhost
    DocumentRoot /home/pi/Domoticz_Scripts/xee-car-data-to-domoticz-php

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    <Directory /home/pi/Domoticz_Scripts/xee-car-data-to-domoticz-php/>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
        Allow from all
    </Directory>

</VirtualHost>
````

````
# active apache vhost
sudo a2ensite xee.local.conf
# reboot aparche service
sudo service apache2 reload
# create file and change permision
sudo nano /home/pi/Domoticz_Scripts/xee-car-data-to-domoticz-php/xee_token.txt
sudo chmod 777 /home/pi/Domoticz_Scripts/xee-car-data-to-domoticz-php/xee_token.txt
````

# Informations

- Les informations de la première voiture sont envoyées vers domoticz
- Il est nécessaire de créer des custom sensor ou autres dans domoticz pour les afficher

- Nouveau paramètre dans l'url pour utiliser le script comme "api" -> ?data=
 - ?data=domoticz envoie les données vers domoticz
 - Retourne un json
 	- ?data=car renvoie les dernières informations de la voiture
 	- ?data=trips renvoie la liste des derniers trajets
 	- ?data=trip&trip_id=45678905678456789 renvoie les données de la voiture sur un trajet spécifique

Fonctionntalités utilisées pour mon miroir connecté https://github.com/T3kstiil3/The_Mirror

# Liens
[Boîtier Xee](http://www.amazon.fr/gp/product/B01AIE4CHE/ref=as_li_tl?ie=UTF8&camp=1642&creative=6746&creativeASIN=B01AIE4CHE&linkCode=as2&tag=aureli-21)<br />
[Raspberry Pi 3] (http://www.amazon.fr/gp/product/B01CCOXV34/ref=as_li_tl?ie=UTF8&camp=1642&creative=19458&creativeASIN=B01CCOXV34&linkCode=as2&tag=aureli-21)<br />
[Xee Développeur](https://developer.xee.com/)<br />
[Domoticz](https://domoticz.com/)<br />

# TODO
- [x] V3 de l'api Xee
- [x] Utiliser le script sous forme d'api local
- [ ] Commenter le code
- [ ] Gérer plusieurs véhicules
