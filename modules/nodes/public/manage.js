/*
 * This file is part of the Publishr package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

manager.addEvent
(
	'ready', function()
	{
		manager.element.addEvent
		(
			'click', function(ev)
			{
				var target = ev.target;

				if (!target.match('input.is_online'))
				{
					return;
				}

				var operation = new Request.API
				({
					url: manager.destination + '/' + target.value + '/' + (target.checked ? 'online' : 'offline'),
					onSuccess: function(response)
					{
						if (!response.rc)
						{
							//
							// if for some reason the operation failed,
							// we reset the checkbox
							//

							target.checked = !target.checked;

							target.fireEvent('change', {});
						}
					}

				});

				operation.post();
			}
		);
	}
);