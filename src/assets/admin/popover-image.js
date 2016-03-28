!function (Brickrouge) {

	var PopoverImage = new Class({

		initialize: function(el, src)
		{
			this.src = src
			this.element = el
			this.element.addEvents({

				mouseenter: this.onMouseEnter.bind(this),
				mouseleave: this.onMouseLeave.bind(this)

			})
		},

		onMouseEnter: function()
		{
			this.cancel = false

			;(this.popover ? this.show : this.load)
			.delay(this.element.getAttribute('data-popover-delay') || 100, this)
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
					var targetSelector = this.element.getAttribute('data-popover-target')
					, target = this.element
					, coord

					if (targetSelector)
					{
						target = this.element.closest(targetSelector) || target
					}

					coord = target.getCoordinates()

					popover.id = 'popover-image'
					popover.setStyles({

						top: coord.top + (coord.height - popover.height) / 2 - 2,
						left: coord.left + coord.width + 20,
						opacity: 0

					})

					popover.set('tween', { duration: 'short', link: 'cancel' })
					popover.addEvent('mouseenter', this.onMouseLeave.bind(this))
					popover.width = popover.naturalWidth
					popover.height = popover.naturalHeight

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

			if (!popover || !popover.parentNode) return

			this.popover = null

			popover.get('tween').start('opacity', 0).chain(function() {

				document.body.removeChild(popover)

			})
		}
	})

	, popovers = []

	document.body.addEvent('mouseenter:relay([data-popover-image])', function(ev, el) {

		var uniqueNumber = Brickrouge.uidOf(el)
		, popover

		if (popovers[uniqueNumber]) return

		popover = new PopoverImage(el, el.getAttribute('data-popover-image'))
		popover.load()

		popovers[uniqueNumber] = popover

	})

} (Brickrouge);
