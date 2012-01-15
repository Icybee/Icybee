<?php

namespace ICanBoogie\Modules\Pages\Hooks;

return array
(
	'events' => array
	(
		'resources.files.path.change' => __NAMESPACE__ . '::resources_files_path_change',
		'ICanBoogie\ActiveRecord\Page::urlchange' => __NAMESPACE__ . '::on_urlchange',

		/*
		 * cache support
		 */

		'ICanBoogie\Modules\System\Cache\Module::alter.block.manage' => __NAMESPACE__ . '::alter_block_manage',

		'Icybee::render:before' => __NAMESPACE__ . '::before_icybee_render',

		'ICanBoogie\Operation\ActiveRecord\Save::process' => __NAMESPACE__ . '::invalidate_cache',
		'ICanBoogie\Operation\ActiveRecord\Delete::process' => __NAMESPACE__ . '::invalidate_cache',
		'ICanBoogie\Operation\Nodes\Online::process' => __NAMESPACE__ . '::invalidate_cache',
		'ICanBoogie\Operation\Nodes\Offline::process' => __NAMESPACE__ . '::invalidate_cache',

		'BrickRouge\Document::render_title:before' => __NAMESPACE__ . '::on_document_render_title'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::url' => 'site_pages_view_WdHooks::url',
		'ICanBoogie\ActiveRecord\Node::absolute_url' => 'site_pages_view_WdHooks::absolute_url',
		'ICanBoogie\ActiveRecord\Node::__get_url' => 'site_pages_view_WdHooks::get_url',
		'ICanBoogie\ActiveRecord\Node::__get_absolute_url' => 'site_pages_view_WdHooks::get_absolute_url',
		'ICanBoogie\ActiveRecord\Site::__get_home' => __NAMESPACE__ . '::get_home',

		/*
		 * The following hooks are for the unified cache support
		 */

		'ICanBoogie\Operation\System\Cache\Enable::enable_pages' => __NAMESPACE__ . '::enable_cache',
		'ICanBoogie\Operation\System\Cache\Disable::disable_pages' => __NAMESPACE__ . '::disable_cache',
		'ICanBoogie\Operation\System\Cache\Stat::stat_pages' => __NAMESPACE__ . '::stat_cache',
		'ICanBoogie\Operation\System\Cache\Clear::clear_pages' => __NAMESPACE__ . '::clear_cache',

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
			__NAMESPACE__ . '::markup_page_translations', array
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
			__NAMESPACE__ . '::markup_breadcrumb', array
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
			__NAMESPACE__ . '::markup_page_region', array
			(
				'id' => array('required' => true)
			)
		)
	)
);