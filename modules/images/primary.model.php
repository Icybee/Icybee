<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Images;

class Model extends \Icybee\Modules\Files\Model
{
	static protected $accept = array
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
		# We update the "width" and "height" properties if the file is updated.
		#

		if ($uploaded || isset($properties[Image::PATH]))
		{
			if (!$key)
			{
				$key = $rc;
			}

			$path = $this->parent->select(Image::PATH)->filter_by_nid($key)->rc;

			if ($path)
			{
				list($w, $h) = getimagesize(\ICanBoogie\DOCUMENT_ROOT . $path);

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