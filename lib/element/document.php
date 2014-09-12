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

use Brickrouge\Element;

use Icybee\Modules\Pages\PageController;

class Document extends \Brickrouge\Document
{
	/**
	 * Getter hook for the use ICanBoogie\Core::$document property.
	 *
	 * @return Document
	 */
	static public function get()
	{
		global $document;

		return $document = new static;
	}

	/*
	 * Markups
	 */

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
	 * CSS assets can be collected and rendered into `LINK` elements with the `p:document:css`
	 * element. The `href` attribute is used to add an asset to the collection. The `weight`
	 * attribute specifies the weight of that asset. If the `weight` attribute is not specified,
	 * the weight of the asset is defaulted to 100. If the `href` attribute is not specified,
	 * the assets are rendered. If a template is specified the collection is passed as `this`,
	 * otherwise the collection is rendered into an HTML string of `LINK` elements.
	 *
	 * Note: Currently, the element is not rendered right away, a placeholder is inserted instead
	 * and is replaced when the `Icybee\Modules\Pages\PageController::render` event is fired.
	 *
	 * <pre>
	 * <p:document:css
	 *     href = string
	 *     weight = int>
	 *     <!-- Content: p:with-params, template? -->
	 * </p:document:css>
	 * </pre>
	 *
	 * Example:
	 *
	 * <pre>
	 * <p:document:css href="/public/page.css" />
	 * <p:document:css href="/public/reset.css" weight="-100" />
	 *
	 * <p:document:css />
	 * </pre>
	 *
	 * Produces:
	 *
	 * <pre>
	 * <link href="/public/reset.css" type="text/css" rel="stylesheet" />
	 * <link href="/public/page.css" type="text/css" rel="stylesheet" />
	 * </pre>
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

		if (isset($args['href']))
		{
			$core->document->css->add($args['href'], $args['weight'], dirname($engine->get_file()));

			return;
		}

		$key = '<!-- document-css-placeholder-' . md5(uniqid()) . ' -->';

		$core->events->attach(function(PageController\RenderEvent $event, PageController $target) use($engine, $template, $key)
		{
			#
			# The event is chained so that it gets executed once the event chain has been
			# processed.
			#

			$event->chain(function(PageController\RenderEvent $event) use($engine, $template, $key)
			{
				global $core;

				$document = $core->document;

				$html = $template ? $engine($template, $document->css) : (string) $document->css;

				$event->html = str_replace($key, $html, $event->html);
			});
		});

		return PHP_EOL . $key;
	}

	/**
	 * JavaScript assets can be collected and rendered into `SCRIPT` elements with the `p:document:js`
	 * element. The `href` attribute is used to add an asset to the collection. The `weight`
	 * attribute specifies the weight of that asset. If the `weight` attribute is not specified,
	 * the weight of the asset is defaulted to 100. If the `href` attribute is not specified,
	 * the assets are rendered. If a template is specified the collection is passed as `this`,
	 * otherwise the collection is rendered into an HTML string of `SCRIPT` elements.
	 *
	 * <pre>
	 * <p:document:js
	 *     href = string
	 *     weight = int>
	 *     <!-- Content: p:with-params, template? -->
	 * </p:document:js>
	 * </pre>
	 *
	 * Example:
	 *
	 * <pre>
	 * <p:document:js href="/public/page.js" />
	 * <p:document:js href="/public/reset.js" weight="-100" />
	 *
	 * <p:document:js />
	 * </pre>
	 *
	 * Produces:
	 *
	 * <pre>
	 * <script src="/public/reset.css" type="text/javascript"></script>
	 * <script src="/public/page.css" type="text/javascript"></script>
	 * </pre>
	 *
	 * @param array $args
	 * @param Patron\Engine $engine
	 * @param mixed $template
	 *
	 * @return void|string
	 */
	static public function markup_document_js(array $args, \Patron\Engine $engine, $template)
	{
		global $core;

		$document = $core->document;

		if (isset($args['href']))
		{
			$document->js->add($args['href'], $args['weight'], dirname($engine->get_file()));

			return;
		}

		return $template ? $engine($template, $document->js) : (string) $document->js;
	}

	/*
	 * Object
	 */

	public $title;
	public $page_title;

	public function __construct()
	{
		global $core;

		parent::__construct();

		$cache_assets = $core->config['cache assets'];

		$this->css->use_cache = $cache_assets;
		$this->js->use_cache = $cache_assets;
	}

	public function __get($property)
	{
		$value = parent::__get($property);

		if ($property === 'css_class_names')
		{
			new \Brickrouge\AlterCSSClassNamesEvent($this, $value);
		}

		return $value;
	}

	/**
	 * Returns the CSS class of the node.
	 *
	 * @return string
	 */
	protected function get_css_class()
	{
		return $this->css_class();
	}

	/**
	 * Returns the CSS class names of the node.
	 *
	 * @return array[string]mixed
	 */
	protected function get_css_class_names()
	{
		global $core;

		$names = $core->request->context->page->css_class_names;

		unset($names['active']);

		return $names;
	}

	/**
	 * Return the CSS class of the node.
	 *
	 * @param string|array $modifiers CSS class names modifiers
	 *
	 * @return string
	 */
	public function css_class($modifiers=null)
	{
		return \Brickrouge\render_css_class($this->css_class_names, $modifiers);
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
 *
 * @todo-20130318: is `title` the only property of the payload ? there should be `page_title`,
 * `site_title` and `separator`. Or an array of parts with the `page` and `site` key.
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