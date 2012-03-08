<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Images;

use ICanBoogie\Operation;
use Brickrouge\Element;

class GalleryManager extends ManageBlock
{
	public function __construct($module, array $tags=array())
	{
		parent::__construct
		(
			$module, $tags + array
			(
				self::T_BLOCK => 'gallery'
			)
		);
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add('public/gallery.css');
	}

	protected function retrieve_options($name)
	{
		return parent::retrieve_options($name . '/gallery');
	}

	protected function store_options(array $options, $name)
	{
		return parent::store_options($options, $name . '/gallery');
	}

	protected function render_body()
	{
		global $core;

		$size = 128; // TODO-20110627: make this customizable
		$size = min($size, max($size, 16), 512);

		$module_id = (string) $this->module;

		$order = $this->options['order'];

		$rc = PHP_EOL . '<tr id="gallery"><td colspan="' . (count($this->columns) + 1) . '">';

		$user = $core->user;
		$context = $core->site->path;

		foreach ($this->entries as $entry)
		{
			$title = $entry->title;
			$path = $entry->path;

			$key = null;

			$label = new Element
			(
				'a', array
				(
					Element::INNER_HTML => wd_entities($title),

					'class' => 'edit',
					'title' => t('Edit this item'),
					'href' => $context . '/admin/' . $module_id . '/' . $entry->nid . '/edit'
				)
			);

			if ($size >= 64)
			{
				if ($user->has_ownership($module_id, $entry))
				{
					$this->checkboxes++;

					$key = new Element
					(
						Element::TYPE_CHECKBOX, array
						(
							'class' => 'key',
							'title' => t('Toggle selection for entry #\1', array($entry->nid)),
							'value' => $entry->nid
						)
					);
				}

				if (isset($order['modified']))
				{
					$label .= ' <span class="small">(' . $this->render_cell_datetime($entry, 'modified') . ')</span>';
				}
				else if (isset($order['size']))
				{
					$label .= ' <span class="small">(' . $this->render_cell_size($entry, 'size') . ')</span>';
				}
			}

			$img = new Element
			(
				'img', array
				(
					'src' => Operation::encode
					(
						$entry->constructor . '/' . $entry->nid . '/thumbnail', array
						(
							'w' => $size,
							'h' => $size,
							'method' => 'constrained',
							'quality' => 90
						)
					),

					'title' => $title,
					'alt' => $title
				)
			);

			$rc .= <<<EOT
<div class="thumbnailer-wrapper" style="width: {$size}px;">
<a href="$path" rel="lightbox[]">$img</a>
<div class="key">$key</div>
$label
</div>
EOT;

		}

		$rc .= '</td></tr>' . PHP_EOL;

		return $rc;
	}
}