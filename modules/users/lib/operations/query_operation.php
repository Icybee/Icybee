<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users;

use ICanBoogie\Operation;

class QueryOperationOperation extends \Icybee\Operation\Module\QueryOperation
{
	protected function query_activate()
	{
		$keys = $this->request['keys'];
		$count = count($keys);

		return array
		(
			/*
			'title' => $count == 1 ? 'Activate user' : 'Activate users',
			'message' => $count == 1
				? t('Are you sure you want to active the selected user ?')
				: t('Are you sure you want to activate the :count selected users ?', array(':count' => $count)),
			'confirm' => array('Don\'t activate', 'Activate'),
			*/
			'params' => array
			(
				'keys' => $keys
			)
		);
	}

	protected function query_deactivate()
	{
		$keys = $this->request['keys'];
		$count = count($keys);

		return array
		(
			/*
			'title' => $count == 1 ? 'Deactivate user' : 'Deactivate users',
			'message' => $count == 1
				? t('Are you sure you want to deactive the selected user ?')
				: t('Are you sure you want to deactivate the :count selected users ?', array(':count' => $count)),
			'confirm' => array('Don\'t deactivate', 'Deactivate'),
			*/
			'params' => array
			(
				'keys' => $keys
			)
		);
	}

	protected function query_send_password()
	{
		return array
		(
			'params' => array
			(
				'keys' => $this->request['keys']
			)
		);
	}

	protected function operation_queryOperation(Operation $operation)
	{
		switch ($operation->params['operation'])
		{
			case self::OPERATION_PASSWORD:
			{
				global $core;

				$user = $core->user;

//				if (!$user->has_permission(self::PERMISSION_MANAGE, $this))
				{
					\ICanBoogie\log_error('You don\'t have the permission to query this operation');

					return false;
				}

				$entries = $operation->request['entries'];
				$count = count($entries);

				$message = ($count == 1)
					? 'Êtes-vous sûr de vouloir envoyer un nouveau mot de passe à l\'entrée sélectionnée'
					: 'Êtes-vous sûr de vouloir envoyer un nouveau mot de passe aux :count entrées sélectionnées ?';

				$operation->terminus = true;

				return array
				(
					'title' => 'Nouveau mot de passe',
					'message' => t($message, array(':count' => $count)),
					'confirm' => array('Ne pas envoyer', 'Envoyer'),
					'params' => array
					(
						'entries' => $entries
					)
				);
			}
			break;
		}

		return parent::operation_queryOperation($operation);
	}
}