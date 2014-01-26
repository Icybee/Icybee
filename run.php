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

global $core;

Core::add_path(DIR);

/**
 * The core instance is the heart of the ICanBoogie framework.
 *
 * @var Core
 */
$core = new Core
(
	array
	(
		'modules paths' => array
		(
			DIR . 'modules' . DIRECTORY_SEPARATOR,
			\ICanBoogie\Modules\DIR
		)
	)
);

\ICanBoogie\I18n\Helpers::patch('get_cldr', function() use($core) { return $core->cldr; });

\Brickrouge\Helpers::patch('t', 'ICanBoogie\I18n\t');
\Brickrouge\Helpers::patch('render_exception', 'ICanBoogie\Debug::format_alert');
\Brickrouge\Helpers::patch('get_document', function() use($core) { return $core->document; });
\Brickrouge\Helpers::patch('check_session', function() use($core) { return $core->session; });

// \ICanBoogie\log_time('core created');

$core();

// \ICanBoogie\log_time('core is running');