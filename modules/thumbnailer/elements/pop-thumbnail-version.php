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
	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('pop-thumbnail-version.js');
	}

	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'a', $attributes + array
			(
				'class' => 'spinner'
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

	protected function render_class(array $class_names)
	{
		if (!$this['value'])
		{
			$class_names['placeholder'] = true;
		}

		return parent::render_class($class_names);
	}

	protected function render_inner_html()
	{
		$html = parent::render_inner_html();

		$value = $this['value'] ?: $this[self::DEFAULT_VALUE];
		$value = json_decode($value, true);

		$input = new Element
		(
			'input', array
			(
				'name' => $this['name'],
				'type' => 'hidden',
				'value' => $value ? json_encode($value) : ''
			)
		);

		$content = '';

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

			/*
			if ($value['no-upscale'])
			{
				$options[] = 'ne pas agrandir';
			}
			*/

			if ($options)
			{
				$options = '&ndash; ' . implode(', ', $options);
			}
			else
			{
				$options = '';
			}

			$content = <<<EOT
{$w}×{$h} {$method} $format <span class="small light">$options</span>
EOT;
		}

		$placeholder = 'Version non définie';

		return <<<EOT
$input <span class="spinner-content">$content</span> <em class="spinner-placeholder">$placeholder</em> $html
EOT;
	}
}