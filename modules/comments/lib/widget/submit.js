
Brickrouge.Widget.SubmitComment = new Class({

	initialize: function(el)
	{
		this.element = $(el)
		this.source = el.getElement('textarea')
		this.operation = null
		this.lastValue = null

		this.source.addEvent('keypress', this.handle.bind(this))

		if (this.source.value) this.update()
	},

	show: function()
	{
		var target = new Element('div.preview')
		, header = new Element('h5', { 'html':  'Apperçu de votre commentaire' })
		, wrapper = new Element('div.control-group.control-group--preview')

		wrapper.appendChild(header)
		wrapper.appendChild(target)
		wrapper.inject(this.source.getParent('.control-group'), 'after')

		this.target = target
		this.wrapper = wrapper
	},

	hide: function()
	{
		if (!this.target) return

		this.wrapper.destroy()
		this.target.destroy()

		this.wrapper = null
		this.target = null
	},

	handle: function(ev)
	{
		if (this.timer) clearTimeout(this.timer)
		this.timer = this.update.delay(500, this)
	},

	update: function()
	{
		var value = this.source.value

		if (value == this.lastValue) return

		this.lastValue = value

		if (!this.operation)
		{
			this.operation = new Request.API
			({
				url: 'comments/preview',
				onSuccess: function(response)
				{
					if (!response.rc)
					{
						this.hide()

						return
					}

					if (!this.target) this.show()
					this.target.innerHTML = response.rc
				}
				.bind(this)
			})
		}

		this.operation.get({ contents: value })
	}
})