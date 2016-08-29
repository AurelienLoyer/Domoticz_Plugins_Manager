# xee-car-data-to-domoticz-php

Récupérer les informations de votre voiture grâce au boîtier Xee et envoyez-les vers Domoticz !

# Aperçu

![Preview img](screen/cap_domoticz.png)

![Preview img](screen/cap_domoticz_plan.png)

# Informations

- Première utilisation ? Lancer le script depuis un serveur web pour récupérer le token Xee
- Le token est enregistré dans le fichier token.txt
- Le script utilise le token ou fait la demande d'un nouveau token si celui-ci est expérié automatiquement grace au refresh_token
- Il est nécessaire de creer un compte developpeur sur https://developer.xee.com/ et de créer une application
- Renseigner les informations de l'application dans un fichier xee_conf.json sous cette forme :

```json
{
  "Client_Id" : "clientid",
  "Client_secret" : "clientsecret",
  "domoticz_url" : "127.0.0.1:8080",
  "garage_lat" : 52.626142,
  "garage_lng" : 1.032489,
  "garage_radis_size" : 0.7
}

```

- Modifier le script avec vos informations
	- Client id
	- Client secret
	- Domoticz Url
	- ...
- Les informations de la premiere voiture sont envoyées vers domoticz
- Il est nécessaire de créer des custom sensor ou autres dans domoticz pour les afficher

# Liens
[Boîtier Xee](http://www.amazon.fr/gp/product/B01AIE4CHE/ref=as_li_tl?ie=UTF8&camp=1642&creative=6746&creativeASIN=B01AIE4CHE&linkCode=as2&tag=aureli-21)<br />
[Raspberry Pi 3] (http://www.amazon.fr/gp/product/B01CCOXV34/ref=as_li_tl?ie=UTF8&camp=1642&creative=19458&creativeASIN=B01CCOXV34&linkCode=as2&tag=aureli-21)<br />
[Xee Développeur](https://developer.xee.com/)<br />
[Domoticz](https://domoticz.com/)<br />

# TODO
- [X] Passer à la v3 de l'api Xee
- [ ] Commenter le code
- [ ] Gérer plusieurs véhicules
