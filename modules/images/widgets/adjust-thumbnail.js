/**
 * This file is part of the Publishr software
 *
 * @author Olivier Laviale <olivier.laviale@gmail.com>
 * @link http://www.wdpublisher.com/
 * @copyright Copyright (c) 2007-2011 Olivier Laviale
 * @license http://www.wdpublisher.com/license.html
 */

Widget.AdjustThumbnailOptions = new Class
({
	Implements: [ Events ],

	initialize: function(el)
	{
		this.element = $(el);
		this.element.addEvent('change', this.onChange.bind(this));

		this.w = this.element.getElement('[name=w]');
		this.h = this.element.getElement('[name=h]');
		this.method = this.element.getElement('[name=method]');
		this.background = this.element.getElement('[name=background]');
		this.format = this.element.getElement('[name=format]');
		this.quality = this.element.getElement('[name=quality]');
		this['no-upscale'] = this.element.getElement('[name=no-upscale]');
		this.interlace = this.element.getElement('[name=interlace]');
		this.lightbox = this.element.getElement('[name=lightbox]');

		this.checkMethod();
		this.checkQuality();
	},

	checkMethod: function()
	{
		var h = this.h;
		var w = this.w;

		switch (this.method.get('value'))
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
	},

	checkQuality: function()
	{
		var value = this.format.get('value');

		this.quality.getParent().setStyle('display', (value != 'jpeg') ? 'none' : '');
	},

	getValues: function()
	{
		var values =
		{
			w: this.w.get('value'),
			h: this.h.get('value'),
			method: this.method.get('value'),
			background: this.background.get('value'),
			format: this.format.get('value'),
			quality: this.quality.get('value'),
			'no-upscale': this['no-upscale'].checked,
			interlace: this.interlace.checked,
			lightbox: this.lightbox.checked
		};

		if (!values['no-upscale'])
		{
			delete values['no-upscale'];
		}

		if (!values.interlace)
		{
			delete values.interlace;
		}

		if (values.format != 'jpeg')
		{
			delete values.quality;
		}

		if (this.w.readOnly)
		{
			delete values.w;
		}

		if (this.h.readOnly)
		{
			delete values.h;
		}

		return values;
	},

	setValues: function(values)
	{
		Object.each
		(
			values, function(value, key)
			{
				if (!this[key])
				{
					return;
				}

				if (key == 'no-upscale' || key == 'interlace' || key == 'lightbox')
				{
					this[key].set('checked', true);
				}
				else
				{
					this[key].set('value', value);
				}
			},

			this
		);
	},

	onChange: function(ev)
	{
		this.checkMethod();
		this.checkQuality();

		var values = this.getValues();

		this.fireEvent('change', { target: this.element, widget: this, values: values });
	}
});


Widget.AdjustThumbnail = new Class
({
	Implements: [ Events ],

	initialize: function(el, options)
	{
		this.element = $(el);

		this.thumbnailOptions = this.element.getElement('.widget-adjust-thumbnail-options').retrieve('widget');
		this.thumbnailOptions.addEvent('change', this.onChange.bind(this));

		this.image = this.element.getElement('.widget-adjust-image').retrieve('widget');
		this.image.addEvent('change', this.onChange.bind(this));

		this.element.getFirst('.more').addEvent
		(
			'click', function(ev)
			{
				this.element.toggleClass('unfold-thumbnail');
				this.fireEvent('adjust', { target: this.element, widget: this });
			}
			.bind(this)
		);
	},

	setValues: function(values)
	{
		var nid = values.get('data-nid');

		if (nid)
		{
			this.image.setSelected(nid);
		}

		var src = values.get('src');

		if (src && src.substring(0, 5) != 'data:')
		{
			var i = src.indexOf('?');

			if (i)
			{
				var options = src.substring(i + 1).parseQueryString();

				this.thumbnailOptions.setValues(options);
			}
		}
	},

	onChange: function(ev)
	{
		var image = this.image.selected;
		var options = this.thumbnailOptions.getValues();
		var nid = null;
		var url = null;

		if (image)
		{
			nid = image.get('data-nid');

			if ((options.w !== "" && options.h !== "") && (options.w || options.h))
			{
				url = '/api/resources.images/' + nid + '/thumbnail?' + Object.toQueryString(options);
			}
			else
			{
				url = image.get('data-path');
			}
		}

		this.fireEvent('change', { target: this.element, widget: this, url: url, nid: nid, image: image, options: options });
	}
});