# 📰 Mandarin En Travées – Réseau de Fact-Checking Collaboratif

Bienvenue dans *Mandarin En Travées*, une application web qui s'inspire d'un réseau social, dédiée à la vérification participative de l'information.  
Les utilisateurs peuvent y soumettre des affirmations douteuses, consulter les vérifications précédentes, voter, commenter, et contribuer à un journalisme collaboratif 💬🔍

---

## 🚀 Objectif du projet

Cette application Symfony a été réalisée dans le cadre d’un projet universitaire. Elle permet de :
- Créer un compte et se connecter
- Soumettre une demande de fact-checking
- Consulter et voter sur les demandes existantes
- Contribuer à des vérifications
- Suivre les statistiques de participation et rapports
- Accéder à une **interface d’administration** pour gérer les utilisateurs, catégories et contenus
- Utiliser une **interface de modération** pour vérifier les demandes et contributions

---

## 🛠️ Installation & Paramétrage de l’Environnement

### 1. 📦 Cloner le projet

```bash
git clone git@github.com:luceg02/MandarinEnTravees.git
cd MandarinEnTravees
```
---

### 2. 💡 Prérequis

Assurez-vous d’avoir les éléments suivants installés :

- PHP 8.2 ou plus
- Composer
- Symfony CLI (`symfony`)
- Un serveur MySQL
---

### 3. 📂 Installation des dépendances

À la racine du projet, exécute la commande :

```bash
composer install
```

Cela installera toutes les librairies nécessaires indiquées dans le `composer.json`.

---

### 4. ⚙️ Configuration de l’environnement

1. Copiez le fichier `.env` :

```bash
cp .env .env.local
```

2. Modifiez la ligne de connexion à la base de données dans `.env.local` :

```dotenv
DATABASE_URL="mysql://votre_user:mot_de_passe@127.0.0.1:3306/mandarin_en_travees"
```

🧠 *Assurez-vous d’avoir une base de données locale nommée `mandarin_en_travees`.*

---

### 5. 🧱 Import de la base de données

À la racine du projet, vous trouverez un export SQL (`mandarin_en_travees.sql` ou similaire).

Dans votre outil de gestion de base de données (ex: phpMyAdmin, Adminer, DBeaver), **importez ce fichier** dans la base de données que vous venez de créer.

---

### 6. 📡 Démarrer le serveur

Lancez le serveur Symfony avec :

```bash
symfony server:start
```

Le projet sera alors accessible à l’adresse affichée dans la console, généralement `https://127.0.0.1:8000`

---