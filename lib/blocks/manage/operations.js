window.addEvent('domready', function() {

	var selected = []
	, container = null
	, containerWrapper = null

	function queryOperation(operation, keys)
	{
		Icybee.manager.element.set('slide', { duration: 'short', resetHeight: true })

		new Request.API
		({
			url: 'query-operation/' + Icybee.manager.destination + '/' + operation,

			onSuccess: function(response)
			{
				var rc = response.rc
				, html = ''

				html += '<h3>' + rc.title + '</h3>';
				html += '<div class="confirm">';
				html += '<p>' + rc.message + '</p>';
				html += '<button name="cancel" class="btn">' + rc.confirm[0] + '</button>';
				html += '<span class="spacer">&nbsp;</span>';
				html += '<button name="ok" class="btn btn-warning">' + rc.confirm[1] + '</button>';
				html += '</div>';

				container = new Element
				(
					'div',
					{
						'id': 'manage-job',
						'class': 'group',
						'html': html
					}
				);

				containerWrapper = new Element
				(
					'div',
					{
						'class': 'wrapper',
						'styles':
						{
							'overflow': 'hidden'
						}
					}
				);

				container.inject(containerWrapper)
				container.set('slide', { duration: 'short', wrapper: containerWrapper })
				container.store('wrapper', containerWrapper)
				container.getElement('button[name="cancel"]').addEvent('click', cancelOperation)

				container.getElement('button[name="ok"]').addEvent('click', function() {

					var confirm = container.getElement('div.confirm')

					confirm.set('tween', { property: 'opacity', duration: 'short' })

					confirm.get('tween').start(0).chain(function() {

						confirm.destroy()

						startOperation(operation, rc.params)
					})
				})

				Icybee.manager.element.get('slide').slideOut().chain(function() {

					//
					// insert just after the element wrapper
					//

					container.slide('hide')
					containerWrapper.inject(Icybee.manager.element.getParent(), 'after')
					container.slide('in')
				})
			}
		}).get({ keys: keys })
	}

	function cancelOperation()
	{
		container.get('slide').slideOut().chain(function() {

			containerWrapper.destroy()
			containerWrapper = null
			container = null

			Icybee.manager.element.slide('in')
		})
	}

	function startOperation(operation, params)
	{
		var progress = new Element('div.progress')





		progress.set('tween', { property: 'opacity', duration: 'short' })
		progress.fade('hide')

		var gauge = new WdGauge({ max: params.keys.length })

		gauge.element.inject(progress)

		var message = new Element('p')

		message.inject(progress)

		progress.inject(container).fade('in')

		/* iterator */

		var keys = params.keys
		, iterations = keys.length

		function iterator()
		{
			var key = keys.pop()

			if (!key)
			{
				progress.get('tween').start(0).chain(function() {

					progress.destroy()

					finishOperation(operation, 'Operation complete !')
				})

				return
			}

			new Request.API
			({
				url: Icybee.manager.destination + '/' + key + '/' + operation,

				onFailure: function(response)
				{
					keys = []
					progress.destroy()

					finishOperation(operation, response.errors)
				},

				onSuccess: function(response)
				{
					gauge.set(iterations - keys.length)

					if (response.message)
					{
						message.set('html', response.message)
					}

					iterator()
				}

			}).post()
		}

		iterator()
	}

	function finishOperation(operation, message)
	{
		var el = new Element
		(
			'div.finish',
			{
				'html': '<p>' + message + '</p><div class="confirm"><button name="ok" class="btn btn-success">Ok</button></div>'
			}
		)

		el.getElement('button').addEvent('click', function() {

			container.get('slide').slideOut().chain(function() {

				el.destroy()

				containerWrapper.destroy()
				containerWrapper = null
				container = null

				Icybee.manager.update({ start: 1 })
				Icybee.manager.element.slide('in')
				Icybee.actionbar.display()
			})
		})

		el.fade('hide').inject(container).fade('in')
	}

	Icybee.manager.addEvent('select', function(ev) {

		selected = ev.selected

		Icybee.actionbar.display(ev.selected.length ? 'operations' : '')

	})

	window.addEvent('click:relay([data-target="manager"][data-operation])', function(ev, el) {

		var operation = el.get('data-operation')

		queryOperation(operation, selected)

	})
})