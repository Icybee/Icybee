<?php

return array
(
	'site_pages.search' => array
	(
		'found' => array
		(
			'none' => 'Aucun résulat trouvé dans les pages.',
			'one' => 'Un résultat trouvé dans les pages.',
			'other' => ':count résultats trouvés dans les pages.'
		),

		'more' => array
		(
			'one' => 'Voir le résultat trouvé pour %search dans les pages',
			'other' => 'Voir les :count résultats trouvés pour %search dans les pages'
		)
	),

	'content.title' => array
	(
		'body' => 'Corps de la page'
	),

	'description' => array
	(
		'label' => "L'étiquette est une version plus concise du titre. Elle est utilisée de
		préférence au titre pour créer les liens des menus et du fil d'Ariane.",

		'location' => 'Redirection depuis cette page vers une autre page.',

		'parentid' => "Les pages peuvent être organisées hiérarchiquement pour former une
		arborescence. Il n'y a pas de limites à la profondeur de cette arborescence.",

		'pattern' => "Le motif permet de distribuer les paramètres d'une URL pour créer une URL
		sémantique.",

		'contents.inherit' => "Les contenus suivants peuvent être hérités. Si la page ne définit
		pas un contenu, alors celui d'une page parente est utilisé."
	),

	'label' => array
	(
		'is_navigation_excluded' => 'Exclure la page de la navigation principale',
		'label' => 'Étiquette de la page',
		'location' => 'Redirection',
		'parentid' => 'Page parente',
		'pattern' => 'Motif',
		'template' => 'Gabarit'
	),

	'section.title' => array
	(
		'advanced' => 'Options avancées',
		'contents' => 'Contenu'
	),

	"The template defines a page model of which some elements are editable."
	=> "Le gabarit définit un modèle de page dont certains éléments sont modifiables.",

	"The following elements are editable:"
	=> "Les éléments suivants sont éditables&nbsp;:",

	"The <q>:template</q> template does not define any editable element."
	=> "Le gabarit <q>:template</q> ne définit aucun élément éditable.",

	'No parent page define this content.'
	=> "Aucune page parente ne définit ce contenu.",

	'This content is currently inherited from the <q><a href="!url">!title</a></q> parent page – <a href="#edit">Edit the content</a>'
	=> 'Ce contenu est actuellement hérité depuis la page parente <q><a href="!url">!title</a></q> – <a href="#edit">Éditer le contenu</a>',

	'This page uses the <q>:template</q> template, inherited from the parent page <q><a href="!url">!title</a></q>.'
	=> 'Cette page utilise le gabarit <q>:template</q>, hérité de la page parente <q><a href="!url">!title</a></q>.',

	"All records" => "Tous les enregistrements"
);