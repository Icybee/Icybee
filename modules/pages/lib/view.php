<?php

namespace ICanBoogie;

use ICanBoogie\Exception;

class View
{
	protected $options;
	protected $id;
	protected $engine;
	protected $document;
	protected $page;
	protected $template;

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
	}

	public function __invoke()
	{
		global $core;

		$this->validate_access();

		$view = $this->options;
		$root = $view['root'];
		$document = $this->document;

		if (isset($view['assets']['js']))
		{
			$assets = (array) $view['assets']['js'];

			foreach ($assets as $asset)
			{
				list($file, $priority) = (array) $asset + array(1 => 0);

				$document->js->add($file, $priority, $root);
			}
		}

		if (isset($view['assets']['css']))
		{
			$assets = (array) $view['assets']['css'];

			foreach ($assets as $asset)
			{
				list($file, $priority) = (array) $asset + array(1 => 0);

				$document->css->add($file, $priority, $root);
			}
		}

		$rc = $this->render_outer_html();

		Event::fire
		(
			'render', array
			(
				'id' => $this->id,
				'rc' => &$rc
			),

			$this
		);

		return $rc;
	}

	protected function render_inner_html()
	{
		global $core;

		$view = $this->options;
		$bind = null;
		$id = $this->id;
		$patron = $this->engine;
		$page = $this->page;

		if (!empty($view['provider']))
		{
			list($constructor, $name) = explode('/', $id);

			$module = $core->modules[$constructor];
			$bind = $module->provide_view($name, $patron);

			if (!$bind)
			{
				if ($module && $name == 'list')
				{
					$placeholder = $core->site->metas[$module->flat_id . '.place_holder'];

					if ($placeholder)
					{
						return $placeholder;
					}

					$rc = '<p>' . t('empty_view', array(), array('scope' => array($module->flat_id, $name), 'default' => 'No record found.')) . '</p>';

					if (preg_match('#\.html$#', $page->template))
					{
						$class = 'view constructor-' . wd_normalize($constructor) . ' ' . $name;

						$rc = '<div id="view-' . wd_normalize($id) . '" class="' . $class . ' empty">' . $rc . '</div>';
					}

					return $rc;
				}

				return;
			}

			if ($module instanceof Module\Nodes)
			{
				if ($name == 'view')
				{
					$page->node = $bind;
					// $page->title = $bind->title; TODO-20110701: what if $page->title is set to something else by the provider
				}
				else if ($bind instanceof ActiveRecord\Node)
				{
					Event::fire('nodes_load', array('nodes' => array($bind)), $patron);
				}
				else if (is_array($bind))
				{
					$first = current($bind);

					if ($first instanceof ActiveRecord\Node)
					{
						Event::fire('nodes_load', array('nodes' => $bind), $patron);
					}
				}

			}
		}

		#
		#
		#

		$rc = '';

		if (isset($view['file']))
		{
			$file = $core->site->resolve_path("templates/views/$id.php");

			if (!$file)
			{
				$file = $core->site->resolve_path("templates/views/$id.html");
			}

			if ($file)
			{
				$file = $_SERVER['DOCUMENT_ROOT'] .  $file;
			}
			else
			{
				$file = $view['file'];
			}

			$scope = isset($view['scope']) ? $view['scope'] : null;

			if ($scope)
			{
				I18n::push_scope($scope);
			}

			try
			{
				if (preg_match('#\.php$#', $file))
				{
					$module = null;

					if (isset($view['module']))
					{
						$module = $core->modules[$view['module']];
					}

					ob_start();

					wd_isolated_require($file, array('core' => $core, 'document' => $core->document, 'page' => $page, 'patron' => $patron, 'module' => $module));

					$rc = ob_get_clean();
				}
				else if (preg_match('#\.html$#', $file))
				{
					$rc = Patron(file_get_contents($file), $bind, array('file' => $file));
				}
				else
				{
					throw new Exception('Unable to process file %file, unsupported type', array('%file' => $file));
				}
			}
			catch (Exception $e)
			{
				if ($scope)
				{
					I18n::pop_scope($scope);
				}

				throw $e;
			}

			if ($scope)
			{
				I18n::pop_scope($scope);
			}
		}
		else if (isset($view['module']) && isset($view['block']))
		{
			$rc = $core->modules[$view['module']]->getBlock($view['block']);
		}
		else
		{
			throw new Exception('Unable to render view %view. The description of the view is invalid: !descriptor', array('%view' => $id, '!descriptor' => $view));
		}

		return $rc;
	}

	protected function render_outer_html()
	{
		$rc = $this->render_inner_html();
		$page = $this->page;
		$id = $this->id;

		if (preg_match('#\.html$#', $page->template))
		{
			$class = 'view';

			if (strpos($id, '/'))
			{
				list($constructor, $type) = explode('/', $id, 2);

				$class .= ' constructor-' . wd_normalize($constructor) . ' ' . $type;
			}

			$rc = '<div id="view-' . wd_normalize($id) . '" class="' . $class . '">' . $rc . '</div>';
		}

		return $rc;
	}

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