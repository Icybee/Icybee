Brickrouge.Widget.Progress = new Class({

	initialize: function(el, options)
	{
		this.element = el = document.id(el)
		this.bar = el.getElement('.progress-bar')
		this.label = el.getElement('.progress-bar-label')
	},

	setValue: function(value)
	{
		var bar = this.bar
		, max = bar.get('aria-valuemax')

		bar.set('aria-valuenow', value)
		bar.setStyle('width', (value / max * 100) + '%')
	}

})

!function() {

	Brickrouge.Widget.QueryOperation = new Class({

		Implements: [ Options, Events ],

		options: {

			keys: null,
			operation: null,
			destination: null

		},

		initialize: function(el, options)
		{
			var log

			this.element = el = document.id(el)
			this.setOptions(options)
			this.canceled = null

			log = el.getElement('.log')
			log.store('scroll', new Fx.Scroll(log, { duration: 'short' }))

			this.logEl = log

			el.addEvent('click:relay([data-action])', this.onAction.bind(this))

		},

		onAction: function(ev, el)
		{
			var action = el.get('data-action')

			ev.stop()

			this[action]()
		},

		cancel: function()
		{
			this.canceled = true

			this.fireEvent('complete').fireEvent('cancel')
		},

		start: function()
		{
			var keys = this.options.keys
			, iterations
			, operationName = this.options.operation
			, progress = this.element.getElement('.progress').get('widget')
			, iterator = null

			if (typeOf(keys) != 'array')
			{
				keys = keys.split('|')
			}

			this.element.set('data-state', 'processing')

			iterations = keys.length
			this.canceled = false

			iterator = function ()
			{
				if (this.canceled)
				{
					return
				}

				var key = keys.pop()

				if (!key)
				{
					this.element.set('data-state', 'success')

					return
				}

				new Request.API
				({
					url: this.options.destination + '/' + key + '/' + operationName,

					onFailure: function(xhr, response)
					{
						if (response.message)
						{
							this.log(response.message, 'alert-error')
						}
						else if (response.errors && response.errors._base)
						{
							this.log(response.errors._base, 'alert-error')
						}
					}
					.bind(this),

					onSuccess: function(response)
					{
						if (response.message)
						{
							this.log(response.message)
						}
					}
					.bind(this),

					onComplete: function()
					{
						progress.setValue(iterations - keys.length)

						iterator()
					}

				}).post()

			}.bind(this)

			iterator()
		},

		log: function(message, cl)
		{
			console.log('log', message, cl)

			var line = new Element('li', { html: message, 'class': cl || '' })
			this.logEl.adopt(line)
			this.logEl.retrieve('scroll').toElement(line)
		},

		success: function()
		{
			this.fireEvent('complete').fireEvent('success')
		}

	})

} ()