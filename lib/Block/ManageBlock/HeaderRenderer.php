<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block\ManageBlock;

use Brickrouge\Element;

/**
 * Default header renderer.
 */
class HeaderRenderer
{
	protected $column;

	public function __construct(Column $column)
	{
		$this->column = $column;
	}

	public function __invoke()
	{
		$column = $this->column;
		$id = $column->id;
		$title = $column->title;
		$t = $this->column->manager->t;

		if ($title)
		{
			$title = $t($id, [], [ 'scope' => 'column', 'default' => $title ]);
		}

		if ($column->is_filtering)
		{
			$a_title = $t('View all');
			$title = $title ?: '&nbsp;';

			return <<<EOT
<a href="{$column->reset}" title="{$a_title}"><span class="title">{$title}</span></a>
EOT;
		}

		if ($title && $column->orderable)
		{
			$order = $column->order;
			$order_reverse = ($order === null) ? $column->default_order : -$order;

			return new Element('a', [

				Element::INNER_HTML => '<span class="title">' . $title . '</span>',

				'title' => $t('Sort by: :identifier', [ ':identifier' => $title ]),
				'href' => "?order=$id:" . ($order_reverse < 0 ? 'desc' : 'asc'),
				'class' => $order ? ($order < 0 ? 'desc' : 'asc') : null

			]);
		}

		return $title;
	}
}
