# 🚀 Gestion des Notes de Frais - API Symfony

## 📖 Contexte
Un commercial travaillant pour différentes sociétés doit pouvoir se faire rembourser les frais engagés pour son travail.
Cette API permet la gestion complète des notes de frais, incluant :

- 🔍 Consultation des notes de frais
- ➕ Ajout d'une nouvelle note
- ✏ Modification d'une note existante
- ❌ Suppression d'une note
- 🔐 Sécurisation par JWT
Elle est conçue en Symfony 6, avec une architecture hexagonale, et exposée via API REST documentée avec Swagger.

### 🛠 Prérequis
Avant d’installer le projet, assure-toi d’avoir les outils suivants installés :


| Outil                  | Version Recommandée  | Commande de Vérification       |
|------------------------|----------------------|--------------------------------|
| **Docker**            | >= 20.10.0           | `docker --version`             |
| **Docker Compose**    | >= 2.0               | `docker compose version`       |
| **Git**               | >= 2.30              | `git --version`                |
| **PHP**               | >= 8.2 (via Docker)  | `php -v` (hors Docker)         |
| **Composer**          | >= 2.4 (via Docker)  | `composer --version`           |
| **Symfony CLI** *(facultatif)* | >= 5.4  | `symfony -v`                   |

## 📦 Installation et Lancement du Projet

### 1️⃣ Cloner le projet
Commence par récupérer le code source du projet :

```sh
git clone git@github.com:fodekar/gestion-notes-frais.git
cd gestion-notes-frais
```

### 2️⃣ Configuration des Variables d'Environnement
Copie le fichier .env.example et renomme-le en .env :

```sh
cp .env.example .env
```
🔧 Modifie le fichier .env pour adapter la configuration de la base de données si nécessaire.

### 3️⃣ Démarrer les Conteneurs Docker
Copie le fichier .env.example et renomme-le en .env :

```sh
docker compose up -d
```
📌 Cela démarre les conteneurs pour PHP, PostgreSQL et Symfony.

### 4️⃣ Installer les Dépendances PHP
Une fois les conteneurs lancés, installe les dépendances avec Composer :

```sh
docker compose exec app composer install
```

### 5️⃣ Générer la Clé JWT pour l'Authentification
L'application utilise JWT pour l'authentification, génère les clés JWT avec :
```sh
docker compose exec app php bin/console lexik:jwt:generate-keypair
```
- Il crée un dossier config/jwt/ s'il n'existe pas.
- Il génère deux fichiers de clés :
   - 🔑 Clé privée : config/jwt/private.pem
   - 🔑 Clé publique : config/jwt/public.pem
- Il génère également un fichier de passphrase : config/jwt/passphrase

Utilisation des clés :
- La clé privée (private.pem) est utilisée pour signer les tokens JWT.
- La clé publique (public.pem) est utilisée pour vérifier la validité des tokens JWT.

### 🛠 Vérifier si les clés ont bien été générées
Après avoir exécuté la commande, tu peux vérifier si les fichiers sont bien créés avec :

```sh
ls -l config/jwt/
```
Si tout est correct, tu devrais voir :
```sh
-rw------- 1 user user 1708 Mar 13 15:12 private.pem
-rw-r--r-- 1 user user  451 Mar 13 15:12 public.pem
-rw------- 1 user user   32 Mar 13 15:12 passphrase
```
### 6️⃣ Créer la Base de Données et Appliquer les Migrations
Exécute les commandes suivantes pour créer et configurer la base de données :

```sh
docker compose exec app php bin/console doctrine:database:create
docker compose exec app php bin/console doctrine:migrations:migrate
```
💾 Ajoute les données de test avec les fixtures :

```sh
docker compose exec app php bin/console doctrine:fixtures:load
```

### 7️⃣ Démarrer le Serveur Symfony et Tester l'API
Une fois l’installation terminée, il est temps de démarrer le serveur Symfony et de tester l’API.

🖥️ Lancer le serveur Symfony
Exécute cette commande pour démarrer l'application :

```sh
docker compose up -d
```
Cela démarre les conteneurs en arrière-plan.

Vérifie que le serveur fonctionne :
```sh
docker compose logs -f app
```
L’API devrait être disponible à l’adresse suivante : 📍 http://127.0.0.1:8000

📨 Tester l'API avec Postman 
Une fois le serveur démarré, tu peux tester l'API avec Postman.

📌 Exemples de requêtes API :
📌 Créer un utilisateur (POST /api/register)
```json
{
    "firstName": "John",
    "lastName": "Doe",
    "email": "john.doe@example.com",
    "password": "securepassword",
    "birthDate": "1990-01-01"
}
```

📌 Se connecter (POST /api/login)
```json
{
    "email": "john.doe@example.com",
    "password": "securepassword"
}
```
👉 Cela retourne un token JWT à utiliser pour les autres requêtes.
📌 Lister les notes de frais (GET /api/expenses) par exemple.

### 8️⃣ Exécuter les Tests Automatisés
Notre projet contient des tests fonctionnels et unitaires pour garantir son bon fonctionnement.

📋 Prérequis pour lancer les tests
Avant de lancer les tests, assure-toi que :

1- Les dépendances sont installées
```sh
docker compose exec app composer install
```

2- La base de données de test est prête
```sh
docker compose exec app php bin/console doctrine:database:create --env=test
docker compose exec app php bin/console doctrine:migrations:migrate --env=test
```
On supprime et recrée la base de données de test pour garantir un environnement propre.

3- Exécuter les migrations
```sh
docker compose exec app php bin/console doctrine:migrations:migrate --env=test --no-interaction
```
Cela applique toutes les migrations pour avoir la bonne structure de base de données.

4- Exécuter les fixtures (données de test)
```sh
docker compose exec app php bin/console doctrine:fixtures:load --env=test --no-interaction
```
Cette commande remplit la base de test avec des données d'exemple.

📌 Lancer tous les tests
Une fois les 4 étapes précédentes terminées, exécute cette commande pour lancer les tests :

```sh
docker compose exec app php bin/phpunit
```

### 🔍 Analyse du Code avec PHPStan
PHPStan permet de détecter les erreurs potentielles avant l'exécution du code.
```sh
docker compose exec app composer require --dev phpstan/phpstan
```

🚀  Exécute PHPStan pour analyser le code :
```sh
docker compose exec app vendor/bin/phpstan analyse src --level=max
``` 

### 📚 Documentation de l'API avec Swagger
L’API est documentée avec Swagger UI et est accessible ici :
http://127.0.0.1:8000/api/doc

Tu peux aussi récupérer la documentation au format JSON :
```sh
docker compose exec app php bin/console nelmio:apidoc:dump --format=json > openapi.json
``` 