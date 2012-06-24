<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie;
use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\ActiveRecord\Site;
use ICanBoogie\Exception;
use ICanBoogie\Event;
use ICanBoogie\I18n;
use ICanBoogie\Object;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge;
use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\SplitButton;

use Icybee\ConfigOperation as ConfigOperation;

/**
 * Extends the Module class with the following features:
 *
 * - Special handling for the 'edit', 'new' and 'configure' blocks.
 * - Inter-users edit lock on records.
 */
class Module extends \ICanBoogie\Module
{
	const OPERATION_CONFIG = 'config';

	/**
	 * Returns the views defined by the module.
	 *
	 * Each _key/value_ pair defines a view, _key_ is its type, _value_ its definition:
	 *
	 * - (string) title: Title of the view. The title of the view is localized use the
	 * "<module_flat_id>.view" scope.
	 *
	 * @return array[string]array
	 */
	protected function get_views()
	{
		return array();
	}

	public function getBlock($name)
	{
		global $core;

		$args = func_get_args();

		$class_name = $this->resolve_block_class($name);

		if ($class_name)
		{
			array_shift($args);

			I18n::push_scope($this->flat_id);
			I18n::push_scope($this->flat_id . '.' . $name);

			try
			{
				$block = new $class_name($this, array(), $args);

				$rendered_block = (string) $block;
			}
			catch (\Exception $e)
			{
				$rendered_block = \ICanBoogie\Debug::format_alert($e);
			}

			I18n::pop_scope();
			I18n::pop_scope();

			return $rendered_block;
		}

// 		\ICanBoogie\log_info("Block class not found for <q>$name</q> falling to callbacks.");

		return call_user_func_array((PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 2)) ? 'parent::' . __FUNCTION__ : array($this, 'parent::' . __FUNCTION__), $args);
	}

	protected function resolve_block_class($name)
	{
		$module = $this;
		$class_name = \ICanBoogie\camelize('-' . $name) . 'Block';

		while ($module)
		{
			$try = $module->descriptor[self::T_NAMESPACE] . '\\' . $class_name;

			if (class_exists($try, true))
			{
				return $try;
			}

			$module = $module->parent;
		}
	}

	private function create_activerecord_lock_name($key)
	{
		return "activerecord_locks.$this->flat_id.$key";
	}

	/**
	 * Locks an activerecord.
	 *
	 * @param int $key
	 *
	 * @throws Exception
	 * @return array|false
	 */
	public function lock_entry($key, &$lock=null)
	{
		global $core;

		$user_id = $core->user_id;

		if (!$user_id)
		{
			throw new Exception('Guest users cannot lock records');
		}

		if (!$key)
		{
			throw new Exception('There is no key baby');
		}

		#
		# is the node already locked by another user ?
		#
		$registry = $core->registry;

		$lock_name = $this->create_activerecord_lock_name($key);
		$lock = json_decode($registry[$lock_name], true);
		$lock_uid = $user_id;
		$lock_until = null;

		$now = time();
		$until = date('Y-m-d H:i:s', $now + 2 * 60);

		if ($lock)
		{
			$lock_uid = $lock['uid'];
			$lock_until = $lock['until'];

			if ($now > strtotime($lock_until))
			{
				#
				# Because the lock has expired we can claim it.
				#

				$lock_uid = $user_id;
			}
			else if ($lock_uid != $user_id)
			{
				return false;
			}
		}

		$lock = array
		(
			'uid' => $lock_uid,
			'until' => $until
		);

		$registry[$lock_name] = json_encode($lock);

		return true;
	}

	public function unlock_entry($key)
	{
		global $core;

		$registry = $core->registry;

		$lock_name = $this->create_activerecord_lock_name($key);
		$lock = json_decode($registry[$lock_name], true);

		if (!$lock)
		{
			return;
		}

		if ($lock['uid'] != $core->user_id)
		{
			return false;
		}

		unset($registry[$lock_name]);

		return true;
	}
}