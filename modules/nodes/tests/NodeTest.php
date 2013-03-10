<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Nodes;

class NodeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Checks the the defined slug is returned and not created from the title, and that the
	 * slug is exported by {@link Node::to_array()}.
	 */
	public function test_slug()
	{
		$node = new Node;
		$node->title = 'The quick brown fox';
		$node->slug = 'madonna';
		$this->assertEquals('madonna', $node->slug);
		$this->assertArrayHasKey('slug', $node->to_array());
		$this->assertContains('slug', $node->__sleep());

		$node = Node::from(array('title' => 'The quick brown fox', 'slug' => 'madonna'));
		$this->assertEquals('madonna', $node->slug);
		$this->assertArrayHasKey('slug', $node->to_array());
		$this->assertContains('slug', $node->__sleep());
	}

	/**
	 * Checks that the {@link Node::$slug} property is created from {@link Node::$title} and that
	 * it is not exported by {@link Node::to_array()} when it is created that way.
	 */
	public function test_lazy_slug()
	{
		$node = new Node;
		$node->title = 'The quick brown fox';
		$this->assertEquals('the-quick-brown-fox', $node->slug);
		$this->assertArrayNotHasKey('slug', $node->to_array());
		$this->assertNotContains('slug', $node->__sleep());

		$node = Node::from(array('title' => 'The quick brown fox'));
		$this->assertEquals('the-quick-brown-fox', $node->slug);
		$this->assertArrayNotHasKey('slug', $node->to_array());
		$this->assertNotContains('slug', $node->__sleep());
	}
}