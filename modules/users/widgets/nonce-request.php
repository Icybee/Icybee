<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Widget\Users;

use ICanBoogie\Operation;
use Brickrouge;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class NonceRequest extends Form
{
	public function __construct($tags=array())
	{
		parent::__construct
		(
			$tags + array
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

				'class' => 'widget-nonce-request',
				'name' => 'users/nonce-request',
				'data-widget-constructor' => 'NonceRequest'
			)
		);
	}

	/**
	 * Adds the "widget.css" and "widget.js" assets.
	 *
	 * @param \Brickrouge\Document $document
	 */
	protected static function add_assets(\Brickrouge\Document $document)
	{
		$document->css->add('../assets/widget.css');
		$document->js->add('../assets/widget.js');

		parent::add_assets($document);
	}
}