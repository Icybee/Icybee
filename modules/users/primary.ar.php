<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

use ICanBoogie\ActiveRecord;
use ICanBoogie\ActiveRecord\Users\Role;
use ICanBoogie\Exception;
use ICanBoogie\Security;
use ICanBoogie\Module;

/**
 * @author Olivier Laviale <olivier.laviale@gmail.com>
 *
 * @property string $name The formatted name of the user.
 * @property-read boolean $is_admin true if the user is admin, false otherwise.
 * @property-read boolean $is_guest true if the user is a guest, false otherwise.
 */
class User extends ActiveRecord
{
	const UID = 'uid';
	const EMAIL = 'email';
	const PASSWORD = 'password';
	const PASSWORD_HASH = 'password_hash';
	const USERNAME = 'username';
	const FIRSTNAME = 'firstname';
	const LASTNAME = 'lastname';
	const DISPLAY = 'display';
	const CREATED = 'created';
	const LASTCONNECTION = 'lastconnection';
	const CONSTRUCTOR = 'constructor';
	const LANGUAGE = 'language';
	const TIMEZONE = 'timezone';
	const IS_ACTIVATED = 'is_activated';
	const ROLES = 'roles';
	const RESTRICTED_SITES = 'restricted_sites';

	public $uid;
	public $email;
	public $password;
	private $password_hash;
	public $username;
	public $firstname;
	public $lastname;
	public $display;
	public $created;
	public $lastconnection;
	public $constructor;
	public $language;
	public $timezone;
	public $is_activated;

	/**
	 * Returns the formatted name of the user.
	 *
	 * @return string
	 */
	protected function __get_name()
	{
		$values = array
		(
			$this->username,
			$this->firstname,
			$this->lastname,
			$this->firstname . ' ' . $this->lastname,
			$this->lastname . ' ' . $this->firstname
		);

		$rc = isset($values[$this->display]) ? $values[$this->display] : null;

		if (!trim($rc))
		{
			return $this->username;
		}

		return $rc;
	}

	protected function __get_role()
	{
		global $core;

		$permissions = array();

		foreach ($this->roles as $role)
		{
			foreach ($role->perms as $access => $permission)
			{
				$permissions[$access] = $permission;
			}
		}

		$role = new Role($core->models['users.roles']);
		$role->perms = $permissions;

		return $role;
	}

	/**
	 * Returns all the roles associated with the user.
	 *
	 * @return array
	 */
	protected function __get_roles()
	{
		global $core;

		try
		{
			$model = $core->models['users.roles'];

			if (!$this->uid)
			{
				return array($model[1]);
			}
		}
		catch (\Exception $e)
		{
			return array();
		}

		$rids = $core->models['users/has_many_roles']->select('rid')->find_by_uid($this->uid)->all(\PDO::FETCH_COLUMN);

		if (!in_array(2, $rids))
		{
			array_unshift($rids, 2);
		}

		return $core->models['users.roles']->find($rids);
	}

	/**
	 * Checks if the user is the admin user.
	 *
	 * This is the getter for the {@link $is_admin} magic property.
	 *
	 * @return boolean true if the user is the admin user, false otherwise.
	 */
	protected function __volatile_get_is_admin()
	{
		return ($this->uid == 1);
	}

	protected function __set_is_admin()
	{
		throw new Exception('The %property property is readonly', array('%property' => 'is_admin'));
	}

	/**
	 * Checks if the user is a guest user.
	 *
	 * This is the getter for the {@link $is_guest} magic property.
	 *
	 * @return boolean true if the user is a guest user, false otherwise.
	 */
	protected function __volatile_get_is_guest()
	{
		return ($this->uid == 0);
	}

	protected function __set_is_guest()
	{
		throw new Exception('The %property property is readonly', array('%property' => 'is_guest'));
	}

	/**
	 * Returns the ids of the sites the user is restricted to.
	 *
	 * @return array The array is empty if the user has no site restriction.
	 */
	protected function __get_restricted_sites_ids()
	{
		global $core;

		return $this->is_admin ? array() : $core->models['users/has_many_sites']->select('siteid')->find_by_uid($this->uid)->all(\PDO::FETCH_COLUMN);
	}

	public function has_permission($access, $target=null)
	{
		if ($this->is_admin)
		{
			return Module::PERMISSION_ADMINISTER;
		}

		return $this->role->has_permission($access, $target);
	}

	/**
	 * Checks if the user has the ownership of an entry.
	 *
	 * If the ownership information is missing from the entry (the 'uid' property is null), the user
	 * must have the ADMINISTER level to be considered the owner.
	 *
	 * @param $module
	 * @param $record
	 *
	 * @return boolean
	 */
	public function has_ownership($module, $record)
	{
		$permission = $this->has_permission(Module::PERMISSION_MAINTAIN, $module);

		if ($permission == Module::PERMISSION_ADMINISTER)
		{
			return true;
		}

		if (is_array($record))
		{
			$record = (object) $record;
		}

		if (!is_object($record))
		{
			throw new Exception('%var must be an object', array('%var' => 'entry'));
		}

		if (empty($record->uid))
		{
			return $permission == Module::PERMISSION_ADMINISTER;
		}

		if (!$permission || $record->uid != $this->uid)
		{
			return false;
		}

		return true;
	}

	public static function hash_password($password)
	{
		global $core;

		$config = $core->configs['user'];

		if (!$config || empty($config['password_salt']))
		{
			throw new Exception
			(
				'<em>password_salt</em> is empty in the <em>user</em> config, here is one generated randomly: %salt', array
				(
					'%salt' => Security::generate_token(64, 'wide')
				)
			);
		}

		return sha1(Security::pbkdf2($password, $config['password_salt']));
	}

	/**
	 * Compare a password to the user password.
	 *
	 * @param string $password
	 *
	 * @return bool true if the password match the password hash, false otherwise.
	 */
	public function is_password($password)
	{
		return $this->password_hash === self::hash_password($password);
	}

	/**
	 * Log the user in.
	 *
	 * A user is logged in by setting its id in the `application[user_agent]` session key.
	 *
	 * Note: The method does *not* checks the user authentication !
	 *
	 * The following things happen when the user is logged in:
	 *
	 * - The `$core->user` property is set to the user.
	 * - The `$core->user_id` property is set to the user id.
	 * - The session id is regenerated and the user id, ip and user agent are stored in the session.
	 *
	 * @throws Exception when attempting to login a guest user.
	 *
	 * @return boolean true if the login is successful.
	 *
	 * @see \ICanBoogie\Hooks\Users\get_user_id
	 */
	public function login()
	{
		global $core;

		if (!$this->uid)
		{
			throw new Exception('Guest users cannot be logged in');
		}

		$core->user = $this;
		$core->user_id = $this->uid;
		$core->session->regenerate_id(true);
		$core->session->regenerate_token();
		$core->session->users['user_id'] = $this->uid;

		return true;
	}

	public function url($id)
	{
		global $core;

		if ($id == 'profile')
		{
			return $core->site->path . '/admin/profile';
		}

		return '#unknown-url';
	}
}