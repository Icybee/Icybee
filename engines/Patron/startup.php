<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Patron;

/**
 * Version string of the engine.
 *
 * @var string
 */
const VERSION = '2-0-dev (2012-01-06)';

/**
 * The ROOT directory of the engine.
 *
 * @var string
 */
defined('Patron\ROOT') or define('Patron\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * The charset used by the application. Defaults to "utf-8".
 *
 * @var string
 */
defined('Patron\CHARSET') or define('Patron\CHARSET', 'utf-8');

/*
 * helpers
 */
require_once ROOT . 'lib/helpers.php';