<?php

namespace Icybee\Element;

use Brickrouge\Element;
use Brickrouge\Decorator;

/**
 * Decorates a component with a _block element_.
 *
 * The component is wrapped in a `div.block.block--<name>.block--<module>--<name>` element. Where
 * `<name>` is the normalized name of the block, and `<module>` is the normalized identifier of
 * the module that created the component.
 */
class BlockDecorator extends Decorator
{
	/**
	 * Name of the block.
	 *
	 * @var string
	 */
	protected $block_name;

	/**
	 * The identifier of the module providing the block.
	 *
	 * @var string
	 */
	protected $module_id;

	/**
	 * Initialiazes the {@link $block_name} and {@link $module_id} properties.
	 *
	 * @param mixed $block The block to decorate.
	 * @param string $block_name The name of the block.
	 * @param string $module_id The identifier of the module providing the block.
	 */
	public function __construct($block, $block_name, $module_id)
	{
		$this->block_name = $block_name;
		$this->module_id = $module_id;

		parent::__construct($block);
	}

	public function render()
	{
		$normalized_block_name = \Brickrouge\normalize($this->block_name);
		$normalized_module_id = \Brickrouge\normalize($this->module_id);

		return new Element('div', [

			Element::INNER_HTML => $this->component,

			'class' => "block block--{$normalized_block_name} block--{$normalized_module_id}--{$normalized_block_name}"

		]);
	}
}
