var Brickrouge,require=IDependOnYou.require;define("brickrouge",()=>Brickrouge),String.prototype.shorten=function(e,t){void 0===e&&(e=32),void 0===t&&(t=.75);const n=this.length;return n<=e?this:(e--,0==(t=Math.round(t*e))?"…"+this.substring(n-e):t==e?this.substring(0,e)+"…":this.substring(0,t)+"…"+this.substring(n-(e-t)))},Request.API&&(Request.Element=new Class({Extends:Request.API,onSuccess:function(e,t){var n=Elements.from(e.rc).shift();e.assets?Brickrouge.updateAssets(e.assets,function(){this.fireEvent("complete",[e,t]).fireEvent("success",[n,e,t]).callChain()}.bind(this)):this.parent(n,e,t)}}),Request.Widget=new Class({Extends:Request.Element,initialize:function(e,t,n){void 0==n&&(n={}),n.url="widgets/"+e,n.onSuccess=t,this.parent(n)}})),document.body.addEvent('click:relay(.group-toggler input[type="checkbox"])',(e,t)=>{t.getParent(".group-toggler")[t.checked?"addClass":"removeClass"]("enabled")}),function(){var e=null,t=null,n=null,o=null;ICanBoogie.XHR.NOTICE_DELAY=1e3,window.addEvent("icanboogie.xhr.shownotice",function(){e||(e=new Element("div.xhr-dummy"),n=new Element("div.xhr-message"),(t=new Fx.Tween(e,{property:"opacity",duration:"short",link:"cancel"})).set(0),(o=new Fx.Tween(n,{property:"opacity",duration:"short",link:"cancel"})).set(0)),document.body.appendChild(e),document.body.appendChild(n),t.start(1),o.start(1)}),window.addEvent("icanboogie.xhr.hidenotice",function(){e&&e.getParent()&&(o.start(0),t.start(0).chain(function(){e.dispose(),n.dispose()}))})}(),function(e){e.register("action-bar",(e,t)=>new class{constructor(e){this.element=e,this.setUpAnchoring(),e.addDelegatedEventListener("[data-target]","click",e=>{const t=document.body.querySelector(e.target.getAttribute("data-target"));t&&"FORM"===t.tagName&&t.submit()})}toElement(){return this.element}setUpAnchoring(){const e=this.element;let t=e.getPosition().y;function n(){const n=document.html.scrollTop||document.body.scrollTop;e[t<n?"addClass":"removeClass"]("fixed")}window.addEvents({load:n,resize:n,scroll:n})}}(e,t))}(Brickrouge),define(["brickrouge"],e=>{e.observeUpdate(e=>{e.fragment.querySelectorAll("textarea.code").forEach(e=>{e.spellcheck&&(e.spellcheck=!1)})}),e.observeRunning(()=>{function e(e){const t=[],n=[],o={};e.querySelectorAll("[name]").forEach(e=>{if(e.disabled)return;const i=e.getAttribute("name"),s=e.value;t.push(i),n.push(s),o[i]=s});const i=t.slice(0),s={};i.sort();for(let e=0;e<i.length;e++){const t=i[e];s[t]=o[t]}return new Hash(s).toQueryString()}let t=!1;const n=document.body.querySelector(".form-primary");if(!n)return;let o=e(n);window.addEvent("load",()=>{o=e(n)}),window.onbeforeunload=(()=>{if(t)return t=!1,null;const i=e(n);return o==i?null:"Des changements ont été fait sur la page. Si vous changez de page maintenant, ils seront perdus."}),n.addEvent("submit",i=>{t=!0,o=e(n)})})}),function(e){var t=new Class({initialize:function(e,t){this.src=t,this.element=e,this.element.addEvents({mouseenter:this.onMouseEnter.bind(this),mouseleave:this.onMouseLeave.bind(this)})},onMouseEnter:function(){this.cancel=!1,(this.popover?this.show:this.load).delay(this.element.getAttribute("data-popover-delay")||100,this)},onMouseLeave:function(){this.cancel=!0,this.hide()},load:function(){this.cancel||this.popover||new Asset.image(this.src,{onload:function(e){var t,n=this.element.getAttribute("data-popover-target"),o=this.element;n&&(o=this.element.closest(n)||o),t=o.getCoordinates(),e.id="popover-image",e.setStyles({top:t.top+(t.height-e.height)/2-2,left:t.left+t.width+20,opacity:0}),e.set("tween",{duration:"short",link:"cancel"}),e.addEvent("mouseenter",this.onMouseLeave.bind(this)),e.width=e.naturalWidth,e.height=e.naturalHeight,this.popover?e.destroy():(this.popover=e,this.show())}.bind(this)})},show:function(){if(!this.cancel){var e=this.popover;document.body.appendChild(e),e.fade("in")}},hide:function(){var e=this.popover;e&&e.parentNode&&(this.popover=null,e.get("tween").start("opacity",0).chain(function(){document.body.removeChild(e)}))}}),n=[];document.body.addEvent("mouseenter:relay([data-popover-image])",function(o,i){var s,r=e.uidOf(i);n[r]||((s=new t(i,i.getAttribute("data-popover-image"))).load(),n[r]=s)})}(Brickrouge),Brickrouge.observeRunning(e=>{const t=document.body.querySelector(".actionbar .record-save-mode"),n=document.body.querySelector(".form-actions .save-mode"),o=n?n.querySelectorAll('input[type="radio"]'):[],i=document.body.querySelector(".form-actions .btn-primary");t&&o.length&&(t.addEventListener("click",e=>{const t=e.target,n=t.getAttribute("data-key");if(t.match(".btn-primary:first-child"))return e.preventDefault(),void i.click();n&&(e.preventDefault(),Array.prototype.forEach.call(o,e=>{e.checked=e.value===n}),i.click())}),n.addDelegatedEventListener('[type="radio"]',"click",(e,n)=>{const o=n.value;t.querySelectorAll(".dropdown-item").forEach(e=>{const n=e.getAttribute("data-key");o===n?(e.classList.add("active"),t.querySelector(".btn").innerHTML=e.innerHTML):e.classList.remove("active")})}))}),define("icybee/spinner",["brickrouge"],e=>(class{constructor(e,t){this.element=e,this.options=t,this.control=e.querySelector("input"),this.content=e.querySelector(".spinner-content"),this.popover=null,this.resetValue=null,this.listenToClick()}listenToClick(){this.element.addEventListener("click",e=>{e.preventDefault(),this.open()})}set value(t){if(this.content){const n=this.formatValue(t),o=typeOf(n);e.empty(this.content),"element"==o||"elements"==o?this.content.adopt(n):"string"==o&&(this.content.innerHTML=n)}this.element[t?"removeClass":"addClass"]("placeholder"),this.control.value=this.encodeValue(t)}get value(){return this.decodeValue(this.control.value)}encodeValue(e){return e}decodeValue(e){return e}formatValue(e){return e}open(){this.resetValue=this.value,this.popover?(this.popover.adjust.value=this.resetValue,this.popover.show()):this.createPopover(e=>{this.popover=e,e.show(),e.observeAction(e=>this.action(e.action)),e.adjust.observeChange(e=>this.change(e.value))})}close(){this.popover&&this.popover.hide()}createPopover(e){throw new Error("The method must be implemented by sub-classes.")}change(e){this.value=e,this.popover&&this.popover.reposition()}action(e){switch(e){case"cancel":this.reset();break;case"remove":this.remove();break;case"use":this.use(this.popover.value)}this.close()}reset(){this.value=this.resetValue}remove(){this.value=null}use(e){this.value=e}})),function(e){let t;e.register("spinner",(e,n)=>(t||(t=require("icybee/spinner")),new t(e,n)))}(Brickrouge),define("icybee/adjust-popover",["brickrouge"],e=>{const t=e.Subject,n=t.createEvent(function(e){this.value=e}),o=t.createEvent(function(){});return class extends e.Popover{static get UpdateEvent(){return n}static get LayoutEvent(){return o}get adjust(){return e.from(this.element.querySelector(".popover-content :first-child"))}get value(){return this.adjust.value}set value(e){this.adjust.value=e}show(){super.show();const e=this.adjust;if(e)try{e.observeLayout(()=>{this.repositionCallback(),this.notify(new o(this))}),e.observeChange(e=>{this.quickRepositionCallback(),this.notify(new n(e.value))})}catch(e){console.error(e)}}observeUpdate(e){this.observe(n,e)}observeLayout(e){this.observe(o,e)}}}),define("icybee/adjust",["brickrouge"],e=>{const t=e.Subject,n=t.createEvent(function(e,t){this.target=e,this.value=t}),o=t.createEvent(function(e){this.target=e});return class extends(e.mixin(Object,t)){static get ChangeEvent(){return n}static get LayoutEvent(){return o}constructor(e,t){super(),this.element=e,this.options=t}observeChange(e){this.observe(n,e)}observeLayout(e){this.observe(o,e)}}}),function(e){let t=window.devicePixelRatio;const n=[];function o(o){const i=o.getAttribute("width"),s=o.getAttribute("height"),r=Math.round(i*t),a=Math.round(s*t);if(!i&&!s)return;if(i&&r==o.naturalWidth||s&&a==o.naturalHeight)return;const c=function(t){const o=e.uidOf(t);if(o in n)return n[o];const i=n[o]=document.createElement("img");return i.onload=(e=>t.src=i.src),i}(o),u=o.src.replace(/(\\?|&)(device-pixel-ratio|dpr)=[^&]+/,"");c.src=u+(-1===u.indexOf("?")?"?":"&")+"dpr="+t}function i(e){Array.prototype.forEach.call(e.querySelectorAll("img"),o)}window.addEventListener("load",n=>{e.observeUpdate(e=>{const t=e.fragment;"IMG"===t.tagName?o(t):i(t)}),setInterval(e=>{t!==window.devicePixelRatio&&(t=window.devicePixelRatio,i(document.body))},1e3),i(document.body)})}(Brickrouge);