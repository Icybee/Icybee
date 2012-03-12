<?php

/*
 * This file is part of the Element package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Debug;

use Brickrouge\Element;
use Brickrouge\Form;

class WdSectionedForm extends Form
{
	public function render_inner_html()
	{
		$this->contextPush();

		$groups = $this[self::GROUPS] ?: array();

		self::sort_by($groups, 'weight');

		#
		# dispatch children into groups
		#

		foreach ($this->children as $name => $element)
		{
			if (!$element)
			{
				continue;
			}

			$group = is_object($element) ? ($element[Element::GROUP] ?: 'primary') : 'primary';

			$groups[$group][self::CHILDREN][$name] = $element;
		}

		#
		# now we the groups
		#

		$children = array();

		foreach ($groups as $group_id => $group)
		{
			if (empty($group[self::CHILDREN]))
			{
				continue;
			}

			#
			# sort children
			#

			self::sort_elements_by($group[self::CHILDREN], self::WEIGHT);

			#
			# section title
			#

			if (isset($group['title']))
			{
				$title = $group['title'];

				if (is_array($title))
				{
					$title = $title[$key ? ($permission ? 1 : 2) : 0];
				}
				else if (is_string($title) && $title{0} == '.')
				{
					$title = t(substr($title, 1), array(), array('scope' => array('form', 'section', 'title')));
				}
				else
				{
					$title = t($title);
				}

				$children[] = '<h3 id="section-title-' . $group_id . '">' . $title . '</h3>';
			}

			if (isset($group['description']))
			{
				$description = $group['description'];

				if ($description{0} == '.')
				{
					$description = t(substr($description, 1), array(), array('scope' => array('form', 'section', 'description')));
				}

				$children[] = '<div class="form-section-description"><div class="contents">' . $description . '</div></div>';
			}

			#
			# section
			#

			$css_class = isset($group['class']) ? $group['class'] : 'form-section';

			if (empty($group['template']))
			{
				if (1)
				{
					$class = empty($group['no-panels']) ? 'Brickrouge\Section' : 'Brickrouge\Element';

					$children[] = new $class
					(
						'div', array
						(
							self::CHILDREN => $group[self::CHILDREN],

							'class' => $css_class,
							'id' => 'section-' . wd_normalize($group_id)
						)
					);
				}
				else
				{
					$children[] = new Element\Group
					(
						array
						(
							self::CHILDREN => $group[self::CHILDREN],

							'class' => $css_class,
							'id' => 'section-' . wd_normalize($group_id)
						)
					);
				}
			}
			else
			{
				$children[] = '<div id="section-' . $group_id . '" class="' . $css_class . '">' . $this->publishTemplate($group['template'], $group[self::CHILDREN]) . '</div>';
			}
		}

		#
		#
		#

		$this->children = $children;

		$rc = parent::render_inner_html();

		$this->contextPop();

		return $rc;
	}

	static protected function sort_by(&$array, $by, $order='asc')
	{
		$groups = array();

		foreach ($array as $key => $value)
		{
			$order = isset($value[$by]) ? $value[$by] : null;

			$groups[$order][$key] = $value;
		}

		if (!$groups)
		{
			return;
		}

		($order == 'desc') ? krsort($groups) : ksort($groups);

		$array = call_user_func_array('array_merge', $groups);
	}

	static protected function sort_elements_by(&$array, $by, $order='asc')
	{
		$groups = array();

		foreach ($array as $key => $value)
		{
			if (!$value)
			{
				continue;
			}

			$order = is_object($value) ? $value[$by] : $value[$by];

			$groups[$order][$key] = $value;
		}

		($order == 'desc') ? krsort($groups) : ksort($groups);

		$array = call_user_func_array('array_merge', $groups);
	}

	// TODO-20100517: share this code copied from the WdTemplatedForm class

	static protected $label_right_separator = '<span class="separator">&nbsp;:</span>';
	static protected $label_left_separator = '<span class="separator">:&nbsp;</span>';

	public function publishTemplate($template, array $children)
	{
		$replace = array();

		foreach ($children as $name => $child)
		{
			if (!$child)
			{
				continue;
			}

			if (!is_object($child))
			{
				Debug::trigger('Child must be an object, given: !child', array('!child' => $child));

				continue;
			}

			#
			# label
			#

			$label = $child[self::LABEL];

			if ($label)
			{
				$label = t($label);
				$is_required = $child[self::REQUIRED];

				$child_id = $child->id;

				// TODO: clean up this mess

				$markup_start = '<label';

				if ($is_required)
				{
					$markup_start .= ' class="required mandatory"';
				}

				$markup_start .= ' for="' . $child_id . '">';

				$start =  $is_required ? $markup_start . $label . '&nbsp;<sup>*</sup>' : $markup_start . $label;
				$finish = '</label>';

				$complement = $child[self::LABEL_COMPLEMENT];

				if ($complement)
				{
					$finish = ' <span class="complement">' . $complement . '</span>' . $finish;
				}

				$replace['{$' . $name . '.label}'] = $start . $finish;
				$replace['{$' . $name . '.label:}'] = $start . self::$label_right_separator . $finish;
				$replace['{$' . $name . '.:label}'] = $markup_start . self::$label_left_separator . $start . $finish;
			}

			#
			# element
			#

			$replace['{$' . $name . '}'] = (string) $child;
		}

		return strtr($template, $replace);
	}
}