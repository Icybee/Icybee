<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use ICanBoogie\ActiveRecord\Node;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Module;
use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Widget;
use Icybee\Manager;

use WdPatron;
use WdAdjustNodeWidget;

class Nodes extends \Icybee\Module
{
	const PERMISSION_MODIFY_BELONGING_SITE = 'modify belonging site';

	protected function resolve_primary_model_tags($tags)
	{
		return parent::resolve_model_tags($tags, 'primary') + array
		(
			Model\Nodes::T_CONSTRUCTOR => $this->id
		);
	}

	protected function block_edit(array $properties, $permission)
	{
		global $core;

		$uid_el = null;
		$siteid_el = null;
		$hiddens = array();

		if ($core->user->has_permission(self::PERMISSION_ADMINISTER, $this))
		{
			$users = $core->models['users']->select('uid, username')->order('username')->pairs;

			if (count($users) > 1)
			{
				$uid_el = new Element
				(
					'select', array
					(
						Element::T_LABEL => '.user',
						Element::T_LABEL_POSITION => 'before',
						Element::T_OPTIONS => array(null => '')	+ $users,
						Element::T_REQUIRED => true,
						Element::T_DEFAULT => $core->user->uid,
						Element::T_GROUP => 'admin',
						Element::T_DESCRIPTION => '.user'
					)
				);
			}
		}

		if ($core->user->has_permission(self::PERMISSION_MODIFY_BELONGING_SITE, $this))
		{
			// TODO-20100906: this should be added by the "sites" modules using the alter event.

			$sites = $core->models['sites']->select('siteid, IF(admin_title != "", admin_title, concat(title, ":", language))')->order('admin_title, title')->pairs;

			if (count($sites) > 1)
			{
				$siteid_el = new Element
				(
					'select', array
					(
						Element::T_LABEL => '.siteid',
						Element::T_LABEL_POSITION => 'before',
						Element::T_OPTIONS => array(null => '') + $sites,
						Element::T_GROUP => 'admin',
						Element::T_DESCRIPTION => '.siteid'
					)
				);
			}
			else
			{
				$hiddens[Node::SITEID] = $core->site_id;
			}
		}

		return array
		(
			Form::T_HIDDENS => $hiddens,

			Element::T_GROUPS => array
			(
				'node' => array
				(
					'weight' => -10,
					'title' => 'do not use node section anymore!',
					'class' => 'form-section flat'
				),

				'visibility' => array
				(
					'title' => '.visibility',
					'class' => 'form-section flat',
					'weight' => 400
				)
			),

			Element::T_CHILDREN => array
			(
				Node::TITLE => new Widget\TitleSlugCombo
				(
					array
					(
						Form::T_LABEL => '.title',
						Element::T_REQUIRED => true,
						Widget\TitleSlugCombo::T_NODEID => $properties[Node::NID],
						Widget\TitleSlugCombo::T_SLUG_NAME => 'slug'
					)
				),

				Node::UID => $uid_el,

				Node::SITEID => $siteid_el,

				Node::IS_ONLINE => new Element
				(
					Element::E_CHECKBOX, array
					(
						Element::T_LABEL => '.is_online',
						Element::T_DESCRIPTION => '.is_online',
						Element::T_GROUP => 'visibility'
					)
				)
			)
		);
	}

