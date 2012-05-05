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

class HTMLParser
{
	const T_ERROR_HANDLER = 'error-handler';

	private $encoding;
	private $matches;
	private $escaped;
	private $opened = array();

	protected $error_handler;
	protected $namespace;

	public function __construct(array $tags=array())
	{
		$tags += array
		(
			self::T_ERROR_HANDLER => 'ICanBoogie\Debug::trigger'
		);

		$this->error_handler = $tags[self::T_ERROR_HANDLER];
	}

	public function parse($html, $namespace=null, $encoding='utf-8')
	{
		$this->encoding = $encoding;
		$this->namespace = $namespace;

		#
		# we take care of escaping comments and processing options. they will not be parsed
		# and will end as text nodes
		#

		$html = $this->escapeSpecials($html);

		#
		# in order to create a tree, we first need to split the HTML using the markups,
		# creating a nice flat array of texts and opening and closing markups.
		#
		# the array can be read as follows :
		#
		# i+0 => some text
		# i+1 => '/' for closing markups, nothing otherwise
		# i+2 => the markup it self, without the '<' '>'
		#
		# note that i+2 might end with a '/' indicating an auto-closing markup
		#

		$this->matches = preg_split
		(
			'#<(/?)' . $namespace . '([^>]*)>#', $html, -1, PREG_SPLIT_DELIM_CAPTURE
		);

		#
		# the flat representation is now ready, we can create our tree
		#

		$tree = $this->buildTree();

		#
		# if comments or processing options where escaped, we can
		# safely unescape them now
		#

		if ($this->escaped)
		{
			$tree = $this->unescapeSpecials($tree);
		}

		return $tree;
	}

	protected function escapeSpecials($html)
	{
		#
		# here we escape comments
		#

		$html = preg_replace_callback('#<\!--.+-->#sU', array($this, 'escapeSpecials_callback'), $html);

		#
		# and processing options
		#

		$html = preg_replace_callback('#<\?.+\?>#sU', array($this, 'escapeSpecials_callback'), $html);

		return $html;
	}

	protected function escapeSpecials_callback($m)
	{
		$this->escaped = true;

		$text = $m[0];

		$text = str_replace
		(
			array('<', '>'),
			array("\x01", "\x02"),
			$text
		);

		return $text;
	}

	protected function unescapeSpecials($tree)
	{
		return is_array($tree) ? array_map(array($this, 'unescapeSpecials'), $tree) : str_replace
		(
			array("\x01", "\x02"),
			array('<', '>'),
			$tree
		);
	}

	protected function buildTree()
	{
		$nodes = array();

		$i = 0;
		$text = null;

		while (($value = array_shift($this->matches)) !== null)
		{
			switch ($i++ % 3)
			{
				case 0:
				{
					#
					# if the trimed value is not empty we preserve the value,
					# otherwise we discard it.
					#

					if (trim($value))
					{
						$nodes[] = $value;
					}
				}
				break;

				case 1:
				{
					$closing = ($value == '/');
				}
				break;

				case 2:
				{
					if (substr($value, -1, 1) == '/')
					{
						#
						# auto closing
						#

						$nodes[] = $this->parseMarkup(substr($value, 0, -1));
					}
					else if ($closing)
					{
						#
						# closing markup
						#

						$open = array_pop($this->opened);

						if ($value != $open)
						{
							$this->error($value, $open);
						}

						return $nodes;
					}
					else
					{
						#
						# this is an open markup with possible children
						#

						$node = $this->parseMarkup($value);

						#
						# push the markup name into the opened markups
						#

						$this->opened[] = $node['name'];

						#
						# create the node and parse its children
						#

						$node['children'] = $this->buildTree($this->matches);

						$nodes[] = $node;
					}
				}
			}
		}

		return $nodes;
	}

	protected function parseMarkup($markup)
	{
		#
		# get markup's name
		#

		preg_match('#^[^\s]+#', $markup, $matches);

		$name = $matches[0];

		#
		# get markup's arguments
		#

		preg_match_all('#\s+([^=]+)\s*=\s*"([^"]+)"#', $markup, $matches, PREG_SET_ORDER);

		#
		# transform the matches into a nice key/value array
		#

		$args = array();

		foreach ($matches as $m)
		{
			#
			# we unescape the html entities of the argument's value
			#

			$args[$m[1]] = html_entity_decode($m[2], ENT_QUOTES, $this->encoding);
		}

		return array('name' => $name, 'args' => $args);
	}

	protected function error($markup, $expected)
	{
		$this->malformed = true;

		call_user_func
		(
			$this->error_handler, $expected
			? 'unexpected closing markup %markup, should be %expected'
			: 'unexpected closing markup %markup, when none was opened', array
			(
				'%markup' => $this->namespace . $markup, '%expected' => $expected
			)
		);
	}

	public static function collectMarkup($nodes, $markup)
	{
		$collected = array();

		foreach ($nodes as $node)
		{
			if (!is_array($node))
			{
				continue;
			}

			if ($node['name'] == $markup)
			{
				$collected[] = $node;
			}

			if (isset($node['children']))
			{
				$collected = array_merge($collected, self::collectMarkup($node['children'], $markup));
			}
		}

		return $collected;
	}
}