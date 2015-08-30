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

const DECORATE_WITH_BLOCK = 1;
const DECORATE_WITH_ADMIN = 2;
const DECORATE_WITH_DOCUMENT = 4;

/**
 * @var string $block_name
 * @var mixed $content
 * @var \ICanBoogie\Module $module
 * @var \ICanBoogie\View\View $view
 */

$decorate_flags = $view->controller->request['decorate_flags'];

if (preg_match('/Block$/', get_class($content)))
{
	$content = new BlockDecorator($content, $block_name, $module->id);
}

if ($decorate_flags === null)
{
	$content = new AdminDecorator($content);
	$content = new DocumentDecorator($content);
}

echo $content;
