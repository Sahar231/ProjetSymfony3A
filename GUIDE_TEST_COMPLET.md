# 🧪 Guide de Test : Nouvelles Fonctionnalités Sprint Final

Ce guide détaille les étapes pour valider chaque nouvelle brique implémentée dans votre plateforme.

---

## 🛠️ Étape Préalable : Mise à jour de la Base
Avant de commencer, vous devez synchroniser la structure de votre base de données pour supporter l'héritage (STI) :
1. Ouvrez votre terminal.
2. Exécutez :
   ```bash
   php bin/console doctrine:schema:update --force
   ```

---

## 1. Test de l'Héritage (STI) & CRUD Admin
L'objectif est de vérifier que les utilisateurs sont créés avec la bonne "classe" PHP.
1. Connectez-vous en tant qu'**Admin** (`admin@test.com`).
2. Allez dans **Users** -> **Add User**.
3. Choisissez le rôle **Student** et remplissez le formulaire.
4. Répétez l'opération pour un **Instructor**.
5. **Vérification DB** : Dans la table `user`, vérifiez la colonne `type`. Vous devriez voir `student` et `instructor` (au lieu de seulement `user`).

---

## 2. Test de la Pagination et du Tri
1. Allez dans la liste des **Students** ou **Instructors**.
2. **Tri** : Cliquez sur les en-têtes de colonnes (Full Name, Email, Joined At).
   - *Vérification* : La liste doit se recharger avec l'ordre ASC ou DESC.
3. **Pagination** : Si vous avez plus de 8 utilisateurs, les boutons de page apparaîtront en bas.
   - *Vérification* : Cliquez sur "Next" ou une page spécifique pour naviguer.
4. **Recherche** : Utilisez la barre de recherche.
   - *Vérification* : La pagination et le tri doivent rester actifs même après une recherche.

---

## 3. Test de l'Exportation PDF
1. Dans la liste des étudiants ou instructeurs, cliquez sur le bouton **"Export to PDF"**.
2. **Vérification** : Un fichier PDF doit se télécharger automatiquement.
3. Ouvrez le PDF : il doit contenir le tableau des utilisateurs filtrés selon votre recherche/tri actuel.

---

## 4. Test de Google Sign-In
> [!IMPORTANT]
> Assurez-vous d'avoir rempli vos credentials dans `.env.local`.

1. Allez sur la page de **Login**.
2. Cliquez sur le bouton **"Login with Google"**.
3. Connectez-vous avec un compte Google.
4. **Validation PHP** : Le système vérifie l'email. Si c'est votre première connexion, un compte `Student` est créé automatiquement.
5. **Vérification** : Vous devriez être redirigé vers la Home (ou le dashboard étudiant).

---

## 🚩 En cas de problème
- **Erreur 500 au Login Google** : Vérifiez que vos `Client ID` et `Secret` sont corrects dans `.env.local`.
- **Tri ne fonctionne pas** : Vérifiez que vous avez bien mis à jour le schéma de la base de données.
- **PDF vide** : Vérifiez que vous avez des utilisateurs correspondants aux filtres actuels.
