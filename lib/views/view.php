<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Views;

use ICanBoogie;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\I18n;
use ICanBoogie\Module;

use BrickRouge\Document;
use BrickRouge\Element;

/**
 * A view on provided data.
 */
class View
{
	protected $options;
	protected $id;
	protected $engine;
	protected $document;
	protected $page;
	protected $template;

	public $module;
	public $module_id;
	public $type;

	public function __construct($id, $options, $engine, $document, $page, $template=null)
	{
		$this->options = $options + array
		(
			'access_callback' => null
		);

		$this->id = $id;
		$this->engine = $engine;
		$this->document = $document;
		$this->page = $page;
		$this->template = $template;

		$this->module_id = $options['module'];
		$this->type = $options['type'];
	}

	/**
	 * Renders the view.
	 *
	 * @return mixed
	 */
	public function __invoke()
	{
		global $core;

		$this->validate_access();

		$assets = array('css' => array(), 'js' => array());
		$options = $this->options;

		if (isset($options['assets']))
		{
			$assets = $options['assets'];
		}

		$this->add_assets($this->document, $assets);

		#

		$this->fire_render_before(array('id' => $this->id));

		$rc = $this->render_outer_html();

		$this->fire_render(array('id' => $this->id, 'rc' => &$rc));

		return $rc;
	}

	/**
	 * Alters template context.
	 *
	 * @param Context $context
	 */
	protected function alter_context(Context $context)
	{
		$context['view'] = $this;
	}

	/**
	 * Adds view's assets to the document.
	 *
	 * @param WdDocument $document
	 * @param array $assets
	 */
	protected function add_assets(Document $document, array $assets=array())
	{
		if (isset($assets['js']))
		{
			foreach ((array) $assets['js'] as $asset)
			{
				list($file, $priority) = (array) $asset + array(1 => 0);

				$document->js->add($file, $priority);
			}
		}

		if (isset($assets['css']))
		{
			foreach ((array) $assets['css'] as $asset)
			{
				list($file, $priority) = (array) $asset + array(1 => 0);

				$document->css->add($file, $priority);
			}
		}
	}

	/**
	 * Fires the `render:before` event on the view using the specified parameters.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	protected function fire_render_before(array $params=array())
	{
		return Event::fire('render:before', $params, $this);
	}

	/**
	 * Fires the `render` event on the view using the specified parameters.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	protected function fire_render(array $params=array())
	{
		return Event::fire('render', $params, $this);
	}

	/**
	 * Returns the placeholder for the empty view.
	 *
	 * @return string
	 */
	protected function render_empty_inner_html()
	{
		global $core;

		$rc = null;
		$default = t('The view %name is empty.', array('%name' => $this->id));
		$type = $this->type;
		$module = $this->module;
		$module_flat_id = $module->flat_id;

		if (isset($view['on_empty']))
		{
			$rc = call_user_func($view['on_empty'], $this);
		}
		else if ($module && $this->type == 'list')
		{
			$placeholder = $core->site->metas[$module_flat_id . '.place_holder']; // FIXME-20111222: rename 'place_holder' as 'placeholder'

			if ($placeholder)
			{
				$rc = $placeholder;
			}
			else
			{
// 				$rc = '<p>' . t('empty_view', array(), array('scope' => array($module->flat_id, $type), 'default' => 'No record found.')) . '</p>';
				$default = 'No record found.';
			}
		}
		else if ($module)
		{
			$default .= t
			(
				' <ul><li>The placeholder %placeholder was tried, but it does not exists.</li><li>The %message was tried, but it does not exists.</li></ul>', array
				(
					'%placeholder' => $module_flat_id . ".$type.placeholder",
					'%message' => $module_flat_id . '.' . $type . '.empty_view'
				)
			);
		}

		if (!$rc)
		{
			$rc = t('empty_view', array(), array('scope' => $module_flat_id . '.' . $type, 'default' => $default));

			if ($rc)
			{
				$rc = '<div class="alert-message">' . $rc . '</div>';
			}
		}

		return $rc;
	}

