
!function() {

	var defaultImage = 'data:image/gif;base64,'
	+ 'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ'
	+ 'bWFnZVJlYWR5ccllPAAAAAZQTFRF////IiIiHNlGNAAAAAF0Uk5TAEDm2GYAAAAeSURBVHjaYmBk'
	+ 'YMCPKJVnZBi1YtSKUSuGphUAAQYAxEkBVsmDp6QAAAAASUVORK5CYII='

	Brickrouge.Widget.PopImage = new Class({

		Extends: Brickrouge.Widget.PopNode,

		options: {

			previewWidth: 64,
			previewHeight: 64
		},

		initialize: function(el, options)
		{
			this.parent(el, options)

			this.img = new Element('img', { src: defaultImage })

			this.img.addEvent('load', function(ev) {

				if (!this.popover) return

				this.popover.reposition()

			}.bind(this))
		},

		change: function(ev)
		{
			this.setValue(ev.target.get('data-nid'))
		},

		formatValue: function(value)
		{
			if (!value)
			{
				return ''
			}

			this.img.src = Request.API.encode('images/' + value + '/' + this.options.previewWidth + 'x' + this.options.previewHeight + '/surface?f=png')

			return this.img
		}
	})

}()