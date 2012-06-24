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

class Vocabulary extends ActiveRecord
{
	const VID = 'vid';
	const SITEID = 'siteid';
	const VOCABULARY = 'vocabulary';
	const VOCABULARYSLUG = 'vocabularyslug';
	const IS_TAGS = 'is_tags';
	const IS_MULTIPLE = 'is_multiple';
	const IS_REQUIRED = 'is_required';
	const WEIGHT = 'weight';
	const SCOPE = 'scope';

	/**
	 * Identifier of the vocabulary
	 *
	 * @var int
	 */
	public $vid;

	/**
	 * Identifier of the site the vocabulary is attached to.
	 *
	 * This value maybe 0, indicating that the vocabulary is not attached to any site.
	 *
	 * @var int
	 */
	public $siteid;

	/**
	 * Name of the vocabulary.
	 *
	 * @var string
	 */
	public $vocabulary;

	/**
	 * Version of the {@vocabulary} property that can be used in URLs. Written in lowercase, it
	 * contains only unaccentuated letters, numbers and hyphens.
	 *
	 * @var string
	 */
	public $vocabularyslug;

	/**
	 * Can terms be defined as coma-separated values ?
	 *
	 * @var bool
	 */
	public $is_tags;

	/**
	 * Can multiple terms be associated to a node ?
	 *
	 * @var bool
	 */
	public $is_multiple;

	/**
	 * Is the vocabulary required for the associated scope ?
	 *
	 * @var bool
	 */
	public $is_required;

	/**
	 * Weight of the vocabulary relative to other vocabulary.
	 *
	 * @var int
	 */
	public $weight;

	/**
	 * Removes the `scope` and `terms` properties.
	 *
	 * @see ICanBoogie.ActiveRecord::__sleep()
	 */
	public function __sleep()
	{
		$properties = parent::__sleep();

		$properties = array_flip($properties);

		unset($properties['scope']);
		unset($properties['terms']);

		return array_flip($properties);
	}

	/**
	 * Returns the scope of the vocabulary, that is the constructors to which the vocabulary is
	 * associated.
	 *
	 * @return array[]string
	 */
	protected function get_scope()
	{
		global $core;

		return $core->models['taxonomy.vocabulary/scopes']->select('constructor')
		->find_by_vid($this->vid)->all(\PDO::FETCH_COLUMN);
	}

	/**
	 * Returns the terms associated to this vocabulary, ordered by weight.
	 *
	 * @return array[]Term
	 */
	protected function get_terms()
	{
		global $core;

		$model = $core->models['taxonomy.terms'];

		return $model->select('term.*')->find_by_vid($this->vid)
		->order('weight')->all(\PDO::FETCH_CLASS, __NAMESPACE__ . '\Term', array($model));
	}
}