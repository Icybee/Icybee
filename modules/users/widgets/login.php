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

use ICanBoogie\ActiveRecord\User;
use ICanBoogie\Operation;

use BrickRouge;
use BrickRouge\Button;
use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;

class Login extends Form
{
	public function __construct($tags=array())
	{
		global $core;

		parent::__construct
		(
			$tags + array
			(
				self::T_RENDERER => 'Simple',

				self::T_HIDDENS => array
				(
					Operation::DESTINATION => 'users',
					Operation::NAME => \ICanBoogie\Module\Users::OPERATION_LOGIN,
					Operation::SESSION_TOKEN => $core->session->token
				),

				self::T_CHILDREN => array
				(
					User::USERNAME => new Text
					(
						array
						(
							Form::T_LABEL => 'username',
							Element::T_REQUIRED => true,

							'autofocus' => true
						)
					),

					User::PASSWORD => new Element
					(
						Element::E_PASSWORD, array
						(
							Form::T_LABEL => 'password',
							Element::T_REQUIRED => true,
							Element::T_DESCRIPTION => '<a href="#lost-password" rel="nonce-request">' . t
							(
								'lost_password', array(), array
								(
									'scope' => array('user_users', 'form', 'label'),
									'default' => 'I forgot my password'
								)
							)

							.

							'</a>'
						)
					),

					'#submit' => new Button
					(
						'Connect', array
						(
							'type' => 'submit',
							'class' => 'continue'
						)
					)
				),

				'class' => 'widget-login group login stacked',
				'name' => 'users/login'
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