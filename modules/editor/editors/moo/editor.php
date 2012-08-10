<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Modules;
use ICanBoogie\Modules\Thumbnailer\Thumbnail;
use ICanBoogie\Operation;

use Brickrouge\Element;

class moo_WdEditorElement extends WdEditorElement
{
	const T_ACTIONS = '#editor-actions';

	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			'textarea', $tags + array
			(
				'class' => 'editor moo',

				'rows' => 16
			)
		);
	}

	public function render_outer_html()
	{
		global $core;

		$document = $core->document;

		$css = $this[self::T_STYLESHEETS] ?: array();

//		\ICanBoogie\log('css: \1', array($css));

		if (!$css)
		{
			$info = Modules\Pages\Module::get_template_info('page.html');

			if (isset($info[1]))
			{
				$css = $info[1];
			}
		}

		array_unshift($css, $document->resolve_url('public/body.css'));

		if (count($css) == 1)
		{
			$css[] = $document->resolve_url(\Brickrouge\ASSETS . 'brickrouge.css');
		}

		$try = \ICanBoogie\DOCUMENT_ROOT . 'public/page.css';

		if (file_exists($try))
		{
			$css[] = $document->resolve_url($try);
		}

		$document->css->add('public/assets/MooEditable.css');
		$document->css->add('public/assets/MooEditable.Image.css');
		$document->css->add('public/assets/MooEditable.Extras.css');
		$document->css->add('public/assets/MooEditable.SilkTheme.css');
		$document->css->add('public/assets/MooEditable.Paste.css');

		$document->js->add('public/source/MooEditable.js');
		$document->js->add('public/source/MooEditable.Image.js');
		$document->js->add('public/source/MooEditable.UI.MenuList.js');
		$document->js->add('public/source/MooEditable.Extras.js');
		$document->js->add('public/source/MooEditable.Paste.js');
		$document->js->add('public/source/MooEditable.CleanPaste.js');
		$document->js->add('public/source/MooEditable.Outline.js');

		$document->js->add('public/auto.js');

		$actions = $this[self::T_ACTIONS] ?: 'standard';

		if ($actions == 'standard')
		{
			$actions = 'bold italic underline strikethrough | formatBlock justifyleft justifyright justifycenter justifyfull | insertunorderedlist insertorderedlist indent outdent | undo redo | createlink unlink | image | removeformat paste outline toggleview';

			if (0)
			{
				$actions .= ' / tableadd | tableedit | tablerowspan tablerowsplit tablerowdelete | tablecolspan tablecolsplit tablecoldelete';

				$document->css->add('public/assets/MooEditable.Table.css');
				$document->js->add('public/source/MooEditable.Table.js');
			}
		}
		else if ($actions == 'minimal')
		{
			$actions = 'bold italic underline strikethrough | insertunorderedlist insertorderedlist | undo redo | createlink unlink | removeformat paste toggleview';
		}

		$this->dataset['base-url'] = '/';
		$this->dataset['actions'] = $actions;
		$this->dataset['external-css'] = $css;

		return parent::render_outer_html();
	}

	static public function render($contents)
	{
		return preg_replace_callback
		(
			'#<img\s+[^>]+>#', function($match)
			{
				global $core;

				preg_match_all('#([\w\-]+)\s*=\s*\"([^"]+)#', $match[0], $attributes);

				$attributes = array_combine($attributes[1], $attributes[2]);
				$attributes = array_map(function($v) { return html_entity_decode($v, ENT_COMPAT, ICanBoogie\CHARSET); }, $attributes) + array
				(
					'width' => null,
					'height' => null,
					'data-nid' => null
				);

				$w = $attributes['width'];
				$h = $attributes['height'];
				$nid = $attributes['data-nid'];

				if ($w && $h && $nid)
				{
					$attributes['src'] = Operation::encode('images/' . $nid . '/' . $w . 'x' . $h);
				}
				else if (($w || $h) && preg_match('#^/repository/files/image/(\d+)#', $attributes['src'], $matches))
				{
					$nid = $matches[1];

					$options = $attributes;

					unset($options['src']);

					$thumbnail = new Thumbnail($core->models['images'][$nid], $options);

// 					var_dump($thumbnail->url, $thumbnail);

					$attributes['src'] = $thumbnail->url;
				}

				$path = null;

				if (isset($attributes['data-lightbox']) && $nid)
				{
					$attributes['src'] = preg_replace('#\&amp;lightbox=true#', '', $attributes['src']);
					$path = $core->models['images']->select('path')->find_by_nid($nid)->rc;
				}

				unset($attributes['data-nid']);
				unset($attributes['data-lightbox']);

				$rc = (string) new Element('img', $attributes);

				if ($path)
				{
					$rc = '<a href="' . \ICanBoogie\escape($path) . '" rel="lightbox[]">' . $rc . '</a>';
				}

				return $rc;
			},

			$contents
		);
	}
}