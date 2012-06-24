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

use ICanBoogie\ActiveRecord\User;
use ICanBoogie\Operation;

use Brickrouge;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Login extends Form
{
	public function __construct($tags=array())
	{
		global $core;

		parent::__construct
		(
			$tags + array
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
					Operation::NAME => \ICanBoogie\Modules\Users\Module::OPERATION_LOGIN,
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

				'class' => 'widget-login',
				'name' => 'users/login',
				'data-widget-constructor' => 'Login'
			)
		);
	}

	/**
	 * Adds the "widget.css" and "widget.js" assets.
	 *
	 * @param Brickrouge\Document $document
	 */
	protected static function add_assets(\Brickrouge\Document $document)
	{
		$document->css->add('../assets/widget.css');
		$document->js->add('../assets/widget.js');

		parent::add_assets($document);
	}
}