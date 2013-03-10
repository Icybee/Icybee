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
const VERSION = 'dev-master (2013-02-21)';

/**
 * Root path for the Icybee package.
 *
 * @var string
 */
define('Icybee\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * Assets path for the Icybee package.
 *
 * @var string
 */
define('Icybee\ASSETS', ROOT . 'assets' . DIRECTORY_SEPARATOR);

/*
 * Helpers
 */
require_once ROOT . 'lib/helpers.php';
require_once ROOT . 'lib/helpers-compat.php';