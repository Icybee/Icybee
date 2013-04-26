<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Members;

use ICanBoogie\Debug;
use ICanBoogie\HTTP\Request;
use ICanBoogie\I18n\FormattedString;
use ICanBoogie\Uploaded;
use ICanBoogie\Operation;

use Icybee\Modules\Users\User;

class SaveOperation extends \Icybee\Modules\Users\SaveOperation
{
	protected $accept = array
	(
		'image/gif',
		'image/jpeg',
		'image/png'
	);

	protected function validate(\ICanboogie\Errors $errors)
	{
		$file = new Uploaded('photo', $this->accept, false);

		if ($file)
		{
			if ($file->er)
			{
				$errors['photo'] = new FormattedString('Unable to upload file %file: :message.', array
				(
					'%file' => $file->name,
					':message' => $file->er_message
				));

				return false;
			}

			if ($file->location)
			{
				$this->request['photo'] = $file;
			}
		}

		#
		# email verify
		#

		if (!$this->key && isset($this->properties['email-verify']) && $this->request['email-verify'] != $this->properties['email'])
		{
			$errors['email-verify'] = new FormattedString("E-mail and E-mail confirm don't match");

			return false;
		}

		return parent::validate($errors);
	}
}