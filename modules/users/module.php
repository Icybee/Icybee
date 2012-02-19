<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Users;

use ICanBoogie;
use ICanBoogie\ActiveRecord\User;
use ICanBoogie\Exception;
use ICanBoogie\Operation;
use Brickrouge;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;
use Brickrouge\Widget;

class Module extends \Icybee\Module
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
			Model::T_CONSTRUCTOR => $this->id
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
		return new \Brickrouge\Widget\Users\LoginCombo;
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
}