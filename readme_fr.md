# Installation
Contr�le du Synology Surveillance Station depuis eedomus
  
### Les principes
  
D�clencher depuis eedomus l'enregistrement d'une ou plusieurs cam�ras.  
Transmettre un snapshot sur un ftp eedomus.  
Monitorer les ressources du NAS Synology.  
  
### Ajout des p�riph�riques
Cliquez sur "Configuration" / "Ajouter ou supprimer un p�riph�rique" / "Store eedomus" / "Surveillance Station" / "Cr�er"  

  
*Voici les diff�rents champs � renseigner:*

* [Obligatoire] - L'IP locale  
* [Obligatoire] - Login Surveillance Station  
* [Obligatoire] - Mot de passe (certains caract�res sp�ciaux peuvent ne pas �tre support�s) 
* [Obligatoire] - Monitoring Oui/Non
  
Si Monitoring � oui, trois p�riph�riques de monitoring du processeur, de la m�moire RAM et des transmissions LAN seront �galement install�s.  
  
Apr�s installation du plugin, vous pourrez :  
* Modifier � tout moment les donn�es d'acc�s au Surveillance Station dans [VAR1] du p�riph�rique "statut"  
* Pr�ciser les donn�es de connexion � un FTP eedomus dans [VAR2] au format : camera.eedomus.com,loginftp,passftp  
* Ajouter des contr�les de cam�ras suppl�mentaires dans le p�riph�rique "controle", en dupliquant une valeur existante et en modifiant la donn�e "camid"  
  
Pour l'envoi en FTP eedomus, il faut au pr�alable cr�er une cam�ra g�n�rique dans la configuration eedomus.  
    
Le p�riph�rique "Statut" vous donne l'�tat de connexion du Surveillance Station :
* le nombre de cam�ras. 
* les id des cam�ras. Si l'Id est � "x", alors la cam�ra n'est pas op�rationnelle.  
  
Vous pouvez conna�tre la version du Surveillance Station et les informations d�taill�es des cam�ras dans l'XML complet via la configuration du p�riph�rique "statut" et le lien "tester". 
   
  
Influman 2019
therealinfluman@gmail.com  
[Paypal Me](https://www.paypal.me/influman "paypal.me")  


  



 

 

  


