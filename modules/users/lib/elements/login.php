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

use Brickrouge;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class LoginForm extends Form
{
	/**
	 * Adds the "widget.css" and "widget.js" assets.
	 *
	 * @param Brickrouge\Document $document
	 */
	static protected function add_assets(\Brickrouge\Document $document)
	{
		$document->css->add('../../assets/widget.css');
		$document->js->add('../../assets/widget.js');

		parent::add_assets($document);
	}

	public function __construct(array $attributes=array())
	{
		global $core;

		parent::__construct
		(
			$attributes + array
			(
				Form::ACTIONS => array
				(
					new Button
					(
						'Connect', array
						(
							'type' => 'submit',
							'class' => 'btn-primary'
						)
					)
				),

				Form::RENDERER => 'Simple',

				Form::HIDDENS => array
				(
					Operation::DESTINATION => 'users',
					Operation::NAME => Module::OPERATION_LOGIN,
					Operation::SESSION_TOKEN => $core->session->token
				),

				Element::CHILDREN => array
				(
					User::USERNAME => new Text
					(
						array
						(
							Form::LABEL => 'username',
							Element::REQUIRED => true,

							'autofocus' => true
						)
					),

					User::PASSWORD => new Text
					(
						array
						(
							Form::LABEL => 'password',
							Element::REQUIRED => true,
							Element::DESCRIPTION => '<a href="#lost-password" rel="nonce-request">' . t
							(
								'lost_password', array(), array
								(
									'scope' => array('user_users', 'form', 'label'),
									'default' => 'I forgot my password'
								)
							) . '</a>',

							'type' => 'password'
						)
					)
				),

				Element::WIDGET_CONSTRUCTOR => 'Login',

				'class' => 'widget-login',
				'name' => 'users/login'
			)
		);
	}
}