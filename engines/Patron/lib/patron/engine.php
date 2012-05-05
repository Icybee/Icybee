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

use ICanBoogie\Debug;
use ICanBoogie\Exception;
use ICanBoogie\Hook;
use ICanBoogie\Exception\HTTP as HTTPException;

use Brickrouge\Alert;

define('WDPATRON_DELIMIT_MACROS', false);

class Engine extends TextHole
{
	protected $trace_templates = false;

	public function __construct()
	{
		#
		# create context
		#

		$this->contextInit();

		#
		# add functions
		#

		$this->functions['to_s'] = function($a)
		{
			if (is_array($a) || (is_object($a) && !method_exists($a, '__toString')))
			{
				return \ICanBoogie\dump($a);
			}

			return (string) $a;
		};

		$this->functions['add'] = function($a,$b)
		{
			return ($a + $b);
		};

		$this->addFunction('try', array($this, '_get_try'));

		#
		# some operations
		#

		//FIXME: add more operators

		$this->addFunction('if', create_function('$a,$b,$c=null', 'return $a ? $b : $c;'));
		$this->addFunction('or', create_function('$a,$b', 'return $a ? $a : $b;'));
		$this->addFunction('not', create_function('$a', 'return !$a;'));
		$this->addFunction('mod', create_function('$a,$b', 'return $a % $b;'));
		$this->addFunction('bit', create_function('$a,$b', 'return (int) $a & (1 << $b);'));

		$this->addFunction('greater', create_function('$a,$b', 'return ($a > $b);'));
		$this->addFunction('smaller', create_function('$a,$b', 'return ($a < $b);'));
		$this->addFunction('equals', create_function('$a,$b', 'return ($a == $b);'));
		$this->addFunction('different', create_function('$a,$b', 'return ($a != $b);'));

		#
		#
		#

		$this->addFunction('split', create_function('$a,$b=","', 'return explode($b,$a);'));
		$this->addFunction('join', create_function('$a,$b=","', 'return implode($b,$a);'));
		$this->addFunction('index', create_function('', '$a = func_get_args(); $i = array_shift($a); return $a[$i];'));

		$this->addFunction('replace', create_function('$a,$b,$c=""', 'return str_replace($b, $c, $a);'));

		#
		# array (mostly from ruby)
		#

		/**
		 * Returns the first element, or the first n elements, of the array. If the array is empty,
		 * the first form returns nil, and the second form returns an empty array.
		 *
		 * a = [ "q", "r", "s", "t" ]
		 * a.first    // "q"
		 * a.first(1) // ["q"]
		 * a.first(3) // ["q", "r", "s"]
		 *
		 */

		$this->addFunction('first', create_function('$a,$n=null', '$rc = array_slice($a, 0, $n ? $n : 1);  return $n === null ? array_shift($rc) : $rc;'));

		// TODO-20100507: add the 'last' method

		#
		#
		#

		$this->addFunction('markdown', create_function('$txt', 'require_once "' . __DIR__ . '/textmark.php' . '"; return Markdown($txt);'));
	}

	private static $singleton;

	public static function get_singleton()
	{
		if (self::$singleton)
		{
			return self::$singleton;
		}

		$class = get_called_class();

		self::$singleton = $singleton = new $class();

		return $singleton;
	}

	public function _get_try($from, $which, $default=null)
	{
		$form = (array) $from;

		return isset($from[$which]) ? $from[$which] : $default;
	}

	/*
	**

	SYSTEM

	**
	*/

	protected $trace = array();
	protected $errors = array();

	public function trace_enter($a)
	{
		array_unshift($this->trace, $a);
	}

	public function trace_exit()
	{
		array_shift($this->trace);
	}

	public function error($alert, array $args=array())
	{
		if ($alert instanceof \ICanBoogie\Exception\Config)
		{
			$this->errors[] = new Alert($alert->getMessage());

			return;
		}
		else if ($alert instanceof \Exception)
		{
			$alert = Debug::format_alert($alert);
		}
		else
		{
			$alert = \ICanBoogie\format($alert, $args);
		}

		#
		#
		#

		$trace = null;

		if ($this->trace)
		{
			$i = count($this->trace);
			$root = \ICanBoogie\DOCUMENT_ROOT;
			$root_length = strlen($root);

			foreach ($this->trace as $trace)
			{
				list($which, $message) = $trace;

				if ($which == 'file')
				{
					if (strpos($message, $root_length) === 0)
					{
						$message = substr($message, $root_length);
					}
				}

				$trace .= sprintf('#%02d: in %s "%s"', $i--, $which, $message) . '<br />';
			}

			if ($trace)
			{
				$trace = '<pre>' . $trace . '</pre>';
			}
		}

		#
		#
		#

		$this->errors[] = '<div class="alert alert-error">' . $alert . $trace . '</div>';
	}

	public function fetchErrors()
	{
		$rc = implode(PHP_EOL, $this->errors);

		$this->errors = array();

		return $rc;
	}