	protected function block_manage()
	{
		return new Manager\Nodes
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'title', 'uid', 'constructor', 'created', 'modified', 'is_online'
				)
			)
		);
	}

	protected function block_adjust(array $params)
	{
		return new WdAdjustNodeWidget
		(
			array
			(
				WdAdjustNodeWidget::T_CONSTRUCTOR => $this->id,
				Element::T_DESCRIPTION => null,

				'value' => isset($params['value']) ? $params['value'] : null
			)
		);
	}

	protected function block_adjustResults(array $options=array())
	{
		$options += array
		(
			'page' => 0,
			'limit' => 10,
			'search' => null,
			'selected' => null
		);

		#
		# search
		#

		$where = array();
		$values = array();

		if ($this->id != 'nodes')
		{
			$where[] = 'constructor = ?';
			$values[] = $this->id;
		}

		$search = $options['search'];

		if ($search)
		{
			$concats = array();

			$words = explode(' ', $options['search']);
			$words = array_map('trim', $words);

			foreach ($words as $word)
			{
				$where[] = 'title LIKE ?';
				$values[] = '%' . $word . '%';
			}
		}

		$page = $options['page'];
		$limit = $options['limit'];
		$selected = $options['selected'];

		list($entries, $count) = $this->adjust_loadRange($where, $values, $limit, $page);

		$rc = '<div class="results">';

		if ($count)
		{
			$rc .= '<ul>';

			foreach ($entries as $record)
			{
				$rc .= ($record->nid == $selected) ? '<li class="selected">' : '<li>';
				$rc .= $this->adjust_createEntry($record);
				$rc .= '</li>' . PHP_EOL;
			}

			$rc .= '</ul>';

			$rc .= new Element\Nodes\Pager
			(
				'div', array
				(
					Pager::T_COUNT => $count,
					Pager::T_LIMIT => $limit,
					Pager::T_POSITION => $page,

					'class' => 'pager'
				)
			);
		}
		else
		{
			$rc .= '<p class="no-response">';

			$rc .= $search
				? t('Aucun objet ne correspond aux termes de recherche spécifiés (%search)', array('%search' => $search))
				: t('Aucune entrée dans le module %module', array('%module' => $this->id));

			$rc .= '</p>';
		}

		$rc .= '</div>';

		return $rc;
	}

	protected function adjust_loadRange(array $conditions, array $conditions_args, $limit, $page)
	{
		$arq = $this->model->where(implode(' AND ', $conditions), $conditions_args);

		$count = $arq->count;
		$entries = array();

		if ($count)
		{
			$entries = $arq->limit($page * $limit, $limit)->order('title')->all;
		}

		return array($entries, $count);
	}

	public function adjust_createEntry($record)
	{
		$rc  = '<input class="nid" type="hidden" value="' . $record->nid . '" />';

		if ($record->title)
		{
			$title = $record->title ? wd_shorten($record->title, 32, .75, $shortened) : '<';

			$rc .= '<span class="title"' . ($shortened ? ' title="' . wd_entities($record->title) . '"' : '') . '>' . $title . '</span>';
		}
		else
		{
			$rc .= '<em class="light">Untitled node <span class="small">(' . $this->id . '.' . $record->nid . ')</span></em>';
		}

		return $rc;
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
			$category = $descriptor[Module::T_CATEGORY];

			if (!isset($categories[$category]))
			{
				$category = $default_category;
			}

			$title = t($descriptor[Module::T_TITLE], array(), array('scope' => 'module.title'));

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
			$head .= '<th>&nbsp;</th><th>' . t($category, array(), array('scope' => 'module_category.title')) . '</th>';
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

			$title = wd_shorten($record->title, 48);
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

	protected function provide_view_view(Query $query, WdPatron $patron)
	{
		global $core, $page;

		$record = $query->one;

		if (!$record)
		{
			throw new HTTPException('The requested record was not found.', array(), 404);
		}
		else if (!$record->is_online)
		{
			if (!$core->user->has_permission(Module::PERMISSION_ACCESS, $record->constructor))
			{
				throw new HTTPException('The requested record requires authentication.', array(), 401);
			}

			$record->title .= ' ✎';
		}

		$page->title = $record->title;

		return $record;
	}

	protected function provide_view_alter_query($name, Query $query, array $conditions)
	{
		$query->own->similar_site->similar_language;

		if ($name != 'view')
		{
			$query->where('is_online = 1');
		}

		return parent::provide_view_alter_query($name, $query, $conditions);
	}

	protected function provide_view_alter_query_view($query, array $conditions)
	{
		if (isset($conditions['nid']))
		{
			$query->where('nid = ?', $conditions['nid']);
		}
		else if (isset($conditions['slug']))
		{
			$query->where('slug = ?', $conditions['slug']);
		}
		else
		{
			$query->where('is_online = 1');
		}

		return $query->limit(1);
	}

	protected function provide_view_list(Query $query, \WdPatron $patron)
	{
		global $core;

		$count = $query->count;

		$limit = $core->site->metas->get("$this->flat_id.limits.list", 10);
		$position = isset($_GET['page']) ? $_GET['page'] : 0;

		if ($limit)
		{
			$query->limit($position * $limit, $limit);
		}

		$patron->context['self']['range'] = array
		(
			'count' => $count,
			'page' => $position,
			'limit' => $limit
		);

		return $query->all;
	}
}

namespace BrickRouge\Element\Nodes;

class Pager extends \BrickRouge\Pager
{
	protected function getURL($n)
	{
		return '#' . $n;
	}
}