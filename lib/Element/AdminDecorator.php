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

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\Debug;

use Brickrouge\Alert;

/**
 * @property-read \ICanBoogie\HTTP\Request $request
 * @property-read \ICanBoogie\Session $session
 * @property-read \Icybee\Modules\Sites\Site $site
 * @property-read int $site_id
 * @property-read \Icybee\Modules\Users\User $user
 */
class AdminDecorator
{
	use AccessorTrait;

	protected $component;
	private $app;

	protected function get_request()
	{
		return $this->app->request;
	}

	protected function get_session()
	{
		return $this->app->session;
	}

	protected function get_site()
	{
		return $this->app->site;
	}

	protected function get_site_id()
	{
		return $this->app->site_id;
	}

	protected function get_user()
	{
		return $this->app->user;
	}

	public function __construct($component)
	{
		$this->component = $component;
		$this->app = \ICanBoogie\app();
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
		$app = $this->app;
		$this->add_assets($app->document);

		$session = $this->session;
		$site_id = $this->site_id;

		if ($session['last_site_id'])
		{
			if ($session['last_site_id'] != $site_id)
			{
				$session['last_site_id'] = $site_id;

				if (!$this->request['ssc'])
				{
					$this->changed_site = true;
				}
			}
		}
		else
		{
			$session['last_site_id'] = $site_id;
		}

		if ($this->changed_site)
		{
			\ICanBoogie\log_info("Vous avez changé de site.");
		}

		#

		$component = (string) $this->component;
		$actionbar = new \Icybee\Element\ActionBar;
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
		$document->css->add("http://v4-alpha.getbootstrap.com/dist/css/bootstrap.min.css", -250);
		$document->css->add(\Brickrouge\ASSETS . 'brickrouge.css', -250);
		$document->css->add(\Icybee\ASSETS . 'icybee.css', -240);
		$document->css->add(\Icybee\ASSETS . 'admin.css', -200);
		$document->css->add(\Icybee\ASSETS . 'admin-more.css', -200);

		$document->js->add(\Icybee\ASSETS . 'page.js', -200);
		$document->js->add(\Brickrouge\ASSETS . 'brickrouge.js', -190);
		$document->js->add(\Icybee\ASSETS . 'admin.js', -180);
	}

	protected function render_navigation()
	{
		$user = $this->user;

		if ($user->is_guest || $user instanceof \Icybee\Modules\Members\Member)
		{
			$this->title = 'Icybee';

			return null;
		}

		return new \Icybee\Element\Navigation([ 'id' => 'navigation' ]);
	}

	protected function render_shortcuts()
	{
		$user = $this->user;
		$site = $this->site;

		if ($user->is_guest)
		{
			$this->page_title = 'Icybee';

			$html = '<a href="' . $site->url . '" class="home">' . \ICanBoogie\escape($site->title) . '</a> <i class="icon-home icon-white"></i>';
		}
		else
		{
			$html = new \Icybee\Element\SiteMenu([ 'class' => 'pull-left' ])
			. new \Icybee\Element\UserMenu([ 'class' => 'pull-right' ]);
		}

		return '<div id="quick">' . $html . '</div>';
	}

	protected function render_alerts()
	{
		static $mapping = [

			'success' => Alert::CONTEXT_SUCCESS,
			'info' => Alert::CONTEXT_INFO,
			'error' => Alert::CONTEXT_DANGER

		];

		$html = '';

		if (Debug::$mode == Debug::MODE_DEV || $this->user->is_admin)
		{
			$mapping['debug'] = Alert::CONTEXT_WARNING;
		}

		foreach ($mapping as $type => $context)
		{
			$html .= new Alert(Debug::fetch_messages($type), [

				Alert::CONTEXT => $context,
				Alert::DISMISSIBLE => true

			]);
		}

		return $html;
	}
}
