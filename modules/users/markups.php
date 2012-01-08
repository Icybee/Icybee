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
use BrickRouge\Button;
use BrickRouge\Element;
use BrickRouge\Form;

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
					Form::HIDDENS => array
					(
						Operation::DESTINATION => 'users',
						Operation::NAME => Module\Users::OPERATION_DISCONNECT
					),

					Element::CHILDREN => array
					(
						new Button
						(
							'logout', array
							(
								'class' => 'logout',
								'type' => 'submit'
							)
						)
					),

					'name' => 'logout'
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
					Form::HIDDENS => array
					(
						Operation::DESTINATION => 'users',
						Operation::NAME => Module\Users::OPERATION_CONNECT
					),

					Element::CHILDREN => array
					(
						User::USERNAME => new Text
						(
							array
							(
								Form::LABEL => 'Username',
								Element::REQUIRED => true
							)
						),

						User::PASSWORD => new Text
						(
							array
							(
								Form::LABEL => 'Password',
								Element::REQUIRED => true,

								'type' => 'password'
							)
						),

						new Button
						(
							'Connect', array
							(
								'class' => 'connect',
								'type' => 'submit'
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