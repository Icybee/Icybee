<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Module;
use ICanBoogie\Operation;
use BrickRouge\Form;
use BrickRouge\Element;

class user_users_WdMarkups
{
	static protected function model($name='users')
	{
		return parent::model($name);
	}

	public static function connect(array $args, WdPatron $patron, $template)
	{
		global $core;

		$user = $core->user;

		if (!$user->is_guest)
		{
			$form = new Form
			(
				array
				(
					Form::T_HIDDENS => array
					(
						Operation::DESTINATION => 'users',
						Operation::NAME => Module\Users::OPERATION_DISCONNECT
					),

					Element::T_CHILDREN => array
					(
						new Element
						(
							Element::E_SUBMIT, array
							(
								Element::T_INNER_HTML => t('Deconnection'),
								'class' => 'disconnect'
							)
						)
					),

					'name' => 'disconnect'
				),

				'div'
			);

			$rc = '<p>';
			$rc .= t
			(
				'Welcome back :username&nbsp;!
				You can use :icybee to manage your articles and images.', array
				(
					':username' => $user->username,
					':publisher' => '<a href="/admin">Icybee</a>'
				)
			);
			$rc .= '</p>';
			$rc .= $form;

			return $rc;
		}
		else
		{
			$form = new element\Form
			(
				array
				(
					Form::T_HIDDENS => array
					(
						Operation::DESTINATION => 'users',
						Operation::NAME => Module\Users::OPERATION_CONNECT
					),

					Element::T_CHILDREN => array
					(
						User::USERNAME => new Text
						(
							array
							(
								Form::T_LABEL => 'Username',
								Element::T_REQUIRED => true
							)
						),

						User::PASSWORD => new Element
						(
							Element::E_PASSWORD, array
							(
								Form::T_LABEL => 'Password',
								Element::T_REQUIRED => true
							)
						),

						new Element
						(
							Element::E_SUBMIT, array
							(
								Element::T_INNER_HTML => t('Connect'),
								'class' => 'connect'
							)
						)
					),

					'class' => 'login',
					'name' => 'connect'
				),

				'div'
			);

			return $form;
		}
	}

	static public function user(WdHook $hook, WdPatrong $patron, $template)
	{
		$entry = self::model()->find($args['select']);

		if (!$entry)
		{
			return;
		}

		return $patron($template, $entry);
	}
}