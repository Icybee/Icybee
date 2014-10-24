<?php

return array
(
	'block.title' => array
	(
		'config' => 'Config.',
		'delete' => 'Supprimer',
		'edit' => 'Éditer',
		'manage' => 'Liste',
		'new' => 'Nouveau'
	),

	'button' => array
	(
		'Delete' => 'Supprimer',
		'New' => 'Nouveau',
		'Remove' => 'Retirer',
		'View' => 'Voir'
	),

	'group.legend' => array
	(
		'Organization' => 'Organisation'
	),

	'option' => array
	(
		'save_mode_list' => 'Enregistrer et aller à la liste',
		'save_mode_continue' => "Enregistrer et continuer l'édition",
		'save_mode_new' => "Enregistrer et éditer un nouveau"
	),

	'delete.operation' => array
	(
		'title' => 'Supprimer des enregistrements',
		'short_title' => 'Supprimer',
		'continue' => 'Supprimer',
		'cancel' => "Ne pas supprimer",

		'confirm' => array
		(
			'one' => "Êtes-vous sûr de vouloir supprimer l'enregistrement sélectionné ?",
			'other' => "Êtes-vous sûr de vouloir supprimer les :count enregistrements sélectionnés ?"
		)
	),

	/*
	 * lib/blocks/manage.php
	 */

	'manage' => array
	(
		'column' => array
		(
			'created' => 'Crée le',
			'created_at' => 'Crée le',
			'modified' => 'Modifié le',
			'updated_at' => 'Modifié le',

			'Date created' => 'Crée le',
			'Date modified' => 'Modifié le'
		),

		'create_first' => "<strong><a href=\"!url\">Créer le premier enregistrement…</a></strong>",

		'edit' => "Éditer l'enregistrement",
		'edit_named' => "Éditer l'enregistrement : :title",

		'records_count' => array
		(
			'none' => "Aucun enregistrement",
			'one' => 'Un enregistrement',
			'other' => ':count enregistrements'
		),

		'records_count_with_filters' => array
		(
			'none' => 'Aucun enregistrement ne correspond aux filters',
			'one' => 'Un enregistrement correspond aux filtres',
			'other' => ':count enregistrements correspondent aux filtres'
		)
	),

	#

	'module_category' => array
	(
		'features' => 'Fonctionnalités',
		'feedback' => 'Intéractions',
		'organize' => 'Organiser',
		'structure' => 'Structure',
		'system' => 'Système'
	),

	'Hello :username' => 'Bonjour :username',
	'See the website' => 'Voir le site',

	# DeleteBlock

	'Delete a record' => 'Supprimer un enregistrement',
	'Are you sure you want to delete :name?' => "Êtes-vous sûr de vouloir supprimer :name ?",
	'The following dependencies were found, they will also be deleted:' => "Les dépendances suivantes ont été trouvées, elles seront également supprimées :",

	#

	'Edit' => 'Éditer',
	'Edit: !title' => "Éditer : !title",
	'Search' => 'Rechercher',
	':page_limit_selector by page' => ':page_limit_selector par page',
	'For the selection…' => 'Pour la sélection…',

	':count entries' => ':count entrées',
	'Display all' => 'Tout afficher',
	'Display everything' => 'Tout afficher',
	'Display only: :identifier' => 'Afficher uniquement : :identifier',
	'From :start to :finish on :count' => 'De :start à :finish sur :count',
	'Sort by: :identifier' => 'Trier par : :identifier',

	# Icybee\ManageBlock

	"Your search %search did not match any record."
	=> "Votre recherche %search ne correspond à aucun enregistrement.",

	"Reset search filter"
	=> "Réinitialiser le filtre de recherche",

	# Icybee\Modules\Pages\PageController

	"The requested URL requires authentication." => "L'URL demandée requiert une authentification.",

	# Icybee\Element\ActionbarTitle

	"List page on the website" => "Page de la liste sur le site",

	# Icybee\ConfigOperation

	"The configuration has been saved." => "La configuration a été enregistrée.",
);