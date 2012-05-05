<?php

namespace ICanBoogie\Modules\Pages;

return array
(
	'autoload' => array
	(
		__NAMESPACE__ . '\BreadcrumbElement' => $path . 'lib/elements/breadcrumb.php',
		__NAMESPACE__ . '\NavigationBranchElement' => $path . 'lib/elements/navigation-branch.php',

		'WdPageSelectorElement' => $path . 'elements/pageselector.php',
		'WdAdjustTemplateElement' => $path . 'elements/adjusttemplate.php',
		'view_WdEditorElement' => $path . 'elements/view.editor.php',
		'site_pages_view_WdHooks' => $path . 'view.hooks.php',
		'site_pages_WdMarkups' => $path . 'markups.php',
		'site_pages_languages_WdMarkup' => $path . 'markups.php',
		'site_pages_navigation_WdMarkup' => $path . 'markups.php',
		'site_pages_sitemap_WdMarkup' => $path . 'markups.php',

		__NAMESPACE__ . '\CacheManager' => $path . 'lib/cache-manager.php',
		__NAMESPACE__ . '\ListView' => $path . 'lib/views/list.php',
		__NAMESPACE__ . '\Blueprint' => $path . 'lib/blueprint.php'
	)
);