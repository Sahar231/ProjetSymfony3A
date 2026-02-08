# Plan détaillé – Gestion des formations (certifications)

Site éducatif type Coursera/Udemy. Ce document décrit **étape par étape** la mise en place du module Formations (PHP/Symfony), avec les commandes à exécuter et les points d’intégration avec User/Student (tâche de ton collègue) et Stripe.

---

## Rappel des acteurs et règles métier

| Acteur | Droits |
|--------|--------|
| **Enseignant** | Proposer une formation (formulaire complet). La formation n’apparaît qu’après validation par l’admin. CRUD sur ses propres formations (avant/après validation selon règles). |
| **Étudiant** | Voir les formations disponibles (description, prix). Créer un wallet (coordonnées + carte). 100 crédits offerts à la 1ère inscription wallet. Acheter une formation (paiement Stripe). Passer le quiz (QCM, temps limité, questions tirées aléatoirement). Obtenir un badge (affiché via `list_badges()` dans le profil – tâche collègue). |
| **Admin** | Dashboard formations : voir toutes les formations, CRUD, **approuver** les formations proposées par les enseignants. Voir les paiements effectués. |

**Quiz**  
- QCM, chronomètre par formation (durée définie par enseignant ou admin).  
- À la fin du temps, le quiz se ferme automatiquement.  
- Nombre total de questions et nombre de questions affichées à l’étudiant définis dans le formulaire enseignant ; tirage aléatoire des questions depuis la base.

---

## Prérequis

- PHP 8.1+, Composer, Symfony CLI.
- Base de données configurée (`.env` : `DATABASE_URL`).
- **Coordination** : entité `User` (ou équivalent) et entité `Student` avec `list_badges()` gérées par le collègue « User ». Tu te baseras sur ces entités pour les relations (Formation → créateur, Wallet/Enrollment → Student).

---

## Phase 1 – Entités et base de données

### 1.1 Vérifier / compléter l’entité Formation

Tu as déjà `Formation` avec : `title`, `description`, `content`, `price`, `isApproved`, `createdAt`.

À ajouter (si pas encore fait) :

- Lien vers le **créateur** (enseignant) : par ex. `User $createdBy` (ou `Instructor` selon le modèle du projet).
- Lien **1–1** vers le **Quiz** de la formation : `Quiz $quiz` (une formation = un quiz de certification).

**Commande utile (si tu génères des getters/setters) :**

```bash
php bin/console make:entity Formation
```

Répondre aux questions pour ajouter les propriétés manquantes, ou éditer `src/Entity/Formation.php` à la main.

### 1.2 Créer l’entité Quiz

Un quiz est attaché à une formation. Champs utiles :

- `formation` (OneToOne avec Formation, côté Formation ou Quiz selon ton choix).
- `durationMinutes` (integer) : durée en minutes du chronomètre.
- `totalQuestions` (integer) : nombre total de questions en base pour cette formation.
- `displayedQuestions` (integer) : nombre de questions affichées à l’étudiant (tirage aléatoire).

**Commande :**

```bash
php bin/console make:entity Quiz
```

Exemple de propriétés : `formation` (OneToOne), `durationMinutes`, `totalQuestions`, `displayedQuestions`.

### 1.3 Créer l’entité QuizQuestion

Pour stocker les questions QCM (une question avec plusieurs choix, une bonne réponse).

Propriétés suggérées :

- `quiz` (ManyToOne vers Quiz).
- `questionText` (text).
- `optionA`, `optionB`, `optionC`, `optionD` (string, ou une seule colonne JSON pour plus de flexibilité).
- `correctAnswer` (string, ex. "A", "B", "C", "D").

**Commande :**

```bash
php bin/console make:entity QuizQuestion
```

### 1.4 Entité Wallet (étudiant)

Pour le portefeuille : crédits + lien Stripe.

- `student` (ManyToOne vers Student – entité du collègue).
- `credits` (float ou integer).
- `stripeCustomerId` (string, nullable) pour Stripe.
- Coordonnées : `firstName`, `lastName`, `phone` (ou dans une entité liée / dans User selon votre choix).

**Commande :**

```bash
php bin/console make:entity Wallet
```

### 1.5 Entité Payment

Pour tracer les paiements (achats de formations).

- `student` (ManyToOne vers Student).
- `formation` (ManyToOne vers Formation).
- `amount` (float).
- `stripePaymentIntentId` ou `stripeSessionId` (string, nullable).
- `createdAt` (datetime).

**Commande :**

```bash
php bin/console make:entity Payment
```

### 1.6 Entité FormationEnrollment (inscription étudiant à une formation)

Pour savoir quels étudiants ont acheté / obtenu quelle formation (et donc badge).

- `student` (ManyToOne vers Student).
- `formation` (ManyToOne vers Formation).
- `purchasedAt` (datetime).
- `quizPassed` (boolean) : quiz réussi ou non.
- `quizCompletedAt` (datetime, nullable).

