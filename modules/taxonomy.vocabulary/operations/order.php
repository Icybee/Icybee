<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Taxonomy\Vocabulary;

use ICanBoogie\Operation;

class Order extends Operation
{
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_OWNERSHIP => true
		)

		+ parent::__get_controls();
	}

	protected function validate()
	{
		return !empty($this->params['terms']);
	}

	protected function process()
	{
		global $core;

		$w = 0;
		$weights = array();
		$update = $core->models['taxonomy.terms']->prepare('UPDATE {self} SET weight = ? WHERE vtid = ?');

		foreach ($this->params['terms'] as $vtid => $dummy)
		{
			$update->execute(array($w, $vtid));
			$weights[$vtid] = $w++;
		}

		return true;
	}
}