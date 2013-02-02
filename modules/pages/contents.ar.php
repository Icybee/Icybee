<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Pages;

/**
 * A content of a page.
 *
 * @property-read mixed $rendered The rendered version of the content.
 */
class Content extends \ICanBoogie\ActiveRecord
{
	/**
	 * The identifier of the page the content belongs to.
	 *
	 * @var int
	 */
	public $pageid;

	/**
	 * The identifier of the content.
	 *
	 * @var string
	 */
	public $contentid;

	/**
	 * The content.
	 *
	 * @var string
	 */
	public $content;

	/**
	 * The editor name used to edit and render the content.
	 *
	 * @var string
	 */
	public $editor;

	/**
	 * The rendered version of the content.
	 *
	 * @var string|object
	 */
	private $rendered;

	/**
	 * Returns the rendered contents.
	 *
	 * @return mixed
	 */
	protected function volatile_get_rendered()
	{
		return $this->render();
	}

	/**
	 * Renders the content as a string or an object.
	 *
	 * Exceptions thrown during the rendering are caught. The message of the exception is used
	 * as rendered content and the exception is rethrown.
	 *
	 * @throws Exception
	 *
	 * @return string|object The rendered content.
	 */
	public function render()
	{
		if ($this->rendered !== null)
		{
			return $this->rendered;
		}

		/*
		 * TODO-20120905: Ok we handle HTTPError, but what about RecordNotFound and
		 * others ?
		 */
		try
		{
			$editor = \ICanBoogie\Core::get()->editors[$this->editor];
			$rendered = $editor->render($editor->unserialize($this->content));
		}
		catch (\ICanBoogie\HTTP\HTTPError $e)
		{
			$rendered = $e->getMessage();
		}
		/*
		catch (\Exception $e)
		{
			$rendered = \ICanBoogie\Debug::format_alert($e);
		}
		*/

		$this->rendered = $rendered;

		return $rendered;
	}

	public function __toString()
	{
		try
		{
			return (string) $this->render();
		}
		catch (\Exception $e)
		{
			return \ICanBoogie\Debug::format_alert($e);
		}
	}
}

/*
 * Events
 */
namespace Icybee\Modules\Pages\Content;

/**
 * Event class for the `Icybee\Modules\Pages\Content::render` event.
 */
class RenderEvent extends \ICanBoogie\Event
{
	public function __construct(\Icybee\Modules\Pages\Content $target)
	{
		parent::__construct($target, 'render');
	}
}