Le **badge** côté profil étudiant peut s’appuyer sur les formations où `quizPassed = true` (ou simplement « inscrit » selon votre règle). Le collègue utilisera ça pour `list_badges()`.

**Commande :**

```bash
php bin/console make:entity FormationEnrollment
```

### 1.7 Entité QuizAttempt (optionnel mais recommandé)

Pour enregistrer chaque tentative de quiz (réponses, temps, succès).

- `student` (ManyToOne).
- `quiz` (ManyToOne).
- `startedAt`, `submittedAt` (datetime).
- `score` (integer ou float).
- `passed` (boolean).
- Réponses stockées (ex. JSON ou table détaillée).

**Commande :**

```bash
php bin/console make:entity QuizAttempt
```

---

## Phase 2 – Migrations

Après avoir créé/modifié toutes les entités :

```bash
php bin/console make:migration
```

Vérifier le fichier généré dans `migrations/`, puis :

```bash
php bin/console doctrine:migrations:migrate
```

(Pour SQLite, pas de mot de passe ; pour MySQL/PostgreSQL, la DB doit exister et `DATABASE_URL` correcte.)

---

## Phase 3 – Formulaires Symfony (Forms)

### 3.1 Formulaire Formation (enseignant / admin)

Champs : titre, description, contenu, prix, quiz (nombre total de questions, nombre affiché, durée en minutes), et les questions du quiz (liste de QuizQuestion).

**Commandes :**

```bash
php bin/console make:form FormationType Formation
php bin/console make:form QuizType Quiz
```

Ensuite, soit un formulaire imbriqué (FormationType contient QuizType + collection de QuizQuestionType), soit une page en plusieurs étapes (1) Formation + Quiz, (2) Liste des questions. À adapter selon l’UX.

Pour les questions QCM :

```bash
php bin/console make:form QuizQuestionType QuizQuestion
```

### 3.2 Formulaire Wallet (étudiant)

Champs : nom, prénom, téléphone, infos carte (ou utilisation de Stripe Elements côté front, pas de stockage carte côté vous).

```bash
php bin/console make:form WalletType Wallet
```

---

## Phase 4 – Contrôleurs et logique métier

### 4.1 Frontend – Enseignant

- **Liste des formations** : `Instructor\FormationController::list()` – formations créées par l’enseignant connecté (avec statut approuvé ou en attente).
- **Créer une formation** : `create()` – afficher le formulaire Formation + Quiz + nombre de questions + durée. À la soumission : créer `Formation` (isApproved = false), créer `Quiz`, créer les `QuizQuestion`. Rediriger vers « Formation ajoutée, en attente de validation admin ».
- **Modifier / Supprimer** : edit/delete sur les formations dont il est le créateur (et selon vos règles : avant ou après approbation).

Tu peux t’appuyer sur les contrôleurs existants dans `src/Controller/Instructor/FormationController.php` et y injecter les services (repository Formation, Quiz, etc.) et les formulaires.

### 4.2 Frontend – Étudiant

- **Liste des formations disponibles** : formations approuvées, avec description et prix (déjà prévu dans le contrôleur public ou `Student\FormationController`).
- **Wallet** : une route dédiée (ex. `Student/WalletController`) – création/mise à jour du wallet, formulaire coordonnées + intégration Stripe (création du client Stripe, 100 crédits à la première création).
- **Achat formation** : route du type « s’inscrire à une formation » – vérifier les crédits (ou paiement Stripe direct), créer `Payment` et `FormationEnrollment`, déduire les crédits si besoin.
- **Passer le quiz** : route type `student/formation/{id}/quiz` – afficher les questions (tirage aléatoire de `displayedQuestions` parmi `totalQuestions`), chronomètre côté front (JavaScript), à la fin du temps ou à la soumission : enregistrer `QuizAttempt`, mettre à jour `FormationEnrollment.quizPassed` si réussi. Le badge côté profil viendra de `list_badges()` (collègue) basé sur ces données.

### 4.3 Backend – Admin

- **Dashboard formations** : liste de toutes les formations (approuvées et en attente).
- **Approuver** : bouton/route pour passer `formation.isApproved = true` (la formation devient visible côté front).
- **CRUD** : ajout, modification, suppression de formations (et du quiz associé si besoin).
- **Paiements** : liste des `Payment` (avec student, formation, amount, date).

Tu peux compléter `src/Controller/Admin/FormationController.php` et éventuellement un `Admin/PaymentController.php`.

---

## Phase 5 – Stripe (paiement et wallet)

### 5.1 Installation

```bash
composer require stripe/stripe-php
```

### 5.2 Configuration

Dans `.env` (à ne pas commiter en clair en prod) :

```env
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...   # pour webhooks
```

Référencer ces variables dans `config/services.yaml` ou dans les paramètres Symfony (ex. `%env(STRIPE_SECRET_KEY)%`).

### 5.3 Création du wallet (première fois)

- Lors de la création du wallet : créer un **Customer Stripe** avec les infos (nom, prénom, email, téléphone). Stocker `stripeCustomerId` dans `Wallet`.
- À la première création du wallet, créditer 100 crédits (mise à jour du champ `credits`).

