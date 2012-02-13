<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Contents\News;

use ICanBoogie\ActiveRecord\Content;
use ICanBoogie\Modules\Contents\Manager;
use Brickrouge\Element;
use Brickrouge\Form;

class Module extends \ICanBoogie\Modules\Contents\Module
{
	protected function block_manage()
	{
		return new Manager
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
				Element::CHILDREN => array
				(
					Content::DATE => new \Brickrouge\Date
					(
						array
						(
							Form::LABEL => 'Date',
							Element::REQUIRED => true,
							Element::DEFAULT_VALUE => date('Y-m-d')
						)
					)
				)
			)
		);
	}
}