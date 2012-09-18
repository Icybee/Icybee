<?php

namespace ICanBoogie\Modules\Pages;

$hooks = __NAMESPACE__ . '\Hooks';

return array
(
	'events' => array
	(
		'resources.files.path.change' => $hooks . '::resources_files_path_change',

		'Brickrouge\Document::render_title:before' => $hooks . '::on_document_render_title',

		'ICanBoogie\ActiveRecord\Page::urlchange' => $hooks . '::on_urlchange',
		__NAMESPACE__ . '\SaveOperation::process' => $hooks . '::on_save',
		'ICanBoogie\HTTP\Dispatcher::populate' => $hooks . '::on_http_dispatcher_populate',

// 		'Icybee\Pagemaker::render:before' => $hooks . '::before_icybee_render', TODO-20120313: we need to use another event

		'ICanBoogie\SaveOperation::process' => $hooks . '::invalidate_cache',
		'ICanBoogie\DeleteOperation::process' => $hooks . '::invalidate_cache',
		'ICanBoogie\Modules\Nodes\OnlineOperation::process' => $hooks . '::invalidate_cache',
		'ICanBoogie\Modules\Nodes\OfflineOperation::process' => $hooks . '::invalidate_cache'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::url' => 'site_pages_view_WdHooks::url',
		'ICanBoogie\ActiveRecord\Node::absolute_url' => 'site_pages_view_WdHooks::absolute_url',
		'ICanBoogie\ActiveRecord\Node::get_url' => 'site_pages_view_WdHooks::get_url',
		'ICanBoogie\ActiveRecord\Node::get_absolute_url' => 'site_pages_view_WdHooks::get_absolute_url',
		'ICanBoogie\ActiveRecord\Site::get_home' => $hooks . '::get_home',

		'ICanBoogie\Core::volatile_get_page' => $hooks . '::core__volatile_get_page',

		/*
		 * views
		 */

		'ICanBoogie\ActiveRecord\Site::resolve_view_target' => 'site_pages_view_WdHooks::resolve_view_target',
		'ICanBoogie\ActiveRecord\Site::resolve_view_url' => 'site_pages_view_WdHooks::resolve_view_url'
	),

	'patron.markups' => array
	(
		'page:content' => array
		(
			'site_pages_WdMarkups::content', array
			(
				'id' => array('required' => true),
				'title' => array('required' => true),
				'editor' => null,
				'render' => array('required' => true, 'default' => 'auto'),
				'no-wrapper' => false
			)
		),

		'page:translations' => array
		(
			$hooks . '::markup_page_translations', array
			(
				'select' => array('expression' => true, 'required' => true, 'default' => '$page')
			)
		),

		'page:languages' => array
		(
			'o:site_pages_languages_WdMarkup', array
			(
			)
		),

		'menu' => array
		(
			array('site_pages_WdMarkups', 'menu'), array
			(
				'select' => null,
				'parent' => null,
				'nest' => true
			)
		),

		'navigation' => array
		(
			'o:site_pages_navigation_WdMarkup', array
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
			$hooks . '::markup_navigation_leaf', array
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
			$hooks . '::markup_breadcrumb', array
			(
				'page' => array('expression' => true, 'required' => true, 'default' => 'this')
			)
		),

		'sitemap' => array
		(
//			array('site_pages_WdMarkups', 'sitemap'), array
			'o:site_pages_sitemap_WdMarkup', array
			(
				'parent' => null,
				'nest' => false
			)
		),

		#
		# views
		#

		'call-view' => array
		(
			$hooks . '::markup_call_view', array
			(
				'name' => array('required' => true)
			)
		),

		#
		# cache
		#

		'cache' => array
		(
			array('site_pages_WdMarkups', 'cache'), array
			(
				'scope' => 'global'
			)
		),

		'page:region' => array
		(
			$hooks . '::markup_page_region', array
			(
				'id' => array('required' => true)
			)
		),

		'page:title' => array
		(
			$hooks . '::markup_page_title', array()
		)
	)
);