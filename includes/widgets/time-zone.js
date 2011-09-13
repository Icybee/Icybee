/**
 *
 */

Widget.TimeZone = new Class({

	Implements: [ Options ],

	options: {

		offsets: null
	},

	initialize: function(el, options) {

		this.element = $(el);
		this.offsetSelectElement = this.element.getElement('select');
		this.zoneSelectElement = new Element('select', { 'class': 'zones', name: this.offsetSelectElement.get('name'), styles: { 'margin-left': '1ex', display: 'none' }});
		this.setOptions(options);

		if (typeOf(this.options.offsets) == 'string') {

			this.options.offsets = JSON.decode(this.options.offsets);
		}

		this.offsetSelectElement.addEvent('change', this.updateZones.bind(this));

		this.zoneSelectElement.inject(this.element);

		this.updateZones();
	},

	updateZones: function() {

		var offset = this.offsetSelectElement.get('value');
		var select = this.zoneSelectElement;
		var zone = this.options.zone;

		select.empty();

		if (offset) {

			this.options.offsets[offset].each(function(name) {

				var option = new Element('option', { text: name, selected: name == zone });

				option.inject(select);
			});

			select.setStyle('display', '');
		}
		else {

			select.setStyle('display', 'none');
		}
	}
});