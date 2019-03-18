# Installation
Contrôle du Synology Surveillance Station depuis eedomus
  
### Les principes
  
Déclencher depuis eedomus l'enregistrement d'une ou plusieurs caméras.  
Transmettre un snapshot sur un ftp eedomus.  
Monitorer les ressources du NAS Synology.  
  
### Ajout des périphériques
Cliquez sur "Configuration" / "Ajouter ou supprimer un périphérique" / "Store eedomus" / "Surveillance Station" / "Créer"  

  
*Voici les différents champs à renseigner:*

* [Obligatoire] - L'IP locale  
* [Obligatoire] - Login Surveillance Station  
* [Obligatoire] - Mot de passe (certains caractères spéciaux peuvent ne pas être supportés) 
* [Obligatoire] - Monitoring Oui/Non
  
Si Monitoring à oui, trois périphériques de monitoring du processeur, de la mémoire RAM et des transmissions LAN seront également installés.  
  
Après installation du plugin, vous pourrez :  
* Modifier à tout moment les données d'accès au Surveillance Station dans [VAR1] du périphérique "statut"  
* Préciser les données de connexion à un FTP eedomus dans [VAR2] au format : camera.eedomus.com,loginftp,passftp  
* Ajouter des contrôles de caméras supplémentaires dans le périphérique "controle", en dupliquant une valeur existante et en modifiant la donnée "camid"  
  
Pour l'envoi en FTP eedomus, il faut au préalable créer une caméra générique dans la configuration eedomus.  
    
Le périphérique "Statut" vous donne l'état de connexion du Surveillance Station :
* le nombre de caméras. 
* les id des caméras. Si l'Id est à "x", alors la caméra n'est pas opérationnelle.  
  
Vous pouvez connaître la version du Surveillance Station et les informations détaillées des caméras dans l'XML complet via la configuration du périphérique "statut" et le lien "tester". 
   
  
Influman 2019
therealinfluman@gmail.com  
[Paypal Me](https://www.paypal.me/influman "paypal.me")  


  



 

 

  


