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
		minuscules,  il ne contient que lettres, chiffres et traits d'union. S'il est vide lors de
		l'enregistrement, le <q>slug</q> est automatiquement crée à partir du titre.",

		'siteid' => "Parce que vous en avez la permission, vous pouvez choisir le site
		d'appartenance pour l'enregistrement. Un enregistrement appartenant à un site en hérite la
		langue et n'est visible que sur ce site.",

		'user' => "Parce que vous en avez la permission, vous pouvez choisir l'utilisateur
		propriétaire de cet enregistrement."
	),

	'title' => array
	(
		'visibility' => 'Visibilité'
	),

	'label' => array
	(
		'is_online' => 'Publié',
		'siteid' => "Site d'appartenance",
		'title' => 'Titre',
		'user' => 'Utilisateur'
	),

	'manager.label' => array
	(
		'constructor' => 'Constructeur',
		'created' => 'Crée le',
		'is_online' => 'Publié',
		'modified' => 'Modifié le',
		'uid' => 'Utilisateur'
	),

	'module_category.title.other' => 'Autre',

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

	/*DIRTY:I18N,

	'@operation.online.title' => 'Mettre en ligne',
	'@operation.online.confirm' => "Voulez-vous mettre l'entrée sélectionnée en ligne ?",
	'@operation.online.confirmN' => "Voulez-vous mettre les :count entrées sélectionnées en ligne ?",
	'@operation.online.do' => 'Mettre en ligne',
	'@operation.online.dont' => "Ne pas mettre en ligne",

	'@operation.offline.title' => 'Mettre hors ligne',
	'@operation.offline.confirm' => "Voulez-vous mettre l'entrée sélectionnée hors ligne ?",
	'@operation.offline.confirmN' => 'Voulez-vous mettre les :count entrées sélectionnées hors ligne ?',
	'@operation.offline.do' => 'Mettre hors ligne',
	'@operation.offline.dont' => "Ne pas mettre hors ligne"
	*/
);