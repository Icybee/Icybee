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
		var table = $(document.body).getElement('table.manage');
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

							/*
							onRequest: function()
							{
								el.disabled = true;
							},

							onComplete: function()
							{
								el.disabled = false;
							},
							*/

							onSuccess: function(response)
							{
								var target = el.getParent('tr').getElement('td.usage');

								target[(response.rc[0] ? 'remove' : 'add') + 'Class']('empty');
								target.innerHTML = response.rc[1];
							}

						});

						req.send();
					}
				);
			}
		);

		var popover = null;
		var popoverTrigger = null;

		table.addEvent
		(
			'click', function(ev)
			{
				var target = ev.target;

				if (target.tagName == 'BUTTON' && target.getParent('td.config'))
				{
					var cacheId = target.getParent('tr').get('data-cache-id');

					if (popover)
					{
						if (popoverTrigger == cacheId)
						{
							return;
						}

						popover.hide();

						delete popover;

						popover = null;
					}

					popoverTrigger = cacheId;

					new Request.API
					({
						url: 'system.cache/' + cacheId + '/editor',
						onSuccess: function(response)
						{
							var popover=null;

							popover = new Brickrouge.Popover
							(
								Elements.from(response.rc).shift(),
								{
									anchor: target,
									placement: 'above',
									onAction: function(ev)
									{
										if (ev.action == 'cancel')
										{
											popover.hide();
											popover = null;
										}
										else if (ev.action == 'ok')
										{
											var form = popover.element.getElement('form');

											popover.hide();
											popover = null;

											new Request.API
											({
												url: 'system.cache/' + cacheId + '/config',
												onSuccess: function(response)
												{
													target.innerHTML = response.rc;
												}

											}).post(form);
										}
									}
								}
							);

							document.body.appendChild(popover.element);

							popover.show();
						}
					}).get();
				}
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

		Brickrouge.awakeWidgets();
	}
);