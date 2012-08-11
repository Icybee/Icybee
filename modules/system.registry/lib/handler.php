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
use ICanBoogie\Exception;

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
			//\ICanBoogie\log('delete %name because is has been set to null', array('%name' => $name));

			$this->model->find_by_targetid_and_name($this->targetid, $name)->delete();
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