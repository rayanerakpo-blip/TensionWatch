# TensionWatch

Application web de suivi médical destinée aux proches aidants de patients atteints de troubles cardiovasculaires.

Projet réalisé par **Rayane Akpo**, étudiant en 1ère année de BTS SIO (option SLAM), dans le cadre d'un stage en autonomie.

**Démo en ligne :** https://tensionwatch.wuaze.com

---

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Stack technique](#stack-technique)
- [Structure du projet](#structure-du-projet)
- [Base de données](#base-de-données)
- [Installation en local](#installation-en-local)
- [Déploiement en production](#déploiement-en-production)
- [Sécurité](#sécurité)
- [Limites connues et pistes d'amélioration](#limites-connues-et-pistes-damélioration)
- [Auteur](#auteur)

---

## Fonctionnalités

### Authentification
- Inscription avec hashage sécurisé du mot de passe (`password_hash`)
- Connexion avec vérification (`password_verify`) et redirection après succès
- Déconnexion avec destruction complète de la session
- Point d'entrée unique (`index.php`) redirigeant selon l'état de connexion

### Profil patient
- Création et modification des informations du patient suivi (identité, date de naissance, groupe sanguin, poids, médecin traitant, antécédents médicaux)
- Un formulaire unique qui bascule automatiquement entre création et modification selon qu'un patient existe déjà

### Suivi de la tension artérielle
- Ajout de mesures (systolique, diastolique, date/heure, commentaire)
- Validation des valeurs saisies dans une plage physiologiquement possible (systolique : 40-300, diastolique : 20-200)
- Impossibilité de saisir une mesure à une date future
- Évaluation automatique du niveau de gravité de chaque mesure (normale / élevée / hypertension / crise hypertensive), sur la base des seuils médicaux usuels (OMS / HAS)
- Alerte visuelle sur le tableau de bord en cas de dernière mesure anormale (bandeau clignotant en cas de crise hypertensive)
- Graphique d'évolution (Chart.js) et historique complet avec statut par mesure

### Traitements
- Ajout et suppression de médicaments (nom, dosage, fréquence, heure de prise, date de début)
- Affichage des traitements en cours

### Effets indésirables
- Signalement d'un effet indésirable (type, intensité sur 10, fréquence, description, date)
- Impossibilité de déclarer un effet à une date future
- Historique avec badges colorés selon l'intensité (basse / moyenne / haute)

### Rappels
- Création de rappels (prise de médicament, rendez-vous médical, etc.) avec titre, description, date et heure
- Contrairement aux mesures de tension et aux effets indésirables, les rappels acceptent des dates futures (c'est leur fonction première)
- Séparation automatique entre rappels à venir et rappels passés

### Conseils
- Contenu informatif organisé par thématique (alimentation, activité physique, gestion du stress, suivi du traitement, signes d'urgence)

### Contacts d'urgence
- Numéros d'urgence nationaux (SAMU, Pompiers, numéro d'urgence européen)
- Affichage du médecin traitant et des antécédents médicaux du patient
- Ajout de contacts d'urgence personnalisés (nom, téléphone, lien avec le patient)

### Compte utilisateur
- Modification des informations personnelles (nom, prénom, email) avec vérification d'unicité de l'email
- Changement de mot de passe avec vérification de l'ancien mot de passe

### Export du dossier patient
- Page dédiée à l'impression regroupant informations patient, historique de tension, traitements et effets indésirables
- Export en PDF via la fonction d'impression du navigateur (« Enregistrer au format PDF »)

---

## Stack technique

| Domaine | Technologie |
|---|---|
| Langage serveur | PHP 8 (PDO) |
| Base de données | MySQL |
| Frontend | HTML5, CSS3, JavaScript |
| Framework CSS | Bootstrap 5 |
| Graphiques | Chart.js |
| Police | Comfortaa |
| Environnement de développement | AMPPS (Windows) |
| Hébergement | InfinityFree |
| Versioning | Git / GitHub |

**Charte graphique :** teal `#048b9a` et vert `#34c759` sur fond blanc.

---

## Structure du projet

```
tensionwatch/
├── auth/
│   ├── login.php           # Connexion
│   ├── register.php        # Inscription
│   └── logout.php          # Déconnexion
├── config/
│   ├── database.php         # Connexion PDO (exclu du dépôt Git)
│   ├── database.example.php # Modèle de configuration à copier
│   └── fonctions.php        # Fonctions communes (évaluation de la tension, etc.)
├── pages/
│   ├── accueil.php          # Tableau de bord
│   ├── patient.php          # Création / modification du profil patient
│   ├── tension.php          # Suivi de la tension artérielle
│   ├── medicaments.php      # Gestion des traitements
│   ├── effets.php           # Effets indésirables
│   ├── rappels.php          # Rappels (médicaments, rendez-vous)
│   ├── conseils.php         # Conseils d'accompagnement
│   ├── urgences.php         # Contacts d'urgence
│   ├── compte.php           # Gestion du compte utilisateur
│   └── export_pdf.php       # Export imprimable du dossier patient
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── index.php                 # Point d'entrée, redirige selon l'état de connexion
├── tensionwatch.sql          # Structure complète de la base de données
├── .gitignore
└── README.md
```

> **Remarque sur la casse des fichiers :** le dossier `pages/` est en minuscule sur l'ensemble du projet et de l'hébergement (Linux étant sensible à la casse, contrairement à Windows). Tous les chemins et liens internes du code respectent cette casse.

---

## Base de données

Le schéma complet est disponible dans `tensionwatch.sql`. Tables principales :

| Table | Description |
|---|---|
| `utilisateurs` | Comptes des proches aidants |
| `patients` | Patients suivis, rattachés à un utilisateur |
| `tensions` | Historique des mesures de tension artérielle |
| `medicaments` | Traitements du patient |
| `effets_indesirables` | Effets indésirables signalés |
| `contacts_urgence` | Contacts d'urgence personnalisés |
| `rappels` | Rappels (prise de médicament, rendez-vous) |

Toutes les tables liées à un patient référencent `patients.id` par clé étrangère.

---

## Installation en local (XAMPP / AMPPS / WAMP)

1. **Cloner le dépôt** dans le dossier `htdocs` de votre serveur local :
   ```bash
   git clone https://github.com/votre-utilisateur/tensionwatch.git
   ```

2. **Créer une base de données MySQL** nommée `tensionwatch` (via phpMyAdmin ou en ligne de commande).

3. **Importer la structure** depuis `tensionwatch.sql` (onglet « Importer » de phpMyAdmin).

4. **Copier le fichier de configuration modèle** :
   ```bash
   cp config/database.example.php config/database.php
   ```

5. **Renseigner vos identifiants locaux** dans `config/database.php` (hôte, nom de base, utilisateur, mot de passe).

6. **Démarrer Apache et MySQL**, puis accéder à :
   ```
   http://localhost/tensionwatch/
   ```

---

## Déploiement en production

Le projet est hébergé sur **InfinityFree**, un hébergeur gratuit PHP + MySQL.

1. Créer un compte et un site sur InfinityFree, récupérer le sous-domaine attribué.
2. Créer une base de données MySQL depuis le vPanel et importer `tensionwatch.sql` via phpMyAdmin.
3. Adapter `config/database.php` avec les identifiants MySQL fournis par l'hébergeur (hôte, base, utilisateur, mot de passe).
4. Envoyer l'ensemble des fichiers dans le dossier `htdocs` via le Gestionnaire de fichiers ou en FTP, **en conservant l'arborescence exacte** (`config/`, `pages/`, `auth/`, `assets/css/`, `assets/js/`).
5. Vérifier que la casse des noms de fichiers et des liens internes correspond exactement (Linux est sensible à la casse).

> **Note sur la réputation du domaine :** les sous-domaines gratuits partagés (comme `wuaze.com`) peuvent apparaître sur des listes de vérification anti-phishing en raison d'abus par des tiers, indépendamment du contenu du site. À garder en tête si le lien est partagé dans un cadre professionnel.

---

## Sécurité

- Mots de passe hashés avec `password_hash()` et vérifiés avec `password_verify()` — jamais stockés en clair
- Requêtes SQL exclusivement préparées (PDO) contre les injections SQL
- Vérification de session (`$_SESSION['utilisateur_id']`) sur toutes les pages protégées, avec redirection vers la connexion sinon
- Toutes les opérations de suppression vérifient que la ressource appartient bien au patient de l'utilisateur connecté, avant toute action en base
- `config/database.php` exclu du dépôt Git via `.gitignore` afin de ne jamais exposer les identifiants réels
- Échappement systématique des sorties HTML (`htmlspecialchars`) contre les failles XSS
- Changement de mot de passe soumis à la vérification de l'ancien mot de passe

---

## Limites connues et pistes d'amélioration

- Un utilisateur ne peut suivre qu'**un seul patient** ; la prise en charge de plusieurs patients par aidant nécessiterait un sélecteur de patient sur plusieurs pages
- Le lien « Mot de passe oublié » n'est pas encore fonctionnel
- L'export PDF repose sur l'impression navigateur plutôt que sur une génération PDF native (ex : FPDF)
- `assets/js/main.js` est prévu pour de futures interactions côté client (validation en temps réel, etc.), non encore développées

---

## Auteur

**Rayane Akpo** — Étudiant BTS SIO, option SLAM
