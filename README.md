# Test Drupal

## Description du module
Le module jcblock contient à la fois le plugin nécessaire à l'affichage de 3 événements sur les pages événements et à la dépublication des événements passés.

## Installation
Une fois le module activé il faut placer le block **Autres événements** sur la région souhaitée en le limitant au type de contenu 'Événement'.

## Versions
Dans une première version j'ai envoyé au bloc le code html nécessaire à l'affichage, dans une V2 je suis passé par un **template twig** dédié

## Temps passé, difficultés
Une fois le site en place, j'ai passé 1h / 1h30 sur le développement du block en V1 (qui fonctionnait alors) et une demi-heure de plus pour la mise en place du template, l'amélioration du code et des commentaires.

Pour le pluggin de dépublication j'ai passé un temps équivalent, mais je pourrais être désormais plus efficace.
En effet je n'avais jamais utilisé de **QueueWorker**, il a donc fallu au préalable que je me renseigne sur leurs fonctionnements.

Heureusement l'excellent site adimeo.fr était là pour m'aider ;)

J'ai bloqué pas mal de temps sur la création de l'objet $queue et ai fini par employer une autre méthode que celle utilisée sur le site adimeo, je ne suis pas certain de mon choix, j'espère que nous aurons l'occasion d'en discuter.

## Remarque
Une fois la tache cron en place, le filtre *dont la date de fin n'est pas dépassée* sur les événements affichés dans le bloc n'est plus vraiment nécessaire :)