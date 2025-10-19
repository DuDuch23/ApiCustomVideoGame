# Api Jeux vidéos

## Installer les dépendances : composer i

## Base de données : 
- php bin/console doctrine:database:create
- symfony console doctrine:migrations:migrate

## Charger les fixtures : symfony console d:f:l

## Générer son token d'authentification (JWT) :
- php bin/console lexik:jwt:generate-keypair

## Lancer la commande d'envoie de newsletter aux abonnés :
- php bin/console app:send-newsletter

## Lancer le cron d'envoie de newsletter tous les lundi à 8h30 :
- php bin/console messenger:consume