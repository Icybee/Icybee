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

/**
 * Queries a module about an operation.
 *
 * This class is the base classe for operation queries, it is used by default by the
 * {@link Icybee\Hooks::dispatch_query_operation} controler if the target module doesn't define
 * a suitale operation class.
 */
class QueryOperation extends Operation
{
	private $callback;

	protected function get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true
		)

		+ parent::get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

		$request = $this->request;

		$this->module = $core->modules[$request['module']];
		$this->callback = $callback = 'query_' . $request['operation'];

		if (!$this->has_method($callback))
		{
			throw new Exception('Missing callback %callback.', array('%callback' => $callback));
		}

		return true;
	}

	protected function process()
	{
		$request = $this->request;
		$name = $request['operation'];
		$t_options = array('scope' => array($this->module->flat_id, $name, 'operation'));

		$keys = isset($request['keys']) ? $request['keys'] : array();
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
		$keys = $this->request['keys'];
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