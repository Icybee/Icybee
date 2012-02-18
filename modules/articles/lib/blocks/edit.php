<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Articles;

use ICanBoogie\ActiveRecord\Content;

use Brickrouge\Form;
use Brickrouge\Element;

class EditBlock extends \ICanBoogie\Modules\Contents\EditBlock
{
	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				Content::DATE => new \Brickrouge\DateTime
				(
					array
					(
						Form::LABEL => 'Date',
						Element::REQUIRED => true,
						Element::DEFAULT_VALUE => date('Y-m-d H:i:s')
					)
				)
			)
		);
	}
}
