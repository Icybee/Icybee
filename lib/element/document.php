<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Events;
use ICanBoogie\Exception;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\Alert;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;

use Icybee\Modules\Pages\PageController;
use Icybee\Modules\Users\Users\Role;

class Document extends \Brickrouge\Document
{
	public $on_setup = false;
	protected $changed_site;

	public $title;
	public $page_title;

	public $content;

	public function __construct()
	{
		global $core;

		parent::__construct();

		$cache_assets = $core->config['cache assets'];

		$this->css->use_cache = $cache_assets;
		$this->js->use_cache = $cache_assets;
	}

	/**
	 * Getter hook for the use ICanBoogie\Core::$document property.
	 *
	 * @return Document
	 */
	static public function hook_get_document()
	{
		global $document;

		return $document = new \Brickrouge\Document();
	}

	static public function markup_document_title(array $args, \Patron\Engine $patron, $template)
	{
		global $core;

		$document = $core->document;

		$title = isset($document->title) ? $document->title : null;

		new Document\BeforeRenderTitleEvent($document, array('title' => &$title));

		$rc = '<title>' . \ICanBoogie\escape($title) . '</title>';

		new Document\RenderTitleEvent($document, array('html' => &$rc));

		return $rc;
	}

	/**
	 * Returns the rendered metas of the document.
	 *
	 * {@link Document\BeforeRenderMetasEvent} is fired to collect HTTP equiv tags and meta tags.
	 * {@link Document\RenderMetasEvent} is fired once the metas have been rendered into a HTML
	 * string.
	 *
	 * @return string
	 */
	static public function markup_document_metas()
	{
		global $core;

		$document = $core->document;

		$http_equiv = array
		(
			'Content-Type' => 'text/html; charset=' . \ICanBoogie\CHARSET
		);

		$metas = array
		(
			'og' => array()
		);

		new Document\BeforeRenderMetasEvent($document, array('http_equiv' => &$http_equiv, 'metas' => &$metas));

		$html = '';

		foreach ($http_equiv as $name => $content)
		{
			$html .= '<meta http-equiv="' . \ICanBoogie\escape($name) . '" content="' . \ICanBoogie\escape($content) . '" />' . PHP_EOL;
		}

		foreach ($metas as $name => $content)
		{
			if (is_array($content))
			{
				continue;
			}

			$html .= '<meta name="' . \ICanBoogie\escape($name) . '" content="' . \ICanBoogie\escape($content) . '" />' . PHP_EOL;
		}

		foreach ($metas as $name => $properties)
		{
			if (!is_array($properties))
			{
				continue;
			}

			foreach ($properties as $property => $content)
			{
				$html .= '<meta property="' . $name . ':' . \ICanBoogie\escape($property) . '" content="' . \ICanBoogie\escape($content) . '" />' . PHP_EOL;
			}
		}

		new Document\RenderMetasEvent($document, array('html' => &$html));

		return $html;
	}

	/**
	 * Adds or return the CSS assets of the document.
	 *
	 * @param array $args
	 * @param Patron\Engine $engine
	 * @param mixed $template
	 *
	 * @return void|string
	 */
	static public function markup_document_css(array $args, \Patron\Engine $engine, $template)
	{
		global $core;

		if (isset($args['add']))
		{
			$file = $engine->get_file();

			\ICanBoogie\log(__FILE__ . '::' . __FUNCTION__ . '::file: \1', array($file));

			$core->document->css->add($args['add'], dirname($file));

			return;
		}

		$key = '<!-- document-css-placeholder-' . md5(uniqid()) . ' -->';

		Event\attach
		(
			function(PageController\RenderEvent $event, PageController $target) use($engine, $template, $key)
			{
				#
				# The event is chained so that is gets executed once the event chain has been
				# processed.
				#

				$event->chain(function(PageController\RenderEvent $event) use($engine, $template, $key)
				{
					global $core;

					$document = $core->document;

					$html = $template ? $engine($template, $document->css) : (string) $document->css;

					$event->html = str_replace($key, $html, $event->html);
				});
			}
		);

		return PHP_EOL . $key;
	}

	static public function markup_document_js(array $args, \Patron\Engine $patron, $template)
	{
		global $core;

		$document = $core->document;

		return $template ? $patron($template, $document->js) : (string) $document->js;
	}
}

namespace Icybee\Document;

/**
 * Event class for the `Brickrouge\Document::render_metas:before` event.
 */
class BeforeRenderMetasEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the HTTP equivalent array.
	 *
	 * @var array[string]string
	 */
	public $http_equiv;

	/**
	 * Reference to the metas array.
	 *
	 * The `og` array is used to define OpenGraph metas.
	 *
	 * @var array[string]string
	 */
	public $metas;

	/**
	 * The event is constructed with the type `render_metas:before`.
	 *
	 * @param \Brickrouge\Document $target
	 * @param array $payload
	 */
	public function __construct(\Brickrouge\Document $target, array $payload)
	{
		parent::__construct($target, 'render_metas:before', $payload);
	}
}

/**
 * Event class for the `Brickrouge\Document::render_metas` event.
 */
class RenderMetasEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the rendered HTML.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The event is constructed with the type `render_metas`.
	 *
	 * @param \Brickrouge\Document $target
	 * @param array $payload
	 */
	public function __construct(\Brickrouge\Document $target, array $payload)
	{
		parent::__construct($target, 'render_metas', $payload);
	}
}

/**
 * Event class for the `Brickrouge\Document::render_title:before` event.
 */
class BeforeRenderTitleEvent extends \ICanBoogie\Event
{
	/**
	 * Reference of the title to render.
	 *
	 * @var string
	 */
	public $title;

	/**
	 * The event is constructed with the type `render_title:before`.
	 *
	 * @param \Brickrouge\Document $target
	 * @param array $payload
	 */
	public function __construct(\Brickrouge\Document $target, array $payload)
	{
		parent::__construct($target, 'render_title:before', $payload);
	}
}

/**
 * Event class for the `Brickrouge\Document::render_title` event.
 */
class RenderTitleEvent extends \ICanBoogie\Event
{
	/**
	 * HTML of the `TITLE` markup.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The event is constructed with the type `render_title`.
	 *
	 * @param \Brickrouge\Document $target
	 * @param array $payload
	 */
	public function __construct(\Brickrouge\Document $target, array $payload)
	{
		parent::__construct($target, 'render_title', $payload);
	}
}