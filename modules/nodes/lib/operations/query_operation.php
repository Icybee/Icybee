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

class QueryOperationOperation extends \Icybee\Operation\Module\QueryOperation
{
	protected function query_online()
	{
		return array
		(
			'params' => array
			(
				'keys' => $this->request['keys']
			)
		);
	}

	protected function query_offline()
	{
		return array
		(
			'params' => array
			(
				'keys' => $this->request['keys']
			)
		);
	}
}