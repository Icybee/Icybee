<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Registry;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Core;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Modules;
use ICanBoogie\Operation;
use ICanBoogie\Operation\ProcessEvent;

use Icybee\EditBlock\AlterValuesEvent;

class Hooks
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
	static public function on_editblock_alter_values(AlterValuesEvent $event, \Icybee\EditBlock $block)
	{
		global $core;

		if (!$event->key)
		{
			return;
		}

		$module = $event->module;

		if ($module instanceof Modules\Nodes\Module)
		{
			$type = 'node';
		}
		else if ($module instanceof Modules\Users\Module)
		{
			$type = 'user';
		}
		else if ($module instanceof Modules\Sites\Module)
		{
			$type = 'site';
		}
		else
		{
			throw new Exception('Metadatas are not supported for instances of the given class: %class', array('%class' => get_class($sender)));
		}

		$model = $core->models['system.registry/' . $type];
		$metas = $model->select('name, value')->find_by_targetid($event->key)->pairs;

		$values = &$event->values;

		if (isset($values['metas']))
		{
			if ($values['metas'] instanceof MetasHandler)
			{
				$values['metas'] = $values['metas']->to_a();
			}

			$values['metas'] += $metas;
		}
		else
		{
			$values['metas'] = $metas;
		}
	}

	/**
	 * This callback saves the metadatas associated with the object targeted by the operation.
	 *
	 * @param Event $event
	 *
	 * @throws Exception
	 */
	public static function on_operation_save(ProcessEvent $event, \ICanBoogie\SaveOperation $sender)
	{
		global $core;

		$params = $event->request->params;

		if (!array_key_exists('metas', $params))
		{
			return;
		}

		$targetid = $event->rc['key'];

		if ($sender instanceof Modules\Nodes\SaveOperation)
		{
			$type = 'node';
		}
		else if ($sender instanceof Modules\Users\SaveOperation)
		{
			$type = 'user';
		}
		else if ($sender instanceof Modules\Sites\SaveOperation)
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

	static public function on_operation_delete(ProcessEvent $event, \ICanBoogie\DeleteOperation $operation)
	{
		global $core;

		$module = $operation->module;

		if ($module instanceof Modules\Nodes\Module)
		{
			$type = 'node';
		}
		else if ($module instanceof Modules\Users\Module)
		{
			$type = 'user';
		}
		else if ($module instanceof Modules\Sites\Module)
		{
			$type = 'site';
		}
		else
		{
			throw new Exception('Metadatas are not supported for instances of the given class: %class', array('%class' => get_class($module)));
		}

		$model = $core->models['system.registry/' . $type];

		$model->execute('DELETE FROM {self} WHERE targetid = ?', array($operation->key));
	}
}