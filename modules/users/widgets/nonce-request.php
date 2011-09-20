<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Widget\Users;

use BrickRouge;
use BrickRouge\Button;
use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;

class NonceRequest extends Form
{
	public function __construct($tags)
	{
		parent::__construct
		(
			$tags + array
			(
				self::T_RENDERER => 'Simple',

				self::T_CHILDREN => array
				(
					'email' => new Text
					(
						array
						(
							Form::T_LABEL => 'your_email',
							Element::T_REQUIRED => true
						)
					),

					'submit' => new Button
					(
						'Send', array
						(
							'type' => 'submit',
							'class' => 'warn'
						)
					)
				),

				'name' => 'users/nonce-request'
			)
		);
	}
}