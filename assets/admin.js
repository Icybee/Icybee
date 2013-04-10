window.addEvent('click:relay(.group-toggler input[type="checkbox"])',function(a,b){var c=b.getParent(".group-toggler");c[b.checked?"addClass":"removeClass"]("enabled")}),!function(){var a=null,b=null,c=null,d=null;ICanBoogie.XHR.NOTICE_DELAY=1e3,window.addEvent("icanboogie.xhr.shownotice",function(){a||(a=new Element("div.xhr-dummy"),c=new Element("div.xhr-message",{html:"Loadin..."}),b=new Fx.Tween(a,{property:"opacity",duration:"short",link:"cancel"}),b.set(0),d=new Fx.Tween(c,{property:"opacity",duration:"short",link:"cancel"}),d.set(0)),document.body.appendChild(a),document.body.appendChild(c),b.start(1),d.start(1)}),window.addEvent("icanboogie.xhr.hidenotice",function(){a&&a.getParent()&&(d.start(0),b.start(0).chain(function(){a.dispose(),c.dispose()}))})}(),window.addEvent("domready",function(){function c(){var c=document.html.scrollTop||document.body.scrollTop;a[c>b?"addClass":"removeClass"]("fixed")}var b,a=document.id(document.body).getElement(".actionbar");a&&(b=a.getPosition().y,window.addEvents({load:c,resize:c,scroll:c}),a.addEvent("click:relay([data-target])",function(a){var b=document.id(document.body).getElement(a.target.get("data-target"));b&&"FORM"==b.tagName&&b.submit()}))}),this.Icybee={Widget:{AdjustPopover:new Class({Extends:Brickrouge.Popover,Implements:[Options,Events],initialize:function(a,b){this.parent(a,b),this.adjust=null,this.selected=null},show:function(){this.parent(),this.adjust=this.element.getElement(".popover-content :first-child").get("widget"),this.adjust&&(this.adjust.addEvent("results",this.repositionCallback),this.adjust.addEvent("adjust",this.quickRepositionCallback))}})}},Brickrouge.Widget.Spinner=new Class({Implements:[Options,Events],initialize:function(a,b){this.element=a=document.id(a),this.setOptions(b),this.control=a.getElement("input"),this.content=a.getElement(".spinner-content"),this.popover=null,this.resetValue=null,this.resetContent=null,a.addEvent("click",function(a){a.stop(),this.open()}.bind(this))},open:function(){},setValue:function(a){if(this.content){var b=this.formatValue(a),c=typeOf(b);this.content.empty(),"element"==c||"elements"==c?this.content.adopt(b):"string"==c&&(this.content.innerHTML=b)}this.element[a?"removeClass":"addClass"]("placeholder"),this.control.set("value",this.encodeValue(a))},getValue:function(){return this.decodeValue(this.control.get("value"))},encodeValue:function(a){return a},decodeValue:function(a){return a},formatValue:function(a){return a},attachAdjust:function(){}});



;!function() {

	var PopupImage = new Class
	({
		initialize: function(el, src)
		{
			this.element = document.id(el)
			this.src = src

			this.element.addEvent('mouseenter', this.onMouseEnter.bind(this))
			this.element.addEvent('mouseleave', this.onMouseLeave.bind(this))

			this.onMouseMove = function(ev)
			{
				var popup = self.popup
				, target = ev.target

				if (!popup)
				{
					return
				}

				popup.setStyle('left', ev.client.x + target.getSize().x + 10)
			}
		},

		onMouseEnter: function()
		{
			this.cancel = false

			var func = this.popup ? this.show : this.load
			, delay = this.element.get('data-popover-delay') || 100

	//		window.addEvent('mousemove', this.onMouseMove);

			func.delay(delay, this)
		},

		load: function()
		{
			if (this.cancel || this.popup)
			{
				return
			}

			new Asset.image(this.src, {

				onload: function(popup)
				{
					var targetSelector = this.element.get('data-popover-target')
					, target = this.element

					if (targetSelector)
					{
						target = this.element.getParent(targetSelector) || target
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

					popup.set('tween', { duration: 'short', link: 'cancel' })
					popup.addEvent('mouseenter', this.onMouseLeave.bind(this))

					if (this.popup)
					{
						//console.info('kill multiple for: %s', this.src);

						popup.destroy()

						return
					}

					this.popup = popup

					//
					// show
					//

					this.show()
				}
				.bind(this)
			})
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
			var popup = this.popup

			if (!popup || !popup.parentNode)
			{
				return
			}

			this.popup = null

			popup.get('tween').start('opacity', 0).chain(function() {

				document.body.removeChild(popup)

				delete popup
			})
		}
	})

	, popovers = []

	document.body.addEvent('mouseenter:relay([data-popover-image])', function(ev, el) {

		var uniqueNumber = el.uniqueNumber
		, popover

		popovers[uniqueNumber]

		if (popovers[uniqueNumber]) return

		popover = new PopupImage(el, el.get('data-popover-image'))
		popover.load()

		popovers[uniqueNumber] = popover

	})

} ()

/*
 * drag and drop
 */

Object.append(Element.NativeEvents, {

	dragstart: 2, dragenter: 2, dragover: 2, dragleave: 2, dragend: 2, drop: 2

})

;['dragstart', 'dragenter', 'dragover', 'dragleave', 'dragend', 'drop' ].each(function(name) {

	document.html.addEvent(name, function(ev) {

		console.log(name + ':', ev)

	})
})

document.html.addEvent('dragenter', function(ev) {

	document.body.addClass('dragging')

})

document.html.addEvent('dragleave', function(ev) {

	document.body.removeClass('dragging')

})