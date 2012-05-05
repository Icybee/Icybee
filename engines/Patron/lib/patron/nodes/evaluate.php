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

use ICanBoogie\Exception;

class EvaluateNode extends ExpressionNode
{
	protected $original_expression;
	protected $engine_context;

	public function __construct($expression, $escape)
	{
		$this->original_expression = $expression;

		parent::__construct($this->tokenize($expression), $escape);
	}

	private $engine;

	public function __invoke(Engine $engine, $context)
	{
		$this->engine = $engine;
		$this->engine_context = $context;

		return parent::__invoke($engine, $context);
	}

	protected function render($parts)
	{
		$value = $this->engine_context;
		$expression = $this->original_expression;
		$silent = false;
		$previous_identifier = '__context__';

		foreach ($parts as $i => $part)
		{
			$identifier = $part[self::TOKEN_VALUE];

			switch ($part[self::TOKEN_TYPE])
			{
				case self::TOKEN_TYPE_IDENTIFIER:
				{
					if (!is_array($value) && !is_object($value))
					{
						throw new \InvalidArgumentException(\ICanBoogie\format
						(
							'Unexpected variable type: %type (%value) for %identifier in expression %expression, should be either an array or an object', array
							(
								'%type' => gettype($value),
								'%value' => $value,
								'%identifier' => $identifier,
								'%expression' => $expression
							)
						));
					}

					$exists = false;

					if (is_array($value))
					{
						$exists = array_key_exists($identifier, $value);
					}
					else
					{
						$exists = property_exists($value, $identifier);

						if (!$exists && method_exists($value, 'has_property'))
						{
							$exists = $value->has_property($identifier);
						}
						else
						{
							if (!$exists && method_exists($value, 'offsetExists'))
							{
								$exists = $value->offsetExists($identifier);
							}

							if (!$exists && method_exists($value, '__get'))
							{
								$exists = true;
							}
						}
					}

					if (!$exists)
					{
						if (!$silent)
						{
							throw new Exception
							(
								'%identifier of expression %expression does not exists in %var (defined: :keys) in: !value', array
								(
									'%identifier' => $identifier,
									'%expression' => $expression,
									'%var' => $previous_identifier,
									':keys' => implode(', ', array_keys((array) $value)),
									'!value' => $value
								)
							);
						}

						return;
					}

					$value = (is_array($value) || method_exists($value, 'offsetExists')) ? $value[$identifier] : $value->$identifier;
					$previous_identifier = $identifier;
				}
				break;

				case self::TOKEN_TYPE_FUNCTION:
				{
					$method = $identifier;
					$args = $part[self::TOKEN_ARGS];
					$args_evaluate = $part[self::TOKEN_ARGS_EVALUATE];

					if ($args_evaluate)
					{
						$this->error('we should evaluate %eval', array('%eval' => $args_evaluate));
					}

					#
					# if value is an object, we check if the object has the method
					#

					if (is_object($value) && method_exists($value, $method))
					{
						$value = call_user_func_array(array($value, $method), $args);

						break;
					}

					#
					# well, the object didn't have the method,
					# we check internal functions
					#

					$callback = $this->findFunction($this->engine, $method);

					#
					# if no internal function matches, we try string and array functions
					# depending on the type of the value
					#

					if (!$callback)
					{
						if (is_string($value))
						{
							if (function_exists('str' . $method))
							{
								$callback = 'str' . $method;
							}
							else if (function_exists('str_' . $method))
							{
								$callback = 'str_' . $method;
							}
						}
						else if (is_array($value) || is_object($value))
						{
							if (function_exists('ICanBoogie\array_' . $method))
							{
								$callback = 'ICanBoogie\array_' . $method;
							}
							else if (function_exists('array_' . $method))
							{
								$callback = 'array_' . $method;
							}
						}
					}

					#
					# our last hope is to try the function "as is"
					#

					if (!$callback)
					{
						if (function_exists($method))
						{
							$callback = $method;
						}
					}

					if (!$callback)
					{
						if (is_object($value) && method_exists($value, '__call'))
						{
							$value = call_user_func_array(array($value, $method), $args);

							break;
						}
					}

					#
					#
					#

					if (!$callback)
					{
						throw new Exception
						(
							'Unknown method %method for expression %expression.', array
							(
								'%method' => $method,
								'%expression' => $expression
							)
						);
					}

					#
					# create evaluation
					#

					array_unshift($args, $value);

					if (PHP_MAJOR_VERSION > 5 || (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION > 2))
					{
						if ($callback == 'array_shift')
						{
							$value = array_shift($value);
						}
						else
						{
							$value = call_user_func_array($callback, $args);
						}
					}
					else
					{
						$value = call_user_func_array($callback, $args);
					}
				}
				break;
			}
		}

		return $value;
	}

