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
		manager.element.getElements('td.is_activated input[type="checkbox"]').each
		(
			function(el)
			{
				el.addEvent
				(
					'click', function(ev)
					{
						var destination = this.form['#destination'].value;

						var operation = new Request.API
						({
							url: destination + '/' + this.value + '/' + (this.checked ? 'activate' : 'deactivate'),

							onRequest: function()
							{
								this.disabled = true;
							},

							onSuccess: function(response)
							{
								this.disabled = false;

								//
								// if for some reason the operation failed, we reset the
								// checkbox
								//

								if (!response.rc)
								{
									this.checked = !this.checked;

									this.fireEvent('change', {});
								}
							}
							.bind(this)
						});

						operation.get();
					}
				);
			}
		);
	}
);