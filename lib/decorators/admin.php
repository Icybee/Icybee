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
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\Alert;
use Brickrouge\DropdownMenu;

class AdminDecorator
{
	protected $component;

	public function __construct($component)
	{
		$this->component = $component;
	}

	protected $changed_site = false;

	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (\Exception $e)
		{
			return Debug::format_alert($e);
		}
	}

	public function render()
	{
		global $core;

		$this->add_assets($core->document);

		#

		if ($core->session->last_site_id)
		{
			if ($core->session->last_site_id != $core->site_id)
			{
				$core->session->last_site_id = $core->site_id;

				if (!$core->request['ssc'])
				{
					$this->changed_site = true;
				}
			}
		}
		else
		{
			$core->session->last_site_id = $core->site_id;
		}

		if ($this->changed_site)
		{
			\ICanBoogie\log_info("Vous avez changé de site.");
		}

		#

		$component = (string) $this->component;
		$actionbar = new \Icybee\Element\Actionbar;
		$shortcuts = $this->render_shortcuts();
		$navigation = $this->render_navigation();

		$alert = $this->render_alerts();

		return <<<EOT
<div id="body-wrapper">
	$shortcuts
	$navigation
	$actionbar

	<div id="contents">
		<div class="alert-wrapper">$alert</div>
		$component
	</div>
</div>
EOT;
	}

	protected function add_assets(\Brickrouge\Document $document)
	{
		$document->css->add(\Brickrouge\ASSETS . 'brickrouge.css', -250);
		$document->css->add(\Icybee\ASSETS . 'icybee.css', -240);
		$document->css->add(\Icybee\ASSETS . 'admin.css', -200);
		$document->css->add(\Icybee\ASSETS . 'admin-more.css', -200);

		$document->js->add(\Icybee\ASSETS . 'mootools.js', -200);
		$document->js->add(\ICanBoogie\ASSETS . 'icanboogie.js', -190);
		$document->js->add(\Brickrouge\ASSETS . 'brickrouge.js', -190);
		$document->js->add(\Icybee\ASSETS . 'admin.js', -180);
	}

	protected function render_navigation()
	{
		global $core;

		$user = $core->user;

		if ($user->is_guest || $user instanceof \Icybee\Modules\Members\Member)
		{
			$this->title = 'Icybee';

			return;
		}

		return new \Icybee\Element\Navigation(array('id' => 'navigation'));
	}

	protected function render_shortcuts()
	{
		global $core;

		$user = $core->user;
		$site = $core->site;

		if ($user->is_guest)
		{
			$this->page_title = 'Icybee';

			$html = '<a href="' . $site->url . '" class="home">' . \ICanBoogie\escape($site->title) . '</a> <i class="icon-home icon-white"></i>';
		}
		else
		{
			$html = new \Icybee\Element\SiteMenu(array('class' => 'pull-left'))
			. new \Icybee\Element\UserMenu(array('class' => 'pull-right'));
		}

		return '<div id="quick">' . $html . '</div>';
	}

	protected function render_alerts()
	{
		global $core;

		$html = '';
		$types = array('success', 'info', 'error');

		if (Debug::$mode == Debug::MODE_DEV || $core->user->is_admin)
		{
			$types[] = 'debug';
		}

		foreach ($types as $type)
		{
			$html .= new Alert(Debug::fetch_messages($type), array(Alert::CONTEXT => $type));
		}

		return $html;
	}
}