<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use ICanBoogie;
use ICanBoogie\ActiveRecord\User;
use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\Exception;
use ICanBoogie\Operation;
use BrickRouge;
use BrickRouge\Button;
use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;
use BrickRouge\Widget;
use Icybee\Manager;

class Users extends \Icybee\Module
{
	const OPERATION_LOGIN = 'login';
	const OPERATION_LOGOUT = 'logout';
	const OPERATION_ACTIVATE = 'activate';
	const OPERATION_DEACTIVATE = 'deactivate';
	const OPERATION_IS_UNIQUE = 'is_unique';

	static $config_default = array
	(
		'notifies' => array
		(
			/*
			'password' => array
			(
				'subject' => 'Vos paramètres de connexion à Icybee',
				'from' => 'no-reply@wdpublisher.com',
				'template' => 'Bonjour,

Voici vos paramètres de connexion au système de gestion de contenu Icybee :

Identifiant : "#{@username}" ou "#{@email}"
Mot de passe : "#{@password}"

Une fois connecté vous pourrez modifier votre mot de passe. Pour cela cliquez sur votre nom dans la barre de titre et éditez votre profil.

Cordialement'
			)
			*/
		)
	);

	protected function resolve_primary_model_tags($tags)
	{
		return parent::resolve_model_tags($tags, 'primary') + array
		(
			Model\Users::T_CONSTRUCTOR => $this->id
		);
	}

	/**
	 * Override the method to check if the "user" config is correctly created.
	 *
	 * @see ICanBoogie.Module::is_installed()
	 */
	public function is_installed(\ICanBoogie\Errors $errors)
	{
		global $core;

		$config = $core->configs['user'];

		if (!$config)
		{
			$errors[$this->id] = t('The <q>user</q> config is missing.');

			return false;
		}

		return parent::is_installed($errors);
	}

	/**
	 * Override the method to create the "user" config.
	 *
	 * The "user" config is stored at "<DOCUMENT_ROOT>/protected/all/config/user.php" and contains
	 * the randomly generated salts used to encrypt users' password, the unlock login tokens and
	 * the nonce login tokens.
	 *
	 * The "user" config file must be writtable.
	 *
	 * @see ICanBoogie.Module::install()
	 */
	public function install(\ICanBoogie\Errors $errors)
	{
		$path = ICanBoogie\DOCUMENT_ROOT . 'protected/all/config/user.php';

		if (!file_exists($path))
		{
			if (!is_writable(dirname($path)))
			{
				$errors[$this->id] = t('The file %path must be writable during installation', array('%path' => $path));

				return false;
			}

			$password_salt = ICanBoogie\Security::generate_token(64, 'wide');
			$unlock_login_salt = ICanBoogie\Security::generate_token(64, 'wide');
			$nonce_login_salt = ICanBoogie\Security::generate_token(64, 'wide');

			$config = <<<EOT
<?php

return array
(
	'password_salt' => '$password_salt',
	'unlock_login_salt' => '$unlock_login_salt',
	'nonce_login_salt' => '$nonce_login_salt'
);
EOT;

			file_put_contents($path, $config);
		}

		return parent::install($errors);
	}

	protected function block_connect()
	{
		return new \BrickRouge\Widget\Users\LoginCombo;
	}

	protected function block_logout()
	{
		return new Form
		(
			array
			(
				Form::HIDDENS => array
				(
					Operation::NAME => self::OPERATION_LOGOUT,
					Operation::DESTINATION => $this->id
				),

				Element::CHILDREN => array
				(
					new Button
					(
						'logout', array
						(
							'type' => 'submit'
						)
					)
				)
			)
		);
	}

