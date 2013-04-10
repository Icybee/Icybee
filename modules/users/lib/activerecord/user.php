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

use ICanBoogie\ActiveRecord\RecordNotFound;
use ICanBoogie\DateTime;
use ICanBoogie\Exception;
use ICanBoogie\Security;
use ICanBoogie\PropertyNotWritable;

use Icybee\Modules\Users\Roles\Role;

/**
 * A user.
 *
 * @property-read string $name The formatted name of the user.
 * @property-read boolean $is_admin true if the user is admin, false otherwise.
 * @property-read boolean $is_guest true if the user is a guest, false otherwise.
 * @property-read \Icybee\Modules\Users\Users\Role $role
 *
 * @property \DateTime|mixed $logged_at The date at which the user logged.
 */
class User extends \ICanBoogie\ActiveRecord implements \Icybee\CSSClassNames
{
	const UID = 'uid';
	const EMAIL = 'email';
	const PASSWORD = 'password';
	const PASSWORD_HASH = 'password_hash';
	const USERNAME = 'username';
	const FIRSTNAME = 'firstname';
	const LASTNAME = 'lastname';
	const NICKNAME = 'nickname';
	const CREATED = 'created';
	const LOGGED_AT = 'logged_at';
	const CONSTRUCTOR = 'constructor';
	const LANGUAGE = 'language';
	const TIMEZONE = 'timezone';
	const IS_ACTIVATED = 'is_activated';
	const ROLES = 'roles';
	const RESTRICTED_SITES = 'restricted_sites';

	const NAME_AS = 'name_as';

	/**
	 * The {@link $name} property should be created from `$username`.
	 *
	 * @var int
	 */
	const NAME_AS_USERNAME = 0;

	/**
	 * The {@link $name} property should be created from `$firstname`.
	 *
	 * @var int
	 */
	const NAME_AS_FIRSTNAME = 1;

	/**
	 * The {@link $name} property should be created from `$lastname`.
	 *
	 * @var int
	 */
	const NAME_AS_LASTNAME = 2;

	/**
	 * The {@link $name} property should be created from `$firstname $lastname`.
	 *
	 * @var int
	 */
	const NAME_AS_FIRSTNAME_LASTNAME = 3;

	/**
	 * The {@link $name} property should be created from `$lastname $firstname`.
	 *
	 * @var int
	 */
	const NAME_AS_LASTNAME_FIRSTNAME = 4;

	/**
	 * The {@link $name} property should be created from `$nickname`.
	 *
	 * @var int
	 */
	const NAME_AS_NICKNAME = 5;

	/**
	 * User identifier.
	 *
	 * @var string
	 */
	public $uid;

	/**
	 * Constructor of the user record (module id).
	 *
	 * The property MUST be defined to persist the record.
	 *
	 * @var string
	 */
	public $constructor;

	/**
	 * User email.
	 *
	 * The property MUST be defined to persist the record.
	 *
	 * @var string
	 */
	public $email;

	/**
	 * User password.
	 *
	 * The property is only used to update the {@link $password_hash} property when the
	 * record is saved.
	 *
	 * @var string
	 */
	public $password;

	/**
	 * User password hash.
	 *
	 * Note: The property MUST NOT be private, otherwise only instances of the class can be
	 * initialized with a value, for subclasses instances the property would be `null`.
	 *
	 * @var string
	 */
	protected $password_hash;

	/**
	 * Username of the user.
	 *
	 * The property MUST be defined to persist the record.
	 *
	 * @var string
	 */
	public $username;

	/**
	 * First name of the user.
	 *
	 * @var string
	 */
	public $firstname = '';

	/**
	 * Last name of the user.
	 *
	 * @var string
	 */
	public $lastname = '';

	/**
	 * Nickname of the user.
	 *
	 * @var string
	 */
	public $nickname = '';

	/**
	 * Prefered format to create the value of the {@link $name} property.
	 *
	 * @var string
	 */
	public $name_as = self::NAME_AS_USERNAME;

	/**
	 * The date the user record was created.
	 *
	 * @var string
	 */
	public $created;

	/**
	 * The date at which the user logged.
	 *
	 * @var mixed
	 */
	private $logged_at;

	/**
	 * Returns the login date.
	 *
	 * @return \ICanBoogie\DateTime
	 */
	protected function volatile_get_logged_at()
	{
		$logged_at = $this->logged_at;

		if ($logged_at instanceof DateTime)
		{
			return $logged_at;
		}

		return $this->logged_at = $logged_at === null ? DateTime::none() : new DateTime($logged_at, 'utc');
	}

	/**
	 * Sets the {@link $logget_at} property.
	 *
	 * @param mixed $value
	 */
	protected function volatile_set_logged_at($value)
	{
		$this->logged_at = $value;
	}

	/**
	 * Prefered language of the user.
	 *
	 * @var string
	 */
	public $language = '';

	/**
	 * Prefered timezone of the user.
	 *
	 * @var string
	 */
	public $timezone = '';

	/**
	 * State of the user account activation.
	 *
	 * @var bool
	 */
	public $is_activated = false;

