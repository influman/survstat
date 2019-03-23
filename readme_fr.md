# Installation
Contr�le du Synology Surveillance Station depuis eedomus
  
### Les principes
  
D�clencher depuis eedomus l'enregistrement d'une ou plusieurs cam�ras.  
Transmettre un snapshot sur un ftp eedomus.  
Enable/Disable les cam�ras.  
Enable/Disable la d�tection de mouvement (celle de Surveillance Station).     
Contr�ler les cam�ras PTZ.  
Monitorer les ressources du NAS Synology.  
  
### Ajout des p�riph�riques
Cliquez sur "Configuration" / "Ajouter ou supprimer un p�riph�rique" / "Store eedomus" / "Surveillance Station" / "Cr�er"  

  
*Voici les diff�rents champs � renseigner:*

* [Obligatoire] - L'IP locale  
* [Obligatoire] - Login Surveillance Station  
* [Obligatoire] - Mot de passe 
* [Obligatoire] - Gestion du PTZ Oui/Non  
* [Obligatoire] - Monitoring Oui/Non
  
Si Gestion du PTZ � oui, deux p�riph�riques compl�mentaires seront install�s. Le premier est un capteur qui donne les cam�ras PTZ actives, et les "presets" disponibles.  
Le second est un actionneur pour contr�ler le PTZ des cam�ras : mouvement directionnel, zoom in/out, aller sur un preset donn�.  
  
Si Monitoring � oui, trois p�riph�riques de monitoring du processeur, de la m�moire RAM et des transmissions LAN seront �galement install�s.  
  
  
Apr�s installation du plugin, vous pourrez :  
* Modifier � tout moment les donn�es d'acc�s au Surveillance Station dans [VAR1] du p�riph�rique "Statut"  
* Pr�ciser les donn�es de connexion � un FTP eedomus dans [VAR2] au format : camera.eedomus.com,loginftp,passftp  
* Ajouter/Modifier des contr�les de cam�ras dans le p�riph�rique "Controle", en dupliquant une valeur existante et en modifiant la donn�e "camid"  
* Ajouter/Modifier des contr�les PTZ  dans le p�riph�rique "PTZ Controle", en dupliquant une valeur existante et en modifiant les donn�es "camid" et "presetid"  
  
Pour l'envoi en FTP eedomus, il faut au pr�alable cr�er une cam�ra g�n�rique dans la configuration eedomus.  
    
Le p�riph�rique "Statut" vous donne l'�tat de connexion du Surveillance Station :  
* le nombre de cam�ras.  
* les id des cam�ras. Si l'Id est � "x", alors la cam�ra n'est pas op�rationnelle.  
  
  
Vous pouvez conna�tre la version du Surveillance Station et les informations d�taill�es des cam�ras dans l'XML complet via la configuration du p�riph�rique "statut" et le lien "tester". 
  
  
NB1 : Pour les p�riph�riques li�es aux cam�ras, l'utilisateur Synology utilis� doit �tre habilit� a minima � l'application Surveillance Station.  
NB2 : Pour les p�riph�riques li�es au Monitoring, aux actions Reboot/Shutdwon, aux enable/disable des cam�ras, l'utilisateur doit avoir des privil�ges sup�rieurs (faire partie du groupe d'administrateurs).  
NB3 : Les caract�res sp�ciaux # et & dans un mot de passe ne sont pas compatibles avec ce plugin. 
  
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


  



 

 

  


