asker
=====

A Symfony project created on November 28, 2016, 2:34 pm.


# Deprecated install method  
# Please use https://forge.univ-lyon1.fr/romain.chanu/asker_deploy  
sudo -E apt update  
sudo -E apt install apache2 php5 libapache2-mod-php5 php5-ldap php5-mysql mysql-server git acl  
cd /var/www/html/  
sudo chown $(whoami):$(whoami) ../html  
git clone https://forge.univ-lyon1.fr/romain.chanu/asker.git  
cd asker/  
php composer.phar install  
HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)  
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX app/cache app/logs  
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX app/cache app/logs  

#Ne pas oublier de créer la database dans mysql  
php app/console doctrine:schema:update -f

#on utilise le dump mysql  
Maintenant on peut utiliser son navigateur  
http://*YOUR_IP*/asker/web/app_dev.php

#generate release version  
bash new_assets_release.sh


