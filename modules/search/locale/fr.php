<?php

return array
(
	'search' => array
	(
		'found' => array
		(
			'none' => 'Aucun résultat trouvé.',
			'one' => 'Un résultat trouvé.',
			'other' => ':count résultats trouvés.'
		),

		'more' => array
		(
			'one' => 'Voir le résultat trouvé pour %search',
			'other' => 'Voir les :count résultats trouvés pour %search'
		),

		'label' => array
		(
			'keywords' => 'Mots clé',
			'in' => 'Rechercher dans',
			'search' => 'Rechercher'
		),

		'option.all' => '<Tout>'
	),

	'features_search.config' => array
	(
		'description' => 'Le moteur de recherche se trouve actuellement sur la page <q>:link</q>',

		'description_nopage' => "Il n'y a pas de page définie poue l'affichage des résulats de
		recherche. Si vous souhaitez proposer le moteur de recherche à vos visiteurs, rendez-vous
		dans l'onglet :link, choisissez la page que vous souhaitez dédier à la recherche, changez
		l'éditeur du corps de la page pour <q>Vue</q> et choisissez la vue
		<q>Structure/Rechercher/Rechercher sur le site</q>.",

		'limits_home' => "Nombre de résultats maximum par module lors de la recherche initiale",
		'limits_list' => "Nombre de résultats maximum lors de la recherche ciblée",

		'element.label.scope' => "Portée de la recherche",
		'element.description.scope' => "Sélectionner les modules pour lesquels activer la
		recherche. Ordonner les modules par glisser-déposer pour définir l'ordre dans lequel
		s'effectue la recherche."
	)
);