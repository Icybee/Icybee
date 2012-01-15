<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Images;

use ICanBoogie\ActiveRecord\Image;

class Model extends \ICanBoogie\Modules\Files\Model
{
	protected static $accept = array
	(
		'image/gif', 'image/png', 'image/jpeg'
	);

	public function save(array $properties, $key=null, array $options=array())
	{
		$options += array
		(
			self::ACCEPT => self::$accept,
			self::UPLOADED => &$uploaded
		);

		$rc = parent::save($properties, $key, $options);

		#
		# we update the "width" and "height" properties if the file is updated
		#

		if ($rc && ($uploaded || isset($properties[Image::PATH])))
		{
			if (!$key)
			{
				$key = $rc;
			}

			$path = $this->parent->select(Image::PATH)->where(array('{primary}' => $key))->rc;

			if ($path)
			{
				list($w, $h) = getimagesize($_SERVER['DOCUMENT_ROOT'] . $path);

				$this->update
				(
					array
					(
						Image::WIDTH => $w,
						Image::HEIGHT => $h
					),

					$key
				);
			}
		}

		return $rc;
	}
}