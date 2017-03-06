<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element;

use function ICanBoogie\app;
use ICanBoogie\Debug;

use Brickrouge\Element;

class DocumentDecorator
{
	protected $component;
	private $app;

	public function __construct($component)
	{
		$this->component = $component;
		$this->app = app();
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
		$component = (string) $this->component;
		$app = $this->app;
		$document = $app->document;
		$title = \Brickrouge\escape('Icybee');
		$favicon = Document::resolve_url(\Icybee\ASSETS . 'favicon.png');

		$body = $this->body;
		$body[Element::INNER_HTML] = $component . PHP_EOL . PHP_EOL . $document->js;

		$api_base = '';

		try
		{
			$api_base = $app->site->path;
		}
		catch (\Exception $e)
		{
			#
			# Too bad we can't get the current site
			#
		}

		return <<<EOT
<!DOCTYPE html>
<html lang="{$app->language}" data-api-base="{$api_base}">
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
