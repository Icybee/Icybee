<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element;

use function ICanBoogie\app;
use Icybee\Modules\Pages\PageRenderer;

/**
 * @property \ICanBoogie\Application $app
 * @property \Icybee\Modules\Pages\Page $page
 */
class Document extends \Brickrouge\Document
{
	/**
	 * Getter hook for the use ICanBoogie\Application::$document property.
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

	static public function markup_document_title()
	{
		$document = \Brickrouge\get_document();

		$title = isset($document->title) ? $document->title : null;

		new Document\BeforeRenderTitleEvent($document, [ 'title' => &$title ]);

		$rc = '<title>' . \ICanBoogie\escape($title) . '</title>';

		new Document\RenderTitleEvent($document, [ 'html' => &$rc ]);

		return $rc;
	}

	/**
	 * Returns the rendered metas of the document.
	 *
	 * {@link Document\BeforeRenderMetaEvent} is fired to collect HTTP equiv tags and meta tags.
	 * {@link Document\RenderMetaEvent} is fired once the metas have been rendered into a HTML
	 * string.
	 *
	 * @return string
	 */
	static public function markup_document_metas()
	{
		$document = \Brickrouge\get_document();
		$http_equiv = [ 'Content-Type' => 'text/html; charset=' . \ICanBoogie\CHARSET ];
		$meta = [ 'og' => [] ];

		new Document\BeforeRenderMetaEvent($document, $http_equiv, $meta);

		$html = '';

		foreach ($http_equiv as $name => $content)
		{
			$html .= '<meta http-equiv="' . \ICanBoogie\escape($name) . '" content="' . \ICanBoogie\escape($content) . '" />' . PHP_EOL;
		}

		foreach ($meta as $name => $content)
		{
			if (is_array($content))
			{
				continue;
			}

			$html .= '<meta name="' . \ICanBoogie\escape($name) . '" content="' . \ICanBoogie\escape($content) . '" />' . PHP_EOL;
		}

		foreach ($meta as $name => $properties)
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

		new Document\RenderMetaEvent($document, $html);

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
	 * and is replaced when the `Icybee\Modules\Pages\PageRenderer::render` event is fired.
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
	 * @param \Patron\Engine $engine
	 * @param mixed $template
	 *
	 * @return void|string
	 */
	static public function markup_document_css(array $args, \Patron\Engine $engine, $template)
	{
		$app = app();
		$document = $app->document;

		if (isset($args['href']))
		{
			$document->css->add($args['href'], $args['weight'], dirname($engine->get_file()));

			return null;
		}

		$placeholder = '<!-- document-css-placeholder-' . md5(uniqid()) . ' -->';

		$app->events->attach(function(PageRenderer\RenderEvent $event, PageRenderer $target) use($engine, $template, $placeholder, $document)
		{
			#
			# The event is chained so that it gets executed once the event chain has been
			# processed.
			#

			$event->chain(function(PageRenderer\RenderEvent $event) use($engine, $template, $placeholder, $document)
			{
				$html = $template ? $engine($template, $document->css) : (string) $document->css;

				$event->replace($placeholder, $html);
			});
		});

		return PHP_EOL . $placeholder;
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
	 * @param \Patron\Engine $engine
	 * @param mixed $template
	 *
	 * @return void|string
	 */
	static public function markup_document_js(array $args, \Patron\Engine $engine, $template)
	{
		$app = app();
		$document = $app->document;

		if (isset($args['href']))
		{
			$document->js->add($args['href'], $args['weight'], dirname($engine->get_file()));

			return null;
		}

		$placeholder = '<!-- document-js-placeholder-' . md5(uniqid()) . ' -->';

		$app->events->attach(function(PageRenderer\RenderEvent $event, PageRenderer $target) use($engine, $template, $placeholder, $document)
		{
			#
			# The event is chained so that it gets executed once the event chain has been
			# processed.
			#

			$event->chain(function(PageRenderer\RenderEvent $event) use($engine, $template, $placeholder, $document)
			{
				$html = $template ? $engine($template, $document->js) : (string) $document->js;

				$event->replace($placeholder, $html);
			});
		});

		return PHP_EOL . $placeholder;
	}

	/*
	 * Object
	 */

	public $title;
	public $page_title;

	protected function get_page()
	{
		return $this->app->request->context->page;
	}

	public function __construct()
	{
		parent::__construct();

		$cache_assets = $this->app->config['cache assets'];

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
		$names = $this->page->css_class_names;

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
