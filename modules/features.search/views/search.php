<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Module;

use BrickRouge\Element;
use BrickRouge\Form;

require_once dirname(__DIR__) . '/api.php';

$_home_limit = $core->site->metas->get('site_search.limits.home', 5);
$_list_limit = $core->site->metas->get('site_search.limits.list', 10);

$core->document->css->add('../public/page.css');

#
#
#

$module = $core->modules['features.search'];

$constructors = $core->site->metas['features_search.scope'];

if (!count($constructors))
{
	throw new Exception\Config($module);
}

$constructors = explode(',', $constructors);

foreach ($constructors as $i => $constructor)
{
	if (isset($core->modules[$constructor]))
	{
		continue;
	}

	unset($constructors[$i]);
}

//$constructors[] = 'google';

$constructors_options = array(null => t('search.option.all'));

foreach ($constructors as $constructor)
{
	if ($constructor == 'google')
	{
		$constructors_options[$constructor] = 'Google';

		continue;
	}

	$constructors_options[$constructor] = t(strtr($constructor, '.', '_'), array(), array('scope' => array('module', 'title'), 'default' => $core->modules->descriptors[$constructor][Module::T_TITLE]));
}

$document->js->add('../public/widget.js');

$form = new BrickRouge\Form
(
	array
	(
		BrickRouge\Form::T_VALUES => $_GET,

		Element::T_CHILDREN => array
		(
			'q' => new Element
			(
				Element::E_TEXT, array
				(
					Form::T_LABEL => t('search.label.keywords'),

					'autofocus' => true,
					'placeholder' => t('search.label.keywords'),
					'class' => 'unstyled'
				)
			),

			'constructor' => new Element
			(
				'select', array
				(
					Form::T_LABEL => t('search.label.in'),
					Element::T_OPTIONS => $constructors_options,
					'class' => 'unstyled'
				)
			),

			new Element
			(
				Element::E_SUBMIT, array
				(
					Element::T_INNER_HTML => t('search.label.search'),
					'class' => 'unstyled'
				)
			)
		),

		'method' => 'get',
		'class' => 'widget-search-combo'
	)
);

class site_search__search_WdView
{

}

$sender = new site_search__search_WdView();

Event::fire
(
	'render:before', array
	(
		'form' => &$form
	),

	$sender
);

echo $form;

if (empty($_GET['q']))
{
	return;
}

$document->css->add('../public/page.css');

$search = $_GET['q'];
$position = isset($_GET['page']) ? (int) $_GET['page'] : 0;

if (empty($_GET['constructor']))
{
	$position = 0;
}

if (empty($_GET['constructor']))
{
	foreach ($constructors as $constructor)
	{
		if ($constructor == 'google')
		{
			list($entries, $count) = query_google($search, 0, $_home_limit);
		}
		else
		{
			$model = $core->models[$constructor];

			if ($model instanceof Model\Pages)
			{
				list($entries, $count) = query_pages($search, 0, $_home_limit);
			}
			else
			{
				list($entries, $count) = query_contents($constructor, $search, 0, $_home_limit);
			}
		}

		echo make_set($constructor, $entries, $count, $search);
	}
}
else if (!in_array($_GET['constructor'], $constructors))
{
	echo t("Le constructeur %constructor n'est pas supporté pour la recherche", array('%constructor' => $_GET['constructor']));
}
else
{
	$constructor = $_GET['constructor'];

	if ($constructor == 'google')
	{
		list($entries, $count) = query_google($search, $position, $_list_limit);
	}
	else
	{
		$model = $core->models[$constructor];

		if ($model instanceof Model\Pages)
		{
			list($entries, $count) = query_pages($search, $position, $_list_limit);
		}
		else if ($model instanceof Model\Contents)
		{
			list($entries, $count) = query_contents($constructor, $search, $position, $_list_limit);
		}
		else
		{
			echo "<p>Don't know how to query: <em>$constructor</em></p>";
		}
	}

	echo make_set($constructor, $entries, $count, $search, true);
}