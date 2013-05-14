window.addEvent('domready', function() {

	var toggle = document.id(document.body).getElement('[name="is_notify"]')
	, toggleContainer = null

	function checkToggle()
	{
		toggleContainer[toggle.checked ? 'addClass' : 'removeClass']('enabled')
	}

	if (toggle)
	{
		toggleContainer = toggle.getParent('.control-group')
		toggleContainer.addClass('group-description')

		toggle.addEvent('change', checkToggle)

		checkToggle()
	}

	var block = document.getElement('.block-edit--forms')

	if (!block) return

	var form = block.getElement('form')
	, moldId = document.id(form.modelid)

	function getDefaults(id)
	{
		new Request.API({

			url: 'forms/' + id + '/defaults',

			onSuccess: function(response)
			{
				Object.each(response.rc, function(value, name) {

					console.log('name: ', name, value)

					if (!form[name]) return

					form[name].set('data-default-value', value)
				})

				Brickrouge.updateDocument(form)
			}

		}).get()
	}

	moldId.addEvent('change', function(ev) {

		getDefaults(moldId.value)

	})

	if (moldId.value)
	{
		getDefaults(moldId.value)
	}


//	var form = document.getElement('')

		/*
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
				 * /

				var reset_trigger = is_notify_target.getParent().getElement('button.reset');

				reset_trigger.addEvent
				(
					'click', function(ev)
					{
						var url = this.get('value').replace('%modelid', encodeURIComponent(this.form.elements['modelid'].value));
						var ns = this.get('data-ns');

						if (!url)
						{
							return;
						}

						var form = this.form;

						var op = new Request.API
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
		*/
	}
);