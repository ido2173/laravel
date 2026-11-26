# Session 10 - Robustesse

TagAdminTest contient les tests du back-office des tags : oui
/posts/categorie/inconnue affiche Catégorie introuvable avec le statut 404 : oui
php artisan test est vert : oui

1. Pourquoi le message de messages() l'emporte-t-il sur celui de lang/fr/validation.php ?

Laravel privilégie le message défini pour un champ et une règle précis dans la Form Request. Le fichier de langue fournit le message générique seulement si la requête n'en définit pas.

2. Pourquoi phpunit.xml fixe-t-il APP_LOCALE ?

Les tests comparent des messages en français. Fixer la locale dans phpunit.xml garantit le même résultat sur chaque poste et sur un clone propre.

3. Pourquoi CategoryController n'a-t-il besoin d'aucun try/catch ?

La Form Request intercepte les données invalides, le middleware bloque les écritures en lecture seule et la liaison de modèle produit une 404 pour une catégorie absente. Laravel rend ces réponses via le gestionnaire d'exceptions.
