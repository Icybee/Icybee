window.addEvent
(
	'domready', function()
	{
		$$('form.edit div.is_notify').each
		(
			function(is_notify_target)
			{
				var is_notify = is_notify_target.getElement('input');

				function check_is_notify()
				{
					is_notify_target[(is_notify.checked ? 'add' : 'remove') + 'Class']('unfolded');
				}

				is_notify.addEvent('change', check_is_notify);

				check_is_notify();

				/*
				 * The URL used to get the default values if defined using the 'value' attribute of
				 * the button element.
				 */

				var reset_trigger = is_notify_target.getParent().getElement('button.reset');

				reset_trigger.addEvent
				(
					'click', function(ev)
					{
						var url = this.get('value').replace('%modelid', this.form.elements['modelid'].value);
						var ns = this.get('data-ns');

						if (!url)
						{
							return;
						}

						var form = this.form;

						var op = new Request.JSON
						({
							url: url,
							onSuccess: function(response)
							{
								Object.each
								(
									response.rc, function(value, key)
									{
										if (ns)
										{
											key = ns + '[' + key + ']';
										}

										var el = form.elements[key];

										if (!el)
										{
											return;
										}

										$(el).set('value', value);
									}
								);
							}
						});

						op.get();
					}
				);
			}
		);
	}
);