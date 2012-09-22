<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Images;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class EditBlock extends \Icybee\Modules\Files\EditBlock
{
	protected $accept = array
	(
		'image/gif', 'image/png', 'image/jpeg'
	);

	protected $uploader_class = 'Icybee\Modules\Images\ImageUpload';

	protected function get_children()
	{
		return array_merge
		(
			parent::get_children(), array
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