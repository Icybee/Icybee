window.addEvent('domready', function() {

	var table = document.body.getElement('table.manage')
	, popover = null
	, popoverTrigger = null

	function getCacheName(el)
	{
		return el.getParent('tr').getElement('td.state input').name
	}

	function updateStat(el)
	{
		new Request.API({

			url: 'cache/' + getCacheName(el) + '/stat',

			onSuccess: function(response)
			{
				el[(response.count ? 'remove' : 'add') + 'Class']('empty')
				el.innerHTML = response.rc
			}

		}).get()
	}

	table.addEvents({

		'click:relay(td.state input)': function(ev, el) {

			new Request.API({

				url: 'cache/' + el.name + '/' + (el.checked ? 'enable' : 'disable')

			}).send()
		},

		'click:relay(button[name="clear"])': function(ev, el) {

			var req = new Request.API({

				url: 'cache/' + getCacheName(el) + '/clear',

				onSuccess: function(response)
				{
					var target = el.getParent('tr').getElement('td.usage')

					target[(response.rc[0] ? 'remove' : 'add') + 'Class']('empty')
					target.innerHTML = response.rc[1]
				}
			})

			req.send()
		},

		'click:relay(td.config .spinner)': function(ev, el)	{

			var cacheId = getCacheName(el)

			if (popover)
			{
				if (popoverTrigger == cacheId) return

				popover.hide()

				delete popover

				popover = null
			}

			popoverTrigger = cacheId

			new Request.API
			({
				url: 'cache/' + cacheId + '/editor',
				onSuccess: function(response)
				{
					popover = new Brickrouge.Popover(Elements.from(response.rc).shift(), {

						anchor: el,
						placement: 'above',
						onAction: function(ev)
						{
							if (ev.action == 'cancel')
							{
								popover.hide()
								popover = null
							}
							else if (ev.action == 'ok')
							{
								var form = popover.element.getElement('form')

								popover.hide()
								popover = null

								new Request.API({

									url: 'cache/' + cacheId + '/config',

									onSuccess: function(response)
									{
										el.innerHTML = response.rc
									}

								}).post(form)
							}
						}
					})

					document.body.appendChild(popover.element)

					popover.show()
				}
			}).get()
		}
	})

	Brickrouge.updateDocument()
})