### 5.4 Achat d’une formation

- Option A : paiement par **crédits** – vérifier `wallet.credits >= formation.price`, puis déduire les crédits et créer `Payment` + `FormationEnrollment`.
- Option B : paiement **carte** via Stripe – créer un PaymentIntent (ou Checkout Session), rediriger ou utiliser Stripe.js ; en webhook ou en retour de page, créer `Payment` et `FormationEnrollment` et, si vous utilisez des crédits, créditer/déduire le wallet selon votre règle.

Documentation Stripe : [Payment Intents](https://stripe.com/docs/payments/payment-intents), [Checkout](https://stripe.com/docs/payments/checkout).

---

## Phase 6 – Quiz étudiant : chronomètre et fermeture auto

- **Durée** : stockée dans `Quiz.durationMinutes` (remplie par l’enseignant ou l’admin dans le formulaire).
- **Front** : page quiz avec un compte à rebours en JavaScript (ex. affichage « Il vous reste X min »). Quand le temps atteint 0 :
  - Envoyer automatiquement les réponses actuelles au serveur (ex. POST via fetch).
  - Rediriger ou afficher « Temps écoulé » et enregistrer la tentative comme terminée (sans ajouter de réponses après la fin).
- **Back** : la route qui reçoit la soumission du quiz enregistre `QuizAttempt` et met à jour `FormationEnrollment.quizPassed` si le score est suffisant (seuil à définir).

---

## Phase 7 – Sécurité et droits (access_control)

Exemples dans `config/packages/security.yaml` :

```yaml
access_control:
    - { path: ^/admin, roles: ROLE_ADMIN }
    - { path: ^/instructor, roles: ROLE_INSTRUCTOR }
    - { path: ^/student, roles: ROLE_STUDENT }
```

Et dans les contrôleurs : vérifier que l’enseignant ne modifie que ses formations, que l’étudiant ne voit que ses enrollments et ses tentatives de quiz, et que l’admin a accès au dashboard et à l’approbation.

---

## Récapitulatif des commandes Symfony (dans l’ordre)

```bash
# Entités
php bin/console make:entity Formation
php bin/console make:entity Quiz
php bin/console make:entity QuizQuestion
php bin/console make:entity Wallet
php bin/console make:entity Payment
php bin/console make:entity FormationEnrollment
php bin/console make:entity QuizAttempt

# Migrations
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Formulaires
php bin/console make:form FormationType Formation
php bin/console make:form QuizType Quiz
php bin/console make:form QuizQuestionType QuizQuestion
php bin/console make:form WalletType Wallet

# Stripe
composer require stripe/stripe-php

# Utiles pendant le dev
php bin/console cache:clear
php bin/console debug:router
php bin/console doctrine:schema:validate
```

---

## Intégration avec le collègue « User »

- **Student** : il doit fournir une entité `Student` (liée à User ou héritant). Tu l’utiliseras dans `Wallet`, `Payment`, `FormationEnrollment`, `QuizAttempt` (ManyToOne vers Student).
- **Badges** : il implémente `list_badges()` côté Student (ou User) en s’appuyant sur les formations obtenues (ex. formations où `FormationEnrollment.quizPassed = true` pour cet étudiant). Tu t’assures que les données sont bien enregistrées après le quiz.

---

## Fichiers à créer / modifier (résumé)

| Fichier | Action |
|---------|--------|
| `src/Entity/Formation.php` | Compléter (createdBy, quiz) |
| `src/Entity/Quiz.php` | Créer |
| `src/Entity/QuizQuestion.php` | Créer |
| `src/Entity/Wallet.php` | Créer |
| `src/Entity/Payment.php` | Créer |
| `src/Entity/FormationEnrollment.php` | Créer |
| `src/Entity/QuizAttempt.php` | Créer |
| `migrations/VersionXXXX.php` | Générer puis exécuter |
| `src/Form/FormationType.php` | Créer |
| `src/Form/QuizType.php` | Créer |
| `src/Form/QuizQuestionType.php` | Créer |
| `src/Form/WalletType.php` | Créer |
| `src/Controller/Instructor/FormationController.php` | Implémenter CRUD + formulaire avec quiz |
| `src/Controller/Student/FormationController.php` | Liste, détail, inscription (après paiement) |
| `src/Controller/Student/WalletController.php` | Créer (wallet + Stripe) |
| `src/Controller/Student/QuizController.php` | Passer le quiz (tirage aléatoire, chrono) |
| `src/Controller/Admin/FormationController.php` | Liste, CRUD, approbation |
| `src/Controller/Admin/PaymentController.php` | Liste des paiements |
| `templates/` | Adapter les vues (liste formations, formulaire enseignant, quiz étudiant, wallet) |
| `.env` | Ajouter clés Stripe |

Tu peux suivre ce plan étape par étape et m’indiquer à quelle phase tu es (ou me montrer ton code actuel) pour qu’on détaille la suite (par ex. le formulaire enseignant avec les questions, ou l’intégration Stripe précise).
