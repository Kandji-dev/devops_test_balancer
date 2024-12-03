
# Test de Load Balancing avec Nginx et Symfony

## Objectif

Ce projet a été réalisé dans le but de tester et de démontrer mes compétences en *DevOps*, plus spécifiquement en matière de configuration d'un serveur de *load balancing* avec Nginx, ainsi que l'intégration avec une application Symfony. L'objectif principal de ce test est de répartir la charge entre plusieurs conteneurs Docker représentant une application Symfony.

## Architecture

- **Serveur Nginx** : utilisé comme un proxy inverse pour distribuer le trafic entrant entre plusieurs instances de l'application Symfony.
- **Conteneurs Docker** : deux instances de l'application Symfony (`app_one` et `app_two`), chacune étant servie par un serveur web configuré dans un conteneur.
- **Symfony** : une application PHP Symfony de base qui reçoit les requêtes via Nginx et affiche sur la page d'accueil le nom de l'application utilisée pour chaque requête.

## Description de la configuration

1. **Upstream Nginx** : 
   Nginx est configuré pour rediriger les requêtes HTTP entrantes vers deux instances de l'application Symfony, `app_one` et `app_two`. L'algorithme de *round-robin* est utilisé pour distribuer les requêtes entre ces deux conteneurs.

   ```nginx
   upstream symfony_app {
       server app_one:80;  # Conteneur app_one
       server app_two:80;  # Conteneur app_two
   }
   ```

2. **En-tête personnalisé** :
   Le serveur Nginx ajoute un en-tête personnalisé `X-Server-Used` à chaque requête, indiquant l'adresse du serveur amont (`upstream_addr`) qui a servi la requête. Cet en-tête est ensuite récupéré par l'application Symfony pour afficher quelle instance a traité la requête.

   ```nginx
   proxy_set_header X-Server-Used $upstream_addr;
   ```

3. **Application Symfony** :
   L'application Symfony récupère l'en-tête `X-Server-Used` et l'affiche sur la page d'accueil pour indiquer quel serveur a traité la requête.

   Exemple de code Symfony pour récupérer l'en-tête :
   ```php
   public function index(Request $request): Response
   {
       $serverUsed = $request->headers->get('X-Server-Used', 'unknown');
       return $this->render('home/index.html.twig', [
           'serverUsed' => $serverUsed
       ]);
   }
   ```

4. **Docker Compose** :
   Le projet utilise Docker pour containeriser l'application Symfony et le serveur Nginx. Le fichier `docker-compose.yml` définit les services nécessaires pour déployer les deux applications et le serveur Nginx.

   Exemple de configuration Docker Compose :
   ```yaml
   version: '3'
   services:
     nginx:
       image: nginx:latest
       ports:
         - "80:80"
       volumes:
         - ./nginx.conf:/etc/nginx/nginx.conf
         - ./html:/usr/share/nginx/html
       depends_on:
         - app_one
         - app_two

     app_one:
       image: my_symfony_app
       expose:
         - "80"

     app_two:
       image: my_symfony_app
       expose:
         - "80"
   ```

## Instructions d'installation

1. Clonez le repository :
   ```bash
   git clone https://github.com/votre-utilisateur/load-balancing-test.git
   cd load-balancing-test
   ```

2. Construisez et lancez les services Docker avec Docker Compose :
   ```bash
   docker-compose up --build
   ```

3. Accédez à `http://localhost` dans votre navigateur. Vous devriez voir la page d'accueil de Symfony, indiquant si la requête a été traitée par `app_one` ou `app_two`.

4. Pour tester le comportement du *load balancing*, vous pouvez recharger la page plusieurs fois. Le trafic devrait être distribué de manière alternée entre les deux applications.

## Technologies utilisées

- **Nginx** : Proxy inverse et Load Balancer
- **Symfony** : Framework PHP utilisé pour l'application
- **Docker** : Conteneurisation des services (Nginx et Symfony)
- **Docker Compose** : Orchestration des conteneurs

Voici un guide étape par étape pour tester le projet avec trois terminaux et des logs. Vous utiliserez un script `loop.sh` pour envoyer des requêtes à votre serveur Nginx, et vous afficherez les logs des deux instances de l'application Symfony ainsi que les logs du serveur Nginx. Ce guide suppose que vous avez déjà configuré votre projet avec Docker et que vous avez un script de test comme `loop.sh` pour envoyer des requêtes.

