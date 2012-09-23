<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Nodes;

use ICanBoogie\Operation;

use Brickrouge\Button;
use Brickrouge\Form;

class ExportBlock extends Form
{
	public function __construct(Module $module, array $attributes=array())
	{
		parent::__construct
		(
			$attributes + array
			(
				Form::HIDDENS => array
				(
					Operation::DESTINATION => $module->id,
					Operation::NAME => 'export'
				),

				Form::ACTIONS => new Button('Export', array('class' => 'btn-primary', 'type' => 'submit')),

				'class' => 'form-primary'
			)
		);
	}

	protected function render_inner_html()
	{
		return "ouic" . parent::render_inner_html();
	}
}