# Installation
Contrôle du Synology Surveillance Station depuis eedomus
  
### Les principes
  
Déclencher depuis eedomus l'enregistrement d'une ou plusieurs caméras.  
Transmettre un snapshot sur un ftp eedomus.  
Enable/Disable les caméras.  
Enable/Disable la détection de mouvement (celle de Surveillance Station).     
Contrôler les caméras PTZ.  
Monitorer les ressources du NAS Synology.  
  
### Ajout des périphériques
Cliquez sur "Configuration" / "Ajouter ou supprimer un périphérique" / "Store eedomus" / "Surveillance Station" / "Créer"  

  
*Voici les différents champs à renseigner:*

* [Obligatoire] - L'IP locale  
* [Obligatoire] - Login Surveillance Station  
* [Obligatoire] - Mot de passe 
* [Obligatoire] - Gestion du PTZ Oui/Non  
* [Obligatoire] - Monitoring Oui/Non
  
Si Gestion du PTZ à oui, deux périphériques complémentaires seront installés. Le premier est un capteur qui donne les caméras PTZ actives, et les "presets" disponibles.  
Le second est un actionneur pour contrôler le PTZ des caméras : mouvement directionnel, zoom in/out, aller sur un preset donné.  
  
Si Monitoring à oui, trois périphériques de monitoring du processeur, de la mémoire RAM et des transmissions LAN seront également installés.  
  
  
Après installation du plugin, vous pourrez :  
* Modifier à tout moment les données d'accès au Surveillance Station dans [VAR1] du périphérique "Statut"  
* Préciser les données de connexion à un FTP eedomus dans [VAR2] au format : camera.eedomus.com,loginftp,passftp  
* Ajouter/Modifier des contrôles de caméras dans le périphérique "Controle", en dupliquant une valeur existante et en modifiant la donnée "camid"  
* Ajouter/Modifier des contrôles PTZ  dans le périphérique "PTZ Controle", en dupliquant une valeur existante et en modifiant les données "camid" et "presetid"  
  
Pour l'envoi en FTP eedomus, il faut au préalable créer une caméra générique dans la configuration eedomus.  
    
Le périphérique "Statut" vous donne l'état de connexion du Surveillance Station :  
* le nombre de caméras.  
* les id des caméras. Si l'Id est à "x", alors la caméra n'est pas opérationnelle.  
  
  
Vous pouvez connaître la version du Surveillance Station et les informations détaillées des caméras dans l'XML complet via la configuration du périphérique "statut" et le lien "tester". 
  
  
NB1 : Pour les périphériques liées aux caméras, l'utilisateur Synology utilisé doit être habilité a minima à l'application Surveillance Station.  
NB2 : Pour les périphériques liées au Monitoring, aux actions Reboot/Shutdwon, aux enable/disable des caméras, l'utilisateur doit avoir des privilèges supérieurs (faire partie du groupe d'administrateurs).  
NB3 : Les caractères spéciaux # et & dans un mot de passe ne sont pas compatibles avec ce plugin. 
  
Les codes erreurs potentiels :  
100 Unknown error  
101 Invalid parameters  
102 API does not exist  
103 Method does not exist  
104 This API version is not supported  
105 Insufficient user privilege  
106 Connection time out  
107 Multiple login detected  
400 Invalid password    
401 Guest or disabled account  
402 Permission denied  
403 One time password not specified  
404 One time password authenticate failed  
  
  
 
Influman 2019
therealinfluman@gmail.com  
[Paypal Me](https://www.paypal.me/influman "paypal.me")  


  



 

 

  


