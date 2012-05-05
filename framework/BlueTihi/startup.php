<?php

namespace BlueTihi;

const VERSION = '0.0.1 (2012-04-14)';

/**
 * The ROOT directory of the BlueTihi framework.
 *
 * @var string
 */
defined('BlueTihi\ROOT') or define('BlueTihi\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/*
 * helpers
 */
require_once ROOT . 'lib/helpers.php';