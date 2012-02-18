<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Widget;

use Brickrouge\Element;

class PopThumbnailVersion extends \Brickrouge\Widget
{
	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'button', $attributes + array
			(
				'class' => 'spinner',
				'type' => 'button'
			)
		);
	}

	public function offsetSet($offset, $value)
	{
		if (($offset == 'value' || $offset == self::DEFAULT_VALUE) && is_array($value))
		{
			$value = json_encode($value);
		}

		parent::offsetSet($offset, $value);
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('pop-thumbnail-version.js');
	}

	protected function render_inner_html()
	{
		$rc = parent::render_inner_html();

		$value = $this['value'] ?: $this[self::DEFAULT_VALUE];
		$value = json_decode($value, true);

		if ($value)
		{
			$value += array
			(
				'w' => null,
				'h' => null,
				'no-upscale' => false,
				'method' => 'fill',
				'format' => 'jpeg',
				'interlace' => false
			);

			$options = array();

			$w = $value['w'] ?: '<em>auto</em>';
			$h = $value['h'] ?: '<em>auto</em>';

			$method = $value['method'];
			$format = '.' . $value['format'];

			if ($value['no-upscale'])
			{
				$options[] = 'ne pas agrandir';
			}

			if ($value['interlace'])
			{
				$options[] = 'entrelacer';
			}

			if ($options)
			{
				$options = '&ndash; ' . implode(', ', $options);
			}
			else
			{
				$options = '';
			}

			$rc .= <<<EOT
{$w}×{$h} {$method} $format <span class="small light">$options</span>
EOT;
		}
		else
		{
			$rc .= '<em>Version non définie</em>';
		}

		return $rc;
	}
}