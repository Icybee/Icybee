BrickRouge.Widget.AdjustThumbnailVersion = new Class
({
	Implements: [ Options, Events ],

	initialize: function(el, options)
	{
		this.element = el = $(el);

		var w = el.getElement('input[name="w"]') || el.getElement('input[name$="[w]"]');
		var h = el.getElement('input[name="h"]') || el.getElement('input[name$="[h]"]');
		var method = el.getElement('select[name="method"]') || el.getElement('select[name$="[method]"]');
		var format = el.getElement('select[name="format"]') || el.getElement('select[name$="[format]"]');
		var quality = el.getElement('input[name="quality"]') || el.getElement('input[name$="[quality]"]');

		this.elements =
		{
			w: w,
			h: h,
			method: method,
			format: format,
			quality: quality,
			'no-upscale': el.getElement('input[name="no-upscale"]') || el.getElement('input[name$="[no-upscale]"]'),
			interlace: el.getElement('input[name="interlace"]') || el.getElement('input[name$="[interlace]"]'),
			background: el.getElement('input[name="background"]') || el.getElement('input[name$="[background]"]')
		};

		function checkMethod()
		{
			switch (method.get('value'))
			{
				case 'fixed-height':
				{
					h.readOnly = false;
					w.readOnly = true;
				}
				break;

				case 'fixed-width':
				{
					h.readOnly = true;
					w.readOnly = false;
				}
				break;

				default:
				{
					w.readOnly = false;
					h.readOnly = false;
				}
				break;
			}
		}

		function checkQuality()
		{
			var value = format.get('value');

			quality.getParent().setStyle('display', (value != 'jpeg') ? 'none' : '');
		}

		checkMethod();
		checkQuality();

		method.addEvent('change', checkMethod);
		format.addEvent('change', checkQuality);
	},

	setValue: function(value)
	{
		if (typeOf(value) == 'string')
		{
			value = JSON.decode(value);
		}

		if (!value)
		{
			return;
		}

		Object.each
		(
			value, function(value, key)
			{
				if (!this.elements[key])
				{
					return;
				}

				if (key == 'no-upscale' || key == 'interlace')
				{
					this.elements[key].set('checked', value);
				}
				else
				{
					this.elements[key].set('value', value);
				}
			},

			this
		);
	}
});