	protected function resolve_callback($matches)
	{
		global $core;

		$expression = $matches[1];
		$do_entities = true;

		$modifier = substr($expression, -1, 1);

		if ($modifier == '=')
		{
			$do_entities = false;

			$expression = substr($expression, 0, -1);
		}
		else if ($modifier == '!')
		{
			$this->error('all expressions are automatically escaped now: %expression', array('%expression' => $expression));

			$expression = substr($expression, 0, -1);
		}

		$l = strlen($expression);

		if ($l > 2 && $expression{1} == ':' && $expression{0} == 't')
		{
			$rc = t(substr($expression, 2));
		}
		else if ($l > 4 && $expression{3} == ':' && substr($expression, 0, 3) == 'url')
		{
			$rc = $core->site->resolve_view_url(substr($expression, 4));
		}
		else
		{
			$rc = $this->evaluate($expression);

			if (is_object($rc))
			{
				if (!method_exists($rc, '__toString'))
				{
					$this->error('%expression was resolved to an object of the class %class', array('%expression' => $expression, '%class' => get_class($rc)));
				}

				$rc = (string) $rc;
			}
			else if (is_array($rc))
			{
				$this->error('%expression was resolved to an array with the following keys: :keys', array('%expression' => $expression, ':keys' => implode(', ', array_keys($rc))));
			}
		}

		if ($do_entities)
		{
			$rc = escape($rc);
		}

		return $rc;
	}

	public function get_file()
	{
		foreach ($this->trace as $trace)
		{
			list($which, $data) = $trace;

			if ($which == 'file')
			{
				return $data;
			}
		}
	}

	public function get_template_dir()
	{
		return dirname($this->get_file());
	}

	/*
	**

	TEMPLATES

	**
	*/

	protected $templates = array();
	protected $templates_searched = false;

	protected function search_templates()
	{
		global $core;

		if ($this->templates_searched)
		{
			return;
		}

		$templates = $core->site->partial_templates;

		foreach ($templates as $id => $path)
		{
			$this->addTemplate($id, '!f:' . $path);
		}

		$this->templates_searched = true;
	}

	public function addTemplate($name, $template)
	{
		if (isset($this->templates[$name]))
		{
			$this->error
			(
				'The template %name is already defined ! !template', array
				(
					'%name' => $name, '!template' => $template
				)
			);

			return;
		}

		$this->templates[$name] = $template;
	}

	protected function get_template($name)
	{
		#
		# if the template is not defined, and we haven't searched templates
		# defined by modules, now is the time
		#

		if (empty($this->templates[$name]))
		{
			$this->search_templates();
		}

		if (isset($this->templates[$name]))
		{
			$template = $this->templates[$name];

			#
			# we convert the template into a tree of nodes to speed up following parsings
			#

			if (is_string($template))
			{
				$file = null;

				if ($template{0} == '!' && $template{1} == 'f' && $template{2} == ':')
				{
					$file = substr($template, 3);
					$template = file_get_contents($file);
					$file = substr($file, strlen(\ICanBoogie\DOCUMENT_ROOT));
				}

				/*TODO-20120106: because the template is evaluated elsewhere, we can't attach
				 * its file location.
				$template = $this->htmlparser->parse($template, 'wdp:');

				if ($file)
				{
					$template['file'] = $file;
				}
				*/

				$this->templates[$name] = $template;
			}

			return $template;
		}
	}

	public function callTemplate($name, array $args=array())
	{
// 		echo __FUNCTION__ . '::disabled<br />'; return;

		$template = $this->get_template($name);

		if (!$template)
		{
			$er = 'Unknown template %name';
			$params = array('%name' => $name);

			if ($this->templates)
			{
				$er .= ', available templates: :list';
				$params[':list'] = implode(', ', array_keys($this->templates));
			}

			$this->error($er, $params);

			return;
		}

		array_unshift($this->trace, array('template', $name));

		$this->context['self']['arguments'] = $args;

//		echo l('\1', $this->context['self']['arguments']);

		$rc = $this($template);

		array_shift($this->trace);

		return $rc;
	}

	/*
	**

	CONTEXT

	**
	*/

	/*
	protected $context_depth = 0;
	protected $context_pushed = array();
	*/
	protected $context_shared = array();

	protected function contextInit()
	{
		/*
		foreach ($_SERVER as $key => &$value)
		{
			if (substr($key, 0, 5) == 'HTTP_')
			{
				$_SERVER['http'][strtolower(substr($key, 5))] = &$value;
			}
			else if (substr($key, 0, 7) == 'REMOTE_')
			{
				$_SERVER['remote'][strtolower(substr($key, 7))] = &$value;
			}
			else if (substr($key, 0, 8) == 'REQUEST_')
			{
				$_SERVER['request'][strtolower(substr($key, 8))] = &$value;
			}
		}

		$this->context = array
		(
			'self' => null,
			'this' => null
		);
		*/

		$this->context = new \BlueTihi\Context(array('self' => null, 'this' => null));
	}


	/*
	**

	PUBLISH

	**
	*/

	protected function get_compiled($template)
	{
		$compiler = new Compiler();

		return $compiler($template);
	}

	public function publish($template, $bind=null, array $options=array())
	{
		return $this->__invoke($template, $bind, $options);
	}

