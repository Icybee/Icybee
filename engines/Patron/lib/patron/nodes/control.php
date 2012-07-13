<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Patron;

use ICanBoogie\Hook;

class ControlNode extends Node
{
	public $name;
	public $args;
	public $nodes;

	public function __construct($name, array $args, $nodes)
	{
		$this->name = $name;

		foreach ($nodes as $i => $node)
		{
			if (!($node instanceof self) || $node->name != 'with-param')
			{
				continue;
			}

			$args[$node->args['name']] = $node;

			unset($nodes[$i]);
		}

		$this->args = $args;
		$this->nodes = $nodes;
	}

	public function __invoke(Engine $engine, $context)
	{
		$name = $this->name;

		try
		{
			$hook = Hook::find('patron.markups', $name);
		}
		catch (\Exception $e)
		{
			$engine->error('Unknown markup %name', array('%name' => $name));

			return;
		}

		$args = $this->args;

		$missing = array();
		$binding = empty($hook->tags['no-binding']);

		foreach ($hook->params as $param => $options)
		{
			if (is_array($options))
			{
				#
				# default value
				#

				if (isset($options['default']) && !array_key_exists($param, $args))
				{
					$args[$param] = $options['default'];
				}

				if (array_key_exists($param, $args))
				{
					$value = $args[$param];

					if (isset($options['evaluate']))
					{
						//\ICanBoogie\log('\4:: evaluate "\3" with value: \5, params \1 and args \2', array($hook->params, $args, $param, $name, $value));

						$args[$param] = $engine->evaluate($value);
					}

					if (isset($options['expression']))
					{
						$silent = !empty($options['expression']['silent']);

						//\ICanBoogie\log('\4:: evaluate expression "\3" with value: \5, params \1 and args \2', array($hook->params, $args, $param, $name, $value));

						if ($value{0} == ':')
						{
							$args[$param] = substr($value, 1);
						}
						else
						{
							$args[$param] = $engine->evaluate($value, $silent);
						}
					}
				}
				else if (isset($options['required']))
				{
					$missing[$param] = true;
				}
			}
			else
			{
				//\ICanBoogie\log('options is a value: \1', array($options));

				if (!array_key_exists($param, $args))
				{
					$args[$param] = $options;
				}
			}

			if (!isset($args[$param]))
			{
				$args[$param] = null;
			}
		}

		if ($missing)
		{
			throw new \ICanBoogie\Exception
			(
				'The %param parameter is required for the %markup markup, given %args', array
				(
					'%param' => implode(', ', array_keys($missing)),
					'%markup' => $name,
					'%args' => json_encode($args)
				)
			);
		}

		#
		# call hook
		#

		$engine->trace_enter(array('markup', $name));

		if ($binding)
		{
			array_push($engine->context_markup, array($engine->context['self'], $engine->context['this']));

			$engine->context['self'] = array
			(
				'name' => $name,
				'arguments' => $args
			);
		}

		$rc = null;

		try
		{
			$rc = $hook($args, $engine, $this->nodes);
		}
		catch (HTTPException $e)
		{
			throw $e;
		}
		catch (\Exception $e)
		{
			$engine->error($e);
		}

		if ($binding)
		{
			$a = array_pop($engine->context_markup);

			$engine->context['self'] = $a[0];
			$engine->context['this'] = $a[1];
		}

		$engine->trace_exit();

		return $rc;
	}
}
