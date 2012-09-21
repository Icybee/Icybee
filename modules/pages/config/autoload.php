<?php

namespace ICanBoogie\Modules\Pages;

// TODO-20120918: use namespace

return array
(
	__NAMESPACE__ . '\Blueprint' => $path . 'lib/blueprint.php',
	__NAMESPACE__ . '\BreadcrumbElement' => $path . 'lib/elements/breadcrumb.php',
	__NAMESPACE__ . '\LanguagesElement' => $path . 'lib/elements/languages.php',
	__NAMESPACE__ . '\ListView' => $path . 'lib/views/list.php',
	__NAMESPACE__ . '\NavigationBranchElement' => $path . 'lib/elements/navigation-branch.php',

	'WdPageSelectorElement' => $path . 'elements/pageselector.php',
	'WdAdjustTemplateElement' => $path . 'elements/adjusttemplate.php',
	'site_pages_WdMarkups' => $path . 'markups.php',
	'site_pages_languages_WdMarkup' => $path . 'markups.php',
	'site_pages_navigation_WdMarkup' => $path . 'markups.php',
	'site_pages_sitemap_WdMarkup' => $path . 'markups.php'
);