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
 */
define('Icybee\DIR', __DIR__ . DIRECTORY_SEPARATOR);

/**
 * Assets path for the Icybee package.
 */
define('Icybee\ASSETS', DIR . 'assets' . DIRECTORY_SEPARATOR);

/**
 * Operation save mode constants.
 */
const OPERATION_SAVE_MODE = '_operation_save_mode';
const OPERATION_SAVE_MODE_CONTINUE = 'continue';
const OPERATION_SAVE_MODE_LIST = 'list';
const OPERATION_SAVE_MODE_NEW = 'new';
const OPERATION_SAVE_MODE_DISPLAY = 'display';

/**
 * Patches some helpers and boots ICanBoogie.
 *
 * The following helpers are patched:
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
 * $app = Icybee\boot();
 * $app();
 * </pre>
 *
 * @return \ICanBoogie\Application
 */
function boot()
{
	/* @var $app \ICanBoogie\Application */

	$app = null;

	\Brickrouge\Helpers::patch('t', function($native, array $args = [], array $options = []) use (&$app) {

		return $app->translate($native, $args, $options);

	});

	\Brickrouge\Helpers::patch('render_exception', 'ICanBoogie\Debug::format_alert');
	\Brickrouge\Helpers::patch('get_document', function() use(&$app) { return $app->document; });

	return $app = \IcanBoogie\boot();
}

/*
 * Helpers
 */
require_once DIR . 'lib/helpers.php';