	/**
	 * Fires the `render_inner_html:empty` event on the view using the specified parameters.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	protected function fire_render_empty_inner_html(array $params=array())
	{
		return Event::fire('render_inner_html:empty', $params, $this);
	}

	protected function init_range()
	{
		global $core;

		$limit_key = $this->module->flat_id . '.limits.' . $this->type;
		$limit = $core->site->metas[$limit_key] ?: 10;

		return array
		(
			'page' => empty($_GET['page']) ? 0 : (int) $_GET['page'],
			'limit' => $limit,
			'count' => null
		);
	}

	protected function provide($provider, &$context, array $conditions)
	{
		if ($provider === true)
		{
			wd_log('using module provider for view %view', array('view' => $id));

			$bind = $this->module->provide_view($name, $engine);
		}
		else
		{
			if (!class_exists($provider))
			{
				throw new \InvalidArgumentException(\ICanBoogie\format
				(
					'Provider class %class for view %id does not exists', array
					(
						'class' => $provider,
						'id' => $id
					)
				));
			}

			$provider = new $provider($this, $context, $this->module, $conditions);

			$bind = $provider();
		}

		return $bind;
	}

	/**
	 * Renders the inner HTML of the view.
	 *
	 * @throws WdException
	 * @throws Exception
	 * @return unknown|string|Ambigous <string, mixed>
	 */
	protected function render_inner_html($template_path, $engine)
	{
		global $core;

		$view = $this->options;
		$bind = null;
		$id = $this->id;

		$page = $this->page;


		if (!empty($view['provider']))
		{
			list($constructor, $name) = explode('/', $id);

			$this->module = $module = $core->modules[$constructor];

			$this->range = $this->init_range();

			$bind = $this->provide($this->options['provider'], $engine->context, $page->url_variables + $_GET);

			$engine->context['range'] = $this->range;

			if (!$bind)
			{
				$this->element->add_class('empty');

				$rc = (string) $this->render_empty_inner_html();

				$this->fire_render_empty_inner_html
				(
					array
					(
						'id' => $id,
						'rc' => &$rc
					)
				);

				return $rc;
			}
		}

		#
		#
		#

		$rc = '';

		if ($template_path)
		{
			/*
			$file = $core->site->resolve_path("templates/views/$id.php");

			if (!$file)
			{
				$file = $core->site->resolve_path("templates/views/$id.html");
			}

			if ($file)
			{
				$file = ICanBoogie\DOCUMENT_ROOT . $file;
			}
			else
			{
				$file = $view['file'];
			}
			*/

			/*
			$scope = isset($view['scope']) ? $view['scope'] : null;

			if ($scope)
			{
				I18n::push_scope($scope);
			}
			*/

			I18n::push_scope($this->module->flat_id);

			try
			{
				$extension = pathinfo($template_path, PATHINFO_EXTENSION);

				if ('php' == $extension)
				{
					$module = $core->modules[$this->module_id];

					ob_start();

					//TODO: use a context and the alter_context() method

					wd_isolated_require
					(
						$template_path, array
						(
							'bind' => $bind,
							'context' => &$context,
							'core' => $core,
							'document' => $core->document,
							'page' => $page,
							'module' => $module
						)
					);

					$rc = ob_get_clean();
				}
				else if ('html' == $extension)
				{
					$rc = Patron(file_get_contents($template_path), $bind, array('file' => $template_path));
				}
				else
				{
					throw new Exception('Unable to process file %file, unsupported type', array('file' => $template_path));
				}
			}
			catch (\Exception $e)
			{
// 				if ($scope)
				{
					I18n::pop_scope();
				}

				throw $e;
			}

// 			if ($scope)
			{
				I18n::pop_scope();
			}
		}
		else if (isset($view['module']) && isset($view['block']))
		{
			$rc = $core->modules[$view['module']]->getBlock($view['block']);
		}
		else
		{
			throw new Exception('Unable to render view %view. The description of the view is invalid: :descriptor', array('view' => $id, 'descriptor' => $view));
		}

		return $rc;
	}

	protected $element;

	/**
	 * Returns the HTML representation of the view element and its content.
	 *
	 * @return string
	 */
	protected function render_outer_html()
	{
		$page = $this->page;
		$id = $this->id;
		$class = 'view';

		if (strpos($id, '/'))
		{
			list($constructor, $type) = explode('/', $id, 2);

			$class .= ' constructor-' . wd_normalize($constructor) . ' ' . $type;
		}

		$this->element = new Element
		(
			'div', array
			(
				'id' => 'view-' . wd_normalize($id),
				'class' => $class
			)
		);

		$template_path = $this->resolve_template_location();

		wd_log("view template: $template_path");

		$engine = $this->engine;

		$rc = $this->render_inner_html($template_path, $engine);

		if (preg_match('#\.html$#', $page->template))
		{
			$this->element[Element::INNER_HTML] = $rc;

			$rc = (string) $this->element;
		}

		return $rc;
	}

	protected function resolve_template_location()
	{
		global $core;

		$id = $this->id;
		$type = $this->type;
		$handled = array('php', 'html');

		foreach ($handled as $extension)
		{
			$try = $core->site->resolve_path("templates/views/$id.$extension");

			if ($try)
			{
				return ICanBoogie\DOCUMENT_ROOT . $try;
			}
		}

		if (isset($this->options['file']))
		{
			return $this->options['file'];
		}

		$m = $core->modules[$this->module_id];

		while ($m)
		{
			$base = $m->descriptor[Module::T_PATH] . 'views/' . $type . '.';

			foreach ($handled as $extension)
			{
				if (file_exists($base . $extension))
				{
					return $base . $extension;
				}
			}

			$m = $m->parent;
		}
	}

	/**
	 * Checks if the view access is valid.
	 *
	 * @throws WdHTTPException when the view access requires authentication.
	 *
	 * @return boolean true
	 */
	protected function validate_access()
	{
		$access_callback = $this->options['access_callback'];

		if ($access_callback && !call_user_func($access_callback))
		{
			throw new Exception\HTTP
			(
				'The requested URL %uri requires authentication.', array
				(
					'%uri' => $_SERVER['REQUEST_URI']
				),

				401
			);
		}

		return true;
	}
}