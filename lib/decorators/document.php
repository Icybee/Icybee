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
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\Alert;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;

class DocumentDecorator
{
	protected $component;

	public function __construct($component)
	{
		$this->component = $component;
		$this->body = new Element('body');
	}

	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (\Exception $e)
		{
			return Debug::format_alert($e);
		}
	}

	public function render()
	{
		global $core;

		$component = (string) $this->component;
		$document = $core->document;
		$title = \Brickrouge\escape('Icybee');
		$favicon = Document::resolve_url(\Icybee\ASSETS . 'favicon.png');

		$body = $this->body;
		$body[Element::INNER_HTML] = $component . PHP_EOL . PHP_EOL . $document->js;

		$api_base = '';

		if (isset($core->site))
		{
			$api_base = $core->site->path;
		}

		return <<<EOT
<!DOCTYPE html>
<html lang="{$core->language}" data-api-base="{$api_base}">
<head>
<meta charset="utf-8" />
<title>$title</title>
<link rel="shortcut icon" type="image/png" href="{$favicon}" />
{$document->css}
</head>
{$body}
</html>
EOT;
	}
}