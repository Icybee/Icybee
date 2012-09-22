<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Articles;

use Brickrouge\Form;
use Brickrouge\Element;

class EditBlock extends \ICanBoogie\Modules\Contents\EditBlock
{
	protected function get_children()
	{
		return array_merge
		(
			parent::get_children(), array
			(
				Article::DATE => new \Brickrouge\DateTime
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
