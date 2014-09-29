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

/**
 * Path to the directory of the Icybee package.
 *
 * The path includes a trailing directory separator.
 *
 * @var string
 */
define('Icybee\DIR', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * Assets path for the Icybee package.
 *
 * @var string
 */
define('Icybee\ASSETS', DIR . 'assets' . DIRECTORY_SEPARATOR);

/**
 * Operation save mode constants.
 *
 * @var string
 */
const OPERATION_SAVE_MODE = '_operation_save_mode';
const OPERATION_SAVE_MODE_CONTINUE = 'continue';
const OPERATION_SAVE_MODE_LIST = 'list';
const OPERATION_SAVE_MODE_NEW = 'new';
const OPERATION_SAVE_MODE_DISPLAY = 'display';

/**
 * Starts Icybee.
 *
 * The function instantiates a {@link Core} instance with the ICanBoogie's auto-config and patches
 * the following helpers:
 *
 * - ICanBoogie\I18n\get_cldr
 * - Brickrouge\t
 * - Brickrouge\render_exception
 * - Brickrouge\get_document
 * - Brickrouge\check_session
 *
 * <pre>
 * <?php
 *
 * // index.php
 *
 * $core = Icybee\start();
 * $request = $core();
 * $response = $request();
 * $response();
 * </pre>
 *
 * @return \ICanBoogie\Core
 */
function start()
{
	/**
	 * The core instance is the heart of the ICanBoogie framework.
	 *
	 * @var Core
	 */
	$core = new \ICanBoogie\Core( \ICanBoogie\get_autoconfig() );

	\ICanBoogie\I18n\Helpers::patch('get_cldr', function() use($core) { return $core->cldr; });

	\Brickrouge\Helpers::patch('t', 'ICanBoogie\I18n\t');
	\Brickrouge\Helpers::patch('render_exception', 'ICanBoogie\Debug::format_alert');
	\Brickrouge\Helpers::patch('get_document', function() use($core) { return $core->document; });
	\Brickrouge\Helpers::patch('check_session', function() use($core) { return $core->session; });

	return $core;
}

/*
 * Helpers
 */
require_once DIR . 'lib/helpers.php';
require_once DIR . 'lib/helpers-compat.php';