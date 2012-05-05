<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Users\Roles;

use ICanBoogie\ActiveRecord\Users\Role;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * A block to edit roles.
 */
class EditBlock extends \Icybee\EditBlock
{
	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				Role::NAME => new Text
				(
					array
					(
						Form::LABEL => 'name',
						Element::REQUIRED => true
					)
				)
			)
		);
	}
}