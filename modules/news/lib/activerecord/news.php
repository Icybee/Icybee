<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\News;

/**
 * A representation of a news.
 */
class News extends \Icybee\Modules\Contents\Content
{
	/**
	 * Defaults model to `news`.
	 */
	public function __construct($model='news')
	{
		parent::__construct($model);
	}
}