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

use ICanBoogie\HTTP\Request;

/**
 * Queries a module about an operation.
 *
 * This class is the base classe for operation queries, it is used by default by the
 * {@link Icybee\Hooks::dispatch_query_operation} controler if the target module doesn't define
 * a suitale operation class.
 */
class QueryOperation extends \ICanBoogie\Operation
{
	private $callback;

	protected function get_controls()
	{
		return [

			self::CONTROL_AUTHENTICATION => true

		] + parent::get_controls();
	}

	public function __invoke(Request $request)
	{
		$keys = $request['keys'];

		if (is_string($keys))
		{
			$request['keys'] = explode('|', $keys);
		}

		return parent::__invoke($request);
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		$request = $this->request;

		if (!$request['keys'])
		{
			$errors['keys'] = $errors->format("The parameter %param is empty.", [ 'param' => 'keys' ]);
		}

		$this->module = $this->app->modules[$request['module']];
		$this->callback = $callback = 'query_' . $request['operation'];

		if (!$this->has_method($callback))
		{
			throw new \Exception(\ICanBoogie\format('Missing callback %callback.', [ '%callback' => $callback ]));
		}

		return $errors;
	}

	protected function process()
	{
		$keys = (array) $this->request['keys'];
		$count = count($keys);
		$options = [

			'title' => $this->t('title'),
			'message' => $this->t('confirm', [ ':count' => $count ]),
			'confirm' => [

				$this->t('cancel'),
				$this->t('continue')

			],

			'element_class' => 'Icybee\QueryOperationElement'

		];

		$options = $this->{$this->callback}() + $options;
		$element = $this->resolve_element($options, []);
		$element['data-operation'] = $this->request['operation'];
		$element['data-destination'] = $this->module->id;
		$element = (string) $element;

		$this->response['assets'] = $this->app->document->assets;
		$this->response['options'] = $options;

		return $element;
	}

	protected function t($str, array $args=[], array $options=[])
	{
		$options += [

			'scope' => "{$this->module->flat_id}.{$this->request['operation']}.operation"

		];

		return \ICanBoogie\I18n\t($str, $args, $options);
	}

	/**
	 * Return the element used to confirm the operation and display its progress.
	 *
	 * @param array $options
	 * @param array $attributes
	 *
	 * @return \Brickrouge\Element
	 */
	protected function resolve_element(array $options, array $attributes)
	{
		$element_class = $options['element_class'];

		return new $element_class($options, $attributes);
	}

	protected function query_delete()
	{
		return [ 'params' => [ 'keys' => $this->request['keys'] ] ];
	}
}
