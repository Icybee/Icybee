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

use Brickrouge\EmptyElementException;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;

use Brickrouge\Element;

class LanguagesElement extends Element
{
	static public function markup(array $args, \Patron\Engine $patron, $template)
	{
		if ($template)
		{
			throw new \Exception('Templates are currently not supported :(');
		}

		return new static();
	}

	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'div', array
			(
				'class' => 'btn-group i18n-languages'
			)
		);
	}

	protected function render_inner_html()
	{
		global $core;

		$page = $core->request->context->page;
		$translations_by_language = $this->collect();

		Event::fire // COMPAT
		(
			'alter.page.languages:before', array
			(
				'translations_by_languages' => &$translations_by_language
			),

			$page
		);

		new LanguagesElement\CollectEvent($this, array('languages' => &$translations_by_language));

		if (count($translations_by_language) == 1)
		{
			throw new EmptyElementException;
		}

		/*
		if ($template)
		{
			return $patron($template, $translations_by_language);
		}
		*/

		$page_language = $page->language;
		$links = array();

		foreach ($translations_by_language as $language => $record)
		{
			$link = new Element
			(
				'a', array
				(
					Element::INNER_HTML => $language,

					'class' => 'btn language--' . \Brickrouge\normalize($language),
					'href' => $record->url
				)
			);

			if ($language == $page_language)
			{
				$link->add_class('active');
			}

			$links[$language] = $link;
		}

		new LanguagesElement\AlterEvent($this, array('links' => &$links, 'languages' => &$translations_by_language, 'page' => $page));

		return implode('', $links);
	}

	protected function collect()
	{
		global $core;

		$page = $core->request->context->page;
		$source = $page->node ?: $page;
		$translations = $source->translations;
		$translations_by_language = array();

		if ($translations)
		{
			$translations[$source->nid] = $source;
			$translations_by_language = array_flip
			(
				$core->models['sites']->select('language')->where('status = 1')->order('weight, siteid')->all(\PDO::FETCH_COLUMN)
			);

			if ($source instanceof Page)
			{
				foreach ($translations as $translation)
				{
					if (!$translation->is_accessible)
					{
						continue;
					}

					$translations_by_language[$translation->language] = $translation;
				}
			}
			else // nodes
			{
				foreach ($translations as $translation)
				{
					if (!$translation->is_online)
					{
						continue;
					}

					$translations_by_language[$translation->language] = $translation;
				}
			}

			foreach ($translations_by_language as $language => $translation)
			{
				if (is_object($translation))
				{
					continue;
				}

				unset($translations_by_language[$language]);
			}
		}

		if (!$translations_by_language)
		{
			$translations_by_language = array
			(
				($source->language ? $source->language : $page->language) => $source
			);
		}

		return $translations_by_language;
	}
}

namespace Icybee\Modules\Pages\LanguagesElement;

/**
 * Event class for the `Icybee\Modules\Pages\LanguagesElement::collect` event.
 */
class CollectEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the languages.
	 *
	 * @var array[string]\ICanBoogie\ActiveRecord
	 */
	public $languages;

	/**
	 * The event is constructed with the `render:before` event.
	 *
	 * @param \Icybee\Modules\Pages\LanguagesElement $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Modules\Pages\LanguagesElement $target, array $properties)
	{
		parent::__construct($target, 'collect', $properties);
	}
}

/**
 * Event class for the `Icybee\Modules\Pages\LanguagesElement::alter` event.
 */
class AlterEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the links array.
	 *
	 * @var array[string]\Brickrouge\Element
	 */
	public $links;

	/**
	 * Reference to the language records.
	 *
	 * @var array[string]\ICanBoogie\ActiveRecord
	 */
	public $languages;

	/**
	 * The current page.
	 *
	 * @var \Icybee\Modules\Pages\Page
	 */
	public $page;

	/**
	 * The event is constructed with the `alter` event.
	 *
	 * @param \Icybee\Modules\Pages\LanguagesElement $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Modules\Pages\LanguagesElement $target, array $properties)
	{
		parent::__construct($target, 'alter', $properties);
	}
}