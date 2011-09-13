<?php

return array
(
	'autoload' => array
	(
		'ICanBoogie\View' => $path . 'lib/view.php',
		'BrickRouge\Element\Breadcrumb' => $path . 'lib/element/breadcrumb.php',

		'WdPageSelectorElement' => $path . 'elements/pageselector.php',
		'WdAdjustTemplateElement' => $path . 'elements/adjusttemplate.php',
		'view_WdEditorElement' => $path . 'elements/view.editor.php',
		'site_pages_view_WdHooks' => $path . 'view.hooks.php',
		'site_pages_WdMarkups' => $path . 'markups.php',
		'site_pages_languages_WdMarkup' => $path . 'markups.php',
		'site_pages_navigation_WdMarkup' => $path . 'markups.php',
		'site_pages_sitemap_WdMarkup' => $path . 'markups.php'
	)
);