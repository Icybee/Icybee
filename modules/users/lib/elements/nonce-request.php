<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users;

use ICanBoogie\Operation;

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class NonceRequestForm extends Form
{
	/**
	 * Adds the "widget.css" and "widget.js" assets.
	 *
	 * @param \Brickrouge\Document $document
	 */
	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add(DIR . 'public/widget.css');
		$document->js->add(DIR . 'public/widget.js');
	}

	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			$attributes + array
			(
				Form::ACTIONS => new Button
				(
					'Send', array
					(
						'type' => 'submit',
						'class' => 'btn-warning'
					)
				),

				Form::RENDERER => 'Simple',

				Form::HIDDENS => array
				(
					Operation::DESTINATION => 'users',
					Operation::NAME => 'nonce-login-request'
				),

				Element::CHILDREN => array
				(
					'email' => new Text
					(
						array
						(
							Form::LABEL => 'your_email',
							Element::REQUIRED => true
						)
					)
				),

				Element::WIDGET_CONSTRUCTOR => 'NonceRequest',

				'class' => 'widget-nonce-request',
				'name' => 'users/nonce-request'
			)
		);
	}
}