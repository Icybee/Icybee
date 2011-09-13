<?php

$assets = array
(
	'css' => $path . 'public/page.css'
);

return array
(
	'/home' => array
	(
		'title' => 'Accueil des actualités',
		'provider' => true,
		'assets' => $assets
	),

	'/list' => array
	(
		'title' => 'Liste des actualités',
		'provider' => true,
		'assets' => $assets
	),

	'/view' => array
	(
		'title' => "Détail d'une actualité",
		'provider' => true,
		'assets' => $assets
	),

	'/category' => array
	(
		'title' => "Liste des actualités pour une catégorie",
		'provider' => true,
		'assets' => $assets
	)
);