/*
 * This file is part of the Publishr package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

window.addEvent
(
	'domready', function()
	{
		var ids = [];
		var stat = $$('table.manage td.usage');

		$$('table.manage td.state input').each
		(
			function(el, i)
			{
				ids.push(el.name);

				el.addEvent
				(
					'click', function(ev)
					{
						var target = ev.target;
						var cacheName = target.name;

						var req = new Request.API
						({

							url: 'system.cache/' + ids[i] + '/' + (target.checked ? 'enable' : 'disable')

						});

						req.send();
					}
				);
			}
		);

		$$('table.manage button[name="clear"]').each
		(
			function(el, i)
			{
				el.addEvent
				(
					'click', function(ev)
					{
						var req = new Request.API
						({

							url: 'system.cache/' + ids[i] + '/clear',

							onRequest: function()
							{
								//el.disabled = true;
							},

							onComplete: function()
							{
								//el.disabled = false;

								updateStat(stat[i]);
							}

						});

						req.send();
					}
				);
			}
		);

		function updateStat(el)
		{
			var i = stat.indexOf(el);

			var req = new Request.API
			({

				url: 'system.cache/' + ids[i] + '/stat',

				onSuccess: function(response)
				{
					el[(response.count ? 'remove' : 'add') + 'Class']('empty');
					el.innerHTML = response.rc;
				}
			});

			req.get();
		}

		stat.each(updateStat);
	}
);