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

use ICanBoogie;
use ICanBoogie\Debug;
use ICanBoogie\Exception;
use ICanBoogie\Hooks;
use ICanBoogie\Module;
use ICanBoogie\Operation;
use ICanBoogie\Route;
use BrickRouge\Document;

/**
 * The following properties are injected by the "system.registry" module.
 *
 * @property ICanBoogie\ActiveRecord\Model\System\Registry $registry Global registry object.
 *
 * The following properties are injected by the "sites" module.
 *
 * @property int $site_id Identifier of the current site.
 * @property ICanBoogie\ActiveRecord\Site $site Current site object.
 *
 * The following properties are injected by the "users" module.
 *
 * @property ICanBoogie\ActiveRecord\User $user Current user object (might be a visitor).
 * @property int $user_id Identifier of the current user ("0" for visitors).
 */
class Core extends ICanBoogie\Core
{
	/**
	 * Returns the unique core instance.
	 *
	 * @param array $options
	 * @param string $class
	 *
	 * @return Core The core object.
	 */
	public static function get_singleton(array $options=array())
	{
		$config = array();
		$locale = array();

		$protected_path = ICanBoogie\DOCUMENT_ROOT . 'protected' . DIRECTORY_SEPARATOR . 'all' . DIRECTORY_SEPARATOR;

		if (file_exists($protected_path . 'config'))
		{
			$config[] = $protected_path;
		}

		if (file_exists($protected_path . 'locale'))
		{
			$locale[] = $protected_path;
		}

		return parent::get_singleton
		(
			wd_array_merge_recursive
			(
				$options, array
				(
					'paths' => array
					(
						'config' => $config,
						'locale' => $locale
					)
				)
			)
		);
	}

	/**
	 * Override the method to provide a nicer exception presentation.
	 *
	 * @param \Exception $exception
	 */
	public static function exception_handler(\Exception $exception)
	{
		global $core;

		$code = $exception->getCode() ?: 500;
		$message = $exception->getMessage();
		$class = get_class($exception); // The $class variable is required by the template

		if (!headers_sent())
		{
			$normalized_message = strip_tags($message);
			$normalized_message = str_replace(array("\r\n", "\n"), ' ', $normalized_message);
			$normalized_message = mb_convert_encoding($normalized_message, ICanBoogie\CHARSET, 'ASCII');

			if (strlen($normalized_message) > 32)
			{
				$normalized_message = mb_substr($normalized_message, 0, 29) . '...';
			}

			header('HTTP/1.0 ' . $code . ' ' . $class . ': ' . $normalized_message);
		}

		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
		{
			$rc = json_encode(array('rc' => null, 'errors' => array('_base' => $message)));

			header('Content-Type: application/json');
			header('Content-Length: ' . strlen($rc));

			exit($rc);
		}

		$formated_exception = Debug::format_alert($exception);

		if (!($exception instanceof Exception\HTTP))
		{
			Debug::report($formated_exception);
		}

		if (!headers_sent())
		{
			$site = isset($core->site) ? $core->site : null;
			$version = preg_replace('#\s\([^\)]+\)#', '', VERSION);
			$css = array
			(
				Document::resolve_url(\BrickRouge\ASSETS . 'brickrouge.css'),
				Document::resolve_url(ASSETS . 'css/base.css')
			);

			$formated_exception = require(__DIR__ . '/exception.template');
		}

		exit($formated_exception);
	}

	/**
	 * Override the method to provide our own accessor.
	 *
	 * @see ICanBoogie.Core::__get_modules()
	 *
	 * @return Accessor\Modules
	 */
	protected function __get_modules()
	{
		$config = $this->config;

		return new Accessor\Modules($config['modules'], $config['cache modules'], $config['repository.cache'] . '/core');
	}

	/**
	 * Override the method to select the site corresponding to the URL and set the appropriate
	 * language and timezone.
	 *
	 * @see ICanBoogie.Core::run_context()
	 */
	protected function run_context()
	{
		$this->site = $site = Hooks\Sites::find_by_request($this->request);
		$this->language = $site->language;

		if ($site->timezone)
		{
			$this->timezone = $site->timezone;
		}

		$path = $this->site->path;

		if ($path)
		{
			/*
			 * Contextualize the API string by prefixing it with the current site path.
			 */
			Route::$contextualize_callback = function ($str) use ($path)
			{
				return $path . $str;
			};

			/*
			 * Decontextualize the API string by removing the current site path.
			 */
			Route::$decontextualize_callback = function ($str) use ($path)
			{
				if (strpos($str, $path . '/') === 0)
				{
					$str = substr($str, strlen($path));
				}

				return $str;
			};
		}

		parent::run_context();
	}
}