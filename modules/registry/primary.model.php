<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Registry;

class Model extends \ICanBoogie\ActiveRecord\Model
{
	protected $cached_values = array();

	public function get($name, $default=null)
	{
		if ($default || !array_key_exists($name, $this->cached_values))
		{
			$length = strlen($name);

			if ($name{$length - 1} == '.')
			{
				$rows = $this->where('name like ?', $name . '%')->all(\PDO::FETCH_NUM);

				$rc = $default ? $default : array();

				foreach ($rows as $row)
				{
					list($name, $value) = $row;

					$name = substr($name, $length);

					//\ICanBoogie\log('short: "\1"', array($name));

					$name = '[\'' . str_replace('.', "']['", $name) . '\']';

					// FIXME: an eval really ?

					eval('$rc' . $name . ' = $value;');
				}

				// TODO: handle default values

				//echo t(__CLASS__ . '::' . __FUNCTION__ . ':> rc: \1', array($rc));

				$this->cached_values[$name] = $rc;
			}
			else
			{
				$rc = $this->select('value')->where(array('name' => $name))->rc;

				if ($rc === false)
				{
					$rc = $default;
				}

				$this->cached_values[$name] = $rc;
			}
		}

		return $this->cached_values[$name];
	}

	/**
	 *
	 * Set a value, or a tree of values, in the registry.
	 *
	 * One can delete key (and all its sub keys), by setting it to null.
	 *
	 * @param string $name
	 * @param mixed $value
	 */

	public function set($name, $value)
	{
		$this->cached_values = array();

		$name = (string) $name;

		if (is_array($value))
		{
			$values = self::flatten($value, $name);

//			\ICanBoogie\log('should delete %name[%sub] to save !values from !value', array('%name' => $name . '.', '!values' => $values, '!value' => $value, '%sub' => implode(', ', array_keys($value))));

			foreach ($values as $name => $value)
			{
				$this->set($name, $value);
			}

			return;
		}

		if ($value === null)
		{
			//\ICanBoogie\log('delete %name because is has been set to null', array('%name' => $name));

			$this->where('name = ? OR name LIKE ?', $name, $name . '.%')->delete();
		}
		else
		{
			//\ICanBoogie\log('set <code>:name := !value</code>', array(':name' => $name, '!value' => $value));

			$this->insert
			(
				array
				(
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

	static protected function flatten($values, $prefix)
	{
		if ($prefix)
		{
			$prefix .= '.';
		}

		$flatten = array();

		foreach ($values as $key => $value)
		{
			if (is_array($value))
			{
				$flatten = array_merge
				(
					$flatten, self::flatten($value, $prefix . $key)
				);

				continue;
			}

			$flatten[$prefix . $key] = $value;
		}

		return $flatten;
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