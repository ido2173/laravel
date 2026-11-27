# Session 11 - Défi

/mes-articles ne montre que mes articles, brouillons compris : oui
Un visiteur qui ouvre /mes-articles arrive sur /connexion : oui
Le formulaire de connexion utilise l'option « Se souvenir de moi » : oui
Les tests AuthTest et AuthorizationTest vérifient ces comportements : oui

Pourquoi partir de `$request->user()->posts()` plutôt que de `Post::where('user_id', $request->user()->id)` ?

La relation limite la requête à l'utilisateur authentifié et évite de faire dépendre la propriété de l'article d'une valeur de formulaire ou d'URL.

Pourquoi la case remember ne doit-elle pas être dans le tableau passé à `Auth::attempt()` ?

Les clés du tableau sont utilisées comme critères de recherche des colonnes utilisateur, sauf le mot de passe. `remember` n'est pas une colonne de `users` ; l'option doit être passée séparément comme second argument.
