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

use ICanBoogie\ActiveRecord\User;
use ICanBoogie\ActiveRecord\Users\Role;
use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\Alert;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;

class Document extends \Brickrouge\Document
{
	public $on_setup = false;
	protected $changed_site;

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

	public function __toString()
	{
		global $core;

		try
		{
			$body = $this->getBody();
			$head = $this->getHead();

			$rc  = '<!DOCTYPE html>' . PHP_EOL;
			$rc .= '<html lang="' . $core->language . '" data-api-base="' . \ICanBoogie\escape($core->site->path) . '">' . PHP_EOL;

			$rc .= $head;
			$rc .= $body;

			$rc .= '</html>';
		}
		catch (\Exception $e)
		{
			$rc = \ICanBoogie\Debug::format_alert($e);
		}

		return $rc;
	}

	protected function getHead()
	{
		global $core;

		$site_title = $core->site->title;

		$this->title = 'Icybee (' . $site_title . ')';

		$rc  = '<head>' . PHP_EOL;
		$rc .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . PHP_EOL;

		$rc .= '<title>' . $this->title . '</title>' . PHP_EOL;

		$rc .= $this->css;

		$rc .= '</head>' . PHP_EOL;

		return $rc;
	}

	protected function getBody()
	{
		global $core;

		$user = $core->user;

		$contents = $this->getBlock('contents');

		$shortcuts = $this->get_block_shortcuts();
		$navigation = $this->getNavigation();

		$actionbar = new \Icybee\Admin\Element\Actionbar;

		$alert_changed_site = null;

		if ($this->changed_site)
		{
			\ICanBoogie\log_info("Vous avez changé de site.");
		}

		$body_class = '';

		if ($user->is_guest)
		{
			$body_class = ' page-slug-authenticate';
		}

		$js = $this->js;

		$alert_success = new Alert(Debug::fetch_messages('done'), array(Alert::CONTEXT => 'success'));
		$alert_info = new Alert(Debug::fetch_messages('info'), array(Alert::CONTEXT => 'info'));
		$alert_error = new Alert(Debug::fetch_messages('error'), array(Alert::CONTEXT => 'error'));
		$alert_debug = new Alert(Debug::fetch_messages('debug'), array(Alert::CONTEXT => 'debug'));

		$alert = trim($alert_success . $alert_error . $alert_info . $alert_debug);

		return <<<EOT
<body class="admin{$body_class}">
	<div id="body-wrapper">

		<div id="quick">$shortcuts</div>
		$navigation
		$actionbar

		<div id="contents">
			<div class="alert-wrapper">$alert</div>
			$contents
			$alert_debug
		</div>
	</div>

	$alert_changed_site
	$js
</body>
EOT;

		return $rc;
	}

	protected function get_block_shortcuts()
	{
		global $core;

		$user = $core->user;
		$site = $core->site;

		if ($user->is_guest)
		{
			$this->page_title = 'Icybee';

			return '←&nbsp;<a href="' . $site->url . '" class="home">' . t($site->title) . '</a>';
		}

		$site_title = wd_entities($site->admin_title);

		if (!$site_title)
		{
			$site_title = wd_entities($site->title) . '<span class="language">:' . $site->language . '</span>';
		}

		$options = array();

		try
		{
			$query = $core->models['sites']->order('admin_title, title');

			$restricted_sites = $user->restricted_sites_ids;

			if ($restricted_sites)
			{
				$query->where(array('siteid' => $restricted_sites));
			}

			$sites = $query->all;

			if (count($sites) > 1)
			{
				$path = Route::decontextualize($core->request->path);

				foreach ($sites as $asite)
				{
					$title = $asite->admin_title;

					if (!$title)
					{
						$title = new Element('span', array(Element::INNER_HTML => $asite->title . '<span class="language">:' . $asite->language . '</span>'));
					}

					$options[$asite->siteid] = new A($title, $asite->url . $path/* . '?ssc=1'*/);
				}
			}
		}
		catch (\Exception $e) { /**/ }

		if ($core->session->last_site_id)
		{
			if ($core->session->last_site_id != $core->site_id)
			{
				$core->session->last_site_id = $core->site_id;

				if (empty($_GET['ssc']))
				{
					$this->changed_site = true;
				}
			}
		}
		else
		{
			$core->session->last_site_id = $core->site_id;
		}

		$menu = null;
		$menu_toggler = null;

		if ($options)
		{
			$menu = new DropdownMenu(array
			(
				DropdownMenu::OPTIONS => $options,

				'value' => $site->siteid
			));

			$menu_toggler = <<<EOT
<span class="dropdown-toggle" data-toggle="dropdown"><i class="icon-home icon-white"></i> <span class="caret"></span></span>
EOT;
		}
		else
		{
			$menu_toggler = <<<EOT
<i class="icon-home icon-white"></i>
EOT;
		}

		$rc = <<<EOT
<div class="btn-group">
	<a href="$site->url">$site->title</a>
	$menu_toggler
	$menu
</div>
EOT;


// 		$rc  = '<div class="sites"><span style="float: left">←&nbsp;</span>' . $sites_list . '</div>';
		$rc .= $this->render_shortcut__user();

		return $rc;
	}

