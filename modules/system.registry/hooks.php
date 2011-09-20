<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Hooks\System;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Core;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Module;
use ICanBoogie\Operation;

class Registry
{
	/**
	 * This is the callback for the `metas` virtual property added to the "nodes", "users" and
	 * "sites" active records.
	 *
	 * @param object An instance of ICanBoogie\ActiveRecord\Node, ICanBoogie\ActiveRecord\User or
	 * ICanBoogie\ActiveRecord\Site.
	 *
	 * @return object A MetasHandler object that can be used to access or modify the metadatas
	 * associated with that object.
	 */
	public static function method_get_metas(ActiveRecord $target)
	{
		return new MetasHandler($target);
	}

	/**
	 * This si the callback for the `registry` virtual property added to the core object.
	 *
	 * @param Core $target The core object.
	 * @return Module The "system.registry" module.
	 */

	public static function method_get_registry(Core $target)
	{
		return $target->models['system.registry'];
	}

	/**
	 * This callback alter the edit block of the "nodes", "users" and "sites" modules, adding
	 * support for metadatas by loading the metadatas associated with the edited object and
	 * merging them with the current properties.
	 *
	 * @param Event $event
	 *
	 * @throws Exception
	 */
	static public function on_alter_block_edit(Event $event, Module $sender)
	{
		global $core;

		if (!$event->key)
		{
			return;
		}

		if ($sender instanceof Module\Nodes)
		{
			$type = 'node';
		}
		else if ($sender instanceof Module\Users)
		{
			$type = 'user';
		}
		else if ($sender instanceof Module\Sites)
		{
			$type = 'site';
		}
		else
		{
			throw new Exception('Metadatas are not supported for instances of the given class: %class', array('%class' => get_class($sender)));
		}

		$model = $core->models['system.registry/' . $type];
		$metas = $model->select('name, value')->find_by_targetid($event->key)->pairs;

		if (isset($event->properties['metas']))
		{
			if ($event->properties['metas'] instanceof MetasHandler)
			{
				$event->properties['metas'] = $event->properties['metas']->to_a();
			}

			$event->properties['metas'] += $metas;
		}
		else
		{
			$event->properties['metas'] = $metas;
		}
	}

	/**
	 * This callback saves the metadatas associated with the object targeted by the operation.
	 *
	 * @param Event $event
	 *
	 * @throws Exception
	 */
	public static function on_operation_save(Event $event, Operation\ActiveRecord\Save $sender)
	{
		global $core;

		$params = $event->request->params;

		if (!array_key_exists('metas', $params))
		{
			return;
		}

		$targetid = $event->rc['key'];

		if ($sender instanceof Operation\Nodes\Save)
		{
			$type = 'node';
		}
		else if ($sender instanceof Operation\Users\Save)
		{
			$type = 'user';
		}
		else if ($sender instanceof Operation\Sites\Save)
		{
			$type = 'site';
		}
		else
		{
			throw new Exception('Metadatas are not supported for instances of the given class: %class', array('%class' => get_class($sender)));
		}

		$model = $core->models['system.registry/' . $type];
		$driver_name = $model->connection->driver_name;
		$delete_statement = '';
		$update_groups = array();
		$delete_args = array();

		foreach ($params['metas'] as $name => $value)
		{
			if (is_array($value))
			{
				$value = serialize($value);
			}
			else if (!strlen($value))
			{
				$value = null;

				$delete_statement .= ', ?';
				$delete_args[] = $name;

				continue;
			}

			if ($driver_name == 'sqlite')
			{
				$update_groups[] = array($targetid, $name, $value);
			}
			else
			{
				$update_groups[] = array($targetid, $name, $value, $value);
			}
		}

		$model->connection->begin();

		if ($delete_statement)
		{
			array_unshift($delete_args, $targetid);

			$delete_statement = 'DELETE FROM {self} WHERE targetid = ? AND name IN (' . substr($delete_statement, 2) . ')';

			$model->execute($delete_statement, $delete_args);
		}

		if ($update_groups)
		{
			if ($driver_name == 'sqlite')
			{
				$update = $model->prepare('INSERT OR REPLACE INTO {self} (targetid, name, value) VALUES(?,?,?)');
			}
			else
			{
				$update = $model->prepare('INSERT INTO {self} (targetid, name, value) VALUES(?,?,?) ON DUPLICATE KEY UPDATE value = ?');
			}

			foreach ($update_groups as $values)
			{
				$update->execute($values);
			}
		}

		$model->connection->commit();
	}

	static public function on_operation_delete(Event $event, Operation $sender)
	{
		global $core;

		$module = $sender->module;

		if ($module instanceof Module\Nodes)
		{
			$type = 'node';
		}
		else if ($module instanceof Module\Users)
		{
			$type = 'user';
		}
		else if ($module instanceof Module\Sites)
		{
			$type = 'site';
		}
		else
		{
			throw new Exception('Metadatas are not supported for instances of the given class: %class', array('%class' => get_class($module)));
		}

		$model = $core->models['system.registry/' . $type];

		$model->execute('DELETE FROM {self} WHERE targetid = ?', array($sender->key));
	}
}

/**
 * This class is used to create objects to handle reading and modifing of metadatas associated with
 * a target object.
 */
class MetasHandler implements \ArrayAccess
{
	private static $models;
	private $values;

	public function __construct($target)
	{
		if ($target instanceof ActiveRecord\Node)
		{
			$this->targetid = $target->nid;
			$type = 'node';
		}
		else if ($target instanceof ActiveRecord\User)
		{
			$this->targetid = $target->uid;
			$type = 'user';
		}
		else if ($target instanceof ActiveRecord\Site)
		{
			$this->targetid = $target->siteid;
			$type = 'site';
		}
		else
		{
			throw new Exception('Metadatas are not supported for instances of the given class: %class', array('%class' => get_class($target)));
		}

		if (empty(self::$models[$type]))
		{
			global $core;

			self::$models[$type] = $core->models['system.registry/' . $type];
		}

		$this->model = self::$models[$type];
	}

	public function get($name, $default=null)
	{
		if ($this->values === null)
		{
			$this->values = $this->model->select('name, value')->find_by_targetid($this->targetid)->order('name')->pairs;
		}

		if ($name == 'all')
		{
			return $this->values;
		}

		if (!isset($this->values[$name]))
		{
			return $default;
		}

		return $this->values[$name];
	}

	public function set($name, $value)
	{
		$this->values[$name] = $value;

		if ($value === null)
		{
			//wd_log('delete %name because is has been set to null', array('%name' => $name));

			$this->model->execute
			(
				'DELETE FROM {self} WHERE targetid = ? AND name = ?', array
				(
					$this->targetid, $name
				)
			);
		}
		else
		{
			$this->model->insert
			(
				array
				(
					'targetid' => $this->targetid,
					'name' => $name,
					'value' => $value
				),

				array
				(
					'on duplicate' => true
				)
			);
		}
	}

	public function to_a()
	{
		return $this->get('all');
	}

	public function offsetSet($offset, $value)
	{
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->get($offset) !== null;
    }

    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }

    public function offsetGet($offset)
    {
    	return $this->get($offset);
    }
}