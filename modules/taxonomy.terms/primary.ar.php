<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord\Taxonomy;

use ICanBoogie\ActiveRecord;

class Term extends ActiveRecord implements \IteratorAggregate
{
	const VTID = 'vtid';
	const VID = 'vid';
	const TERM = 'term';
	const TERMSLUG = 'termslug';
	const WEIGHT = 'weight';

	public $vtid;
	public $vid;
	public $term;
	public $termslug;
	public $weight;

	public function __construct($model='taxonomy.terms')
	{
		parent::__construct($model);
	}

	public function __toString()
	{
		return $this->term;
	}

	/**
	 * Returns the iterator for the IteratorAggregate interface.
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->nodes);
	}

	protected function __get_vocabulary()
	{
		global $core;

		return $this->vid ? $core->models['taxonomy.vocabulary'][$this->vid] : null;
	}

	/**
	 * Returns the nodes associated with the term.
	 *
	 * @return array The nodes associated with the term, or an empty array if there is none.
	 */
	protected function __get_nodes()
	{
		global $core;

		$ids = $this->_model
		->select('nid')
		->joins('INNER JOIN {prefix}taxonomy_terms__nodes ttnode USING(vtid)') // FIXME-20110614 Query should be cleverer then that
		->joins(':nodes')
		->find_by_vtid($this->vtid)
		->where('is_online = 1')
		->order('ttnode.weight')
		->all(PDO::FETCH_COLUMN);

		if (!$ids)
		{
			return array();
		}

		$constructors = $core->models['nodes']->select('constructor, nid')->where(array('nid' => $ids))
		->all(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

		$rc = array_flip($ids);

		foreach ($constructors as $constructor => $constructor_ids)
		{
			$records = $core->models[$constructor]->find($constructor_ids);

			foreach ($records as $id => $record)
			{
				$rc[$id] = $record;
			}
		}

		return array_values($rc);
	}

	/**
	 * Returns the CSS class of the term.
	 *
	 * @return string
	 */
	protected function __get_css_class()
	{
		return $this->css_class();
	}

	/**
	 * Returns the CSS class names of the term.
	 *
	 * @return array[string]mixed
	 */
	protected function __get_css_class_names()
	{
		return array
		(
			'type' => 'term',
			'id' => 'term-' . $this->vtid,
			'slug' => 'term-slug--' . $this->termslug,
			'vid' => $this->vid ? 'vocabulary-' . $this->vid : null,
			'vslug' => $this->vid ? 'vocabulary-slug--' . $this->vocabulary->vocabularyslug : null
		);
	}

	/**
	 * Return the CSS class of the term.
	 *
	 * @param string|array $modifiers CSS class names modifiers
	 *
	 * @return string
	 */
	public function css_class($modifiers=null)
	{
		$names = $this->css_class_names;
		$names = array_filter($names);

// 		Event::fire('alter_css_class_names', array('names' => &$names), $this);

		if ($modifiers)
		{
			if (is_string($modifiers))
			{
				$modifiers = explode(' ', $modifiers);
				$modifiers = array_map('trim', $modifiers);
				$modifiers = array_filter($modifiers);
			}

			foreach ($modifiers as $k => $modifier)
			{
				if ($modifier{0} == '-')
				{
					unset($names[substr($modifier, 1)]);
					unset($modifiers[$k]);
				}
			}

			if ($modifiers)
			{
				$names = array_intersect_key($names, array_combine($modifiers, $modifiers));
			}
		}

		array_walk($names, function(&$v, $k) {

			if ($v === true) $v = $k;

		});

		return implode(' ', $names);
	}
}