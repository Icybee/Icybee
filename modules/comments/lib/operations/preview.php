<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Comments;

/**
 * Gives the user a visual feedback of the message he's typing.
 */
class PreviewOperation extends \ICanBoogie\Operation
{
	protected function validate(\ICanboogie\Errors $errors)
	{
		return $this->request['contents'];
	}

	protected function process()
	{
		$contents = $this->request['contents'];
		$contents = \Textmark_Parser::parse($contents);

		return \Icybee\Kses::sanitizeComment($contents);
	}
}