	public function __invoke($template, $bind=null, array $options=array())
	{
		if (is_array($bind) && (isset($bind['bind']) || isset($bind['variables'])))
		{
			throw new Exception('Bind is now an argument sweetheart !');
		}

		if (!$template)
		{
			return;
		}

		if ($bind !== null)
		{
			$this->context['this'] = $bind;
		}

		$file = null;

		foreach ($options as $option => $value)
		{
			switch ((string) $option)
			{
				case 'variables':
				{
					$this->context = array_merge($this->context, $value);
				}
				break;

				case 'file':
				{
					$file = $value;
				}
				break;

				default:
				{
					trigger_error(\ICanBoogie\format('Suspicious option: %option :value', array('%option' => $option, ':value' => $value)));
				}
				break;
			}
		}

		if (is_array($template) && isset($template['file']))
		{
			$file = $template['file'];

			unset($template['file']);
		}

		if ($file)
		{
			array_unshift($this->trace, array('file', $file));
		}

		#
		#
		#

		if (!is_array($template))
		{
			$template = $this->get_compiled($template);
		}

		$rc = '';

		foreach ($template as $node)
		{
			if (!($node instanceof Node))
			{
				var_dump($node); continue;
			}

// 			echo get_class($node) . '//' . is_callable($node) . '<br />';

			try
			{
				$rc .= $node($this, $this->context);
			}
			catch (\Exception $e)
			{
				$rc .= Debug::format_alert($e);
			}

			$rc .= $this->fetchErrors();
		}

		$rc .= $this->fetchErrors();

		#
		#
		#

		if ($file)
		{
			array_shift($this->trace);
		}

		return $rc;
	}

	/*

	#
	# $context_markup is used to keep track of two variables associated with each markup :
	# self and this.
	#
	# 'self' is a reference to the markup itsef, holding its name and the arguments with which
	# it was called, it is also used to store special markup data as for the foreach markup
	#
	# 'this' is a reference to the object of the markup, that being an array, an object or a value
	#
	#

	<wdp:articles>

		self.range.start
		self.range.limit
		self.range.count

		this = array of Articles

		<wdp:foreach>

			self.name = foreach
			self.arguments = array()
			self.position
			self.key
			self.left

			this = an Article object

		</wdp:foreach>
	</wdp:articles>

	*/

	public $context_markup = array(); // should be protected

	protected function __call_markup($name, $args, $template)
	{
		try
		{
			$hook = Hook::find('patron.markups', $name);
		}
		catch (\Exception $e)
		{
			$this->error('Unknown markup %name', array('%name' => $name));

			return;
		}

		// TODO-20100425: remove the following compatibility code

		foreach ($args as $param => $value)
		{
			if (strpos($value, '#{') === false)
			{
				continue;
			}

			$this->error
			(
				'COMPAT: Evaluation of the %param parameter for the %markup markup', array
				(
					'%param' => $param,
					'%markup' => $name
				)
			);

			$args[$param] = $this->resolve($value);
		}

		// /20100425

		$missing = array();
		$binding = empty($hook->tags['no-binding']);

		foreach ($hook->params as $param => $options)
		{
			/* DIRTY
			if ($param == 'no-binding')
			{
				$binding = !$options;

				throw new Exception('<em>no-binding</em> should be a hook tag an not a parameter');
			}
			*/

			if (is_array($options))
			{
				/* DIRTY
				if (array_key_exists('silent', $options))
				{
					throw new Exception('<em>silent</em> should be an option of <em>expression</em> and not a parameter option');
				}
				*/

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

						$args[$param] = $this->evaluate($value);
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
							$args[$param] = $this->evaluate($value, $silent);
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

		#
		# handle 'with-param' special markups
		#

		if ($template)
		{
			foreach ($template as $k => $child)
			{
				if (!is_array($child) || $child['name'] != 'with-param')
				{
					continue;
				}

				$child_args = $child['args'];
				$param = $child_args['name'];

				if (isset($child_args['select']))
				{
					if (isset($child['children']))
					{
						throw new Exception('Ambiguous selection for with-param %name', array('%name' => $param));
					}

					$args[$param] = $this->evaluate($child_args['select']);
				}
				else if (isset($child['children']))
				{
					$args[$param] = $this($child['children']);
				}

				#
				# remove the parameter for the missing paremets list
				#

				unset($missing[$param]);

				#
				# remove the 'with-param' markup from the template
				#

				unset($template[$k]);
			}
		}

		if ($missing)
		{
			throw new Exception
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

		array_unshift($this->trace, array('markup', $name));

		if ($binding)
		{
			array_push($this->context_markup, array($this->context['self'], $this->context['this']));

			$this->context['self'] = array
			(
				'name' => $name,
				'arguments' => $args
			);
		}

		$rc = null;

		try
		{
			$rc = $hook->__invoke($args, $this, $template);
		}
		catch (HTTPException $e)
		{
			throw $e;
		}
		catch (\Exception $e)
		{
			$this->error($e);
		}

		if ($binding)
		{
			$context = array_pop($this->context_markup);

			$this->context['self'] = $context[0];
			$this->context['this'] = $context[1];
		}

		array_shift($this->trace);

		return $rc;
	}
}