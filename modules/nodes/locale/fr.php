<?php

return array
(
	'dashboard.title' => array
	(
		'system-nodes-now' => "D'un coup d'oeil",
		'system-nodes-user-modified' => "Vos dernières modifications"
	),

	'element.title' => array
	(
		'is_online' => "Inclure ou exclure l'enregistrement du site"
	),

	'element.description' => array
	(
		'is_online' => "Seuls les enregistrements publiés sont disponibles pour les visiteurs.
		Cependant, les enregistrements non publiés peuvent être disponibles pour les utilisateurs
		qui en ont l'autorisation.",

		'slug' => "Le <q>slug</q> est la version du titre utilisable dans les URL. Écrit en
		minuscules, il ne contient que lettres non accentuées, chiffres et traits d'union. S'il
		est vide lors de l'enregistrement, le <q>slug</q> est automatiquement créé à partir du
		titre.",

		'siteid' => "Parce que vous en avez la permission, vous pouvez choisir le site
		d'appartenance pour l'enregistrement. Un enregistrement appartenant à un site en hérite la
		langue et n'est visible que sur ce site.",

		'user' => "Parce que vous en avez la permission, vous pouvez choisir l'utilisateur
		propriétaire de cet enregistrement."
	),

	'group.legend' => array
	(
		'Admin' => 'Administration',
		'Advanced' => 'Options avancées',
		'Visibility' => 'Visibilité'
	),

	'label' => array
	(
		'is_online' => 'Publié',
		'siteid' => "Site d'appartenance",
		'title' => 'Titre',
		'user' => 'Utilisateur'
	),

	'manager.title' => array
	(
		'constructor' => 'Constructeur',
		'created' => 'Crée le',
		'is_online' => 'Publié',
		'modified' => 'Modifié le',
		'uid' => 'Utilisateur',

		'Translations' => 'Traductions'
	),

	'module_category.other' => 'Autre',
	'module_title.nodes' => 'Nœuds',

	'option' => array
	(
		'save_mode_display' => 'Enregistrer et afficher'
	),

	'titleslugcombo.element' => array
	(
		'auto' => 'auto',
		'edit' => 'Cliquer pour éditer',
		'fold' => 'Cacher le champ de saisie du <q>slug</q>',
		'reset' => 'Mettre à zéro',
		'view' => 'Voir sur le site'
	),

	'permission' => array
	(
		'modify belonging site' => "Modifier le site d'appartenance"
	),

	'The requested record was not found.' => "L'enregistrement demandé n'a pu être trouvé.",
	'Next: :title' => 'Suivant : :title', // il y a un espace non sécable ici
	'Previous: :title' => 'Précédent : :title', // il y a un espace non sécable ici
);