<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Nodes;

use ICanBoogie\ActiveRecord\RecordNotFound;
use ICanBoogie\Event;
use ICanBoogie\I18n;
use ICanBoogie\Operation\BeforeProcessEvent;

use Brickrouge\A;
use Brickrouge\Element;

class Hooks
{
	/*
	 * Events
	 */

	static public function on_modules_activate(Event $event)
	{
		\Icybee\Modules\Nodes\Module::create_default_routes();
	}

	static public function on_modules_deactivate(Event $event)
	{
		\Icybee\Modules\Nodes\Module::create_default_routes();
	}

	/**
	 * Checks if the user to be deleted has nodes.
	 *
	 * @param BeforeProcessEvent $event
	 * @param \Icybee\Modules\Users\DeleteOperation $operation
	 */
	static public function before_delete_user(BeforeProcessEvent $event, \Icybee\Modules\Users\DeleteOperation $operation)
	{
		global $core;

		$uid = $operation->key;
		$count = $core->models['nodes']->filter_by_uid($uid)->count;

		if (!$count)
		{
			return;
		}

		$event->errors['uid'] = I18n\t('The user %name is used by :count nodes.', array('name' => $operation->record->name, ':count' => $count));
	}

	/**
	 * Adds the orders attached to a member to the dependency collection.
	 *
	 * @param \ICanBoogie\ActiveRecord\CollectDependenciesEvent $event
	 * @param \Icybee\Modules\Users\User $target
	 */
	static public function on_user_collect_dependencies(\ICanBoogie\ActiveRecord\CollectDependenciesEvent $event, \Icybee\Modules\Users\User $target)
	{
		global $core;

		$nodes = $core->models['nodes']
		->select('nid, constructor, title')
		->filter_by_uid($target->uid)
		->order('created DESC')
		->all(\PDO::FETCH_OBJ);

		/* @var $nodes Node */

		foreach ($nodes as $node)
		{
			$event->add($node->constructor, $node->nid, $node->title, true);
		}
	}

	/*
	 * Markups
	 */

	/**
	 * Retrieves a node.
	 *
	 * <pre>
	 * <p:node
	 *     select = expression
	 *     constructor = string>
	 *     <!-- Content: with-param*, template -->
	 * </p:node>
	 * </pre>
	 *
	 * @param array $args
	 * @param \Patron\Engine $patron
	 * @param mixed $template
	 *
	 * @throws RecordNotFound when the record cannot be found.
	 */
	static public function markup_node(array $args, \Patron\Engine $patron, $template)
	{
		global $core;

		$record = null;
		$constructor = $args['constructor'] ?: 'nodes';
		$select = $args['select'];

		if ($select{0} == ':')
		{
			$select = substr($select, 1);
		}

		if (is_numeric($select))
		{
			$record = $core->models[$constructor][$select];
		}
		else
		{
			$record = $core->models[$constructor]->filter_by_slug($select)->ordered->own->one;
		}

		if (!$record)
		{
			throw new RecordNotFound('Unable to find record with the provided arguments: ' . json_encode($args), array());
		}

		return $patron($template, $record);
	}

	static public function markup_node_navigation(array $args, \Patron\Engine $patron, $template)
	{
		global $core;

		$core->document->css->add(DIR . 'public/page.css');

		$record = $patron->context['this'];

		$list = null;
		$cycle = null;

		$list_url = $record->url('list');

		if ($list_url)
		{
			$list = '<div class="list"><a href="' . \ICanBoogie\escape($list_url) . '">' . I18n\t('All records') . '</a></div>';
		}

		$next = null;
		$previous = null;
		$next_record = $record->next;
		$previous_record = $record->previous;

		if ($next_record)
		{
			$title = $next_record->title;

			$next = new A
			(
				\ICanBoogie\escape(\ICanBoogie\shorten($title, 48, 1)), $next_record->url, array
				(
					'class' => "next",
					'title' => I18n\t('Next: :title', array(':title' => $title))
				)
			);
		}

		if ($previous_record)
		{
			$title = $previous_record->title;

			$previous = new A
			(
				\ICanBoogie\escape(\ICanBoogie\shorten($title, 48, 1)), $previous_record->url, array
				(
					'class' => "previous",
					'title' => I18n\t('Previous: :title', array(':title' => $title))
				)
			);
		}

		if ($next || $previous)
		{
			$cycle = '<div class="cycle">' . $next . ' ' . $previous . '</div>';
		}

		if ($list || $cycle)
		{
			return '<div class="node-navigation">' . $list . $cycle . '</div>';
		}
	}

	/*
	 * Dashboard
	 */

	static public function dashboard_now()
	{
		global $core, $document;

		$document->css->add(DIR . 'public/dashboard.css');

		$counts = $core->models['nodes']->similar_site->count('constructor');

		if (!$counts)
		{
			return '<p class="nothing">' . I18n\t('No record yet') . '</p>';
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
			$category = $descriptor[Module::T_CATEGORY];

			if (!isset($categories[$category]))
			{
				$category = $default_category;
			}

			$title = I18n\t($descriptor[Module::T_TITLE], array(), array('scope' => 'module_title'));
			$title = I18n\t(strtr($constructor, '.', '_') . '.name.other', array(), array('default' => $title));

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
			$head .= '<th>&nbsp;</th><th>' . I18n\t($category, array(), array('scope' => 'module_category')) . '</th>';
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

		$document->css->add(DIR . 'public/dashboard.css');

		$model = $core->models['nodes'];

		$entries = $model
		->where('uid = ? AND (siteid = 0 OR siteid = ?)', array($core->user_id, $core->site_id))
		->order('modified desc')
		->limit(10)
		->all;

		if (!$entries)
		{
			return '<p class="nothing">' . I18n\t('No record yet') . '</p>';
		}

		$last_date = null;
		$context = $core->site->path;

		$rc = '<table>';

		foreach ($entries as $record)
		{
			$date = \ICanBoogie\I18n\date_period($record->modified);

			if ($date === $last_date)
			{
				$date = '&mdash;';
			}
			else
			{
				$last_date = $date;
			}

			$title = \ICanBoogie\shorten($record->title, 48);
			$title = \ICanBoogie\escape($title);

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
}