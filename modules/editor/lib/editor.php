<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Editor;

/**
 * The editor interface is used to serialize/unserialize the content edited by the editor it
 * provides, as well as render this content.
 */
interface Editor
{
	/**
	 * Serialize the content.
	 *
	 * @param mixed $content
	 */
	public function serialize($content);

	/**
	 * Unserialize the serialized content.
	 *
	 * @param string $serialized_content
	 */
	public function unserialize($serialized_content);

	/**
	 * Returns the editor element.
	 *
	 * @param array $attributes Attributes that should be used to create the element. The content
	 * of the element must be provided using the `value` attribute, and must be unserialized.
	 *
	 * @return \Brickrouge\Element
	 */
	public function create_element(array $attributes);

	/**
	 * Renders the content into a HTML string or an object that can be stringified into a HTML
	 * string.
	 *
	 * @param string $content
	 */
	public function render($content);
}

interface EditorElement
{
	const STYLESHEETS = '#editot-stylesheet';
}