	protected function render_shortcut__user()
	{
		global $core;

		$user = $core->user;
		$site = $core->site;

		$rc = '<div class="pull-right">';

		$roles = '';

		if ($user->is_admin)
		{
			$roles = 'Admin';
		}
		else if ($user->has_permission(Module::PERMISSION_ADMINISTER, 'users.roles'))
		{
			foreach ($user->roles as $role)
			{
				$roles .= ', <a href="' . $site->path . '/admin/users.roles/' . $role->rid . '/edit">' . $role->name . '</a>';
			}

			$roles = substr($roles, 2);
		}
		else
		{
			$n = count($user->roles);

			foreach ($user->roles as $role)
			{
				if ($n > 1 && $role->rid == Role::USER_RID)
				{
					continue;
				}

				$roles .= ', ' . $role->name;
			}

			$roles = substr($roles, 2);
		}

		$username = new A($user->name, Route::contextualize('/admin/profile'));

		$options = array
		(
			Route::contextualize('/admin/profile') => 'Profile',
			false,
			Operation::encode('users/logout') => 'Logout'
		);

		array_walk
		(
			$options, function(&$v, $k)
			{
				if (!is_string($v))
				{
					return;
				}

				$v = new A($v, $k);
			}
		);

		$menu = new DropdownMenu
		(
			array
			(
				DropdownMenu::OPTIONS => $options,

				'value' => $core->request->path
			)
		);

		$rc .= <<<EOT
$username
<span class="btn-group">
	<span class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> <span class="caret"></span></span>
	$menu
</span>
EOT;

		$rc .= '</div>';

		return $rc;
	}

	protected function getNavigation()
	{
		global $core;

		$user = $core->user;

		if ($user->is_guest || $user instanceof Users\Member)
		{
			$this->title = 'Icybee';

			return;
		}

		return new Admin\Element\Navigation(array('id' => 'navigation'));
	}

	/*
	protected function getFooter()
	{
		$phrases = array
		(
			'Thank you for creating with :link',
			'Light and sweet edition with :link',
			':link is super green'
		);

		$phrase = $phrases[date('md') % count($phrases)];
		$link = '<a href="http://www.wdpublisher.com/" target="_blank">Icybee</a>';

		$rc  = '<div id="footer" class="-sticky">';
		$rc .= '<p>';
		$rc .= t($phrase, array(':link' => $link));
		$rc .= ' › <a href="http://www.wdpublisher.com/docs/" target="_blank">Documentation</a>';// | <a href="http://www.wdpublisher.com/feedback/">Feedback</a>';
		$rc .= '</p>';
		$rc .= '<p class="version">v' . preg_replace('#\s*\(.*#', '', VERSION) . '</p>';
		$rc .= '<div class="clear"></div>';
		$rc .= '</div>';

		return $rc;
	}
	*/

	/*
	**

	BLOCKS

	**
	*/

	var $blocks = array();

	function addToBlock($contents, $blockname)
	{
		if (!is_string($contents))
		{
			throw new Exception('Wrong type for block contents');
		}

		$this->blocks[$blockname][] = $contents;
	}

	function getBlock($name)
	{
		if (empty($this->blocks[$name]))
		{
			return;
		}

		$rc = '';

		foreach ($this->blocks[$name] as $contents)
		{
			$rc .= $contents;
		}

		return $rc;
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

	public static function markup_document_title(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$document = $core->document;

		$title = isset($document->title) ? $document->title : null;

		Event::fire('render_title:before', array('title' => &$title), $document);

		$rc = '<title>' . wd_entities($title) . '</title>';

		Event::fire('render_title', array('rc' => &$rc), $document);

		return $rc;
	}

	static public function markup_document_metas(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$document = $core->document;

		$http_equiv = array
		(
			'Content-Type' => 'text/html; charset=' . \ICanBoogie\CHARSET
		);

		$metas = array
		(

		);

		Event::fire('render_metas:before', array('http_equiv' => &$http_equiv, 'metas' => &$metas), $document);

		$rc = '';

		foreach ($http_equiv as $name => $content)
		{
			$rc .= '<meta http-equiv="' . \ICanBoogie\escape($name) . '" content="' . \ICanBoogie\escape($content) . '" />' . PHP_EOL;
		}

		foreach ($metas as $name => $content)
		{
			$rc .= '<meta name="' . \ICanBoogie\escape($name) . '" content="' . \ICanBoogie\escape($content) . '" />' . PHP_EOL;
		}

		Event::fire('render_metas', array('rc' => &$rc), $document);

		return $rc;
	}
}