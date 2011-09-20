<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Element;

use ICanBoogie\Event;
use BrickRouge\Element;

/**
 * Breadcrumb
 * ==========
 *
 * Renders a _location_ breadcumb, showing where the page is located in the website hierarchy.
 *
 * A breadcrumb is a navigation aid. It allows users to keep track of their locations within the
 * website. A breadcrumb typically appears horizontally across the top of a web page, usually
 * below title bars or headers. It provides links to the parent pages of the current one. The
 * SINGLE RIGHT-POINTING ANGLE QUOTATION MARK character (›) serves as hierarchy separator.
 *
 * The breadcrumb element is made of slices. In each slice there is a link to the page of the slice
 * unless the slice if the last one in which case the in a strong element.
 *
 * The breadcrumb is an OL element and each of its slice is a LI element.
 *
 *
 * Event: render_inner_html:before
 * -------------------------------
 *
 * Fired before the inner HTML of the element is rendered.
 *
 * ### Signature
 *
 * before_render_inner_html($event, $sender);
 *
 * ### Arguments
 *
 * * event - (ICanBoogie\Event) An event object with the following properties:
 *     * slices - (&array) The slices of the breadcrumb
 *     * separator - (&string) The separator for the slices.
 *     * page - (ICanBoogie\ActiveRecord\Page) The current page object.
 *
 * * sender - (BrickRouge\Element\Breadcrumb) The breadcrumb element that fired the event.
 *
 *
 * Event: render_inner_html
 * ------------------------
 *
 * Fired when the inner HTML of the element has been rendered.
 *
 * ### Signature
 *
 * on_render_inner_html($event, $sender);
 *
 * ### Arguments
 *
 * * event - (ICanBoogie\Event) An event object with the following properties:
 *     * rc - (&string) The rendered inner HTML.
 *     * page - (ICanBoogie\ActiveRecord\Page) The current page object.
 *
 * * sender - (BrickRouge\Element\Breadcrumb) The breadcrumb element that fired the event.
 *
 */
class Breadcrumb extends Element
{
	const T_PAGE = '#breadcrumb-page';
	const T_DIVIDER = '#breadcrumb-divider';

	public function __construct($tags)
	{
		parent::__construct
		(
			'ol', $tags + array
			(
				self::T_DIVIDER => ' › ',

				'class' => 'breadcrumb'
			)
		);
	}

	protected function render_inner_html()
	{
		$page = $node = $this->get(self::T_PAGE);
		$slices = array();

		while ($node)
		{
			$url = $node->url;
			$label = $node->label;
			$label = wd_shorten($label, 48);
			$label = wd_entities($label);

			$slices[] = array
			(
				'url' => $url,
				'label' => $label,
				'class' => $node->css_class,
				'page' => $node
			);

			if (!$node->parent && !$node->is_home)
			{
				$node = $node->home;
			}
			else
			{
				$node = $node->parent;
			}
		}

		$slices = array_reverse($slices);
		$divider = $this->get(self::T_DIVIDER, ' › ');

		Event::fire
		(
			__FUNCTION__ . ':before', array
			(
				'slices' => &$slices,
				'divider' => &$divider,
				'page' => $page
			),

			$this
		);

		$rc = '';
		$slices = array_values($slices);
		$last = count($slices) - 1;

		foreach ($slices as $i => $slice)
		{
			$rc .= '<li class="' . $slice['class'] . '">';

			if ($i)
			{
				$rc .= '<span class="divider">' . $divider . '</span>';
			}

			$class = wd_entities($slice['class']);
			$label = wd_entities($slice['label']);

			if ($i != $last)
			{
				$rc .= '<a href="' . wd_entities($slice['url']) . '" class="' . $class . '">' . $label . '</a>';
			}
			else
			{
				$rc .= '<strong class="' . $class . '">' . $label . '</strong>';
			}

			$rc .= '</li>';
		}

		Event::fire
		(
			__FUNCTION__, array
			(
				'rc' => &$rc,
				'page' => $page
			),

			$this
		);

		return $rc;
	}
}