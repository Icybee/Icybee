<?php

/**
 * This file is part of the WdPatron software
 *
 * @author Olivier Laviale <olivier.laviale@gmail.com>
 * @link http://www.weirdog.com/wdpatron/
 * @copyright Copyright (c) 2007-2010 Olivier Laviale
 * @license http://www.weirdog.com/wdpatron/license/
 */

class patron_native_WdMarkups
{
	static public function template(array $args, WdPatron $patron, $template)
	{
		$patron->addTemplate($args['name'], $template);
	}

	static public function call_template(array $args, WdPatron $patron, $template)
	{
		return $patron->callTemplate($args['name'], $args);
	}

	static public function foreach_(array $args, WdPatron $patron, $template)
	{
		#
		# get entries array from context
		#

		$entries = $args['in'];

		if (!$entries)
		{
			return;
		}

		if (!is_array($entries) && !is_object($entries))
		{
			$patron->error
			(
				'Invalid source for %param. Source must either be an array or a traversable object. Given: !entries', array
				(
					'%param' => 'in', '!entries' => $entries
				)
			);

			return;
		}

		#
		# create body from iterations
		#

		$count = count($entries);
		$position = 0;
		$left = $count;
		$even = 'even';
		$key = null;

		$context = array
		(
			'count' => &$count,
			'position' => &$position,
			'left' => &$left,
			'even' => &$even,
			'key' => &$key
		);

		$as = $args['as'];

		$patron->context['self'] = array_merge($patron->context['self'], $context);

		$rc = '';

		foreach ($entries as $key => $entry)
		{
			$position++;
			$left--;
			$even = ($position % 2) ? '' : 'even';

			if ($as)
			{
				$patron->set($as, $entry);
			}

			$rc .= $patron($template, $entry);
		}

		return $rc;
	}

	static public function variable(array $args, WdPatron $patron, $template)
	{
		$select = $args['select'];

		if ($select && $template)
		{
			return $patron->error('Ambiguous selection');
		}
		else if ($select)
		{
			$value = $select;
		}
		else
		{
			$value = $patron($template);
		}

		$name = $args['name'];

		//$patron->context[$name] = $value;

		$patron->set($name, $value);
	}

	static public function with(array $args, WdPatron $patron, $template)
	{
		if ($template === null)
		{
			return $patron->error('Self closing !');
		}

		$select = $args['select'];

		return $patron($template, $select);
	}

	static public function choose(array $args, WdPatron $patron, $template)
	{
		$otherwise = null;

		#
		# handle 'when' children as they are defined.
		# if we find an 'otherwise' we keep it for later
		#

		foreach ($template as $node)
		{
			$name = $node->name;

			if ($name == 'otherwise')
			{
				$otherwise = $node;

				continue;
			}

			if ($name != 'when')
			{
				return $patron->error('Unexpected child: :node', array(':node' => $node));
			}

			$value = $patron->evaluate($node->args['test'], true);

			if ($value)
			{
				return $patron($node->nodes);
			}
		}

		#
		# otherwise
		#

		if (!$otherwise)
		{
			return;
		}

		return $patron($otherwise->nodes);
	}

	static public function if_(array $args, WdPatron $patron, $template)
	{
		#
		# if the evaluation is not empty (0 or ''), we publish the template
		#

		if ($args['test'])
		{
			return $patron($template);
		}
	}
}