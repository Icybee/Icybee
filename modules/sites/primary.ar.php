<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

use ICanBoogie\Modules\Sites\ServerName;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Debug;

/**
 * @property array $translations Translations for the site.
 *
 * @method Icybee\Modules\Pages\Page|null resolve_view_target() resolve_view_target(string $view)
 * Return the page on which the view is displayed, or null if the view is not displayed.
 *
 * This method is injected by the "pages" module.
 *
 * @method string resolve_view_url() resolve_view_url(string $view) Return the URL of the view.
 *
 * This method is injected by the "pages" module.
 */
class Site extends \ICanBoogie\ActiveRecord
{
	const SITEID = 'siteid';
	const SUBDOMAIN = 'subdomain';
	const DOMAIN = 'domain';
	const PATH = 'path';
	const TLD = 'tld';
	const TITLE = 'title';
	const ADMIN_TITLE = 'admin_title';
	const MODEL = 'model';
	const LANGUAGE = 'language';
	const TIMEZONE = 'tmezone';
	const NATIVEID = 'nativeid';
	const STATUS = 'status';
	const MODIFIED = 'modified';

	const BASE = '/protected/';

	const STATUS_OFFLINE = 0;
	const STATUS_ONLINE = 1;
	const STATUS_UNDER_MAINTENANCE = 2;
	const STATUS_DENIED_ACCESS = 3;

	public $siteid;
	public $subdomain;
	public $domain;
	public $path;
	public $tld;
	public $title;
	public $admin_title;
	public $model;
	public $language;
	public $timezone;
	public $nativeid;
	public $status;
	public $modified;

	public function __construct($model='sites')
	{
		if (empty($this->model))
		{
			$this->model = 'default';
		}

		parent::__construct($model);
	}

	public function __wakeup()
	{
		if (empty($this->_model_id))
		{
			$this->_model_id = 'sites';
		}

		unset($this->_model);
	}

	public function save()
	{
		global $core;

		#
		# before saving the site we clear the stes cache.
		#

		unset($core->vars['cached_sites']);

		return parent::save();
	}

	protected function get_url()
	{
		$parts = explode('.', $_SERVER['SERVER_NAME']);
		$parts = array_reverse($parts);

		if ($this->tld)
		{
			$parts[0] = $this->tld;
		}

		if ($this->domain)
		{
			$parts[1] = $this->domain;
		}

		if ($this->subdomain)
		{
			$parts[2] = $this->subdomain;
		}
		else if (empty($parts[2]))
		{
			//$parts[2] = 'www';
			unset($parts[2]);
		}

		return 'http://' . implode('.', array_reverse($parts)) . $this->path;
	}

	/**
	 * Returns the available templates for the site
	 */
	protected function get_templates()
	{
		$templates = array();
		$root = \ICanBoogie\DOCUMENT_ROOT;

		$models = array($this->model, 'all');

		foreach ($models as $model)
		{
			$path = self::BASE . $model . '/templates';

			if (!is_dir($root . $path))
			{
				continue;
			}

			$dh = opendir($root . $path);

			if (!$dh)
			{
				Debug::trigger('Unable to open directory %path', array('%path' => $path));

				continue;
			}

			while (($file = readdir($dh)) !== false)
			{
				if ($file{0} == '.')
				{
					continue;
				}

			 	$pos = strrpos($file, '.');

			 	if (!$pos)
			 	{
			 		continue;
			 	}

				$templates[$file] = $file;
			}

			closedir($dh);
		}

		sort($templates);

		return $templates;
	}

	protected function get_partial_templates()
	{
		$templates = array();
		$root = \ICanBoogie\DOCUMENT_ROOT;

		$models = array($this->model, 'all');

		foreach ($models as $model)
		{
			$path = self::BASE . $model . '/templates/partials';

			if (!is_dir($root . $path))
			{
				continue;
			}

			$dh = opendir($root . $path);

			if (!$dh)
			{
				Debug::trigger('Unable to open directory %path', array('%path' => $path));

				continue;
			}

			while (($file = readdir($dh)) !== false)
			{
				if ($file{0} == '.')
				{
					continue;
				}

			 	$pos = strrpos($file, '.');

			 	if (!$pos)
			 	{
			 		continue;
			 	}

			 	$id = preg_replace('#\.(php|html)$#', '', $file);
				$templates[$id] = $root . $path . '/' . $file;
			}

			closedir($dh);
		}

		return $templates;
	}

	/**
	 * Resolve the location of a relative path according site inheritence.
	 *
	 * @param string $relative The path to the file to locate.
	 */

	public function resolve_path($relative)
	{
		$root = $_SERVER['DOCUMENT_ROOT'];

		$try = self::BASE . $this->model . '/' . $relative;

		if (file_exists($root . $try))
		{
			return $try;
		}

		$try = self::BASE . 'all/' . $relative;

		if (file_exists($root . $try))
		{
			return $try;
		}
	}

	protected function get_native()
	{
		$native_id = $this->nativeid;

		return $native_id ? $this->_model[$native_id] : $this;
	}

	/**
	 * Returns the translations for this site.
	 *
	 * @return array
	 */
	protected function get_translations()
	{
		if ($this->nativeid)
		{
			return $this->_model->where('siteid != ? AND (siteid = ? OR nativeid = ?)', $this->siteid, $this->nativeid, $this->nativeid)->order('language')->all;
		}
		else
		{
			return $this->_model->where('nativeid = ?', $this->siteid)->order('language')->all;
		}
	}

	private $_server_name;

	protected function volatile_get_server_name()
	{
		if ($this->_server_name)
		{
			return $this->_server_name;
		}

		$parts = explode('.', $_SERVER['SERVER_NAME']);
		$parts = array_reverse($parts);

		if (count($parts) > 3)
		{
			$parts[2] = implode('.', array_reverse(array_slice($parts, 2)));
		}

		if ($this->tld)
		{
			$parts[0] = $this->tld;
		}

		if ($this->domain)
		{
			$parts[1] = $this->domain;
		}

		if ($this->subdomain)
		{
			$parts[2] = $this->subdomain;
		}

		return $this->_server_name = new ServerName(array($parts[2], $parts[1], $parts[0]));
	}

	protected function volatile_set_server_name($server_name)
	{
		if (!($server_name instanceof ServerName))
		{
			$server_name = new ServerName($server_name);
		}

		$this->subdomain = $server_name->subdomain;
		$this->domain = $server_name->domain;
		$this->tld = $server_name->tld;

		$this->_server_name = $server_name;
	}
}

namespace ICanBoogie\Modules\Sites;

class ServerName
{
	public $subdomain;
	public $domain;
	public $tld;

	public function __construct($server_name)
	{
		$subdomain = null;
		$domain = null;
		$tld = null;

		if (is_array($server_name))
		{
			list($subdomain, $domain, $tld) = $server_name;
		}
		else
		{
			$parts = explode('.', $server_name);

			if (count($parts) > 1)
			{
				$tld = array_pop($parts);
			}

			if (count($parts) > 1)
			{
				$domain = array_pop($parts);
			}

			$subdomain = implode('.', $parts);
		}

		$this->subdomain = $subdomain;
		$this->domain = $domain;
		$this->tld = $tld;
	}

	public function __toString()
	{
		$parts = array($this->subdomain, $this->domain, $this->tld);
		$parts = array_filter($parts);

		return implode('.', $parts);
	}
}