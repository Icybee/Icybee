<?php

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
	public function __construct($tags)
	{
		global $core;

		parent::__construct
		(
			$tags + array
			(
				Form::T_RENDERER => 'Simple',

				Form::T_HIDDENS => array
				(
					Operation::DESTINATION => 'users',
					Operation::NAME => 'connect',
					Operation::SESSION_TOKEN => $core->session->token
				),

				Element::T_CHILDREN => array
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
							Element::T_DESCRIPTION => '<a href="#lost-password">' . t
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

				'class' => 'group login stacked',
				'name' => 'users/login'
			)
		);
	}
}