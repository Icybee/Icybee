<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Taxonomy\Terms;

/**
 * A term of a vocabulary.
 *
 * @property-read array $nodes_keys
 */
class Term extends \ICanBoogie\ActiveRecord implements \IteratorAggregate
{
	const VTID = 'vtid';
	const VID = 'vid';
	const TERM = 'term';
	const TERMSLUG = 'termslug';
	const WEIGHT = 'weight';

	/**
	 * Identifier of the vocabulary term.
	 *
	 * @var int
	 */
	public $vtid;

	/**
	 * Identifier of the vocabulary the term belongs to.
	 *
	 * @var int
	 */
	public $vid;

	/**
	 * Name of the term.
	 *
	 * @var string
	 */
	public $term;

	/**
	 * Normalized name of the term.
	 *
	 * @var string
	 */
	public $termslug;

	/**
	 * Weight of the term relative to other terms in the same vocabulary.
	 *
	 * @var int
	 */
	public $weight;

	/**
	 * The `$model` property defaults to "taxonomy.terms".
	 *
	 * @param string $model
	 */
	public function __construct($model='taxonomy.terms')
	{
		parent::__construct($model);
	}

	/**
	 * Returns the {@link $term} property.
	 *
	 * @return string
	 */
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

	/**
	 * Returns the vocabulary the term belongs to.
	 *
	 * @return \Icybee\Modules\Taxonomy\Vocabulary\Vocabulary
	 */
	protected function get_vocabulary()
	{
		global $core;

		return $this->vid ? $core->models['taxonomy.vocabulary'][$this->vid] : null;
	}

	static private $nodes_keys_by_vid_and_vtid = array();

	/**
	 * Returns the nodes keys associated with the term.
	 *
	 * Note: In order to reduce the number of database requests, the nodes keys of _all_ the terms
	 * in the same vocabulary are gathered.
	 *
	 * @return array
	 */
	protected function get_nodes_keys()
	{
		global $core;

		$vid = $this->vid;

		if (!isset(self::$nodes_keys_by_vid_and_vtid[$vid]))
		{
			$groups = $core->models['taxonomy.terms/nodes']
			->select('vtid, nid')
			->filter_by_vid($this->vid)
			->order('term_node.weight')
			->all(\PDO::FETCH_COLUMN | \PDO::FETCH_GROUP);

			foreach ($groups as &$keys)
			{
				if (empty($keys)) continue;

				$keys = array_combine($keys, $keys);
			}

			unset($keys);

			self::$nodes_keys_by_vid_and_vtid[$vid] = $groups;
		}

		$vtid = $this->vtid;

		if (!isset(self::$nodes_keys_by_vid_and_vtid[$vid][$vtid]))
		{
			return array();
		}

		return self::$nodes_keys_by_vid_and_vtid[$vid][$vtid];
	}

	/**
	 * Returns the nodes associated with the term.
	 *
	 * @return array The nodes associated with the term, or an empty array if there is none.
	 */
	protected function get_nodes()
	{
		global $core;

		$ids = $this->_model
		->select('nid')
		->joins('INNER JOIN {prefix}taxonomy_terms__nodes ttnode USING(vtid)') // FIXME-20110614 Query should be cleverer then that
		->joins(':nodes')
		->filter_by_vtid($this->vtid)
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
	 * Returns the CSS class names of the term.
	 *
	 * @return array[string]mixed
	 */
	protected function get_css_class_names()
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
		return \Icybee\render_css_class($this->css_class_names, $modifiers);
	}

	/**
	 * Returns the CSS class of the term.
	 *
	 * @return string
	 */
	protected function get_css_class()
	{
		return $this->css_class();
	}
}