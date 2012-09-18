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

use ICanBoogie\ActiveRecord\RecordNotFound;
use ICanBoogie\ActiveRecord\Users\Role;
use ICanBoogie\Exception;
use ICanBoogie\Security;
use ICanBoogie\Module;
use ICanBoogie\PropertyNotWritable;

/**
 * A user.
 *
 * @property-read string $name The formatted name of the user.
 * @property-read boolean $is_admin true if the user is admin, false otherwise.
 * @property-read boolean $is_guest true if the user is a guest, false otherwise.
 * @property-read \ICanBoogie\ActiveRecord\Users\Role $role
 */
class User extends \ICanBoogie\ActiveRecord
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

	/**
	 * User identifier.
	 *
	 * @var string
	 */
	public $uid;

	/**
	 * User email.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * User password.
	 *
	 * The property is only used to modifiy the password.
	 *
	 * @var string
	 */
	public $password;

	/**
	 * User password hash.
	 *
	 * @var string
	 */
	private $password_hash;

	/**
	 * Username of the user.
	 *
	 * @var string
	 */
	public $username;

	/**
	 * Firstname of the user.
	 *
	 * @var string
	 */
	public $firstname;

	/**
	 * Lastname of the user.
	 *
	 * @var string
	 */
	public $lastname;

	/**
	 * Prefered format to create the value of the {@link $name} property.
	 *
	 * @var string
	 */
	public $display;

	/**
	 * The date the user record was created.
	 *
	 * @var string
	 */
	public $created;

	/**
	 * The date of the last connection of the user.
	 *
	 * @var string
	 */
	public $lastconnection;

	/**
	 * Constructor of the user record (module id).
	 *
	 * @var string
	 */
	public $constructor;

	/**
	 * Prefered language of the user.
	 *
	 * @var string
	 */
	public $language;

	/**
	 * Prefered timezone of the user.
	 *
	 * @var string
	 */
	public $timezone;

	/**
	 * State of the user account activation.
	 *
	 * @var bool
	 */
	public $is_activated;

	/**
	 * Returns the formatted name of the user.
	 *
	 * The format of the name is defined by the {@link $display} property. The {@link $username},
	 * {@link $firstname} and {@link $lastname} properties can be used to format the name.
	 *
	 * This is the getter for the {@link $name} magic property.
	 *
	 * @return string
	 */
	protected function volatile_get_name()
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

	/**
	 * @throws PropertyNotWritable in attempt to write {@link $name}.
	 */
	protected function volatile_set_name()
	{
		throw new PropertyNotWritable(array('name', $this));
	}

	/**
	 * Returns the role of the user.
	 *
	 * This is the getter for the {@link $role} magic property.
	 *
	 * @return \ICanBoogie\ActiveRecord\Users\Role
	 */
	protected function get_role()
	{
		global $core;

		$permissions = array();
		$name = null;

		foreach ($this->roles as $role)
		{
			$name .= ', ' . $role->name;

			foreach ($role->perms as $access => $permission)
			{
				$permissions[$access] = $permission;
			}
		}

		$role = new Role();
		$role->perms = $permissions;

		if ($name)
		{
			$role->name = substr($name, 2);
		}

		return $role;
	}

	/**
	 * Returns all the roles associated with the user.
	 *
	 * This is the getter for the {@link $roles} magic property.
	 *
	 * @return array
	 */
	protected function get_roles()
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

		$rids = $core->models['users/has_many_roles']->select('rid')->filter_by_uid($this->uid)->all(\PDO::FETCH_COLUMN);

		if (!in_array(2, $rids))
		{
			array_unshift($rids, 2);
		}

		try
		{
			return $core->models['users.roles']->find($rids);
		}
		catch (RecordNotFound $e)
		{
			trigger_error($e->getMessage());

			return array_filter($e->records);
		}
	}

	/**
	 * Checks if the user is the admin user.
	 *
	 * This is the getter for the {@link $is_admin} magic property.
	 *
	 * @return boolean true if the user is the admin user, false otherwise.
	 */
	protected function volatile_get_is_admin()
	{
		return ($this->uid == 1);
	}

	/**
	 * @throws PropertyNotWritable in attempt to write {@link $is_admin}.
	 */
	protected function volatile_set_is_admin()
	{
		throw new PropertyNotWritable(array('is_admin', $this));
	}

	/**
	 * Checks if the user is a guest user.
	 *
	 * This is the getter for the {@link $is_guest} magic property.
	 *
	 * @return boolean true if the user is a guest user, false otherwise.
	 */
	protected function volatile_get_is_guest()
	{
		return ($this->uid == 0);
	}

	/**
	 * @throws PropertyNotWritable in attempt to write {@link $is_guest}.
	 */
	protected function volatile_set_is_guest()
	{
		throw new PropertyNotWritable(array('is_guest', $this));
	}

	/**
	 * Returns the ids of the sites the user is restricted to.
	 *
	 * This is the getter for the {@link $restricted_sites_ids} magic property.
	 *
	 * @return array The array is empty if the user has no site restriction.
	 */
	protected function get_restricted_sites_ids()
	{
		global $core;

		return $this->is_admin ? array() : $core->models['users/has_many_sites']->select('siteid')->filter_by_uid($this->uid)->all(\PDO::FETCH_COLUMN);
	}

	/**
	 * Checks if the user has a given permission.
	 *
	 * @param string|int $permission
	 * @param mixed $target
	 *
	 * @return int|bool
	 */
	public function has_permission($permission, $target=null)
	{
		if ($this->is_admin)
		{
			return Module::PERMISSION_ADMINISTER;
		}

		return $this->role->has_permission($permission, $target);
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
			throw new \InvalidArgumentException("<q>record</q> must be an object.");
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
				'<q>password_salt</q> is empty in the <q>user</q> config, here is one generated randomly: %salt', array
				(
					'%salt' => Security::generate_token(64, 'wide')
				)
			);
		}

		return sha1(Security::pbkdf2($password, $config['password_salt']));
	}

	/**
	 * Compares a password to the user password.
	 *
	 * @param string $password
	 *
	 * @return bool `true` if the password match the password hash, `false` otherwise.
	 */
	public function same_password($password)
	{
		return $this->password_hash === self::hash_password($password);
	}

	/**
	 * Logs the user in.
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
	 * @see \ICanBoogie\Modules\Users\Hooks\get_user_id
	 */
	public function login()
	{
		global $core;

		if (!$this->uid)
		{
			throw new \Exception('Guest users cannot login.');
		}

		$core->user = $this;
		$core->user_id = $this->uid;
		$core->session->regenerate_id(true);
		$core->session->regenerate_token();
		$core->session->users['user_id'] = $this->uid;

		return true;
	}

	/**
	 * Log the user out.
	 *
	 * The following things happend when the user is logged out:
	 *
	 * - The `$core->user` property is unset.
	 * - The `$core->user_id` property is unset.
	 * - The `$core->session->users['user_id']` property is unset.
	 */
	public function logout()
	{
		global $core;

		unset($core->user);
		unset($core->user_id);
		unset($core->session->users['user_id']);
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