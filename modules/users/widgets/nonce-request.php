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

use ICanBoogie\Operation;
use BrickRouge;
use BrickRouge\Button;
use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;

class NonceRequest extends Form
{
	public function __construct($tags=array())
	{
		parent::__construct
		(
			$tags + array
			(
				self::T_RENDERER => 'Simple',

				self::T_HIDDENS => array
				(
					Operation::DESTINATION => 'users',
					Operation::NAME => 'nonce-login-request'
				),

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

				'class' => 'widget-nonce-request group password login stacked',
				'name' => 'users/nonce-request'
			)
		);
	}

	/**
	 * Adds the "widget.css" and "widget.js" assets.
	 *
	 * @param \BrickRouge\Document $document
	 */
	protected static function add_assets(\BrickRouge\Document $document)
	{
		$document->css->add('../assets/widget.css');
		$document->js->add('../assets/widget.js');

		parent::add_assets($document);
	}
}