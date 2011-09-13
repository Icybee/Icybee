<?php

$assets = array('css' => $path . 'public/page.css');

return array
(
	'/home' => array
	(
		'title' => "Accueil des articles",
		'provider' => true,
		'assets' => $assets
	),

	'/list' => array
	(
		'title' => "Liste des articles",
		'provider' => true,
		'assets' => $assets
	),

	'/view' => array
	(
		'title' => "Détail d'un article",
		'provider' => true,
		'assets' => $assets
	),

	'/archives' => array
	(
		'title' => "Archives des articles",
		'provider' => true,
		'assets' => $assets
	),

	'/categories' => array
	(
		'title' => "Catégories des articles",
		'assets' => $assets
	)
);