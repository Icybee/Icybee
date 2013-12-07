<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users\Roles;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * A block to edit roles.
 */
class EditBlock extends \Icybee\EditBlock
{
	protected function lazy_get_children()
	{
		return array_merge
		(
			parent::lazy_get_children(), array
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