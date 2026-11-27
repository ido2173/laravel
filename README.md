## État actuel (tag lab-06)

- Inscription, connexion avec option « Se souvenir de moi » et déconnexion manuelles ; les mots de passe sont hachés et les sessions régénérées.
- Les comptes ont le rôle `author` par défaut. Après `php artisan migrate:fresh --seed`, `test@example.com` (mot de passe `password`) est administrateur.
- Les visiteurs lisent les articles ; les écritures et le back-office exigent une connexion. Chaque nouvel article est associé à son auteur.
- Les auteurs gèrent leurs propres articles ; les administrateurs peuvent gérer tous les articles et les catégories.
- Gate `manage-categories` et `PostPolicy` appliquent les droits dans les contrôleurs, Form Requests et vues.
- La page « Mes articles » liste les articles et brouillons de l'utilisateur connecté.
- Messages de validation en français (`lang/fr/validation.php`, `APP_LOCALE=fr`) et pages 404/503 personnalisées.
- Tests Pest : `AuthTest`, `AuthorizationTest`, `PostPagesTest`, `PostWriteTest`, `PostFeedbackTest`, `PostRelationsTest`, `PostTaxonomyTest`, `CategoryAdminTest`, `TagAdminTest` et `ErrorPagesTest`.

## Routes disponibles

| Méthode | URI | Nom | Action |
|---|---|---|---|
| GET | `/` | `home` | `PageController@home` |
| GET | `/a-propos` | `about` | `PageController@about` |
| GET | `/heure` | `time` | `PageController@time` |
| GET | `/posts` | `posts.index` | `PostController@index` |
| GET | `/posts/archive/{year}` | `posts.archive` | `PostController@archive` |
| GET | `/posts/categorie/{category:slug}` | `posts.category` | `PostController@byCategory` |
| GET | `/posts/tag/{tag:slug}` | `posts.tag` | `PostController@byTag` |
| GET | `/posts/create` | `posts.create` | `PostController@create` |
| POST | `/posts` | `posts.store` | `PostController@store` |
| GET | `/posts/{post}` | `posts.show` | `PostController@show` |
| GET | `/posts/{post}/edit` | `posts.edit` | `PostController@edit` |
| PUT/PATCH | `/posts/{post}` | `posts.update` | `PostController@update` |
| DELETE | `/posts/{post}` | `posts.destroy` | `PostController@destroy` |
| PATCH | `/posts/{post}/publication` | `posts.publication` | `PostController@togglePublication` |
| GET | `/admin/categories` | `admin.categories.index` | `CategoryController@index` |
| GET | `/admin/categories/create` | `admin.categories.create` | `CategoryController@create` |
| POST | `/admin/categories` | `admin.categories.store` | `CategoryController@store` |
| GET | `/admin/categories/{category}/edit` | `admin.categories.edit` | `CategoryController@edit` |
| PUT/PATCH | `/admin/categories/{category}` | `admin.categories.update` | `CategoryController@update` |
| DELETE | `/admin/categories/{category}` | `admin.categories.destroy` | `CategoryController@destroy` |
| GET | `/admin/tags` | `admin.tags.index` | `TagController@index` |
| GET | `/admin/tags/create` | `admin.tags.create` | `TagController@create` |
| POST | `/admin/tags` | `admin.tags.store` | `TagController@store` |
| GET | `/admin/tags/{tag}/edit` | `admin.tags.edit` | `TagController@edit` |
| PUT/PATCH | `/admin/tags/{tag}` | `admin.tags.update` | `TagController@update` |
| DELETE | `/admin/tags/{tag}` | `admin.tags.destroy` | `TagController@destroy` |
| GET | `/inscription` | `register` | `Auth\RegisterController@create` |
| POST | `/inscription` | `register.store` | `Auth\RegisterController@store` |
| GET | `/connexion` | `login` | `Auth\LoginController@create` |
| POST | `/connexion` | `login.store` | `Auth\LoginController@store` |
| POST | `/deconnexion` | `logout` | `Auth\LoginController@destroy` |
| GET | `/mes-articles` | `my-posts` | `PostController@myPosts` |
| GET | `/admin/users/roles` | `admin.users.roles.index` | `Admin\UserRoleController@index` |
| PATCH | `/admin/users/{user}/role` | `admin.users.roles.update` | `Admin\UserRoleController@update` |

## Données de démonstration

Après un clone, reconstruire la base avec `php artisan migrate:fresh --seed`. Attention : cette commande vide la base avant de la remplir.

### Session 07

![Liste des articles avec leur catégorie, leur auteur et leurs tags](screenshots/s07-liste-relations.png)

![Page d'un article avec ses tags et les articles de la même catégorie](screenshots/s07-detail-tags.png)

### Session 10

Le back-office, les messages de validation en français et les pages d'erreur personnalisées sont couverts par les tests Pest.
Les captures `screenshots/s10-validation.png` et `screenshots/s10-404.png` restent à prendre dans le navigateur si elles sont requises pour le livrable.

### Session 11

Les contrôles d'accès, l'authentification, les rôles et les articles personnels sont couverts par les tests Pest.
Les captures `screenshots/s11-connexion.png` et `screenshots/s11-interdit.png` restent à prendre dans le navigateur si elles sont requises pour le livrable.

### Session 12 (tag `lab-06b`)

- Le middleware `admin` protège toutes les routes `/admin`.
- Les Gates `manage-categories` et `manage-tags` et les Form Requests appliquent les droits d'administration.
- La liste publique utilise `Post::visibleTo()` : les visiteurs voient les articles publiés, les auteurs leurs propres brouillons et les administrateurs tous les articles.
- Les Policies autorisent la consultation des articles publiés et des brouillons propres, et protègent la création.
- Le défi de gestion des rôles ajoute une page réservée aux administrateurs ; un administrateur ne peut pas modifier son propre rôle.
- `AdminAccessTest`, `PostVisibilityTest` et `UserRoleTest` couvrent ces règles.

Les captures `screenshots/s12-admin-interdit.png` et `screenshots/s12-brouillons.png` restent à prendre dans le navigateur si elles sont requises pour le livrable.