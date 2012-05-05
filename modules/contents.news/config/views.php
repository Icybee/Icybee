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
		'provider' => 'Icybee\Modules\Content\ViewProvider',
		'assets' => $assets
	),

	'/list' => array
	(
		'title' => 'Liste des actualités',
		'provider' => true,
		'provider' => 'Icybee\Modules\Content\ViewProvider',
		'assets' => $assets
	),

	'/view' => array
	(
		'title' => "Détail d'une actualité",
		'provider' => true,
		'provider' => 'Icybee\Modules\Content\ViewProvider',
		'assets' => $assets
	),

	'/category' => array
	(
		'title' => "Liste des actualités pour une catégorie",
		'provider' => true,
		'provider' => 'Icybee\Modules\Content\ViewProvider',
		'assets' => $assets
	)
);