	protected function block_edit(array $properties, $permission)
	{
		global $core;

		$core->document->js->add('assets/admin.js');

		#
		# permissions
		#

		$user = $core->user;

		$administer = false;
		$permission = false;
		$permission_modify_own_profile = false;

		$uid = $properties[User::UID];

		if ($user->has_permission(self::PERMISSION_MANAGE, $this))
		{
			$administer = true;
			$permission = true;
		}
		else if (($user->uid == $uid) && $user->has_permission('modify own profile'))
		{
			$permission_modify_own_profile = true;
			$permission = true;
		}

		#
		# display options
		#

		$display_options = array
		(
			'<username>'
		);

		if ($properties[User::USERNAME])
		{
			$display_options[0] = $properties[User::USERNAME];
		}

		$firstname = $properties[User::FIRSTNAME];

		if ($firstname)
		{
			$display_options[1] = $firstname;
		}

		$lastname = $properties[User::LASTNAME];

		if ($lastname)
		{
			$display_options[2] = $lastname;
		}

		if ($firstname && $lastname)
		{
			$display_options[3] = $firstname . ' ' . $lastname;
			$display_options[4] = $lastname . ' ' . $firstname;
		}

		#
		# roles
		#

		$role_el = null;

		if ($uid != 1 && $user->has_permission(self::PERMISSION_ADMINISTER, $this))
		{
			$properties_rid = array
			(
				2 => true
			);

			if ($uid)
			{
				$record = $this->model[$uid];

				foreach ($record->roles as $role)
				{
					$properties_rid[$role->rid] = true;
				}
			}

			$role_el = new Element
			(
				Element::TYPE_CHECKBOX_GROUP, array
				(
					Form::LABEL => '.roles',
					Element::GROUP => 'advanced',
					Element::OPTIONS => $core->models['users.roles']->select('rid, name')->where('rid != 1')->order('rid')->pairs,
					Element::OPTIONS_DISABLED => array(2 => true),
					Element::REQUIRED => true,
					Element::DESCRIPTION => '.roles',

					'class' => 'framed list sortable',
					'value' => $properties_rid
				)
			);
		}

		#
		# languages
		#

		$languages = $core->locale->conventions['localeDisplayNames']['languages'];

		uasort($languages, 'wd_unaccent_compare_ci');

		#
		# restricted sites
		#

		$restricted_sites_el = null;

		if (!$user->is_admin && $user->has_permission(self::PERMISSION_ADMINISTER, $this))
		{
			$value = array();

			if ($uid)
			{
				$record = $this->model[$uid];
				$value = $record->restricted_sites_ids;

				if ($value)
				{
					$value = array_combine($value, array_fill(0, count($value), true));
				}
			}

			$restricted_sites_options = $core->models['sites']->select('siteid, IF(admin_title != "", admin_title, concat(title, ":", language))')->order('admin_title, title')->pairs;

			if ($restricted_sites_options)
			{
				$restricted_sites_el = new Element
				(
					Element::TYPE_CHECKBOX_GROUP, array
					(
						Form::LABEL => '.siteid',
						Element::OPTIONS => $restricted_sites_options,
						Element::GROUP => 'advanced',
						Element::DESCRIPTION => '.siteid',

						'class' => 'list framed',
						'value' => $value
					)
				);
			}
		}

		$rc = array
		(
			Form::DISABLED => !$permission,

			Element::GROUPS => array
			(
				'contact' => array
				(
					'title' => '.contact',
					'class' => 'form-section flat'
				),

				'connection' => array
				(
					'title' => '.connection',
					'class' => 'form-section flat'
				),

				'advanced' => array
				(
					'title' => '.advanced',
					'class' => 'form-section flat'
				)
			),

			Element::CHILDREN => array
			(
				#
				# name group
				#

				User::FIRSTNAME => new Text
				(
					array
					(
						Form::LABEL => '.firstname',
						Element::GROUP => 'contact',

						//'class' => 'autofocus'
					)
				),

				User::LASTNAME => new Text
				(
					array
					(
						Form::LABEL => '.lastname',
						Element::GROUP => 'contact'
					)
				),

				User::USERNAME => $administer ? new Text
				(
					array
					(
						Form::LABEL => '.Username',
						Element::GROUP => 'contact',
						Element::REQUIRED => true
					)
				) : null,

				User::DISPLAY => new Element
				(
					'select', array
					(
						Form::LABEL => '.display_as',
						Element::GROUP => 'contact',
						Element::OPTIONS => $display_options
					)
				),

				#
				# connection group
				#

				User::EMAIL => new Text
				(
					array
					(
						Form::LABEL => '.email',
						Element::GROUP => 'connection',
						Element::REQUIRED => true,

						'autocomplete' => 'off'
					)
				),

				new Element
				(
					'div', array
					(
						Element::GROUP => 'connection',
						Element::CHILDREN => array
						(
							'<div>',

							User::PASSWORD => new Text
							(
								array
								(
									Element::LABEL => '.password',
									Element::LABEL_POSITION => 'above',
									Element::DESCRIPTION => '.password_' . ($uid ? 'update' : 'new'),

									'autocomplete' => 'off',
									'type' => 'password',
									'value' => ''
								)
							),

							'</div><div>',

							User::PASSWORD . '-verify' => new Text
							(
								array
								(
									Element::LABEL => '.password_confirm',
									Element::LABEL_POSITION => 'above',
									Element::DESCRIPTION => '.password_confirm',

									'autocomplete' => 'off',
									'type' => 'password',
									'value' => ''
								)
							),

							'</div>'
						),

						'style' => 'column-count; -moz-column-count: 2; -webkit-column-count: 2'
					)
				),

				User::IS_ACTIVATED => ($uid == 1 || !$administer) ? null : new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						Element::LABEL => '.is_activated',
						Element::GROUP => 'connection',
						Element::DESCRIPTION => '.is_activated'
					)
				),

				User::ROLES => $role_el,

				User::LANGUAGE => new Element
				(
					'select', array
					(
						Form::LABEL => 'Language',
						Element::GROUP => 'advanced',
						Element::DESCRIPTION => '.language',
						Element::OPTIONS => array(null => '') + $languages
					)
				),

				'timezone' => new Widget\TimeZone
				(
					array
					(
						Form::LABEL => '.timezone',
						Element::GROUP => 'advanced',
						Element::DESCRIPTION => "Si la zone horaire n'est pas définie celle
						du site sera utilisée à la place."
					)
				),

				User::RESTRICTED_SITES => $restricted_sites_el
			)
		);

		if ($permission_modify_own_profile)
		{
			$rc[Element::CHILDREN]['#submit'] = new Button
			(
				'Save', array
				(
					Element::GROUP => 'save',
					'class' => 'save',
					'type' => 'submit'
				)
			);
		}

		return $rc;
	}

	protected function block_profile()
	{
		global $core;

		$core->document->page_title = t('My profile');

		$module = $this;
		$user = $core->user;
		$constructor = $user->constructor;

		if ($constructor != $this->id)
		{
			$module = $core->modules[$user->constructor];
		}

		return $module->getBlock('edit', $user->uid);
	}

	protected function block_manage()
	{
		return new Manager\Users
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array(User::USERNAME, User::EMAIL, 'role', User::IS_ACTIVATED, User::CREATED, User::LASTCONNECTION)
			)
		);
	}

	protected function block_config()
	{
		return array
		(
			Element::GROUPS => array
			(
				'notifies.password' => array
				(
					'title' => 'Envoie des informations de connexion',
					'class' => 'form-section flat'/*,
					'no-panels' => true*/
				)
			),

			Element::CHILDREN => array
			(
				/*
				"local[$this->flat_id.notifies.password]" => new WdEMailNotifyElement
				(
					array
					(
						Element::GROUP => 'notifies.password',
						//Element::DEFAULT_VALUE => self::$config_default['notifies']['password']

						Element::DEFAULT_VALUE => array
						(
							'subject' => 'Vos paramètres de connexion à Icybee',
							'from' => 'no-reply@' . $_SERVER['HTTP_HOST'],
							'template' => 'Bonjour,

Voici vos paramètres de connexion à la plateforme de gestion de contenu Publishr :

Identifiant : "#{@username}" ou "#{@email}"
Mot de passe : "#{@password}"

Une fois connecté vous pourrez modifier votre mot de passe. Pour cela cliquez sur votre nom dans la barre de titre et éditez votre profil.

Cordialement'
						)
					)
				)
				*/
			)
		);
	}
}