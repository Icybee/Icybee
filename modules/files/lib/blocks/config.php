<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Files;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

/**
 * A block to configure files.
 */
class ConfigBlock extends \Icybee\ConfigBlock
{
	protected function get_children()
	{
		$ns = $this->module->flat_id;

		return array_merge
		(
			parent::get_children(), array
			(
				"local[$ns.max_file_size]" => new Text
				(
					array
					(
						Form::LABEL => 'max_file_size',
						Text::ADDON => 'Kb', // TODO-20110206: use conventions
						Element::DEFAULT_VALUE => 16000,

						'class' => 'measure',
						'size' => 6
					)
				)
			)
		);
	}
}