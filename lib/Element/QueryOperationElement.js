!function(Brickrouge) {

	const Progress = new Class({

		initialize: function(el, options)
		{
			this.element = el = document.id(el)
			this.bar = el.getElement('.progress-bar')
			this.label = el.getElement('.progress-bar-label')
		},

		animate: function(status)
		{
			const bar = this.bar

			if (status)
			{
				bar.addClass('progress-bar-striped').addClass('animate')
			}
			else
			{
				bar.removeClass('progress-bar-striped').removeClass('animate')
			}
		},

		setValue: function(value)
		{
			const el = this.element
			const bar = this.bar
			const max = el.get('aria-valuemax');

			el.set('aria-valuenow', value)
			bar.setStyle('width', (value / max * 100) + '%')
		}

	})

	const QueryOperation = new Class({

		Implements: [ Options, Events ],

		options: {

			keys: null,
			operation: null,
			destination: null

		},

		initialize: function(el, options)
		{
			this.element = el = document.id(el)
			this.progress = Brickrouge.from(el.getElement('.progress'))
			this.setOptions(options)
			this.canceled = null
			const log = this.logEl = el.getElement('.alert')

			el.removeClass('has-errors')

			log.store('scroll', new Fx.Scroll(log, { duration: 'short' }))

			el.addEvent('click:relay([data-action])', this.onAction.bind(this))

		},

		hasMessages: function()
		{
			return !!this.logEl.getChildren().length
		},

		setState: function(state)
		{
			this.element.set('data-state', state)
		},

		onAction: function(ev, el)
		{
			const action = el.get('data-action')

			ev.stop()

			this[action]()
		},

		log: function(message)
		{
			const line = new Element('p', {html: message});

			this.logEl.adopt(line)
			this.logEl.retrieve('scroll').toElement(line)
			this.element.addClass('has-errors')
		},

		start: function()
		{
			let keys = this.options.keys
			const operationName = this.options.operation
			const progress = this.progress
			const params = this.element.getElement('form').toQueryString()

			if (typeOf(keys) !== 'array')
			{
				keys = keys.split('|')
			}

			this.setState('processing')
			this.canceled = false

			const iterations = keys.length
			const iterator = function ()
			{
				if (this.canceled)
				{
					return
				}

				const key = keys.pop()

				if (!key)
				{
					if (this.hasMessages())
					{
						this.complete()
						this.setState('success')
					}
					else
					{
						this.success()
					}

					return
				}

				new Request.API
				({
					url: this.options.destination + '/' + key + '/' + operationName,

					onFailure: function(xhr, response)
					{
						if (response.message)
						{
							this.log(response.message)
						}
						else if (response.errors && response.errors._base)
						{
							this.log(response.errors._base)
						}
						else
						{
							this.log("An error occurred with record #" + key)
						}
					}
					.bind(this),

					onComplete: function()
					{
						progress.setValue(iterations - keys.length)

						iterator()
					}

				}).post(params)

			}.bind(this)

			progress.animate(true)

			iterator()
		},

		cancel: function()
		{
			this.canceled = true

			this.progress.animate(false)

			if (this.hasMessages())
			{
				this.setState('success')
			}
			else
			{
				this.fireEvent('cancel')
			}

			return this
		},

		complete: function()
		{
			this.progress.animate(false)

			this.fireEvent('complete')

			return this
		},

		success: function()
		{
			this.fireEvent('success')

			return this
		}

	})

	Brickrouge.register('Progress', (element, options) => new Progress(element, options))
	Brickrouge.register('QueryOperation', (element, options) => new QueryOperation(element, options))

} (Brickrouge);
