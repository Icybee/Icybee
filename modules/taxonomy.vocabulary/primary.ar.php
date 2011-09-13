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

	public $vid;
	public $siteid;
	public $vocabulary;
	public $vocabularyslug;
	public $is_tags;
	public $is_multiple;
	public $is_required;
	public $weight;
}