### Étape 1 : Préparer le script `loop.sh`

Assurez-vous que le fichier `loop.sh` est créé et qu'il envoie des requêtes répétées vers votre serveur Nginx. Voici un exemple de contenu pour ce fichier :

```bash
#!/bin/bash

# Lancer des requêtes sur le serveur Nginx
while true; do
  curl -s http://localhost/ > /dev/null
  sleep 1
done
```

Le script envoie des requêtes HTTP toutes les secondes à `http://localhost/` (votre serveur Nginx). Vous pouvez l'adapter selon vos besoins.

### Étape 2 : Ouvrir trois terminaux

1. **Premier terminal : Lancer les conteneurs Docker avec Docker Compose**

   Dans le premier terminal, exécutez la commande suivante pour démarrer les services Docker (Nginx et les deux applications Symfony) :

   ```bash
   docker-compose up --build
   ```

   Cela lancera Nginx et les deux instances de votre application Symfony (app_one et app_two).

2. **Deuxième terminal : Suivre les logs de l'application Symfony 1 (`app_one`)**

   Dans le deuxième terminal, ouvrez les logs de l'application Symfony `app_one`. Vous pouvez exécuter la commande suivante pour afficher les logs de ce conteneur :

   ```bash
   docker logs -f <nom_du_conteneur_app_one>
   ```

   Remplacez `<nom_du_conteneur_app_one>` par le nom exact du conteneur correspondant à `app_one`. Si vous avez utilisé Docker Compose, vous pouvez obtenir le nom du conteneur en exécutant `docker ps`.

3. **Troisième terminal : Suivre les logs de l'application Symfony 2 (`app_two`)**

   Dans le troisième terminal, ouvrez les logs de l'application Symfony `app_two`. Exécutez cette commande pour afficher les logs de ce conteneur :

   ```bash
   docker logs -f <nom_du_conteneur_app_two>
   ```

   Remplacez `<nom_du_conteneur_app_two>` par le nom du conteneur correspondant à `app_two`.

### Étape 3 : Lancer le script `loop.sh`

Dans le terminal 1 (ou un autre terminal de votre choix), vous devez maintenant exécuter le script `loop.sh` pour envoyer des requêtes HTTP à votre serveur Nginx. Assurez-vous que le script a les bonnes permissions d'exécution et exécutez-le avec la commande suivante :

```bash
chmod +x loop.sh  # Si ce n'est pas déjà fait
./loop.sh
```

Le script commencera à envoyer des requêtes répétées toutes les secondes.

### Étape 4 : Observer les logs

- Dans le **deuxième terminal**, vous devriez voir les logs de l'application `app_one` s'afficher. Vous y trouverez des informations concernant les requêtes traitées par cette instance.
  
- Dans le **troisième terminal**, vous verrez les logs de l'application `app_two`, affichant les requêtes traitées par cette deuxième instance.

- Dans le **premier terminal**, vous pouvez également observer les logs du serveur Nginx, si vous avez configuré   un  fichier de logs dans votre fichier `nginx.conf`. Vous pouvez suivre les logs Nginx avec la commande suivante :

   ```bash
   docker logs -f <nom_du_conteneur_nginx>
   ```

Cela vous permettra de voir comment Nginx répartit les requêtes entre les deux applications `app_one` et `app_two`.

### Étape 5 : Analyser les résultats

- Vous devriez voir que les requêtes envoyées par `loop.sh` sont réparties entre les deux applications, grâce au mécanisme de *load balancing* de Nginx.
- Dans les logs de `app_one` et `app_two`, vous devriez observer les requêtes traitées, et cela devrait changer entre les deux applications à chaque nouvelle requête, selon la stratégie de répartition définie dans la configuration Nginx.

---

Ce test vous permettra de vérifier que votre infrastructure de *load balancing* fonctionne correctement et que Nginx distribue les requêtes entre vos deux instances de l'application Symfony.
## Conclusion

Ce test a permis de démontrer mes compétences en matière de configuration de *load balancing* avec Nginx, ainsi que l'intégration avec une application Symfony déployée dans des conteneurs Docker. Il est possible d'étendre cette architecture à un environnement de production en ajoutant des instances supplémentaires ou en configurant des stratégies de mise en cache et de tolérance aux pannes.
