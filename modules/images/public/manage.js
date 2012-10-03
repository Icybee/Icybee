var WdPopupImage = new Class
({
	initialize: function(el, src)
	{
		this.element = $(el);
		this.src = src;

		this.element.addEvent('mouseenter', this.onMouseEnter.bind(this));
		this.element.addEvent('mouseleave', this.onMouseLeave.bind(this));

		this.onMouseMove = function(ev)
		{
			var popup = self.popup;

			if (!popup)
			{
				return;
			}

			var target = ev.target;

			popup.setStyle('left', ev.client.x + target.getSize().x + 10);
		};
	},

	onMouseEnter: function()
	{
		this.cancel = false;

		var func = this.popup ? this.show : this.load;
		var delay = this.element.get('data-pop-preview-delay') || 100;

//		window.addEvent('mousemove', this.onMouseMove);

		func.delay(delay, this);
	},

	load: function()
	{
		if (this.cancel || this.popup)
		{
			return;
		}

		new Asset.image
		(
			this.src,
			{
				onload: function(popup)
				{
					var targetClass = this.element.get('data-pop-preview-target');
					var target = this.element;

					if (targetClass)
					{
						target = this.element.getParent(targetClass) || target;
					}

					//
					// setup image
					//

					coord = target.getCoordinates();

					popup.id = 'pop-preview';

					popup.setStyles
					(
						{
							position: 'absolute',
							top: coord.top + (coord.height - popup.height) / 2 - 2,
							left: coord.left + coord.width + 20,
							opacity: 0
						}
					);

					popup.set('tween', { duration: 'short', link: 'cancel' });

					popup.addEvent('mouseenter', this.onMouseLeave.bind(this));

					if (this.popup)
					{
						//console.info('kill multiple for: %s', this.src);

						popup.destroy();

						return;
					}

					this.popup = popup;

					//
					// show
					//

					this.show();
				}
				.bind(this)
			}
		);
	},

	onMouseLeave: function()
	{
		this.cancel = true;

//		window.removeEvent('mousemove', this.onMouseMove);

//		console.info('set cancel to true');

		this.hide();
	},

	show: function()
	{
		//console.info('show (%d) %a', this.cancel, this);

		if (this.cancel)
		{
			return;
		}

		//
		// clear 'title' attribute
		//

		var popup = this.popup;

		document.body.appendChild(popup);

		popup.fade('in');
	},

	hide: function()
	{
		var popup = this.popup;

		if (!popup)
		{
			return;
		}

		if (!popup.parentNode)
		{
			return;
		}

		this.popup = null;

		popup.get('tween').start('opacity', 0).chain
		(
			function()
			{
				document.body.removeChild(popup);

				delete popup;
			}
		);
	}
});

if (typeof manager != 'undefined')
{
	manager.addEvent
	(
	 	'ready', function()
		{
	 		if (manager.blockName == 'manage')
	 		{
				manager.element.getElements('a[rel="lightbox[]"]').each
				(
					function(el)
					{
						var children = el.getChildren();

						new WdPopupImage(children[0], children[1].value);
					}
				);
	 		}

			Slimbox.scanPage();
		}
	);
}

window.addEvent('brickrouge.update', function(ev) {

	ev.target.getElements('img.pop-preview').each(function (el) {

		if (el.retrieve('pop-preview'))	return

		var nid = el.get('data-nid')
		, popPreview = new WdPopupImage(el, '/api/resources.images/' + nid + '/thumbnail?v=$popup');

		el.store('pop-preview', popPreview)

	})
})