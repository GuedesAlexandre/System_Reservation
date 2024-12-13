# Documentation de l'API de Gestion des Réservations

Cette documentation décrit les différentes étapes pour faire fonctionner l'application et détaille les endpoints de l'API avec l'authentification **Basic Auth** via le header `Authorization`.

---

## **1. Mise en Place et Fonctionnement de l'Application**

### **1.1. Changement de Base de Données**

1. **Configurer la connexion à la base de données** :  
   Dans le fichier `.env`, modifiez la ligne `DATABASE_URL` pour qu'elle pointe vers votre base de données :

   ```dotenv
   DATABASE_URL="mysql://username:password@127.0.0.1:3306/gestion_reservations"
   ```

   Remplacez `username`, `password`, et `gestion_reservations` par les valeurs correspondantes.

2. **Créer la base de données** :  
   Si la base de données n'existe pas encore, exécutez la commande suivante pour la créer :
   ```bash
   php bin/console doctrine:database:create
   ```

### **1.2. Lancer la Commande de Migration et Migrer les Données**

1. **Générer les migrations** :  
   Les entités étant déjà défini, générez les migrations qui mettront à jour la base de données :

   ```bash
   php bin/console make:migration
   ```

1. **Appliquer les migrations** :  
   Exécutez les migrations pour que les tables soient créées dans votre base de données :
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

### **1.3. Charger des Fixtures**

Pour charger des données initiales dans votre application qui sont vos deux users de test, utilisez la commande `load` des fixtures.

1. **Charger les fixtures** :  
   lancez cette commande pour insérer les données dans la base de données :
   ```bash
   php bin/console doctrine:fixtures:load
   ```

---

## **2. Documentation des Endpoints API avec Basic Auth**

### **2.1. Authentification avec Basic Auth**

1. **Format de l'header Authorization (Basic Auth)** :  
   Pour chaque requête API qui nécessite une authentification, vous devez inclure un header `Authorization` avec les informations de l'utilisateur. Le format est :

   ```
   Authorization: Basic base64_encode(email:password)
   ```

   **Exemple** :  
   Si l'email est `user@example.com` et le mot de passe est `password123`, vous devez encoder cette combinaison en base64. Utilisez cette commande en ligne pour générer le header :

   ```bash
   echo -n "user@example.com:password123" | base64
   ```

   Le résultat sera quelque chose comme : `dXNlckBleGFtcGxlLmNvbTpwYXNzd29yZDEyMw==`.

   Dans votre requête, cela ressemble à ceci :

   ```text
   Authorization: Basic dXNlckBleGFtcGxlLmNvbTpwYXNzd29yZDEyMw==
   ```

   De manière général la collection postman que je vous fourni contient déjà les bons tokens à condition que vous ayez bien fait le fixture:load

   Sinon les token basic au cas où il n'y serait pas présent sont :

   ROLE_ADMIN : Basic YWRtaW5AZXhhbXBsZS5jb206YWRtaW4xMjM=
   ROLE_USER : Basic Basic dXNlckBleGFtcGxlLmNvbTp1c2VyMTIz

---

## **3. Règle de sécurité**

Les endpoints qui sont accessibles pour le rôle ["ROLE_ADMIN"] sont :

GET :: http://localhost:8000/api/user
POST :: http://localhost:8000/api/user/new
DELETE :: http://localhost:8000/api/user/:id
GET :: http://localhost:8000/api/reservation

Les endpoints qui sont accessibles pour le rôle ["ROLE_USER"] sont :

GET :: http://localhost:8000/api/user/:id
PUT :: http://localhost:8000/api/user/:id/edit
POST :: http://localhost:8000/api/reservation/new
GET :: http://localhost:8000/api/reservation/:id
PUT :: http://localhost:8000/api/reservation/:id/edit
DELETE :: http://localhost:8000/api/reservation/:id

## **4. Les règles de gestion**

• Une plage horaire ne peut être réservée qu’une fois pour une même date.
• Les réservations doivent se faire au moins 24 heures à l’avance.
• Des messages d’erreur clairs pour chaque validation.

## **5. Collection Postman**

Pour tester l'API, vous pouvez utiliser la collection Postman fournie au format JSON. Importez cette collection dans votre interface Postman et utilisez vos identifiants pour les tests via Basic Auth.

Le fichier se trouve à la racine et s'appelle : SystemReservation.json

Ne pas oublier de lancer l'application avec :

```bash
  symfony server:start
```

---
