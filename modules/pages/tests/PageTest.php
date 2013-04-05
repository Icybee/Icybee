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

use Icybee\Modules\Pages\PageTest\PretendSite;

class PageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_is_accessible()
	{
		$page = new Page;
		$page->is_accessible = true;
	}

	/**
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_is_active()
	{
		$page = new Page;
		$page->is_active = true;
	}

	public function test_get_is_home()
	{
		$page = new Page;
		$this->assertFalse($page->is_home);
		$page->is_online = true;
		$this->assertTrue($page->is_home);
		$page->weight = 1;
		$this->assertFalse($page->is_home);
		$page->weight = 0;
		$page->parentid = 1;
		$this->assertFalse($page->is_home);
	}

	/**
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_is_home()
	{
		$page = new Page;
		$page->is_home = true;
	}

	/**
	 * @expectedException \ICanBoogie\PropertyNotWritable
	 */
	public function test_set_is_trail()
	{
		$page = new Page;
		$page->is_trail = true;
	}

	public function testDefinedLanguage()
	{
		$page = Page::from(array('language' => 'fr'));
		$this->assertEquals('fr', $page->language);
		$this->assertArrayHasKey('language', $page->to_array());
		$this->assertArrayHasKey('language', $page->__sleep());
	}

	/**
	 * The `language` getter MUST NOT create the property.
	 */
	public function testUndefinedLanguageDefaultsToSiteLanguage()
	{
		$page = new Page;

		$page->site = null;
		$this->assertNull($page->language);

		$page->site = new PretendSite();
		$page->site->language = 'fr';
		$this->assertEquals('fr', $page->language);
		$this->assertArrayNotHasKey('language', $page->to_array());
		$this->assertArrayNotHasKey('language', $page->__sleep());
	}

	public function testDefinedLabel()
	{
		$page = Page::from(array('label' => 'Testing'));
		$this->assertEquals('Testing', $page->label);
		$this->assertArrayHasKey('label', $page->to_array());
		$this->assertArrayHasKey('label', $page->__sleep());
	}

	/**
	 * The `label` getter MUST NOT create the property.
	 */
	public function testUndefinedLabelDefaultsToTitle()
	{
		$page = new Page;
		$this->assertNull($page->label);

		$page->title = 'Page title';
		$this->assertEquals('Page title', $page->label);
		$this->assertArrayNotHasKey('label', $page->to_array());
		$this->assertArrayNotHasKey('label', $page->__sleep());
	}
}

namespace Icybee\Modules\Pages\PageTest;

class PretendSite extends \ICanBoogie\Object
{
	public $language;
}