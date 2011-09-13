<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Operation\Module;

use ICanBoogie\Exception;
use ICanBoogie\Operation;

class QueryOperation extends Operation
{
	private $callback;

	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true
		)

		+ parent::__get_controls();
	}

	protected function validate()
	{
		global $core;

		$params = $this->params;

		$this->module = $core->modules[$params['module']];
		$this->callback = $callback = 'query_' . $params['operation'];

		if (!$this->has_method($callback))
		{
			throw new Exception('Missing callback %callback.', array('%callback' => $callback));
		}

		return true;
	}

	protected function process()
	{
		$this->terminus = true;

		$params = $this->params;
		$name = $params['operation'];
		$t_options = array('scope' => array($this->module->flat_id, $name, 'operation'));

		$keys = isset($params['keys']) ? $params['keys'] : array();
		$count = count($keys);

		return $this->{$this->callback}() + array
		(
			'title' => t('title', array(), $t_options),
			'message' => t('confirm', array(':count' => $count), $t_options),
			'confirm' => array
			(
				t('cancel', array(), $t_options),
				t('continue', array(), $t_options)
			)
		);
	}

	protected function query_delete()
	{
		$keys = $this->params['keys'];
		$count = count($keys);

		return array
		(
			'params' => array
			(
				'keys' => $keys
			)
		);
	}
}