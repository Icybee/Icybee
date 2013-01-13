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
use ICanBoogie\I18n;
use ICanBoogie\Module;

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

use Icybee\Modules\Contents\Model as ContentsModel;
use Icybee\Modules\Pages\Model as PagesModel;

require_once dirname(__DIR__) . '/lib/api.php';

$_home_limit = $core->site->metas->get('search.limits.home', 5);
$_list_limit = $core->site->metas->get('search.limits.list', 10);

$core->document->css->add('../public/page.css');

#
#
#

$module = $core->modules['search'];

$constructors = $core->site->metas['search.scope'];

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

$constructors_options = array(null => I18n\t('search.option.all'));

foreach ($constructors as $constructor)
{
	if ($constructor == 'google')
	{
		$constructors_options[$constructor] = 'Google';

		continue;
	}

	$constructors_options[$constructor] = I18n\t(strtr($constructor, '.', '_'), array(), array('scope' => 'module_title', 'default' => $core->modules->descriptors[$constructor][Module::T_TITLE]));
}

$document->js->add('../public/widget.js');

$form = new Brickrouge\Form
(
	array
	(
		Brickrouge\Form::VALUES => $_GET,

		Element::CHILDREN => array
		(
			'q' => new Text
			(
				array
				(
					Form::LABEL => I18n\t('search.label.keywords'),

					'autofocus' => true,
					'placeholder' => I18n\t('search.label.keywords'),
					'class' => 'unstyled'
				)
			),

			'constructor' => new Element
			(
				'select', array
				(
					Form::LABEL => I18n\t('search.label.in'),
					Element::OPTIONS => $constructors_options,
					'class' => 'unstyled'
				)
			),

			new Button
			(
				'search.label.search', array
				(
					'class' => 'unstyled',
					'type' => 'Submit'
				)
			)
		),

		'method' => 'GET',
		'class' => 'widget-search-combo'
	)
);

echo '<div class="conditions">' . $form . '</div>';

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

			if ($model instanceof PagesModel)
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
	echo I18n\t("Le constructeur %constructor n'est pas supporté pour la recherche", array('%constructor' => $_GET['constructor']));
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

		if ($model instanceof PagesModel)
		{
			list($entries, $count) = query_pages($search, $position, $_list_limit);
		}
		else if ($model instanceof ContentsModel)
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