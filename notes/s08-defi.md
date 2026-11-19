# Session 08 - Défi

La route posts.tag utilise {tag:slug} : oui 
La page d'un tag n'affiche ni brouillon ni article d'un autre tag : oui 
Les tags des cartes sont des liens : oui 
L'accueil n'affiche que les tags qui ont au moins un article publié : oui
Les trois nouveaux tests passent et php artisan test est vert : oui 

Pourquoi filtrer les tags vides après get() plutôt que d'afficher (0) ?
Combien de requêtes ajoute la liste des tags populaires, et pourquoi pas une par tag ?
Pour afficher uniquement les tags qui possèdent au moins un article publié.

Un tag avec zéro article n'apporte rien à la liste des tags populaires. On évite donc d'afficher des tags inutiles avec (0).
Une seule requête SQL supplémentaire, si on récupère les tags avec leurs compteurs grâce à withCount().