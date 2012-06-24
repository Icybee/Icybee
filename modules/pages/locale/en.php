<?php

return array
(
	'site_pages.search' => array
	(
		'found' => array
		(
			'none' => 'No result found in the pages.',
			'one' => 'One result found in the pages.',
			'other' => ':count results found in the pages.'
		),

		'more' => array
		(
			'one' => 'See the result found for %search in the pages',
			'other' => 'See the :count results found for %search in the pages'
		)
	),

	'content.title' => array
	(
		'body' => 'Body of the page'
	),

	'description' => array
	(
		'is_navigation_excluded' => "Pages excluded from the main navigation don't appear in menus.
		They might still appear on the sitemap.",

		'label' => "The label is a shorter version of the title. It is used in preference to the
		title to create the links of the menus and the breadcrumb.",

		'location' => 'Redirect from this page to another page.',

		'parentid' => "Pages can be organized hierarchically to form a tree. There is no limit to
		the depth of this tree.",

		'pattern' => "The pattern is used to distribute the URL parameters to create a semantic
		URL.",

		'contents.inherit' => "The following contents can be inherited. That is, if the page
		does not define a content, the content of a parent page is used."
	),

	'label' => array
	(
		'is_navigation_excluded' => 'Exclude the page form the main navigation',
		'label' => 'Label of the page',
		'location' => 'Redirect',
		'parentid' => 'Parent page',
		'pattern' => 'Pattern',
		'template' => 'Template'
	),

	'group.legend' => array
	(
		'advanced' => 'Advanced options'
	),

	'module_title.pages' => 'Pages'
);