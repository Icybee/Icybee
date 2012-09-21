<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Nodes;

use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;
use ICanBoogie\Exception\HTTP as HTTPException;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

use WdAdjustNodeWidget;

class Module extends \Icybee\Module
{
	const PERMISSION_MODIFY_BELONGING_SITE = 'modify belonging site';

	/**
	 * Defines the "view", "list" and "home" views.
	 *
	 * @see Icybee.Module::get_views()
	 */
	protected function get_views()
	{
		return \ICanBoogie\array_merge_recursive
		(
			parent::get_views(), array
			(
				'view' => array
				(
					'title' => "Record detail",
					'provider' => __NAMESPACE__ . '\ViewProvider',
					'assets' => array(),
					'renders' => \Icybee\Modules\Views\View::RENDERS_ONE
				),

				'list' => array
				(
					'title' => 'Records list',
					'provider' => __NAMESPACE__ . '\ViewProvider',
					'assets' => array(),
					'renders' => \Icybee\Modules\Views\View::RENDERS_MANY
				),

				'home' => array
				(
					'title' => 'Records home',
					'provider' => __NAMESPACE__ . '\ViewProvider',
					'assets' => array(),
					'renders' => \Icybee\Modules\Views\View::RENDERS_MANY
				)
			)
		);
	}

	protected function resolve_primary_model_tags($tags)
	{
		return parent::resolve_model_tags($tags, 'primary') + array
		(
			Model::T_CONSTRUCTOR => $this->id
		);
	}

	static public function dashboard_now()
	{
		global $core, $document;

		$document->css->add('public/dashboard.css');

		$counts = $core->models['nodes']->similar_site->count('constructor');

		if (!$counts)
		{
			return '<p class="nothing">' . t('No record yet') . '</p>';
		}

		$categories = array
		(
			'contents' => array(),
			'resources' => array(),
			'other' => array()
		);

		$default_category = 'other';

		foreach ($counts as $constructor => $count)
		{
			if (!isset($core->modules[$constructor]))
			{
				continue;
			}

			$descriptor = $core->modules->descriptors[$constructor];
			$category = $descriptor[self::T_CATEGORY];

			if (!isset($categories[$category]))
			{
				$category = $default_category;
			}

			$title = t($descriptor[self::T_TITLE], array(), array('scope' => 'module_title'));
			$title = t(strtr($constructor, '.', '_') . '.name.other', array(), array('default' => $title));

			$categories[$category][] = array
			(
				$title, $constructor, $count
			);
		}

		$head = '';
		$max_by_category = 0;

		foreach ($categories as $category => $entries)
		{
			$max_by_category = max($max_by_category, count($entries));
			$head .= '<th>&nbsp;</th><th>' . t($category, array(), array('scope' => 'module_category')) . '</th>';
		}

		$body = '';
		$path = $core->site->path;

		for ($i = 0 ; $i < $max_by_category ; $i++)
		{
			$body .= '<tr>';

			foreach ($categories as $category => $entries)
			{
				if (empty($entries[$i]))
				{
					$body .= '<td colspan="2">&nbsp;</td>';

					continue;
				}

				list($title, $constructor, $count) = $entries[$i];

				$body .= <<<EOT
<td class="count">$count</td>
<td class="constructor"><a href="$path/admin/$constructor">$title</a></td>
EOT;
			}

			$body .= '</tr>';
		}

		return $rc = <<<EOT
<table>
	<thead><tr>$head</tr></thead>
	<tbody>$body</tbody>
</table>
EOT;
	}

	static public function dashboard_user_modified()
	{
		global $core, $document;

		$document->css->add('public/dashboard.css');

		$model = $core->models['nodes'];

		$entries = $model
		->where('uid = ? AND (siteid = 0 OR siteid = ?)', array($core->user_id, $core->site_id))
		->order('modified desc')
		->limit(10)
		->all;

		if (!$entries)
		{
			return '<p class="nothing">' . t('No record yet') . '</p>';
		}

		$last_date = null;
		$context = $core->site->path;

		$rc = '<table>';

		foreach ($entries as $record)
		{
			$date = wd_date_period($record->modified);

			if ($date === $last_date)
			{
				$date = '&mdash;';
			}
			else
			{
				$last_date = $date;
			}

			$title = \ICanBoogie\shorten($record->title, 48);
			$title = wd_entities($title);

			$rc .= <<<EOT
	<tr>
	<td class="date light">$date</td>
	<td class="title"><a href="$context/admin/{$record->constructor}/{$record->nid}/edit">{$title}</a></td>
	</tr>
EOT;
		}

		$rc .= '</table>';

		return $rc;
	}

	public static function create_default_routes()
	{
		global $core;

		$routes = array();

		foreach ($core->modules->enabled_modules_descriptors as $module_id => $descriptor)
		{
			if ($module_id == 'nodes' || $module_id == 'contents' || !self::is_extending($module_id, 'nodes'))
			{
				continue;
			}

			$common = array
			(
				'module' => $module_id,
// 				'workspace' => $descriptor[self::T_CATEGORY],
				'controller' => 'Icybee\BlockController',
				'visibility' => 'visible'
			);

// 			\ICanBoogie\log("create default routes for $module_id");

			# manage (index)

			$routes["admin:$module_id"] = array
			(
				'pattern' => "/admin/$module_id",
				'title' => '.manage',
				'block' => 'manage',
				'index' => true
			)

			+ $common;

			if ($module_id == 'contents' || self::is_extending($module_id, 'contents') || $module_id == 'files' || self::is_extending($module_id, 'files'))
			{
				# config'

				$routes["admin:$module_id/config"] = array
				(
					'pattern' => "/admin/$module_id/config",
					'title' => '.config',
					'block' => 'config',
					'permission' => self::PERMISSION_ADMINISTER,
				)

				+ $common;
			}

			# create

			$routes["admin:$module_id/new"] = array
			(
				'pattern' => "/admin/$module_id/new",
				'title' => '.new',
				'block' => 'edit'
			)

			+ $common;

			# edit

			$routes["admin:$module_id/edit"] = array
			(
				'pattern' => "/admin/$module_id/<\d+>/edit",
				'controller' => 'Icybee\EditController',
				'title' => '.edit',
				'block' => 'edit',
				'visibility' => 'auto'
			)

			+ $common;

			# delete

			$routes["admin:$module_id/delete"] = array
			(
				'pattern' => "/admin/$module_id/<\d+>/delete",
				'controller' => 'Icybee\DeleteController',
				'title' => '.delete',
				'block' => 'delete',
				'visibility' => 'auto'
			)

			+ $common;
		}

		Event::fire('create_default_routes', array('routes' => &$routes), $core->modules['nodes']);

// 		var_dump($routes);

		$export = var_export($routes,true);

		$core->vars['default_nodes_routes'] = "<?php\n\nreturn " . $export . ';';
	}
}

namespace Brickrouge\Element\Nodes;

class Pager extends \Brickrouge\Pager
{
	protected function getURL($n)
	{
		return '#' . $n;
	}
}
