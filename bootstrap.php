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
 * Version string for the Icybee package.
 *
 * @var string
 */
const VERSION = 'dev-master (2013-04-05)';

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

/*
 * Helpers
 */
require_once DIR . 'lib/helpers.php';
require_once DIR . 'lib/helpers-compat.php';