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

use ICanBoogie\ActiveRecord;
use ICanBoogie\Debug;

/**
 * @property array $translations Translations for the site.
 *
 * @method ICanBoogie\ActiveRecord\Page|null resolve_view_target() resolve_view_target(string $view)
 * Return the page on which the view is displayed, or null if the view is not displayed.
 *
 * This method is injected by the "pages" module.
 *
 * @method string resolve_view_url() resolve_view_url(string $view) Return the URL of the view.
 *
 * This method is injected by the "pages" module.
 */
class Site extends ActiveRecord
{
	const BASE = '/protected/';

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

	public function __construct($model)
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

	protected function __get_url()
	{
		$parts = explode('.', $_SERVER['HTTP_HOST']);
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
	protected function __get_templates()
	{
		$templates = array();
		$root = $_SERVER['DOCUMENT_ROOT'];

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

	protected function __get_partial_templates()
	{
		$templates = array();
		$root = $_SERVER['DOCUMENT_ROOT'];

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

	protected function __get_native()
	{
		$native_id = $this->nativeid;

		return $native_id ? $this->_model[$native_id] : $this;
	}

	/**
	 * Returns the translations for this site.
	 *
	 * @return array
	 */
	protected function __get_translations()
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
}