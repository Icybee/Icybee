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
use ICanBoogie\Exception;
use ICanBoogie\HTTP\HTTPError;
use ICanBoogie\Module;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\Document;

/**
 * The following properties are injected by the "registry" module.
 *
 * @property Icybee\Modules\Editor\Collection $editors Editors collection. The getter is
 * injected by the "Editors" module.
 *
 * @property ICanBoogie\ActiveRecord\Model\System\Registry $registry Global registry object.
 *
 * The following properties are injected by the "sites" module.
 *
 * @property int $site_id Identifier of the current site.
 * @property Icybee\Modules\Sites\Site $site Current site object.
 *
 * The following properties are injected by the "users" module.
 *
 * @property Icybee\Modules\Users\User $user Current user object (might be a visitor).
 * @property int $user_id Identifier of the current user ("0" for visitors).
 */
class Core extends \ICanBoogie\Core
{
	/**
	 * Adds website config and locale paths.
	 *
	 * @param array $options
	 *
	 * @see \ICanBoogie\Core::__construct
	 */
	public function __construct(array $options=array())
	{
		$config = array();
		$locale = array();

		$protected_path = \ICanBoogie\DOCUMENT_ROOT . 'protected' . DIRECTORY_SEPARATOR . 'all' . DIRECTORY_SEPARATOR;

		if (file_exists($protected_path . 'config'))
		{
			$config[] = $protected_path;
		}

		if (file_exists($protected_path . 'locale'))
		{
			$locale[] = $protected_path;
		}

		return parent::__construct
		(
			\ICanBoogie\array_merge_recursive
			(
				$options, array
				(
					'config paths' => $config,
					'locale paths' => $locale
				)
			)
		);
	}

	/**
	 * Override the method to provide a nicer exception presentation.
	 *
	 * @param \Exception $exception
	 */
	static public function exception_handler(\Exception $exception)
	{
		global $core;

		$code = $exception->getCode() ?: 500;
		$message = $exception->getMessage();
		$class = get_class($exception); // The $class variable is required by the template

		if (!headers_sent())
		{
			$normalized_message = strip_tags($message);
			$normalized_message = str_replace(array("\r\n", "\n"), ' ', $normalized_message);
			$normalized_message = mb_convert_encoding($normalized_message, \ICanBoogie\CHARSET, 'ASCII');

			if (strlen($normalized_message) > 32)
			{
				$normalized_message = mb_substr($normalized_message, 0, 29) . '...';
			}

			header('HTTP/1.0 ' . $code . ' ' . $class . ': ' . $normalized_message);
			header('X-ICanBoogie-Exception: ' . \ICanBoogie\strip_root($exception->getFile()) . '@' . $exception->getLine());
		}

		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
		{
			$rc = json_encode(array('rc' => null, 'errors' => array('_base' => $message)));

			header('Content-Type: application/json');
			header('Content-Length: ' . strlen($rc));

			exit($rc);
		}

		$formated_exception = Debug::format_alert($exception);
		$reported = false;

		if (!($exception instanceof HTTPError))
		{
			Debug::report($formated_exception);

			$reported = true;
		}

		if (!headers_sent())
		{
			$site = isset($core->site) ? $core->site : null;
			$version = preg_replace('#\s\([^\)]+\)#', '', VERSION);

			if (class_exists('Brickrouge\Document'))
			{
				$css = array
				(
					Document::resolve_url(\Brickrouge\ASSETS . 'brickrouge.css'),
					Document::resolve_url(ASSETS . 'admin.css'),
					Document::resolve_url(ASSETS . 'admin-more.css')
				);
			}
			else
			{
				$css = array();
			}

			$formated_exception = require(__DIR__ . '/exception.tpl.php');
		}

		exit($formated_exception);
	}

	/**
	 * Override the method to provide our own accessor.
	 *
	 * @see \ICanBoogie.Core::get_modules()
	 *
	 * @return Accessor\Modules
	 */
	protected function get_modules()
	{
		$config = $this->config;

		return new Modules($config['modules paths'], $config['cache modules'] ? $this->vars : null);
	}
}