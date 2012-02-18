<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Images;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class EditBlock extends \ICanBoogie\Modules\Files\EditBlock
{
	protected $accept = array
	(
		'image/gif', 'image/png', 'image/jpeg'
	);

	protected $uploader_class = 'Brickrouge\Widget\ImageUpload';

	protected function alter_children(array $children, array &$properties, array &$attributes)
	{
		return array_merge
		(
			parent::alter_children($children, $properties, $attributes), array
			(
				'alt' => new Text
				(
					array
					(
						Form::LABEL => 'alt'
					)
				)
			)
		);
	}
}