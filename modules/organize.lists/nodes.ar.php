<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord\Organize\Lists;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Exception;

class Node extends ActiveRecord
{
	public $listid;
	public $nodeid;
	public $parentid;
	public $weight;
	public $label;

	public function __construct()
	{
		if (empty($this->label))
		{
			unset($this->label);
		}
	}

	public function has_property($property)
	{
		if (parent::has_property($property))
		{
			return true;
		}

		return $this->node->has_property($property);
	}

	public function __call($name, $args)
	{
		$node = $this->node;

		if (!$node)
		{
			throw new Exception('Unable to load node %node', array($this->nodeid));
		}

		return call_user_func_array(array($node, $name), $args);
	}

	protected function __get_node()
	{
		global $core;

		return $core->models[isset($this->constructor) ? $this->constructor : 'nodes'][$this->nodeid];
	}

	protected function __get_label()
	{
		$node = $this->node;

		return $node instanceof ActiveRecord\Page ? $node->label : $node->title;
	}

	protected function __defer_get($property, &$success)
	{
		$success = true;

		return $this->node->$property;
	}
}