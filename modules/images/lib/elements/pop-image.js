
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

			var addon = this.element.getNext('.add-on')

			if (addon)
			{
				var file = addon.getElement('input')

				console.log('file:', file)

				file.addEvent('change', function(ev) {

					var fd = new FormData()

					fd.append('path', ev.target.files[0])

					var xhr = new XMLHttpRequest();

					xhr.onreadystatechange = function(ev)
					{
						if (this.readyState != XMLHttpRequest.DONE)
						{
							return;
						}

						var response = null;

						if (this.status == 200)
						{
							response = JSON.parse(this.responseText);

							console.log('done', ev, response)
						}
					}

					xhr.open("POST", '/api/files/save');

					xhr.setRequestHeader('Accept', 'application/json');
					xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
					xhr.setRequestHeader('X-Request', 'JSON');
					xhr.setRequestHeader('X-Using-File-API', true);

					xhr.send(fd);

					/*
					var request = new Request.API({

						url: 'images/save',

						onSuccess: function(response)
						{
							console.log('response:', response)
						},

						headers: {
							'X-Using-File-API': true
						},

						method: 'POST'

					})

					console.log('request:', request)

					request.xhr.send(fd)

					console.log('fd:', fd)
					*/
				})

				addon.addEvent('click', function(ev) {

					if (ev.target != addon) return

					ev.stop()

					console.log('addonclick:', ev)

					file.click()
				})
			}
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