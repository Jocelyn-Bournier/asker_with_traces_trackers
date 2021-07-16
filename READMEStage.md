**Résumé du stage**

Toutes les fonctions ajoutées ont été commentés sur leur utilité, certaines ont été modifiés pour permettre le fonctionnement.

Les pages ajoutées sont : "personal_stats.html.twig" et "detail_stat_user.html.twig".

-----

**Hiérarchie des fichiers modifiés**

asker/
    - src/
        - SimpleIT/
            - ClaireExerciseBundle/
                - Controller/
                    - Frontend/
                        - StatController.php
                - Repository/
                    - DirectoryRepository.php
                    - Exercise/
                        - CreatedExercise/
                            - AnswerRepository.php
                            - AttemptRepository.php
                - Resources/
                    - views/
                        - Frontend/
                            - ajax_detail_stat_directory.html.twig
                            - detail_stat_user.html.twig
                            - personal_stats.html.twig
                    - config/
                        - routing/
                            - frontend/
                                - stats.yml
                - Service/
                    - Directory/
                        - DirectoryService.php

----

**Features**

Dans l'onglet statistiques, pour chaque dossier, un tableau d'étudiants appartenant à ce dernier est affiché.

Des statistiques concernant les étudiants de ce dossier sont affichées, on peut notamment trier les colonnes en cliquant sur le nom de la colonne (ascendant puis descendant).
En cliquant sur la petite loupe d'action on accède aux statistiques détaillées pour l'étudiant voulu.

Toutes les informations concernant l'étudiant sont affichées.
Possibilité d'ajouter, modifier, supprimer un filtre de temps.

Une timeline qui affiche dans le temps les réponses de l'étudiant par modèle.
Passez votre curseur sur les réponses pour en savoir les détails.

Deux diagrammes sunburst sont aussi affichés pour voir les différentes moyennes obtenues.
Le premier pour les modèles, et le deuxième pour les sous-dossiers.
Passez votre curseur sur les modèles et dossiers pour en savoir les détails.

En cliquant sur le deuxième diagramme sunburst, vous pourrez voir les modèles de ce sous-dossier dans un autre diagramme sunburst.

Quand vous revenez sur la page précédente, vous retournerez au tableau d'étudiants.
