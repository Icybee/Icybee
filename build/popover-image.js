;!function() {

	var PopoverImage = new Class
	({
		initialize: function(el, src)
		{
			this.src = src
			this.element = document.id(el)
			this.element.addEvents({
				mouseenter: this.onMouseEnter.bind(this),
				mouseleave: this.onMouseLeave.bind(this)
			})
		},

		onMouseEnter: function()
		{
			this.cancel = false

			;(this.popover ? this.show : this.load).delay(this.element.get('data-popover-delay') || 100, this)
		},

		onMouseLeave: function()
		{
			this.cancel = true
			this.hide()
		},

		load: function()
		{
			if (this.cancel || this.popover) return

			new Asset.image(this.src, {

				onload: function(popover)
				{
					var targetSelector = this.element.get('data-popover-target')
					, target = this.element
					, coord

					if (targetSelector)
					{
						target = this.element.getParent(targetSelector) || target
					}

					coord = target.getCoordinates()

					popover.id = 'popover-image'
					popover.setStyles
					(
						{
							top: coord.top + (coord.height - popover.height) / 2 - 2,
							left: coord.left + coord.width + 20,
							opacity: 0
						}
					)
					popover.set('tween', { duration: 'short', link: 'cancel' })
					popover.addEvent('mouseenter', this.onMouseLeave.bind(this))

					// check concurrency

					if (this.popover)
					{
						popover.destroy()

						return
					}

					this.popover = popover
					this.show()
				}
				.bind(this)
			})
		},

		show: function()
		{
			if (this.cancel) return

			var popover = this.popover

			document.body.appendChild(popover)

			popover.fade('in')
		},

		hide: function()
		{
			var popover = this.popover

			if (!popover || !popover.getParent()) return

			this.popover = null

			popover.get('tween').start('opacity', 0).chain(function() {

				document.body.removeChild(popover)

				delete popover
			})
		}
	})

	, popovers = []

	document.body.addEvent('mouseenter:relay([data-popover-image])', function(ev, el) {

		var uniqueNumber = el.uniqueNumber
		, popover

		if (popovers[uniqueNumber]) return

		popover = new PopoverImage(el, el.get('data-popover-image'))
		popover.load()

		popovers[uniqueNumber] = popover

	})

} ()