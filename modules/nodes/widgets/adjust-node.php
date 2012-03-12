<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Widget;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Query;

use Brickrouge\Element;
use Brickrouge\Pager;
use Brickrouge\Text;

class AdjustNode extends \Brickrouge\Widget
{
	const T_CONSTRUCTOR = '#adjust-constructor';

	public function __construct($tags=array(), $dummy=null)
	{
		parent::__construct
		(
			'div', $tags + array
			(
				self::T_CONSTRUCTOR => 'nodes',

				'class' => 'adjust',
				'data-adjust' => 'adjust-node'
			)
		);

// 		$this->dataset['adjust'] = 'adjust-node';
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('adjust-node.css');
		$document->js->add('adjust-node.js');
	}

	protected function render_inner_html()
	{
		global $core;

		$rc = parent::render_inner_html();
		$constructor = $this[self::T_CONSTRUCTOR];

		$rc .= '<div class="search">';
		$rc .= new Text(array('class' => 'search', 'placeholder' => t('Search')));
		$rc .= $this->get_results(array('selected' => $this['value']), $constructor);
		$rc .= '</div>';

		$this->dataset['constructor'] = $constructor;

		return $rc;
	}

	public function get_results(array $options=array(), $constructor='nodes')
	{
		$options += array
		(
			'page' => null,
			'search' => null,
			'selected' => null
		);

		list($records, $range) = $this->get_records($constructor, $options);

		$rc = $records ? $this->format_records($records, $range, $options) : $this->get_placeholder($options);

		return '<div class="results">' . $rc . '</div>';
	}

	protected function get_records($constructor, array $options, $limit=10)
	{
		global $core;

		$model = $core->models[$constructor];

		if ($constructor == 'nodes')
		{
			$query = new Query($model);
		}
		else
		{
			$query = $model->find_by_constructor($constructor);
		}

		$search = $options['search'];

		if ($search)
		{
			$conditions = '';
			$conditions_args = array();
			$words = explode(' ', trim($options['search']));
			$words = array_map('trim', $words);

			foreach ($words as $word)
			{
				$conditions .= ' AND title LIKE ?';
				$conditions_args[] = '%' . $word . '%';
			}

			$query->where(substr($conditions, 4), $conditions_args);
		}

		$query->visible;

		$count = $query->count;
		$page = $options['page'];
		$selected = $options['selected'];

		if ($selected && $page === null)
		{
			$ids = $query->select('nid')->order('modified DESC')->all(\PDO::FETCH_COLUMN);
			$positions = array_flip($ids);
			$pos = isset($positions[$selected]) ? $positions[$selected] : 0;
			$page = floor($pos / $limit);
			$ids = array_slice($ids, $page * $limit, $limit);
			$records = $ids ? $model->find($ids) : null;
		}
		else
		{
			$records = $query->order('modified DESC')->limit($page * $limit, $limit)->all;
		}

		return array
		(
			$records, array
			(
				Pager::T_COUNT => $count,
				Pager::T_LIMIT => $limit,
				Pager::T_POSITION => $page
			)
		);
	}

	protected function format_records($records, array $range, array $options)
	{
		$selected = $options['selected'];

		$rc = '<ul>';

		foreach ($records as $record)
		{
			$rc .= $this->format_record($record, $selected, $range, $options);
		}

		$n = count($records);
		$limit = $range[Pager::T_LIMIT];

		if ($n < $limit)
		{
			$rc .= str_repeat('<li class="empty">&nbsp;</li>', $limit - $n);
		}

		$rc .= '</ul>';

		$rc .= new Element\Nodes\Pager
		(
			'div', $range + array
			(
				'class' => 'pager'
			)
		);

		return $rc;
	}

	protected function format_record(ActiveRecord\Node $record, $selected, array $range, array $options)
	{
		$recordid = $record->nid;

		return new Element
		(
			'li', array
			(
				Element::INNER_HTML => wd_shorten($record->title),
				Element::DATASET => array
				(
					ActiveRecord\Node::NID => $recordid,
					ActiveRecord\Node::TITLE => $record->title
				),

				'class' => $recordid == $selected ? 'selected' : null
			)
		);
	}

	protected function get_placeholder(array $options)
	{
		$search = $options['search'];

		return '<p class="no-response">' .

		(
			$search
			? t('Aucun enregistrement ne correspond aux termes de recherche spécifiés (%search)', array('%search' => $search))
			: t("Il n'y a pas d'enregistrements")
		)

		. '</p>';
	}
}