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
use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;
use BrickRouge\Widget;
use Icybee\Manager;

class Users extends \Icybee\Module
{
	const OPERATION_CONNECT = 'connect';
	const OPERATION_DISCONNECT = 'disconnect';
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

	public function install()
	{
		$path = ICanBoogie\DOCUMENT_ROOT . 'protected/all/config/user.php';

		if (!file_exists($path))
		{
			if (!is_writable(dirname($path)))
			{
				throw new Exception('The file %path must be writable during installation', array('%path' => $path));
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

		return parent::install();
	}

	public function is_installed()
	{
		global $core;

		$config = $core->configs['user'];

		if (!$config)
		{
			wd_log_error('Missings <em>user</em> config!');

			return false;
		}

		return parent::is_installed();
	}

	protected function block_connect()
	{
		global $core;

		$core->document->js->add('public/connect.js');

		$form = (string) $this->form_connect();

		$label_email = t('label.your_email');
		$label_cancel = t('label.cancel');
		$label_send = t('label.send');

		return <<<EOT
<div id="login">
	<div class="wrapper">
		<div class="slide">$form</div>
	</div>

	<div class="wrapper password" style="height: 0">
		<div class="slide">
		<form class="group password login stacked" name="password" action="">
			<div class="field field--email required clearfix">
				<label class="input-label required" for="email">$label_email</label>

				<div class="input">
					<input type="text" name="email" />
					<div class="element-description"><a href="#" class="cancel">$label_cancel</a></div>
				</div>
			</div>

			<div class="field field--submit clearfix">
				<div class="input">
				<button class="warn" type="submit">$label_send</button>
				</div>
			</div>
		</form>
		</div>
	</div>
</div>
EOT;
	}

	protected function block_disconnect()
	{
		return new Form
		(
			array
			(
				Form::T_HIDDENS => array
				(
					Operation::NAME => self::OPERATION_DISCONNECT,
					Operation::DESTINATION => $this->id
				),

				Element::T_CHILDREN => array
				(
					new Element
					(
						Element::E_SUBMIT, array
						(
							Element::T_INNER_HTML => t('disconnect', array(), array('scope' => array('user_users', 'form', 'label')))
						)
					)
				)
			)
		);
	}

	public function form_connect()
	{
		global $core, $document;

		if (isset($document))
		{
			$document->css->add('public/connect.css');
		}

		return new Form
		(
			array
			(
				Form::T_RENDERER => 'Simple',

				Form::T_HIDDENS => array
				(
					Operation::DESTINATION => $this,
					Operation::NAME => self::OPERATION_CONNECT,
					Operation::SESSION_TOKEN => $core->session->token
				),

				Element::T_CHILDREN => array
				(
					User::USERNAME => new Element
					(
						Element::E_TEXT, array
						(
							Form::T_LABEL => 'username',
							Element::T_REQUIRED => true,

							'class' => 'autofocus'
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

					'#submit' => new Element
					(
						Element::E_SUBMIT, array
						(
							Element::T_INNER_HTML => t('connect', array(), array('scope' => 'user_users.element.label')),

							'class' => 'continue'
						)
					)
				),

				'class' => 'group login stacked',
				'name' => self::OPERATION_CONNECT
			),

			'div'
		);
	}

	protected function block_edit(array $properties, $permission)
	{
		global $core, $document;

		$document->js->add('public/edit.js');

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
				Element::E_CHECKBOX_GROUP, array
				(
					Form::T_LABEL => '.roles',
					Element::T_GROUP => 'advanced',
					Element::T_OPTIONS => $core->models['users.roles']->select('rid, name')->where('rid != 1')->order('rid')->pairs,
					Element::T_OPTIONS_DISABLED => array(2 => true),
					Element::T_REQUIRED => true,
					Element::T_DESCRIPTION => '.roles',

					'class' => 'framed list sortable',
					'value' => $properties_rid
				)
			);
		}

		#
		# restricted sites
		#

		$restricted_sites_el = null;

		if ($user->has_permission(self::PERMISSION_ADMINISTER, $this))
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

			$restricted_sites_el = new Element
			(
				Element::E_CHECKBOX_GROUP, array
				(
					Form::T_LABEL => '.siteid',
					Element::T_OPTIONS => $core->models['sites']->select('siteid, IF(admin_title != "", admin_title, concat(title, ":", language))')->order('admin_title, title')->pairs,
					Element::T_GROUP => 'advanced',
					Element::T_DESCRIPTION => '.siteid',

					'class' => 'list framed',
					'value' => $value
				)
			);
		}

		$rc = array
		(
			Form::T_DISABLED => !$permission,

			Element::T_GROUPS => array
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

			Element::T_CHILDREN => array
			(
				#
				# name group
				#

				User::FIRSTNAME => new Text
				(
					array
					(
						Form::T_LABEL => '.firstname',
						Element::T_GROUP => 'contact',

						//'class' => 'autofocus'
					)
				),

				User::LASTNAME => new Text
				(
					array
					(
						Form::T_LABEL => '.lastname',
						Element::T_GROUP => 'contact'
					)
				),

				User::USERNAME => $administer ? new Text
				(
					array
					(
						Form::T_LABEL => '.username',
						Element::T_GROUP => 'contact',
						Element::T_REQUIRED => true
					)
				) : null,

				User::DISPLAY => new Element
				(
					'select', array
					(
						Form::T_LABEL => '.display_as',
						Element::T_GROUP => 'contact',
						Element::T_OPTIONS => $display_options
					)
				),

				#
				# connection group
				#

				User::EMAIL => new Text
				(
					array
					(
						Form::T_LABEL => '.email',
						Element::T_GROUP => 'connection',
						Element::T_REQUIRED => true,

						'autocomplete' => 'off'
					)
				),

				new Element
				(
					'div', array
					(
						Element::T_GROUP => 'connection',
						Element::T_CHILDREN => array
						(
							'<div>',

							User::PASSWORD => new Element
							(
								Element::E_PASSWORD, array
								(
									Element::T_LABEL => '.password',
									Element::T_LABEL_POSITION => 'above',
									Element::T_DESCRIPTION => '.password_' . ($uid ? 'update' : 'new'),

									'value' => '',
									'autocomplete' => 'off'
								)
							),

							'</div><div>',

							User::PASSWORD . '-verify' => new Element
							(
								Element::E_PASSWORD, array
								(
									Element::T_LABEL => '.password_confirm',
									Element::T_LABEL_POSITION => 'above',
									Element::T_DESCRIPTION => '.password_confirm',

									'value' => '',
									'autocomplete' => 'off'
								)
							),

							'</div>'
						),

						'style' => 'column-count; -moz-column-count: 2; -webkit-column-count: 2'
					)
				),

				User::IS_ACTIVATED => ($uid == 1 || !$administer) ? null : new Element
				(
					Element::E_CHECKBOX, array
					(
						Element::T_LABEL => '.is_activated',
						Element::T_GROUP => 'connection',
						Element::T_DESCRIPTION => '.is_activated'
					)
				),

				User::ROLES => $role_el,

				User::LANGUAGE => new Element
				(
					'select', array
					(
						Form::T_LABEL => 'Language',
						Element::T_GROUP => 'advanced',
						Element::T_DESCRIPTION => '.language',
						Element::T_OPTIONS => array(null => '') + $core->locale->conventions['localeDisplayNames']['languages']
					)
				),

				'timezone' => new Widget\TimeZone
				(
					array
					(
						Form::T_LABEL => '.timezone',
						Element::T_GROUP => 'advanced',
						Element::T_DESCRIPTION => "Si la zone horaire n'est pas définie celle
						du site sera utilisée à la place."
					)
				),

				User::RESTRICTED_SITES => $restricted_sites_el
			)
		);

		if ($permission_modify_own_profile)
		{
			$rc[Element::T_CHILDREN]['#submit'] = new Element
			(
				Element::E_SUBMIT, array
				(
					Element::T_GROUP => 'save',
					Element::T_INNER_HTML => 'Enregistrer',

					'class' => 'save'
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
			Element::T_GROUPS => array
			(
				'notifies.password' => array
				(
					'title' => 'Envoie des informations de connexion',
					'class' => 'form-section flat'/*,
					'no-panels' => true*/
				)
			),

			Element::T_CHILDREN => array
			(
				/*
				"local[$this->flat_id.notifies.password]" => new WdEMailNotifyElement
				(
					array
					(
						Element::T_GROUP => 'notifies.password',
						//Element::T_DEFAULT => self::$config_default['notifies']['password']

						Element::T_DEFAULT => array
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