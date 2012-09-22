<?php

namespace Icybee\Modules\Pages;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'resources.files.path.change' => $hooks . 'resources_files_path_change',

		'Brickrouge\Document::render_title:before' => $hooks . 'on_document_render_title',

		'Icybee\Modules\Pages\Page::urlchange' => $hooks . 'on_urlchange',
		'ICanBoogie\HTTP\Dispatcher::populate' => $hooks . 'on_http_dispatcher_populate',

		'Icybee::nodes_load' => __NAMESPACE__ . '\PageController::on_nodes_load',
		'Patron\Engine::nodes_load' => __NAMESPACE__ . '\PageController::on_nodes_load',

		'ICanBoogie\SaveOperation::process' => $hooks . 'invalidate_cache',
		'ICanBoogie\DeleteOperation::process' => $hooks . 'invalidate_cache',
		'ICanBoogie\Modules\Nodes\OnlineOperation::process' => $hooks . 'invalidate_cache',
		'ICanBoogie\Modules\Nodes\OfflineOperation::process' => $hooks . 'invalidate_cache'
	),

	'prototypes' => array
	(
		'Icybee\Modules\Sites\Site::get_home' => $hooks . 'get_home',
		'ICanBoogie\Core::volatile_get_page' => $hooks . 'get_page'
	),

	'patron.markups' => array
	(
		'page:content' => array
		(
			$hooks . 'markup_page_content', array
			(
				'id' => array('required' => true),
				'title' => array('required' => true),
				'editor' => null,
				'render' => array('required' => true, 'default' => 'auto'),
				'no-wrapper' => false
			)
		),

		'page:languages' => array
		(
			__NAMESPACE__ . '\LanguagesElement::markup', array
			(

			)
		),

		'navigation' => array
		(
			__NAMESPACE__ . '\NavigationElement::markup', array
			(
				'parent' => 0,
				'depth' => array('default' => 2),
				'min-child' => false,
				'from-level' => null,
				'mode' => null
			)
		),

		'navigation:leaf' => array
		(
			__NAMESPACE__ . '\NavigationBranchElement::markup_navigation_leaf', array
			(
				/* FIXME-20120715: not implemented
				'level' => 1,
				'depth' => null,
				'title-link' => null
				*/
			)
		),

		'breadcrumb' => array
		(
			__NAMESPACE__ . '\BreadcrumbElement::markup', array
			(
				'page' => array('expression' => true, 'required' => true, 'default' => 'this')
			)
		),

		#
		# cache
		#

		'page:region' => array
		(
			$hooks . 'markup_page_region', array
			(
				'id' => array('required' => true)
			)
		),

		'page:title' => array
		(
			$hooks . 'markup_page_title', array
			(

			)
		)
	)
);