	const TOKEN_TYPE = 1;
	const TOKEN_TYPE_FUNCTION = 2;
	const TOKEN_TYPE_IDENTIFIER = 3;
	const TOKEN_VALUE = 4;
	const TOKEN_ARGS = 5;
	const TOKEN_ARGS_EVALUATE = 6;

	/*
	 * Tokenize Javascript style function chain into an array of identifiers and functions
	 */
	protected function tokenize($str)
	{
		if ($str{0} == '@')
		{
			$str = 'this.' . substr($str, 1);
		}

		$str .= '.';

		$length = strlen($str);

		$quote = null;
		$quote_closed = null;
		$part = null;
		$escape = false;

		$function = null;
		$args = array();
		$args_evaluate = array();
		$args_count = 0;

		$parts = array();

		for ($i = 0 ; $i < $length ; $i++)
		{
			$c = $str{$i};

			if ($escape)
			{
				$part .= $c;

				$escape = false;

				continue;
			}

			if ($c == '\\')
			{
				//echo "found escape: [$c] @$i<br />";

				$escape = true;

				continue;
			}

			if ($c == '"' || $c == '\'' || $c == '`')
			{
				if ($quote && $quote == $c)
				{
					//echo "found closing quote: [$c]<br />";

					$quote = null;
					$quote_closed = $c;

					if ($function)
					{
						continue;
					}
				}
				else if (!$quote)
				{
					//echo "found opening quote: [$c]<br />";

					$quote = $c;

					if ($function)
					{
						continue;
					}
				}
			}

			if ($quote)
			{
				$part .= $c;

				continue;
			}

			#
			# we are not in a quote
			#

			if ($c == '.')
			{
				//echo "end of part: [$part]<br />";

				// FIXME: added strlen() because of '0' e.g. items.0.price
				// could a part be null ??

				if (strlen($part))
				{
					$parts[] = array
					(
						self::TOKEN_TYPE => self::TOKEN_TYPE_IDENTIFIER,
						self::TOKEN_VALUE => $part
					);
				}

				$part = null;

				continue;
			}

			if ($c == '(')
			{
				//echo "function [$part] begin: @$i<br />";

				$function = $part;

				$args = array();
				$args_count = 0;

				$part = null;

				continue;
			}

			if (($c == ',' || $c == ')') && $function)
			{
				//echo "function push argument [$part] q=[$quote_closed]<br />";

				if ($part !== null)
				{
					if ($quote_closed == '`')
					{
						//echo "we should evaluate [$part][$args_count]<br />";

						$args_evaluate[] = $args_count;
					}

					if (!$quote_closed)
					{
						#
						# end of an unquoted part.
						# it might be an integer, a float, or maybe a constant !
						#

						$part_back = $part;

						switch ($part)
						{
							case 'true':
							case 'TRUE':
							{
								$part = true;
							}
							break;

							case 'false':
							case 'FALSE':
							{
								$part = false;
							}
							break;

							case 'null':
							case 'NULL':
							{
								$part = null;
							}
							break;

							default:
							{
								if (is_numeric($part))
								{
									$part = (int) $part;
								}
								else if (is_float($part))
								{
									$part = (float) $part;
								}
								else
								{
									$part = constant($part);
								}
							}
							break;
						}

						//\ICanBoogie\log('part: [\1] == [\2]', $part_back, $part);
					}

					$args[] = $part;
					$args_count++;

					$part = null;
				}

				$quote_closed = null;

				if ($c != ')')
				{
					continue;
				}
			}

			if ($c == ')' && $function)
			{
				//echo "function end: [$part] @$i<br />";

				$parts[] = array
				(
					self::TOKEN_TYPE => self::TOKEN_TYPE_FUNCTION,
					self::TOKEN_VALUE => $function,
					self::TOKEN_ARGS => $args,
					self::TOKEN_ARGS_EVALUATE => $args_evaluate
				);

				continue;
			}

			if ($c == ' ' && $function)
			{
				continue;
			}

			$part .= $c;
		}

		return $parts;
	}

	public function findFunction(Engine $engine, $name)
	{
		$function = $engine->findFunction($name);

		if ($function)
		{
			return $function;
		}

		$try = 'ICanBoogie\\' . $name;

		if (function_exists($try))
		{
			return $try;
		}

		$try = 'ICanBoogie\I18n\\' . $name;

		if (function_exists($try))
		{
			return $try;
		}

		#
		# 'wd' pseudo namespace // COMPAT
		#

		$try = 'wd_' . str_replace('-', '_', $name);

		if (function_exists($try))
		{
			return $try;
		}

		$try = 'Patron\\' . $name;

		if (function_exists($try))
		{
			return $try;
		}
	}
}