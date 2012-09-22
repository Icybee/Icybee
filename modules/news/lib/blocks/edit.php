<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\News;

use ICanBoogie\ActiveRecord\Content;

use Brickrouge\Form;
use Brickrouge\Element;

/**
 * Edit block for news.
 */
class EditBlock extends \ICanBoogie\Modules\Contents\EditBlock
{
	protected function get_children()
	{
		return parent::get_children() + array
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
		);
	}
}