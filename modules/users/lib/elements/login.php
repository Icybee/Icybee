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

use ICanBoogie\I18n;
use ICanBoogie\Operation;

use Brickrouge;
use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class LoginForm extends Form
{
	const PASSWORD_RECOVERY_LINK = '#password-recovery-link';

	public $lost_password = array();

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

		$this->lost_password = new A(I18n\t('lost_password', array(), array('scope' => 'users.label', 'default' => 'I forgot my password')), "#lost-password", array('rel' => 'nonce-request'));

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
							Element::DESCRIPTION => $this->lost_password,

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

	public function render()
	{
		$password_recovery_link = $this[self::PASSWORD_RECOVERY_LINK];

		if ($password_recovery_link)
		{
			$this->lost_password['href'] = $password_recovery_link;
		}

		return parent::render();
	}
}