/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

BrickRouge.Widget.Popup = new Class
({
	Implements: [ Options, Events ],

	options:
	{
		anchor: null,
		iframe: null
	},

	initialize: function(el, options)
	{
		this.setOptions(options);

		this.element = $(el);
		this.element.addClass('popup');
		this.element.addClass('invisible');

		this.arrow = this.element.getElement('div.arrow');

		if (!this.arrow)
		{
			this.arrow = new Element('div.arrow').adopt(new Element('div'));

			this.arrow.inject(el);
		}

		if (this.options.anchor)
		{
			this.attachAnchor(this.options.anchor);
		}

		this.repositionCallback = this.reposition.bind(this);
	},

	attachAnchor: function(anchor)
	{
		this.anchor = $(anchor);
		this.options.anchor = this.anchor;
	},

	changePositionClass: function(position)
	{
		this.element.removeClass('before');
		this.element.removeClass('after');
		this.element.removeClass('above');
		this.element.removeClass('below');

		this.element.addClass(position);
	},

	reposition: function()
	{
		var aX, aY, aW, aH, anchor = this.anchor, iframe = this.options.iframe;

		if (!anchor)
		{
			return;
		}

		if (iframe)
		{
			var frameCoords = iframe.getCoordinates();
			var iHTML = iframe.contentDocument.documentElement;

			aX = anchor.offsetLeft;
			aY = anchor.offsetTop;
			aW = anchor.offsetWidth;
			aH = anchor.offsetHeight;

			var visibleH = iHTML.clientHeight;
			var hiddenTop = iHTML.scrollTop;

//			console.log('visibleH: %d, hiddenTop: %d', visibleH, hiddenTop);

			aY -= hiddenTop;

			if (aY < 0)
			{
				aH += aY;
			}

			aY = Math.max(aY, 0);
			aH = Math.min(aH, visibleH);

			var visibleW = iHTML.clientWidth;
			var hiddenLeft = iHTML.scrollLeft;

			aX -= hiddenLeft;

			if (aX < 0)
			{
				aW += aX;
			}

			aX = Math.max(aX, 0);
			aW = Math.min(aW, visibleW);

//			console.log('ia: %d:%d %dx%d, scroll: %d, %d', aX, aY, aW, aH, hiddenLeft, hiddenTop);

			aX += frameCoords.left;
			aY += frameCoords.top;
		}
		else
		{
			var anchorCoords = anchor.getCoordinates();

			aX = anchorCoords.left;
			aY = anchorCoords.top;
			aH = anchorCoords.height;
			aW = anchorCoords.width;
		}

//		console.log('anchor: %d:%d %dx%d', aX, aY, aW, aH);

		var anchorMiddleX = aX + aW / 2;
		var anchorMiddleY = aY + aH / 2;

		var body = $(document.body);
		var bodySize = body.getSize();
		var bodyScroll = body.getScroll();
		var bodyX = bodyScroll.x;
		var bodyY = bodyScroll.y;
		var bodyW = bodySize.x;
		var bodyH = bodySize.y;

//		console.log('anchor: %d:%d, %dx%d, body: %dx%d, relative: %a', aX, aY, aW, aH, bodyW, bodyH, anchor.getCoordinates(body));

		var size = this.element.getSize();
		var w = size.x;
		var h = size.y;

		var x;
		var y = Math.round(aY + (aH - h) / 2);

		if (anchorMiddleX > bodyX + bodyW / 2)
		{
			this.changePositionClass('before');

			x = aX - w;
		}
		else
		{
			this.changePositionClass('after');

			x = aX + aW;
		}

		var pad = 50;

		//
		// limit 'x' and 'y' to the limits of the document incuding a padding value.
		//

		x = x.limit(bodyX + pad, bodyX + bodyW - (w + pad));
		y = y.limit(bodyY + pad, bodyY + bodyH - (h + pad));

		//
		// adjust arrow
		//

		//console.log('y: %d, h: %d, aY: %d, aH: %d', y, h, aY, aH);

		var arY = (aY + aH / 2) - y;

		//console.log('min aY: %d', this.element.getElement('div.confirm').getSize().y + aH);

		var confirm = this.element.getElement('div.confirm');

		arY = Math.min(h - (confirm ? confirm.getSize().y : 0) - 10, arY);
		arY = Math.max(50, arY);

		// adjust element Y so that the arrow is always centered on the anchor visible height

		if (y + arY != anchorMiddleY)
		{
			y -= (y + arY) - anchorMiddleY;
		}

		//

		var visible = (this.element.getStyle('visibility') == 'visible');

		if (!visible || this.arrow.getPosition(this.element).y > h)
		{
			this.arrow.setStyle('top', arY);
		}
		else
		{
			this.arrow.tween('top', arY);
		}

		//
		//
		//

		var params = { left: x, top: y };

		visible ? this.element.morph(params) : this.element.setStyles(params);
	},

	open: function()
	{
		var el = this.element;

		el.addClass('invisible');

		document.body.appendChild(el);

		try
		{
			document.fireEvent('elementsready', { target: document.body });
		}
		catch (e)
		{
			console.log('exception: ', e);
		}

		window.addEvents
		({
			'resize': this.repositionCallback,
			'scroll': this.repositionCallback
		});

		if (this.options.iframe)
		{
			$(this.options.iframe.contentWindow).addEvents
			({
				'resize': this.repositionCallback,
				'scroll': this.repositionCallback
			});
		}

		this.reposition();
		el.removeClass('invisible');
	},

	close: function()
	{
		this.element.removeEvent('adjust', this.repositionCallback);
		this.element.addClass('invisible');
		this.element.dispose();

		window.removeEvent('resize', this.repositionCallback);
		window.removeEvent('scroll', this.repositionCallback);

		if (this.options.iframe)
		{
			var contentWindow = $(this.options.iframe.contentWindow);

			contentWindow.removeEvent('resize', this.repositionCallback);
			contentWindow.removeEvent('scroll', this.repositionCallback);
		}
	}
});

BrickRouge.Widget.Popup.Adjust = new Class
({
	Implements: [ Options, Events ],
	Extends: BrickRouge.Widget.Popup,

	initialize: function(el, options)
	{
		this.parent(el, options);

		this.selected = '';
		this.element.addClass('black');

		['cancel', 'continue', 'none'].each
		(
			function(mode)
			{
				var el = this.element.getElement('button.' + mode);

				if (!el)
				{
					return;
				}

				el.addEvent
				(
					'click', function(ev)
					{
						ev.stop();

						this.fireEvent('closeRequest', { target: this, mode: mode });
					}
					.bind(this)
				);
			},

			this
		);
	},

	open: function()
	{
		this.parent();

		this.adjust = this.element.getElement(':first-child').get('widget');

		if (this.adjust)
		{
			this.adjust.addEvent('results', this.repositionCallback);
			this.adjust.addEvent('adjust', this.repositionCallback);
		}
	}
});