	/**
	 * Defaults `$model` to "users".
	 *
	 * Initializes the {@link $constructor} property with the model identifier if it is not
	 * defined.
	 *
	 * @param string|\ICanBoogie\ActiveRecord\Model $model
	 */
	public function __construct($model='users')
	{
		parent::__construct($model);

		if (empty($this->constructor))
		{
			$this->constructor = $this->_model_id;
		}
	}

	public function __get($property)
	{
		$value = parent::__get($property);

		if ($property === 'css_class_names')
		{
			new \Icybee\AlterCSSClassNamesEvent($this, $value);
		}

		return $value;
	}

	/**
	 * Adds the {@link $logged_at} property.
	 */
	public function __sleep()
	{
		return parent::__sleep() + array
		(
			'logged_at' => 'logged_at'
		);
	}

	/**
	 * Adds the {@link $logged_at} property.
	 */
	public function to_array()
	{
		return parent::to_array() + array
		(
			'logged_at' => $this->volatile_get_logged_at()
		);
	}

	/**
	 * Returns the formatted name of the user.
	 *
	 * The format of the name is defined by the {@link $name_as} property. The {@link $username},
	 * {@link $firstname}, {@link $lastname} and {@link $nickname} properties can be used to
	 * format the name.
	 *
	 * This is the getter for the {@link $name} magic property.
	 *
	 * @return string
	 */
	protected function volatile_get_name()
	{
		$values = array
		(
			self::NAME_AS_USERNAME => $this->username,
			self::NAME_AS_FIRSTNAME => $this->firstname,
			self::NAME_AS_LASTNAME => $this->lastname,
			self::NAME_AS_FIRSTNAME_LASTNAME => $this->firstname . ' ' . $this->lastname,
			self::NAME_AS_LASTNAME_FIRSTNAME => $this->lastname . ' ' . $this->firstname,
			self::NAME_AS_NICKNAME => $this->nickname
		);

		$rc = isset($values[$this->name_as]) ? $values[$this->name_as] : null;

		if (!trim($rc))
		{
			return $this->username;
		}

		return $rc;
	}

	/**
	 * Returns the role of the user.
	 *
	 * This is the getter for the {@link $role} magic property.
	 *
	 * @return \Icybee\Modules\Users\Users\Role
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
			if (!$this->uid)
			{
				return array($core->models['users.roles'][1]);
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
	 * @return boolean `true` if the user is the admin user, `false` otherwise.
	 */
	protected function volatile_get_is_admin()
	{
		return $this->uid == 1;
	}

	/**
	 * Checks if the user is a guest user.
	 *
	 * This is the getter for the {@link $is_guest} magic property.
	 *
	 * @return boolean `true` if the user is a guest user, `false` otherwise.
	 */
	protected function volatile_get_is_guest()
	{
		return !$this->uid;
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
		else if (!is_object($record))
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

	/**
	 * Hashes a password.
	 *
	 * @param string $password
	 *
	 * @throws Exception If the `password_salt` key is empty in the "user" configuration.
	 *
	 * @return string
	 */
	static public function hash_password($password)
	{
		global $core;

		$config = $core->configs['user'];

		if (!$config || empty($config['password_salt']))
		{
			throw new Exception
			(
				'<q>password_salt</q> is empty in the <q>user</q> config, here is one generated randomly: %salt', array
				(
					'%salt' => \ICanBoogie\generate_token_wide()
				)
			);
		}

		return sha1(\ICanBoogie\pbkdf2($password, $config['password_salt']));
	}

	/**
	 * Compares a password to the user's password hash.
	 *
	 * The specified password is hashed with the {@link hash_password} method.
	 *
	 * @param string $password
	 *
	 * @return bool `true` if the hashed password matches the user's password hash,
	 * `false` otherwise.
	 */
	public function compare_password($password)
	{
		return self::hash_password($password) === $this->password_hash;
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
	 * @return boolean true if the login is successful.
	 *
	 * @throws \Exception in attempt to log in a guest user.
	 *
	 * @see \Icybee\Modules\Users\Hooks\get_user_id
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
	 * The following things happen when the user is logged out:
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

		if ($id === 'profile')
		{
			return $core->site->path . '/admin/profile';
		}

		return parent::url($id);
	}

	/**
	 * Returns the CSS class of the node.
	 *
	 * @return string
	 */
	protected function get_css_class()
	{
		return $this->css_class();
	}

	/**
	 * Returns the CSS class names of the node.
	 *
	 * @return array[string]mixed
	 */
	protected function get_css_class_names()
	{
		return array
		(
			'type' => 'user',
			'id' => 'user-' . $this->uid,
			'username' => 'user-' . $this->username,
			'constructor' => 'constructor-' . \ICanBoogie\normalize($this->constructor)
		);
	}

	/**
	 * Return the CSS class of the node.
	 *
	 * @param string|array $modifiers CSS class names modifiers
	 *
	 * @return string
	 */
	public function css_class($modifiers=null)
	{
		return \Icybee\render_css_class($this->css_class_names, $modifiers);
	}
}