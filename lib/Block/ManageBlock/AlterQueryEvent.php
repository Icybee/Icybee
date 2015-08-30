<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block\ManageBlock;

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\Event;
use Icybee\Block\ManageBlock;

class AlterQueryEvent extends Event
{
	public $query;

	public $options;

	public function __construct(ManageBlock $target, Query $query, Options $options)
	{
		$this->query = $query;
		$this->options = $options;

		parent::__construct($target, 'alter_query');
	}
}
