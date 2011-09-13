<?php

return array
(
	'events' => array
	(
		'resources.files.path.change' => array
		(
			'ICanBoogie\Hooks\Pages::resources_files_path_change'
		),

		'ICanBoogie\ActiveRecord\Page::urlchange' => array
		(
			'ICanBoogie\Hooks\Pages::on_urlchange'
		),

		/*
		 * cache support
		 */

		'ICanBoogie\Module\System\Cache::alter.block.manage' => 'ICanBoogie\Hooks\Pages::alter_block_manage',

		'Icybee::render:before' => 'ICanBoogie\Hooks\Pages::before_icybee_render',

		'ICanBoogie\Operation\ActiveRecord\Save::process' => 'ICanBoogie\Hooks\Pages::invalidate_cache',
		'ICanBoogie\Operation\ActiveRecord\Delete::process' => 'ICanBoogie\Hooks\Pages::invalidate_cache',
		'ICanBoogie\Operation\Nodes\Online::process' => 'ICanBoogie\Hooks\Pages::invalidate_cache',
		'ICanBoogie\Operation\Nodes\Offline::process' => 'ICanBoogie\Hooks\Pages::invalidate_cache',

		'BrickRouge\Document::render_title:before' => 'ICanBoogie\Hooks\Pages::on_document_render_title'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::url' => 'site_pages_view_WdHooks::url',
		'ICanBoogie\ActiveRecord\Node::absolute_url' => 'site_pages_view_WdHooks::absolute_url',
		'ICanBoogie\ActiveRecord\Node::__get_url' => 'site_pages_view_WdHooks::get_url',
		'ICanBoogie\ActiveRecord\Node::__get_absolute_url' => 'site_pages_view_WdHooks::get_absolute_url',
		'ICanBoogie\ActiveRecord\Site::__get_home' => 'ICanBoogie\Hooks\Pages::get_home',

		/*
		 * The following hooks are for the unified cache support
		 */

		'ICanBoogie\Operation\System\Cache\Enable::enable_pages' => 'ICanBoogie\Hooks\Pages::enable_cache',
		'ICanBoogie\Operation\System\Cache\Disable::disable_pages' => 'ICanBoogie\Hooks\Pages::disable_cache',
		'ICanBoogie\Operation\System\Cache\Stat::stat_pages' => 'ICanBoogie\Hooks\Pages::stat_cache',
		'ICanBoogie\Operation\System\Cache\Clear::clear_pages' => 'ICanBoogie\Hooks\Pages::clear_cache',

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
			array('site_pages_WdMarkups', 'content'), array
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
			'ICanBoogie\Hooks\Pages::markup_page_translations', array
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
			array('site_pages_navigation_WdMarkup', 'navigation_leaf'), array
			(
				'level' => 1,
				'depth' => true,
				'title-link' => true
			)
		),

		'breadcrumb' => array
		(
			'ICanBoogie\Hooks\Pages::markup_breadcrumb', array
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
			array('site_pages_WdMarkups', 'call_view'), array
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
			'ICanBoogie\Hooks\Pages::markup_page_region', array
			(
				'id' => array('required' => true)
			)
		)
	)
);