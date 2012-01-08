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

use ICanBoogie\ActiveRecord\Content,
	ICanBoogie\ActiveRecord\Query,

	BrickRouge,
	BrickRouge\Element,
	BrickRouge\Form,
	BrickRouge\Text;

use Icybee\Manager\Contents as Manager;

use WdPatron;
use WdMultiEditorElement;
use moo_WdEditorElement;
use WdDateElement;

/**
 * The "contents" module extends the "system.nodes" module by offrering a subtitle, a body
 * (with a customizable editor), an optional excerpt, a date and a new visibility option (home).
 */
class Contents extends Nodes
{
	const OPERATION_HOME_INCLUDE = 'home_include';
	const OPERATION_HOME_EXCLUDE = 'home_exclude';

	/**
	 * Overrites the "view", "list" and "home" views to provide different titles and providers.
	 *
	 * @see Icybee.Module::__get_views()
	 */
	protected function __get_views()
	{
		return array
		(
			'view' => array
			(
				'title' => "Content detail",
				'provider' => 'Icybee\Views\Contents\Provider',
				'assets' => array()
			),

			'list' => array
			(
				'title' => 'Content list',
				'provider' => 'Icybee\Views\Contents\Provider',
				'assets' => array()
			),

			'home' => array
			(
				'title' => 'Content home',
				'provider' => 'Icybee\Views\Contents\Provider',
				'assets' => array()
			)
		)

		+ parent::__get_views();
	}

	protected function block_manage()
	{
		return new Manager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'title', /*'category',*/ 'uid', 'is_home_excluded', 'is_online', 'date', 'modified'
				)
			)
		);
	}

	protected function block_config()
	{
		return array
		(
			Element::GROUPS => array
			(
				'limits' => array
				(
					'title' => '.limits',
					'class' => 'form-section flat'
				)
			),

			Element::CHILDREN => array
			(
				"local[$this->flat_id.default_editor]" => new Text
				(
					array
					(
						Form::LABEL => '.default_editor'
					)
				),

				"local[$this->flat_id.use_multi_editor]" => new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => '.use_multi_editor'
					)
				),

				"local[$this->flat_id.limits.home]" => new Text
				(
					array
					(
						Form::LABEL => '.limits_home',
						Element::DEFAULT_VALUE => 3,
						Element::GROUP => 'limits'
					)
				),

				"local[$this->flat_id.limits.list]" => new Text
				(
					array
					(
						Form::LABEL => '.limits_list',
						Element::DEFAULT_VALUE => 10,
						Element::GROUP => 'limits'
					)
				)
			)
		);
	}

	protected function block_edit(array $properties, $permission)
	{
		global $core;

		$default_editor = $core->site->metas->get($this->flat_id . '.default_editor', 'moo');
		$use_multi_editor = $core->site->metas->get($this->flat_id . '.use_multi_editor');

		if ($use_multi_editor)
		{

		}
		else
		{

		}

		return wd_array_merge_recursive
		(
			parent::block_edit($properties, $permission),

			array
			(
				Element::GROUPS => array
				(
					'contents' => array
					(
						'title' => '.contents',
						'class' => 'form-section flat'
					),

					'date' => array
					(

					)
				),

				Element::CHILDREN => array
				(
					Content::SUBTITLE => new Text
					(
						array
						(
							Form::LABEL => '.subtitle'
						)
					),

					Content::BODY => new WdMultiEditorElement
					(
						$properties['editor'] ? $properties['editor'] : $default_editor, array
						(
							Element::LABEL_MISSING => 'Contents', // TODO-20110205: scope => 'element', 'missing', 'label'
							Element::GROUP => 'contents',
							Element::REQUIRED => true,

							'rows' => 16
						)
					),

					Content::EXCERPT => new moo_WdEditorElement
					(
						array
						(
							Form::LABEL => '.excerpt',
							Element::GROUP => 'contents',
							Element::DESCRIPTION => ".excerpt",

							'rows' => 3
						)
					),

					Content::DATE => new BrickRouge\Date
					(
						array
						(
							Form::LABEL => 'Date',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => date('Y-m-d')
						)
					),

					Content::IS_HOME_EXCLUDED => new Element
					(
						Element::TYPE_CHECKBOX, array
						(
							Element::LABEL => ".is_home_excluded",
							Element::GROUP => 'visibility',
							Element::DESCRIPTION => ".is_home_excluded"
						)
					)
				)
			)
		);
	}

	protected function provide_view_view(Query $query, WdPatron $patron)
	{
		global $page;

		$record = $query->one;
		$url_variables = $page->url_variables;

		if (!$record && empty($url_variables['nid']) && isset($url_variables['slug']))
		{
			$slug = $page->url_variables['slug'];
			$tries = $this->model->select('nid, slug')->where('constructor = ?', $this->id)->visible->order('date DESC')->pairs;
			$key = null;
			$max = 0;

			foreach ($tries as $nid => $compare)
			{
				similar_text($slug, $compare, $p);

				if ($p > $max)
				{
					$key = $nid;

					if ($p > 90)
					{
						break;
					}

					$max = $p;
				}
			}

			if ($key)
			{
				$record = $this->model[$key];

				wd_log('The content node %title was rescued !', array('%title' => $record->title));
			}
		}

		$query->one = $record;

		return parent::provide_view_view($query, $patron);
	}

	protected function provide_view_home(Query $query, WdPatron $patron)
	{
		global $page;

		$limit = $page->site->metas->get("$this->flat_id.limits.home", 5);

		if ($limit)
		{
			$query->limit($limit);
		}

		return $query->all;
	}

	protected function provide_view_alter_query($name, Query $query, array $conditions)
	{
		if (!empty($conditions['year']))
		{
			$query->where('YEAR(date) = ?', $conditions['year']);
		}

		if (!empty($conditions['month']))
		{
			$query->where('MONTH(date) = ?', $conditions['month']);
		}

		if (!empty($conditions['day']))
		{
			$query->where('DAY(date) = ?', $conditions['day']);
		}

		if (!empty($conditions['categoryslug']))
		{
			$query->where('nid IN (SELECT nid FROM {prefix}taxonomy_terms
			INNER JOIN {prefix}taxonomy_terms__nodes USING(vtid) WHERE termslug = ?)', $conditions['categoryslug']);
		}

		return parent::provide_view_alter_query($name, $query, $conditions);
	}

	protected function provide_view_alter_query_home(Query $query, array $conditions)
	{
		return $query->where('is_home_excluded = 0')->order('date DESC');
	}

	protected function provide_view_alter_query_list(Query $query, array $conditions)
	{
		return $query->order('date DESC');
	}

	protected function provide_view_archives(Query $query)
	{
		$records = $query->own->visible->order('date DESC')->all;

		$by_date = array();

		foreach ($records as $record)
		{
			$date = substr($record->date, 0, 4);// . '-01-01';
			$by_date[$date][] = $record;
		}

		return $by_date;
	}
}