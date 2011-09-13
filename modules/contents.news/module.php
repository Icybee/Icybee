<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module\Contents;

use ICanBoogie\ActiveRecord\Content;
use BrickRouge\Element;
use BrickRouge\Form;
use Icybee\Manager;

class News extends \ICanBoogie\Module\Contents
{
	protected function block_manage()
	{
		return new Manager\Contents
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'title', 'uid', /*'category',*/ 'is_home_excluded', 'is_online', 'date', 'modified'
				)
			)
		);
	}

	protected function block_edit(array $properties, $permission)
	{
		return wd_array_merge_recursive
		(
			parent::block_edit($properties, $permission), array
			(
				Element::T_CHILDREN => array
				(
					Content::DATE => new Element\Date
					(
						array
						(
							Form::T_LABEL => 'Date',
							Element::T_REQUIRED => true,
							Element::T_DEFAULT => date('Y-m-d')
						)
					)
				)
			)
		);
	}
}