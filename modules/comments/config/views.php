<?php

$assets = array('css' => $path . 'public/page.css');

return array
(
	'/list' => array
	(
		'title' => "Liste des commentaires associés",
		'assets' => $assets,
		'provider' => true
	),

	'/submit' => array
	(
		'title' => "Formulaire de soumission d'un commentaire",
		'assets' => $assets
	)
);