/* @license GPL-2.0-or-later https://www.drupal.org/licensing/faq */
(function($,Drupal,once){Drupal.behaviors.environmentIndicatorSwitcher={attach(context){const indicators=once('environmentIndicatorSwitcher','#environment-indicator',context);if(indicators.length===0)return;indicators.forEach((el)=>{$(el).on('click',()=>{$('.environment-switcher-container',context).slideToggle('fast');});});}};Drupal.behaviors.environmentIndicatorToolbar={attach(context,settings){if(settings.environmentIndicator!==undefined){const $body=$('body');const borderWidth=getComputedStyle(document.body).getPropertyValue('--environment-indicator-border-width')||'6px';if(settings.environmentIndicator.toolbars===true&&!$body.hasClass('gin--vertical-toolbar')&&!$body.hasClass('gin--horizontal-toolbar')){document.querySelector('#toolbar-bar').style.backgroundColor=settings.environmentIndicator.bgColor;document.querySelectorAll('#toolbar-bar .toolbar-item a:not(.is-active)').forEach((el)=>{el.style.borderBottom='0px';el.style.color=settings.environmentIndicator.fgColor;});document.querySelectorAll('.toolbar .toolbar-bar .toolbar-tab > .toolbar-item').forEach((el)=>{el.style.backgroundColor=settings.environmentIndicator.bgColor;});}if($body.hasClass('gin--vertical-toolbar')){document.querySelector('.toolbar-menu-administration').style.borderLeftColor=settings.environmentIndicator.bgColor;document.querySelector('.toolbar-menu-administration').style.borderLeftWidth=borderWidth;document.querySelectorAll('.toolbar-tray-horizontal .toolbar-menu li.menu-item').forEach((el)=>{el.style.marginLeft='calc(var(--environment-indicator-border-width) * -0.5)';return el;});}if($body.hasClass('gin--horizontal-toolbar')){document.querySelector('#toolbar-item-administration-tray').style.borderTopColor=settings.environmentIndicator.bgColor;document.querySelector('#toolbar-item-administration-tray').style.borderTopWidth=borderWidth;}if($body.hasClass('gin--horizontal-toolbar')||$body.hasClass('gin--vertical-toolbar'))$('head',context).append(`<style>.toolbar .toolbar-bar #toolbar-item-administration-tray a.toolbar-icon-admin-toolbar-tools-help.toolbar-icon-default::before{ background-color: ${settings.environmentIndicator.bgColor} }</style>`);}}};})(jQuery,Drupal,once);;
/*!
 * Tinycon - A small library for manipulating the Favicon
 * Tom Moor, http://tommoor.com
 * Copyright (c) 2015 Tom Moor
 * @license MIT Licensed
 */
!function () { var a = {}, b = null, c = null, d = null, e = null, f = {}, g = Math.ceil(window.devicePixelRatio) || 1, h = 16 * g, i = { width: 7, height: 9, font: 10 * g + "px arial", color: "#ffffff", background: "#F03D25", fallback: !0, crossOrigin: !0, abbreviate: !0 }, j = function () { var a = navigator.userAgent.toLowerCase(); return function (b) { return a.indexOf(b) !== -1 } }(), k = { ie: j("trident"), chrome: j("chrome"), webkit: j("chrome") || j("safari"), safari: j("safari") && !j("chrome"), mozilla: j("mozilla") && !j("chrome") && !j("safari") }, l = function () { for (var a = document.getElementsByTagName("link"), b = 0, c = a.length; b < c; b++)if ((a[b].getAttribute("rel") || "").match(/\bicon\b/i)) return a[b]; return !1 }, m = function () { for (var a = document.getElementsByTagName("link"), b = 0, c = a.length; b < c; b++) { void 0 !== a[b] && (a[b].getAttribute("rel") || "").match(/\bicon\b/i) && a[b].parentNode.removeChild(a[b]) } }, n = function () { if (!c || !b) { var a = l(); b = a ? a.getAttribute("href") : "/favicon.ico", c || (c = b) } return b }, o = function () { return e || (e = document.createElement("canvas"), e.width = h, e.height = h), e }, p = function (a) { if (a) { m(); var b = document.createElement("link"); b.type = "image/x-icon", b.rel = "icon", b.href = a, document.getElementsByTagName("head")[0].appendChild(b) } }, q = function (a, b) { if (!o().getContext || k.ie || k.safari || "force" === f.fallback) return r(a); var c = o().getContext("2d"), b = b || "#000000", e = n(); d = document.createElement("img"), d.onload = function () { c.clearRect(0, 0, h, h), c.drawImage(d, 0, 0, d.width, d.height, 0, 0, h, h), (a + "").length > 0 && s(c, a, b), t() }, !e.match(/^data/) && f.crossOrigin && (d.crossOrigin = "anonymous"), d.src = e }, r = function (a) { if (f.fallback) { var b = document.title; "(" === b[0] && (b = b.slice(b.indexOf(" "))), (a + "").length > 0 ? document.title = "(" + a + ") " + b : document.title = b } }, s = function (a, b, c) { "number" == typeof b && b > 99 && f.abbreviate && (b = u(b)); var d = (b + "").length - 1, e = f.width * g + 6 * g * d, i = f.height * g, j = h - i, l = h - e - g, m = 16 * g, n = 16 * g, o = 2 * g; a.font = (k.webkit ? "bold " : "") + f.font, a.fillStyle = f.background, a.strokeStyle = f.background, a.lineWidth = g, a.beginPath(), a.moveTo(l + o, j), a.quadraticCurveTo(l, j, l, j + o), a.lineTo(l, m - o), a.quadraticCurveTo(l, m, l + o, m), a.lineTo(n - o, m), a.quadraticCurveTo(n, m, n, m - o), a.lineTo(n, j + o), a.quadraticCurveTo(n, j, n - o, j), a.closePath(), a.fill(), a.beginPath(), a.strokeStyle = "rgba(0,0,0,0.3)", a.moveTo(l + o / 2, m), a.lineTo(n - o / 2, m), a.stroke(), a.fillStyle = f.color, a.textAlign = "right", a.textBaseline = "top", a.fillText(b, 2 === g ? 29 : 15, k.mozilla ? 7 * g : 6 * g) }, t = function () { o().getContext && p(o().toDataURL()) }, u = function (a) { for (var b = [["G", 1e9], ["M", 1e6], ["k", 1e3]], c = 0; c < b.length; ++c)if (a >= b[c][1]) { a = v(a / b[c][1]) + b[c][0]; break } return a }, v = function (a, b) { return new Number(a).toFixed(b) }; a.setOptions = function (a) { f = {}, a.colour && (a.color = a.colour); for (var b in i) f[b] = a.hasOwnProperty(b) ? a[b] : i[b]; return this }, a.setImage = function (a) { return b = a, t(), this }, a.setBubble = function (a, b) { return a = a || "", q(a, b), this }, a.reset = function () { b = c, p(c) }, a.setOptions(i), "function" == typeof define && define.amd ? define(a) : "undefined" != typeof module ? module.exports = a : window.Tinycon = a }();
;
(function($,Drupal,once){Drupal.behaviors.environmentIndicatorTinycon={attach(context,settings){$(once('env-ind-tinycon','html',context)).each(function(){if(settings.environmentIndicator!==undefined&&settings.environmentIndicator.addFavicon!==undefined&&settings.environmentIndicator.addFavicon)if(typeof Tinycon!=='undefined'){Tinycon.setBubble(settings.environmentIndicator.name.slice(0,1).trim());Tinycon.setOptions({background:settings.environmentIndicator.bgColor,colour:settings.environmentIndicator.fgColor});}else console.warn('Tinycon is not available.');});}};})(jQuery,Drupal,once);;
(function($,Drupal){Drupal.behaviors.filterGuidelines={attach(context){function updateFilterGuidelines(event){const $this=$(event.target);const {value}=event.target;$this.closest('.js-filter-wrapper').find('[data-drupal-format-id]').hide().filter(`[data-drupal-format-id="${value}"]`).show();}$(once('filter-guidelines','.js-filter-guidelines',context)).find(':header').hide().closest('.js-filter-wrapper').find('select.js-filter-list').on('change.filterGuidelines',updateFilterGuidelines).trigger('change.filterGuidelines');}};})(jQuery,Drupal);;
;(function($){'use strict';$.fn.fitVids=function(options){var settings={customSelector:null,ignore:null};if(!document.getElementById('fit-vids-style')){var head=document.head||document.getElementsByTagName('head')[0];var css='.fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}';var div=document.createElement("div");div.innerHTML='<p>x</p><style id="fit-vids-style">'+css+'</style>';head.appendChild(div.childNodes[1]);}if(options)$.extend(settings,options);return this.each(function(){var selectors=['iframe[src*="player.vimeo.com"]','iframe[src*="youtube.com"]','iframe[src*="youtube-nocookie.com"]','iframe[src*="kickstarter.com"][src*="video.html"]','object','embed'];if(settings.customSelector)selectors.push(settings.customSelector);var ignoreList='.fitvidsignore';if(settings.ignore)ignoreList=ignoreList+', '+settings.ignore;var $allVideos=$(this).find(selectors.join(','));$allVideos=$allVideos.not('object object');$allVideos=$allVideos.not(ignoreList);$allVideos.each(function(){var $this=$(this);if($this.parents(ignoreList).length>0)return;if(this.tagName.toLowerCase()==='embed'&&$this.parent('object').length||$this.parent('.fluid-width-video-wrapper').length)return;if((!$this.css('height')&&!$this.css('width'))&&(isNaN($this.attr('height'))||isNaN($this.attr('width')))){$this.attr('height',9);$this.attr('width',16);}var height=(this.tagName.toLowerCase()==='object'||($this.attr('height')&&!isNaN(parseInt($this.attr('height'),10))))?parseInt($this.attr('height'),10):$this.height(),width=!isNaN(parseInt($this.attr('width'),10))?parseInt($this.attr('width'),10):$this.width(),aspectRatio=height/width;if(!$this.attr('name')){var videoName='fitvid'+$.fn.fitVids._count;$this.attr('name',videoName);$.fn.fitVids._count++;}$this.wrap('<div class="fluid-width-video-wrapper"></div>').parent('.fluid-width-video-wrapper').css('padding-top',(aspectRatio*100)+'%');$this.removeAttr('height').removeAttr('width');});});};$.fn.fitVids._count=0;})(window.jQuery||window.Zepto);;
(function($,Drupal,drupalSettings){try{$(drupalSettings.fitvids.selectors).fitVids({customSelector:drupalSettings.fitvids.custom_vendors,ignore:drupalSettings.fitvids.ignore_selectors});}catch(e){window.console&&console.warn('Fitvids stopped with the following exception');window.console&&console.error(e);}})(jQuery,Drupal,drupalSettings);;
((Drupal,drupalSettings,once)=>{Drupal.behaviors.ginAccent={attach:function(context){once("ginAccent","body",context).forEach((()=>{Drupal.ginAccent.checkDarkmode(),Drupal.ginAccent.setAccentColor(),Drupal.ginAccent.setFocusColor();}));}},Drupal.ginAccent={setAccentColor:function(){let preset=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,color=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;const accentColorPreset=null!=preset?preset:drupalSettings.gin.preset_accent_color;document.body.setAttribute("data-gin-accent",accentColorPreset),"custom"===accentColorPreset&&this.setCustomAccentColor(color);},setCustomAccentColor:function(){let color=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,element=arguments.length>1&&void 0!==arguments[1]?arguments[1]:document.body;const accentColor=null!=color?color:drupalSettings.gin.accent_color;if(accentColor){this.clearAccentColor(element);const strippedAccentColor=accentColor.replace("#",""),darkAccentColor=this.mixColor("ffffff",strippedAccentColor,65).replace("#",""),style=document.createElement("style");style.className="gin-custom-colors",style.innerHTML=`\n          [data-gin-accent="custom"] {\n            --gin-color-primary-rgb: ${this.hexToRgb(accentColor)};\n            --gin-color-primary-hover: ${this.shadeColor(accentColor,-10)};\n            --gin-color-primary-active: ${this.shadeColor(accentColor,-15)};\n            --gin-bg-app-rgb: ${this.hexToRgb(this.mixColor("ffffff",strippedAccentColor,97))};\n            --gin-bg-header: ${this.mixColor("ffffff",strippedAccentColor,85)};\n            --gin-color-sticky-rgb: ${this.hexToRgb(this.mixColor("ffffff",strippedAccentColor,92))};\n          }\n          .gin--dark-mode[data-gin-accent="custom"],\n          .gin--dark-mode [data-gin-accent="custom"] {\n            --gin-color-primary-rgb: ${this.hexToRgb(darkAccentColor)};\n            --gin-color-primary-hover: ${this.mixColor("ffffff",strippedAccentColor,55)};\n            --gin-color-primary-active: ${this.mixColor("ffffff",strippedAccentColor,50)};\n            --gin-bg-header: ${this.mixColor("2A2A2D",darkAccentColor,88)};\n          }\n        `,element.append(style);}},clearAccentColor:function(){let element=arguments.length>0&&void 0!==arguments[0]?arguments[0]:document.body;if(element.querySelectorAll(".gin-custom-colors").length>0){const removeElement=element.querySelector(".gin-custom-colors");removeElement.parentNode.removeChild(removeElement);}},setFocusColor:function(){let preset=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,color=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;const focusColorPreset=null!=preset?preset:drupalSettings.gin.preset_focus_color;document.body.setAttribute("data-gin-focus",focusColorPreset),"custom"===focusColorPreset&&this.setCustomFocusColor(color);},setCustomFocusColor:function(){let color=arguments.length>0&&void 0!==arguments[0]?arguments[0]:null,element=arguments.length>1&&void 0!==arguments[1]?arguments[1]:document.body;const accentColor=null!=color?color:drupalSettings.gin.focus_color;if(accentColor){this.clearFocusColor(element);const strippedAccentColor=accentColor.replace("#",""),darkAccentColor=this.mixColor("ffffff",strippedAccentColor,65),style=document.createElement("style");style.className="gin-custom-focus",style.innerHTML=`\n          [data-gin-focus="custom"] {\n            --gin-color-focus: ${accentColor};\n          }\n          .gin--dark-mode[data-gin-focus="custom"],\n          .gin--dark-mode [data-gin-focus="custom"] {\n            --gin-color-focus: ${darkAccentColor};\n          }`,element.append(style);}},clearFocusColor:function(){let element=arguments.length>0&&void 0!==arguments[0]?arguments[0]:document.body;if(element.querySelectorAll(".gin-custom-focus").length>0){const removeElement=element.querySelector(".gin-custom-focus");removeElement.parentNode.removeChild(removeElement);}},checkDarkmode:()=>{const darkmodeClass=drupalSettings.gin.darkmode_class;window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change",((e)=>{e.matches&&"auto"===window.ginDarkmode&&document.querySelector("html").classList.add(darkmodeClass);})),window.matchMedia("(prefers-color-scheme: light)").addEventListener("change",((e)=>{e.matches&&"auto"===window.ginDarkmode&&document.querySelector("html").classList.remove(darkmodeClass);}));},hexToRgb:(hex)=>{hex=hex.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i,(function(m,r,g,b){return r+r+g+g+b+b;}));var result=/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);return result?`${parseInt(result[1],16)}, ${parseInt(result[2],16)}, ${parseInt(result[3],16)}`:null;},mixColor:(color_1,color_2,weight)=>{function h2d(h){return parseInt(h,16);}weight=void 0!==weight?weight:50;for(var color="#",i=0;i<=5;i+=2){for(var v1=h2d(color_1.substr(i,2)),v2=h2d(color_2.substr(i,2)),val=Math.floor(v2+weight/100*(v1-v2)).toString(16);val.length<2;)val="0"+val;color+=val;}return color;},shadeColor:(color,percent)=>{const num=parseInt(color.replace("#",""),16),amt=Math.round(2.55*percent),R=(num>>16)+amt,B=(num>>8&255)+amt,G=(255&num)+amt;return `#${(16777216+65536*(R<255?R<1?0:R:255)+256*(B<255?B<1?0:B:255)+(G<255?G<1?0:G:255)).toString(16).slice(1)}`;}};})(Drupal,drupalSettings,once);;
((Drupal,drupalSettings,once)=>{Drupal.behaviors.ginEscapeAdmin={attach:(context)=>{once("ginEscapeAdmin","[data-gin-toolbar-escape-admin]",context).forEach(((el)=>{const escapeAdminPath=sessionStorage.getItem("escapeAdminPath");drupalSettings.path.currentPathIsAdmin&&null!==escapeAdminPath&&el.setAttribute("href",escapeAdminPath);}));}};})(Drupal,drupalSettings,once);;
((Drupal,drupalSettings,once)=>{const toolbarVariant=drupalSettings.gin.toolbar_variant;Drupal.behaviors.ginToolbar={attach:(context)=>{Drupal.ginToolbar.init(context),Drupal.ginToolbar.initKeyboardShortcut(context);}},Drupal.ginToolbar={init:function(context){once("ginToolbarInit","#gin-toolbar-bar",context).forEach((()=>{const toolbarTrigger=document.querySelector(".toolbar-menu__trigger");"classic"!=toolbarVariant&&localStorage.getItem("Drupal.toolbar.trayVerticalLocked")&&localStorage.removeItem("Drupal.toolbar.trayVerticalLocked"),"true"===localStorage.getItem("Drupal.gin.toolbarExpanded")?(document.body.setAttribute("data-toolbar-menu","open"),toolbarTrigger.classList.add("is-active")):(document.body.setAttribute("data-toolbar-menu",""),toolbarTrigger.classList.remove("is-active")),this.initDisplace();})),once("ginToolbarToggle",".toolbar-menu__trigger",context).forEach(((el)=>el.addEventListener("click",((e)=>{e.preventDefault(),this.toggleToolbar();}))));},initKeyboardShortcut:function(context){once("ginToolbarKeyboardShortcutInit",".toolbar-menu__trigger, .admin-toolbar__expand-button",context).forEach((()=>{document.addEventListener("keydown",((e)=>{!0===e.altKey&&"KeyT"===e.code&&this.toggleToolbar();}));}));},initDisplace:()=>{const toolbar=document.querySelector("#gin-toolbar-bar .toolbar-menu-administration");toolbar&&("vertical"===toolbarVariant?toolbar.setAttribute("data-offset-left",""):toolbar.setAttribute("data-offset-top",""));},toggleToolbar:function(){const toolbarTrigger=document.querySelector(".toolbar-menu__trigger");toolbarTrigger.classList.toggle("is-active"),toolbarTrigger.classList.contains("is-active")?this.showToolbar():this.collapseToolbar();},showToolbar:function(){document.body.setAttribute("data-toolbar-menu","open"),localStorage.setItem("Drupal.gin.toolbarExpanded","true"),this.dispatchToolbarEvent("true"),this.displaceToolbar(),window.innerWidth<1280&&"vertical"===toolbarVariant&&Drupal.ginSidebar.collapseSidebar();},collapseToolbar:function(){const toolbarTrigger=document.querySelector(".toolbar-menu__trigger"),elementToRemove=document.querySelector(".gin-toolbar-inline-styles");toolbarTrigger.classList.remove("is-active"),document.body.setAttribute("data-toolbar-menu",""),elementToRemove&&elementToRemove.parentNode.removeChild(elementToRemove),localStorage.setItem("Drupal.gin.toolbarExpanded","false"),this.dispatchToolbarEvent("false"),this.displaceToolbar();},dispatchToolbarEvent:(active)=>{const event=new CustomEvent("toolbar-toggle",{detail:"true"===active});document.dispatchEvent(event);},displaceToolbar:()=>{ontransitionend=()=>{Drupal.displace(!0);};}};})(Drupal,drupalSettings,once);;
((Drupal,once)=>{Drupal.behaviors.ginFormActions={attach:(context)=>{Drupal.ginStickyFormActions.init(context);}},Drupal.ginStickyFormActions={init:function(context){const newParent=document.querySelector(".gin-sticky-form-actions");newParent&&(context.classList?.contains("gin--has-sticky-form-actions")&&context.getAttribute("id")&&this.updateFormId(newParent,context),once("ginEditForm",".region-content form.gin--has-sticky-form-actions",context).forEach(((form)=>{this.updateFormId(newParent,form),this.moveFocus(newParent,form);})),once("ginMoreActionsToggle",".gin-more-actions__trigger",context).forEach(((el)=>el.addEventListener("click",((e)=>{e.preventDefault(),this.toggleMoreActions(),document.addEventListener("click",this.closeMoreActionsOnClickOutside,!1);})))));},updateFormId:function(newParent,form){const formActions=form.querySelector('[data-drupal-selector="edit-actions"]'),actionButtons=Array.from(formActions.children);if(actionButtons.length>0){const formId=form.getAttribute("id");once("ginSyncActionButtons-"+formId,actionButtons).forEach(((el)=>{const formElement=el.dataset.drupalSelector,buttonDrupalDataSelector=el.getAttribute("data-drupal-selector"),buttonSelector=newParent.querySelector(`[data-drupal-selector="gin-sticky-${formElement}"]`);buttonSelector&&(buttonSelector.removeEventListener("click",this.actionButtonEventListener),buttonSelector.setAttribute("form",formId),buttonSelector.setAttribute("data-gin-sticky-form-selector",buttonDrupalDataSelector),buttonSelector.addEventListener("click",this.actionButtonEventListener));}));}},actionButtonEventListener:function(e){const stickyButton=e.currentTarget,buttonDrupalDataSelector=stickyButton.getAttribute("data-gin-sticky-form-selector"),formId=stickyButton.getAttribute("form"),button=document.querySelector(`#${formId} [data-drupal-selector="${buttonDrupalDataSelector}"]`);null!==button&&(e.preventDefault(),once.filter("drupal-ajax",button).length&&button.dispatchEvent(new Event("mousedown")),button.click());},moveFocus:function(newParent,form){once("ginMoveFocusToStickyBar","[gin-move-focus-to-sticky-bar]",form).forEach(((el)=>el.addEventListener("focus",((e)=>{e.preventDefault(),newParent.querySelector(["button, input, select, textarea, .action-link"]).focus();let element=document.createElement("div");element.style.display="contents",element.innerHTML='<a href="#" class="visually-hidden" role="button" gin-move-focus-to-end-of-form>Moves focus back to form</a>',newParent.appendChild(element),document.querySelector("[gin-move-focus-to-end-of-form]").addEventListener("focus",((eof)=>{eof.preventDefault(),element.remove(),e.target.nextElementSibling?e.target.nextElementSibling.focus():e.target.parentNode.nextElementSibling&&e.target.parentNode.nextElementSibling.focus();}));}))));},toggleMoreActions:function(){document.querySelector(".gin-more-actions__trigger").classList.contains("is-active")?this.hideMoreActions():this.showMoreActions();},showMoreActions:function(){const trigger=document.querySelector(".gin-more-actions__trigger");null!==trigger&&(trigger.setAttribute("aria-expanded","true"),trigger.classList.add("is-active"));},hideMoreActions:function(){const trigger=document.querySelector(".gin-more-actions__trigger");null!==trigger&&(trigger.setAttribute("aria-expanded","false"),trigger.classList.remove("is-active"),document.removeEventListener("click",this.closeMoreActionsOnClickOutside));},closeMoreActionsOnClickOutside:function(e){const trigger=document.querySelector(".gin-more-actions__trigger");null!==trigger&&"false"!==trigger.getAttribute("aria-expanded")&&(e.target.closest(".gin-more-actions")||Drupal.ginStickyFormActions.hideMoreActions());}};})(Drupal,once);;
({"./js/sidebar.js":function(){((Drupal,drupalSettings,once)=>{const toolbarVariant=drupalSettings.gin.toolbar_variant,storageDesktop="Drupal.gin.sidebarExpanded.desktop",resizer=document.getElementById("gin-sidebar-draggable"),resizable=document.getElementById("gin_sidebar");let startX,startWidth,isResizing=!1;Drupal.behaviors.ginSidebar={attach:function(context){Drupal.ginSidebar.init(context);}},Drupal.ginSidebar={init:function(context){once("ginSidebarInit","#gin_sidebar",context).forEach((()=>{localStorage.getItem(storageDesktop)||localStorage.setItem(storageDesktop,"true"),window.innerWidth>=1024&&("true"===localStorage.getItem(storageDesktop)?this.showSidebar():this.collapseSidebar()),document.addEventListener("keydown",((e)=>{!0===e.altKey&&"KeyS"===e.code&&this.toggleSidebar();})),new ResizeObserver(((entries)=>{for(let entry of entries)Drupal.debounce(this.handleResize(entry.contentRect),150);})).observe(document.querySelector("html")),this.resizeInit();})),once("ginSidebarToggle",".meta-sidebar__trigger",context).forEach(((el)=>el.addEventListener("click",((e)=>{e.preventDefault(),this.removeInlineStyles(),this.toggleSidebar();})))),once("ginSidebarClose",".meta-sidebar__close, .meta-sidebar__overlay",context).forEach(((el)=>el.addEventListener("click",((e)=>{e.preventDefault(),this.removeInlineStyles(),this.collapseSidebar();}))));},toggleSidebar:()=>{document.querySelector(".meta-sidebar__trigger").classList.contains("is-active")?(Drupal.ginSidebar.collapseSidebar(),Drupal.ginStickyFormActions?.hideMoreActions()):(Drupal.ginSidebar.showSidebar(),Drupal.ginStickyFormActions?.hideMoreActions());},showSidebar:function(){const chooseStorage=(arguments.length>0&&void 0!==arguments[0]?arguments[0]:window.innerWidth)<1024?"Drupal.gin.sidebarExpanded.mobile":storageDesktop,hideLabel=Drupal.t("Hide sidebar panel"),sidebarTrigger=document.querySelector(".meta-sidebar__trigger");null!==sidebarTrigger&&(sidebarTrigger.querySelector("span").innerHTML=hideLabel,sidebarTrigger.setAttribute("title",hideLabel),sidebarTrigger.nextSibling&&(sidebarTrigger.nextSibling.innerHTML=hideLabel),sidebarTrigger.setAttribute("aria-expanded","true"),sidebarTrigger.classList.add("is-active"),document.body.setAttribute("data-meta-sidebar","open"),localStorage.setItem(chooseStorage,"true"),window.innerWidth<1280&&(Drupal.ginCoreNavigation?.collapseToolbar(),"vertical"===toolbarVariant?Drupal.ginToolbar.collapseToolbar():"new"===toolbarVariant&&Drupal.behaviors.ginNavigation?.collapseSidebar()));},collapseSidebar:function(){const chooseStorage=(arguments.length>0&&void 0!==arguments[0]?arguments[0]:window.innerWidth)<1024?"Drupal.gin.sidebarExpanded.mobile":storageDesktop,showLabel=Drupal.t("Show sidebar panel"),sidebarTrigger=document.querySelector(".meta-sidebar__trigger");null!==sidebarTrigger&&(sidebarTrigger.querySelector("span").innerHTML=showLabel,sidebarTrigger.setAttribute("title",showLabel),sidebarTrigger.nextSibling&&(sidebarTrigger.nextSibling.innerHTML=showLabel),sidebarTrigger.setAttribute("aria-expanded","false"),sidebarTrigger.classList.remove("is-active"),document.body.setAttribute("data-meta-sidebar","closed"),localStorage.setItem(chooseStorage,"false"));},handleResize:function(){let windowSize=arguments.length>0&&void 0!==arguments[0]?arguments[0]:window;Drupal.ginSidebar.removeInlineStyles(),windowSize.width<1024?Drupal.ginSidebar.collapseSidebar(windowSize.width):"true"===localStorage.getItem(storageDesktop)?Drupal.ginSidebar.showSidebar(windowSize.width):Drupal.ginSidebar.collapseSidebar(windowSize.width);},removeInlineStyles:()=>{const elementToRemove=document.querySelector(".gin-sidebar-inline-styles");elementToRemove&&elementToRemove.parentNode.removeChild(elementToRemove);},resizeInit:function(){resizer.addEventListener("mousedown",this.resizeStart),document.addEventListener("mousemove",this.resizeWidth),document.addEventListener("mouseup",this.resizeEnd),resizer.addEventListener("touchstart",this.resizeStart),document.addEventListener("touchmove",this.resizeWidth),document.addEventListener("touchend",this.resizeEnd);},resizeStart:(e)=>{e.preventDefault(),isResizing=!0,startX=e.clientX,startWidth=parseInt(document.defaultView.getComputedStyle(resizable).width,10);},resizeEnd:()=>{isResizing=!1;const setWidth=document.documentElement.style.getPropertyValue("--gin-sidebar-width"),currentWidth=setWidth||resizable.style.width;localStorage.setItem("Drupal.gin.sidebarWidth",currentWidth),document.removeEventListener("mousemove",this.resizeWidth),document.removeEventListener("touchend",this.resizeWidth);},resizeWidth:(e)=>{if(isResizing){let sidebarWidth=startWidth-(e.clientX-startX);sidebarWidth<=240?sidebarWidth=240:sidebarWidth>=560&&(sidebarWidth=560),sidebarWidth=`${sidebarWidth}px`,document.documentElement.style.setProperty("--gin-sidebar-width",sidebarWidth);}}};})(Drupal,drupalSettings,once);}})["./js/sidebar.js"]();;
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
    typeof define === 'function' && define.amd ? define(['exports'], factory) :
      (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.FloatingUICore = {}));
})(this, (function (exports) { 'use strict';

  function getAlignment(placement) {
    return placement.split('-')[1];
  }

  function getLengthFromAxis(axis) {
    return axis === 'y' ? 'height' : 'width';
  }

  function getSide(placement) {
    return placement.split('-')[0];
  }

  function getMainAxisFromPlacement(placement) {
    return ['top', 'bottom'].includes(getSide(placement)) ? 'x' : 'y';
  }

  function computeCoordsFromPlacement(_ref, placement, rtl) {
    let {
      reference,
      floating
    } = _ref;
    const commonX = reference.x + reference.width / 2 - floating.width / 2;
    const commonY = reference.y + reference.height / 2 - floating.height / 2;
    const mainAxis = getMainAxisFromPlacement(placement);
    const length = getLengthFromAxis(mainAxis);
    const commonAlign = reference[length] / 2 - floating[length] / 2;
    const side = getSide(placement);
    const isVertical = mainAxis === 'x';
    let coords;
    switch (side) {
      case 'top':
        coords = {
          x: commonX,
          y: reference.y - floating.height
        };
        break;
      case 'bottom':
        coords = {
          x: commonX,
          y: reference.y + reference.height
        };
        break;
      case 'right':
        coords = {
          x: reference.x + reference.width,
          y: commonY
        };
        break;
      case 'left':
        coords = {
          x: reference.x - floating.width,
          y: commonY
        };
        break;
      default:
        coords = {
          x: reference.x,
          y: reference.y
        };
    }
    switch (getAlignment(placement)) {
      case 'start':
        coords[mainAxis] -= commonAlign * (rtl && isVertical ? -1 : 1);
        break;
      case 'end':
        coords[mainAxis] += commonAlign * (rtl && isVertical ? -1 : 1);
        break;
    }
    return coords;
  }

  /**
   * Computes the `x` and `y` coordinates that will place the floating element
   * next to a reference element when it is given a certain positioning strategy.
   *
   * This export does not have any `platform` interface logic. You will need to
   * write one for the platform you are using Floating UI with.
   */
  const computePosition = async (reference, floating, config) => {
    const {
      placement = 'bottom',
      strategy = 'absolute',
      middleware = [],
      platform
    } = config;
    const validMiddleware = middleware.filter(Boolean);
    const rtl = await (platform.isRTL == null ? void 0 : platform.isRTL(floating));
    let rects = await platform.getElementRects({
      reference,
      floating,
      strategy
    });
    let {
      x,
      y
    } = computeCoordsFromPlacement(rects, placement, rtl);
    let statefulPlacement = placement;
    let middlewareData = {};
    let resetCount = 0;
    for (let i = 0; i < validMiddleware.length; i++) {
      const {
        name,
        fn
      } = validMiddleware[i];
      const {
        x: nextX,
        y: nextY,
        data,
        reset
      } = await fn({
        x,
        y,
        initialPlacement: placement,
        placement: statefulPlacement,
        strategy,
        middlewareData,
        rects,
        platform,
        elements: {
          reference,
          floating
        }
      });
      x = nextX != null ? nextX : x;
      y = nextY != null ? nextY : y;
      middlewareData = {
        ...middlewareData,
        [name]: {
          ...middlewareData[name],
          ...data
        }
      };
      if (reset && resetCount <= 50) {
        resetCount++;
        if (typeof reset === 'object') {
          if (reset.placement) {
            statefulPlacement = reset.placement;
          }
          if (reset.rects) {
            rects = reset.rects === true ? await platform.getElementRects({
              reference,
              floating,
              strategy
            }) : reset.rects;
          }
          ({
            x,
            y
          } = computeCoordsFromPlacement(rects, statefulPlacement, rtl));
        }
        i = -1;
        continue;
      }
    }
    return {
      x,
      y,
      placement: statefulPlacement,
      strategy,
      middlewareData
    };
  };

  function evaluate(value, param) {
    return typeof value === 'function' ? value(param) : value;
  }

  function expandPaddingObject(padding) {
    return {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
      ...padding
    };
  }

  function getSideObjectFromPadding(padding) {
    return typeof padding !== 'number' ? expandPaddingObject(padding) : {
      top: padding,
      right: padding,
      bottom: padding,
      left: padding
    };
  }

  function rectToClientRect(rect) {
    return {
      ...rect,
      top: rect.y,
      left: rect.x,
      right: rect.x + rect.width,
      bottom: rect.y + rect.height
    };
  }

  /**
   * Resolves with an object of overflow side offsets that determine how much the
   * element is overflowing a given clipping boundary on each side.
   * - positive = overflowing the boundary by that number of pixels
   * - negative = how many pixels left before it will overflow
   * - 0 = lies flush with the boundary
   * @see https://floating-ui.com/docs/detectOverflow
   */
  async function detectOverflow(state, options) {
    var _await$platform$isEle;
    if (options === void 0) {
      options = {};
    }
    const {
      x,
      y,
      platform,
      rects,
      elements,
      strategy
    } = state;
    const {
      boundary = 'clippingAncestors',
      rootBoundary = 'viewport',
      elementContext = 'floating',
      altBoundary = false,
      padding = 0
    } = evaluate(options, state);
    const paddingObject = getSideObjectFromPadding(padding);
    const altContext = elementContext === 'floating' ? 'reference' : 'floating';
    const element = elements[altBoundary ? altContext : elementContext];
    const clippingClientRect = rectToClientRect(await platform.getClippingRect({
      element: ((_await$platform$isEle = await (platform.isElement == null ? void 0 : platform.isElement(element))) != null ? _await$platform$isEle : true) ? element : element.contextElement || (await (platform.getDocumentElement == null ? void 0 : platform.getDocumentElement(elements.floating))),
      boundary,
      rootBoundary,
      strategy
    }));
    const rect = elementContext === 'floating' ? {
      ...rects.floating,
      x,
      y
    } : rects.reference;
    const offsetParent = await (platform.getOffsetParent == null ? void 0 : platform.getOffsetParent(elements.floating));
    const offsetScale = (await (platform.isElement == null ? void 0 : platform.isElement(offsetParent))) ? (await (platform.getScale == null ? void 0 : platform.getScale(offsetParent))) || {
      x: 1,
      y: 1
    } : {
      x: 1,
      y: 1
    };
    const elementClientRect = rectToClientRect(platform.convertOffsetParentRelativeRectToViewportRelativeRect ? await platform.convertOffsetParentRelativeRectToViewportRelativeRect({
      rect,
      offsetParent,
      strategy
    }) : rect);
    return {
      top: (clippingClientRect.top - elementClientRect.top + paddingObject.top) / offsetScale.y,
      bottom: (elementClientRect.bottom - clippingClientRect.bottom + paddingObject.bottom) / offsetScale.y,
      left: (clippingClientRect.left - elementClientRect.left + paddingObject.left) / offsetScale.x,
      right: (elementClientRect.right - clippingClientRect.right + paddingObject.right) / offsetScale.x
    };
  }

  const min = Math.min;
  const max = Math.max;

  function within(min$1, value, max$1) {
    return max(min$1, min(value, max$1));
  }

  /**
   * Provides data to position an inner element of the floating element so that it
   * appears centered to the reference element.
   * @see https://floating-ui.com/docs/arrow
   */
  const arrow = options => ({
    name: 'arrow',
    options,
    async fn(state) {
      const {
        x,
        y,
        placement,
        rects,
        platform,
        elements
      } = state;
      // Since `element` is required, we don't Partial<> the type.
      const {
        element,
        padding = 0
      } = evaluate(options, state) || {};
      if (element == null) {
        return {};
      }
      const paddingObject = getSideObjectFromPadding(padding);
      const coords = {
        x,
        y
      };
      const axis = getMainAxisFromPlacement(placement);
      const length = getLengthFromAxis(axis);
      const arrowDimensions = await platform.getDimensions(element);
      const isYAxis = axis === 'y';
      const minProp = isYAxis ? 'top' : 'left';
      const maxProp = isYAxis ? 'bottom' : 'right';
      const clientProp = isYAxis ? 'clientHeight' : 'clientWidth';
      const endDiff = rects.reference[length] + rects.reference[axis] - coords[axis] - rects.floating[length];
      const startDiff = coords[axis] - rects.reference[axis];
      const arrowOffsetParent = await (platform.getOffsetParent == null ? void 0 : platform.getOffsetParent(element));
      let clientSize = arrowOffsetParent ? arrowOffsetParent[clientProp] : 0;

      // DOM platform can return `window` as the `offsetParent`.
      if (!clientSize || !(await (platform.isElement == null ? void 0 : platform.isElement(arrowOffsetParent)))) {
        clientSize = elements.floating[clientProp] || rects.floating[length];
      }
      const centerToReference = endDiff / 2 - startDiff / 2;

      // If the padding is large enough that it causes the arrow to no longer be
      // centered, modify the padding so that it is centered.
      const largestPossiblePadding = clientSize / 2 - arrowDimensions[length] / 2 - 1;
      const minPadding = min(paddingObject[minProp], largestPossiblePadding);
      const maxPadding = min(paddingObject[maxProp], largestPossiblePadding);

      // Make sure the arrow doesn't overflow the floating element if the center
      // point is outside the floating element's bounds.
      const min$1 = minPadding;
      const max = clientSize - arrowDimensions[length] - maxPadding;
      const center = clientSize / 2 - arrowDimensions[length] / 2 + centerToReference;
      const offset = within(min$1, center, max);

      // If the reference is small enough that the arrow's padding causes it to
      // to point to nothing for an aligned placement, adjust the offset of the
      // floating element itself. This stops `shift()` from taking action, but can
      // be worked around by calling it again after the `arrow()` if desired.
      const shouldAddOffset = getAlignment(placement) != null && center != offset && rects.reference[length] / 2 - (center < min$1 ? minPadding : maxPadding) - arrowDimensions[length] / 2 < 0;
      const alignmentOffset = shouldAddOffset ? center < min$1 ? min$1 - center : max - center : 0;
      return {
        [axis]: coords[axis] - alignmentOffset,
        data: {
          [axis]: offset,
          centerOffset: center - offset + alignmentOffset
        }
      };
    }
  });

  const sides = ['top', 'right', 'bottom', 'left'];
  const allPlacements = /*#__PURE__*/sides.reduce((acc, side) => acc.concat(side, side + "-start", side + "-end"), []);

  const oppositeSideMap = {
    left: 'right',
    right: 'left',
    bottom: 'top',
    top: 'bottom'
  };
  function getOppositePlacement(placement) {
    return placement.replace(/left|right|bottom|top/g, side => oppositeSideMap[side]);
  }

  function getAlignmentSides(placement, rects, rtl) {
    if (rtl === void 0) {
      rtl = false;
    }
    const alignment = getAlignment(placement);
    const mainAxis = getMainAxisFromPlacement(placement);
    const length = getLengthFromAxis(mainAxis);
    let mainAlignmentSide = mainAxis === 'x' ? alignment === (rtl ? 'end' : 'start') ? 'right' : 'left' : alignment === 'start' ? 'bottom' : 'top';
    if (rects.reference[length] > rects.floating[length]) {
      mainAlignmentSide = getOppositePlacement(mainAlignmentSide);
    }
    return {
      main: mainAlignmentSide,
      cross: getOppositePlacement(mainAlignmentSide)
    };
  }

  const oppositeAlignmentMap = {
    start: 'end',
    end: 'start'
  };
  function getOppositeAlignmentPlacement(placement) {
    return placement.replace(/start|end/g, alignment => oppositeAlignmentMap[alignment]);
  }

  function getPlacementList(alignment, autoAlignment, allowedPlacements) {
    const allowedPlacementsSortedByAlignment = alignment ? [...allowedPlacements.filter(placement => getAlignment(placement) === alignment), ...allowedPlacements.filter(placement => getAlignment(placement) !== alignment)] : allowedPlacements.filter(placement => getSide(placement) === placement);
    return allowedPlacementsSortedByAlignment.filter(placement => {
      if (alignment) {
        return getAlignment(placement) === alignment || (autoAlignment ? getOppositeAlignmentPlacement(placement) !== placement : false);
      }
      return true;
    });
  }
  /**
   * Optimizes the visibility of the floating element by choosing the placement
   * that has the most space available automatically, without needing to specify a
   * preferred placement. Alternative to `flip`.
   * @see https://floating-ui.com/docs/autoPlacement
   */
  const autoPlacement = function (options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: 'autoPlacement',
      options,
      async fn(state) {
        var _middlewareData$autoP, _middlewareData$autoP2, _placementsThatFitOnE;
        const {
          rects,
          middlewareData,
          placement,
          platform,
          elements
        } = state;
        const {
          crossAxis = false,
          alignment,
          allowedPlacements = allPlacements,
          autoAlignment = true,
          ...detectOverflowOptions
        } = evaluate(options, state);
        const placements = alignment !== undefined || allowedPlacements === allPlacements ? getPlacementList(alignment || null, autoAlignment, allowedPlacements) : allowedPlacements;
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const currentIndex = ((_middlewareData$autoP = middlewareData.autoPlacement) == null ? void 0 : _middlewareData$autoP.index) || 0;
        const currentPlacement = placements[currentIndex];
        if (currentPlacement == null) {
          return {};
        }
        const {
          main,
          cross
        } = getAlignmentSides(currentPlacement, rects, await (platform.isRTL == null ? void 0 : platform.isRTL(elements.floating)));

        // Make `computeCoords` start from the right place.
        if (placement !== currentPlacement) {
          return {
            reset: {
              placement: placements[0]
            }
          };
        }
        const currentOverflows = [overflow[getSide(currentPlacement)], overflow[main], overflow[cross]];
        const allOverflows = [...(((_middlewareData$autoP2 = middlewareData.autoPlacement) == null ? void 0 : _middlewareData$autoP2.overflows) || []), {
          placement: currentPlacement,
          overflows: currentOverflows
        }];
        const nextPlacement = placements[currentIndex + 1];

        // There are more placements to check.
        if (nextPlacement) {
          return {
            data: {
              index: currentIndex + 1,
              overflows: allOverflows
            },
            reset: {
              placement: nextPlacement
            }
          };
        }
        const placementsSortedByMostSpace = allOverflows.map(d => {
          const alignment = getAlignment(d.placement);
          return [d.placement, alignment && crossAxis ?
            // Check along the mainAxis and main crossAxis side.
            d.overflows.slice(0, 2).reduce((acc, v) => acc + v, 0) :
            // Check only the mainAxis.
            d.overflows[0], d.overflows];
        }).sort((a, b) => a[1] - b[1]);
        const placementsThatFitOnEachSide = placementsSortedByMostSpace.filter(d => d[2].slice(0,
          // Aligned placements should not check their opposite crossAxis
          // side.
          getAlignment(d[0]) ? 2 : 3).every(v => v <= 0));
        const resetPlacement = ((_placementsThatFitOnE = placementsThatFitOnEachSide[0]) == null ? void 0 : _placementsThatFitOnE[0]) || placementsSortedByMostSpace[0][0];
        if (resetPlacement !== placement) {
          return {
            data: {
              index: currentIndex + 1,
              overflows: allOverflows
            },
            reset: {
              placement: resetPlacement
            }
          };
        }
        return {};
      }
    };
  };

  function getExpandedPlacements(placement) {
    const oppositePlacement = getOppositePlacement(placement);
    return [getOppositeAlignmentPlacement(placement), oppositePlacement, getOppositeAlignmentPlacement(oppositePlacement)];
  }

  function getSideList(side, isStart, rtl) {
    const lr = ['left', 'right'];
    const rl = ['right', 'left'];
    const tb = ['top', 'bottom'];
    const bt = ['bottom', 'top'];
    switch (side) {
      case 'top':
      case 'bottom':
        if (rtl) return isStart ? rl : lr;
        return isStart ? lr : rl;
      case 'left':
      case 'right':
        return isStart ? tb : bt;
      default:
        return [];
    }
  }
  function getOppositeAxisPlacements(placement, flipAlignment, direction, rtl) {
    const alignment = getAlignment(placement);
    let list = getSideList(getSide(placement), direction === 'start', rtl);
    if (alignment) {
      list = list.map(side => side + "-" + alignment);
      if (flipAlignment) {
        list = list.concat(list.map(getOppositeAlignmentPlacement));
      }
    }
    return list;
  }

  /**
   * Optimizes the visibility of the floating element by flipping the `placement`
   * in order to keep it in view when the preferred placement(s) will overflow the
   * clipping boundary. Alternative to `autoPlacement`.
   * @see https://floating-ui.com/docs/flip
   */
  const flip = function (options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: 'flip',
      options,
      async fn(state) {
        var _middlewareData$flip;
        const {
          placement,
          middlewareData,
          rects,
          initialPlacement,
          platform,
          elements
        } = state;
        const {
          mainAxis: checkMainAxis = true,
          crossAxis: checkCrossAxis = true,
          fallbackPlacements: specifiedFallbackPlacements,
          fallbackStrategy = 'bestFit',
          fallbackAxisSideDirection = 'none',
          flipAlignment = true,
          ...detectOverflowOptions
        } = evaluate(options, state);
        const side = getSide(placement);
        const isBasePlacement = getSide(initialPlacement) === initialPlacement;
        const rtl = await (platform.isRTL == null ? void 0 : platform.isRTL(elements.floating));
        const fallbackPlacements = specifiedFallbackPlacements || (isBasePlacement || !flipAlignment ? [getOppositePlacement(initialPlacement)] : getExpandedPlacements(initialPlacement));
        if (!specifiedFallbackPlacements && fallbackAxisSideDirection !== 'none') {
          fallbackPlacements.push(...getOppositeAxisPlacements(initialPlacement, flipAlignment, fallbackAxisSideDirection, rtl));
        }
        const placements = [initialPlacement, ...fallbackPlacements];
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const overflows = [];
        let overflowsData = ((_middlewareData$flip = middlewareData.flip) == null ? void 0 : _middlewareData$flip.overflows) || [];
        if (checkMainAxis) {
          overflows.push(overflow[side]);
        }
        if (checkCrossAxis) {
          const {
            main,
            cross
          } = getAlignmentSides(placement, rects, rtl);
          overflows.push(overflow[main], overflow[cross]);
        }
        overflowsData = [...overflowsData, {
          placement,
          overflows
        }];

        // One or more sides is overflowing.
        if (!overflows.every(side => side <= 0)) {
          var _middlewareData$flip2, _overflowsData$filter;
          const nextIndex = (((_middlewareData$flip2 = middlewareData.flip) == null ? void 0 : _middlewareData$flip2.index) || 0) + 1;
          const nextPlacement = placements[nextIndex];
          if (nextPlacement) {
            // Try next placement and re-run the lifecycle.
            return {
              data: {
                index: nextIndex,
                overflows: overflowsData
              },
              reset: {
                placement: nextPlacement
              }
            };
          }

          // First, find the candidates that fit on the mainAxis side of overflow,
          // then find the placement that fits the best on the main crossAxis side.
          let resetPlacement = (_overflowsData$filter = overflowsData.filter(d => d.overflows[0] <= 0).sort((a, b) => a.overflows[1] - b.overflows[1])[0]) == null ? void 0 : _overflowsData$filter.placement;

          // Otherwise fallback.
          if (!resetPlacement) {
            switch (fallbackStrategy) {
              case 'bestFit':
              {
                var _overflowsData$map$so;
                const placement = (_overflowsData$map$so = overflowsData.map(d => [d.placement, d.overflows.filter(overflow => overflow > 0).reduce((acc, overflow) => acc + overflow, 0)]).sort((a, b) => a[1] - b[1])[0]) == null ? void 0 : _overflowsData$map$so[0];
                if (placement) {
                  resetPlacement = placement;
                }
                break;
              }
              case 'initialPlacement':
                resetPlacement = initialPlacement;
                break;
            }
          }
          if (placement !== resetPlacement) {
            return {
              reset: {
                placement: resetPlacement
              }
            };
          }
        }
        return {};
      }
    };
  };

  function getSideOffsets(overflow, rect) {
    return {
      top: overflow.top - rect.height,
      right: overflow.right - rect.width,
      bottom: overflow.bottom - rect.height,
      left: overflow.left - rect.width
    };
  }
  function isAnySideFullyClipped(overflow) {
    return sides.some(side => overflow[side] >= 0);
  }
  /**
   * Provides data to hide the floating element in applicable situations, such as
   * when it is not in the same clipping context as the reference element.
   * @see https://floating-ui.com/docs/hide
   */
  const hide = function (options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: 'hide',
      options,
      async fn(state) {
        const {
          rects
        } = state;
        const {
          strategy = 'referenceHidden',
          ...detectOverflowOptions
        } = evaluate(options, state);
        switch (strategy) {
          case 'referenceHidden':
          {
            const overflow = await detectOverflow(state, {
              ...detectOverflowOptions,
              elementContext: 'reference'
            });
            const offsets = getSideOffsets(overflow, rects.reference);
            return {
              data: {
                referenceHiddenOffsets: offsets,
                referenceHidden: isAnySideFullyClipped(offsets)
              }
            };
          }
          case 'escaped':
          {
            const overflow = await detectOverflow(state, {
              ...detectOverflowOptions,
              altBoundary: true
            });
            const offsets = getSideOffsets(overflow, rects.floating);
            return {
              data: {
                escapedOffsets: offsets,
                escaped: isAnySideFullyClipped(offsets)
              }
            };
          }
          default:
          {
            return {};
          }
        }
      }
    };
  };

  function getBoundingRect(rects) {
    const minX = min(...rects.map(rect => rect.left));
    const minY = min(...rects.map(rect => rect.top));
    const maxX = max(...rects.map(rect => rect.right));
    const maxY = max(...rects.map(rect => rect.bottom));
    return {
      x: minX,
      y: minY,
      width: maxX - minX,
      height: maxY - minY
    };
  }
  function getRectsByLine(rects) {
    const sortedRects = rects.slice().sort((a, b) => a.y - b.y);
    const groups = [];
    let prevRect = null;
    for (let i = 0; i < sortedRects.length; i++) {
      const rect = sortedRects[i];
      if (!prevRect || rect.y - prevRect.y > prevRect.height / 2) {
        groups.push([rect]);
      } else {
        groups[groups.length - 1].push(rect);
      }
      prevRect = rect;
    }
    return groups.map(rect => rectToClientRect(getBoundingRect(rect)));
  }
  /**
   * Provides improved positioning for inline reference elements that can span
   * over multiple lines, such as hyperlinks or range selections.
   * @see https://floating-ui.com/docs/inline
   */
  const inline = function (options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: 'inline',
      options,
      async fn(state) {
        const {
          placement,
          elements,
          rects,
          platform,
          strategy
        } = state;
        // A MouseEvent's client{X,Y} coords can be up to 2 pixels off a
        // ClientRect's bounds, despite the event listener being triggered. A
        // padding of 2 seems to handle this issue.
        const {
          padding = 2,
          x,
          y
        } = evaluate(options, state);
        const nativeClientRects = Array.from((await (platform.getClientRects == null ? void 0 : platform.getClientRects(elements.reference))) || []);
        const clientRects = getRectsByLine(nativeClientRects);
        const fallback = rectToClientRect(getBoundingRect(nativeClientRects));
        const paddingObject = getSideObjectFromPadding(padding);
        function getBoundingClientRect() {
          // There are two rects and they are disjoined.
          if (clientRects.length === 2 && clientRects[0].left > clientRects[1].right && x != null && y != null) {
            // Find the first rect in which the point is fully inside.
            return clientRects.find(rect => x > rect.left - paddingObject.left && x < rect.right + paddingObject.right && y > rect.top - paddingObject.top && y < rect.bottom + paddingObject.bottom) || fallback;
          }

          // There are 2 or more connected rects.
          if (clientRects.length >= 2) {
            if (getMainAxisFromPlacement(placement) === 'x') {
              const firstRect = clientRects[0];
              const lastRect = clientRects[clientRects.length - 1];
              const isTop = getSide(placement) === 'top';
              const top = firstRect.top;
              const bottom = lastRect.bottom;
              const left = isTop ? firstRect.left : lastRect.left;
              const right = isTop ? firstRect.right : lastRect.right;
              const width = right - left;
              const height = bottom - top;
              return {
                top,
                bottom,
                left,
                right,
                width,
                height,
                x: left,
                y: top
              };
            }
            const isLeftSide = getSide(placement) === 'left';
            const maxRight = max(...clientRects.map(rect => rect.right));
            const minLeft = min(...clientRects.map(rect => rect.left));
            const measureRects = clientRects.filter(rect => isLeftSide ? rect.left === minLeft : rect.right === maxRight);
            const top = measureRects[0].top;
            const bottom = measureRects[measureRects.length - 1].bottom;
            const left = minLeft;
            const right = maxRight;
            const width = right - left;
            const height = bottom - top;
            return {
              top,
              bottom,
              left,
              right,
              width,
              height,
              x: left,
              y: top
            };
          }
          return fallback;
        }
        const resetRects = await platform.getElementRects({
          reference: {
            getBoundingClientRect
          },
          floating: elements.floating,
          strategy
        });
        if (rects.reference.x !== resetRects.reference.x || rects.reference.y !== resetRects.reference.y || rects.reference.width !== resetRects.reference.width || rects.reference.height !== resetRects.reference.height) {
          return {
            reset: {
              rects: resetRects
            }
          };
        }
        return {};
      }
    };
  };

  async function convertValueToCoords(state, options) {
    const {
      placement,
      platform,
      elements
    } = state;
    const rtl = await (platform.isRTL == null ? void 0 : platform.isRTL(elements.floating));
    const side = getSide(placement);
    const alignment = getAlignment(placement);
    const isVertical = getMainAxisFromPlacement(placement) === 'x';
    const mainAxisMulti = ['left', 'top'].includes(side) ? -1 : 1;
    const crossAxisMulti = rtl && isVertical ? -1 : 1;
    const rawValue = evaluate(options, state);

    // eslint-disable-next-line prefer-const
    let {
      mainAxis,
      crossAxis,
      alignmentAxis
    } = typeof rawValue === 'number' ? {
      mainAxis: rawValue,
      crossAxis: 0,
      alignmentAxis: null
    } : {
      mainAxis: 0,
      crossAxis: 0,
      alignmentAxis: null,
      ...rawValue
    };
    if (alignment && typeof alignmentAxis === 'number') {
      crossAxis = alignment === 'end' ? alignmentAxis * -1 : alignmentAxis;
    }
    return isVertical ? {
      x: crossAxis * crossAxisMulti,
      y: mainAxis * mainAxisMulti
    } : {
      x: mainAxis * mainAxisMulti,
      y: crossAxis * crossAxisMulti
    };
  }

  /**
   * Modifies the placement by translating the floating element along the
   * specified axes.
   * A number (shorthand for `mainAxis` or distance), or an axes configuration
   * object may be passed.
   * @see https://floating-ui.com/docs/offset
   */
  const offset = function (options) {
    if (options === void 0) {
      options = 0;
    }
    return {
      name: 'offset',
      options,
      async fn(state) {
        const {
          x,
          y
        } = state;
        const diffCoords = await convertValueToCoords(state, options);
        return {
          x: x + diffCoords.x,
          y: y + diffCoords.y,
          data: diffCoords
        };
      }
    };
  };

  function getCrossAxis(axis) {
    return axis === 'x' ? 'y' : 'x';
  }

  /**
   * Optimizes the visibility of the floating element by shifting it in order to
   * keep it in view when it will overflow the clipping boundary.
   * @see https://floating-ui.com/docs/shift
   */
  const shift = function (options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: 'shift',
      options,
      async fn(state) {
        const {
          x,
          y,
          placement
        } = state;
        const {
          mainAxis: checkMainAxis = true,
          crossAxis: checkCrossAxis = false,
          limiter = {
            fn: _ref => {
              let {
                x,
                y
              } = _ref;
              return {
                x,
                y
              };
            }
          },
          ...detectOverflowOptions
        } = evaluate(options, state);
        const coords = {
          x,
          y
        };
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const mainAxis = getMainAxisFromPlacement(getSide(placement));
        const crossAxis = getCrossAxis(mainAxis);
        let mainAxisCoord = coords[mainAxis];
        let crossAxisCoord = coords[crossAxis];
        if (checkMainAxis) {
          const minSide = mainAxis === 'y' ? 'top' : 'left';
          const maxSide = mainAxis === 'y' ? 'bottom' : 'right';
          const min = mainAxisCoord + overflow[minSide];
          const max = mainAxisCoord - overflow[maxSide];
          mainAxisCoord = within(min, mainAxisCoord, max);
        }
        if (checkCrossAxis) {
          const minSide = crossAxis === 'y' ? 'top' : 'left';
          const maxSide = crossAxis === 'y' ? 'bottom' : 'right';
          const min = crossAxisCoord + overflow[minSide];
          const max = crossAxisCoord - overflow[maxSide];
          crossAxisCoord = within(min, crossAxisCoord, max);
        }
        const limitedCoords = limiter.fn({
          ...state,
          [mainAxis]: mainAxisCoord,
          [crossAxis]: crossAxisCoord
        });
        return {
          ...limitedCoords,
          data: {
            x: limitedCoords.x - x,
            y: limitedCoords.y - y
          }
        };
      }
    };
  };
  /**
   * Built-in `limiter` that will stop `shift()` at a certain point.
   */
  const limitShift = function (options) {
    if (options === void 0) {
      options = {};
    }
    return {
      options,
      fn(state) {
        const {
          x,
          y,
          placement,
          rects,
          middlewareData
        } = state;
        const {
          offset = 0,
          mainAxis: checkMainAxis = true,
          crossAxis: checkCrossAxis = true
        } = evaluate(options, state);
        const coords = {
          x,
          y
        };
        const mainAxis = getMainAxisFromPlacement(placement);
        const crossAxis = getCrossAxis(mainAxis);
        let mainAxisCoord = coords[mainAxis];
        let crossAxisCoord = coords[crossAxis];
        const rawOffset = evaluate(offset, state);
        const computedOffset = typeof rawOffset === 'number' ? {
          mainAxis: rawOffset,
          crossAxis: 0
        } : {
          mainAxis: 0,
          crossAxis: 0,
          ...rawOffset
        };
        if (checkMainAxis) {
          const len = mainAxis === 'y' ? 'height' : 'width';
          const limitMin = rects.reference[mainAxis] - rects.floating[len] + computedOffset.mainAxis;
          const limitMax = rects.reference[mainAxis] + rects.reference[len] - computedOffset.mainAxis;
          if (mainAxisCoord < limitMin) {
            mainAxisCoord = limitMin;
          } else if (mainAxisCoord > limitMax) {
            mainAxisCoord = limitMax;
          }
        }
        if (checkCrossAxis) {
          var _middlewareData$offse, _middlewareData$offse2;
          const len = mainAxis === 'y' ? 'width' : 'height';
          const isOriginSide = ['top', 'left'].includes(getSide(placement));
          const limitMin = rects.reference[crossAxis] - rects.floating[len] + (isOriginSide ? ((_middlewareData$offse = middlewareData.offset) == null ? void 0 : _middlewareData$offse[crossAxis]) || 0 : 0) + (isOriginSide ? 0 : computedOffset.crossAxis);
          const limitMax = rects.reference[crossAxis] + rects.reference[len] + (isOriginSide ? 0 : ((_middlewareData$offse2 = middlewareData.offset) == null ? void 0 : _middlewareData$offse2[crossAxis]) || 0) - (isOriginSide ? computedOffset.crossAxis : 0);
          if (crossAxisCoord < limitMin) {
            crossAxisCoord = limitMin;
          } else if (crossAxisCoord > limitMax) {
            crossAxisCoord = limitMax;
          }
        }
        return {
          [mainAxis]: mainAxisCoord,
          [crossAxis]: crossAxisCoord
        };
      }
    };
  };

  /**
   * Provides data that allows you to change the size of the floating element —
   * for instance, prevent it from overflowing the clipping boundary or match the
   * width of the reference element.
   * @see https://floating-ui.com/docs/size
   */
  const size = function (options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: 'size',
      options,
      async fn(state) {
        const {
          placement,
          rects,
          platform,
          elements
        } = state;
        const {
          apply = () => {},
          ...detectOverflowOptions
        } = evaluate(options, state);
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const side = getSide(placement);
        const alignment = getAlignment(placement);
        const axis = getMainAxisFromPlacement(placement);
        const isXAxis = axis === 'x';
        const {
          width,
          height
        } = rects.floating;
        let heightSide;
        let widthSide;
        if (side === 'top' || side === 'bottom') {
          heightSide = side;
          widthSide = alignment === ((await (platform.isRTL == null ? void 0 : platform.isRTL(elements.floating))) ? 'start' : 'end') ? 'left' : 'right';
        } else {
          widthSide = side;
          heightSide = alignment === 'end' ? 'top' : 'bottom';
        }
        const overflowAvailableHeight = height - overflow[heightSide];
        const overflowAvailableWidth = width - overflow[widthSide];
        const noShift = !state.middlewareData.shift;
        let availableHeight = overflowAvailableHeight;
        let availableWidth = overflowAvailableWidth;
        if (isXAxis) {
          const maximumClippingWidth = width - overflow.left - overflow.right;
          availableWidth = alignment || noShift ? min(overflowAvailableWidth, maximumClippingWidth) : maximumClippingWidth;
        } else {
          const maximumClippingHeight = height - overflow.top - overflow.bottom;
          availableHeight = alignment || noShift ? min(overflowAvailableHeight, maximumClippingHeight) : maximumClippingHeight;
        }
        if (noShift && !alignment) {
          const xMin = max(overflow.left, 0);
          const xMax = max(overflow.right, 0);
          const yMin = max(overflow.top, 0);
          const yMax = max(overflow.bottom, 0);
          if (isXAxis) {
            availableWidth = width - 2 * (xMin !== 0 || xMax !== 0 ? xMin + xMax : max(overflow.left, overflow.right));
          } else {
            availableHeight = height - 2 * (yMin !== 0 || yMax !== 0 ? yMin + yMax : max(overflow.top, overflow.bottom));
          }
        }
        await apply({
          ...state,
          availableWidth,
          availableHeight
        });
        const nextDimensions = await platform.getDimensions(elements.floating);
        if (width !== nextDimensions.width || height !== nextDimensions.height) {
          return {
            reset: {
              rects: true
            }
          };
        }
        return {};
      }
    };
  };

  exports.arrow = arrow;
  exports.autoPlacement = autoPlacement;
  exports.computePosition = computePosition;
  exports.detectOverflow = detectOverflow;
  exports.flip = flip;
  exports.hide = hide;
  exports.inline = inline;
  exports.limitShift = limitShift;
  exports.offset = offset;
  exports.rectToClientRect = rectToClientRect;
  exports.shift = shift;
  exports.size = size;

  Object.defineProperty(exports, '__esModule', { value: true });

}));
;
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('@floating-ui/core')) :
    typeof define === 'function' && define.amd ? define(['exports', '@floating-ui/core'], factory) :
      (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.FloatingUIDOM = {}, global.FloatingUICore));
})(this, (function (exports, core) { 'use strict';

  function getWindow(node) {
    var _node$ownerDocument;
    return ((_node$ownerDocument = node.ownerDocument) == null ? void 0 : _node$ownerDocument.defaultView) || window;
  }

  function getComputedStyle$1(element) {
    return getWindow(element).getComputedStyle(element);
  }

  function isNode(value) {
    return value instanceof getWindow(value).Node;
  }
  function getNodeName(node) {
    if (isNode(node)) {
      return (node.nodeName || '').toLowerCase();
    }
    // Mocked nodes in testing environments may not be instances of Node. By
    // returning `#document` an infinite loop won't occur.
    // https://github.com/floating-ui/floating-ui/issues/2317
    return '#document';
  }

  function isHTMLElement(value) {
    return value instanceof getWindow(value).HTMLElement;
  }
  function isElement(value) {
    return value instanceof getWindow(value).Element;
  }
  function isShadowRoot(node) {
    // Browsers without `ShadowRoot` support.
    if (typeof ShadowRoot === 'undefined') {
      return false;
    }
    return node instanceof getWindow(node).ShadowRoot || node instanceof ShadowRoot;
  }
  function isOverflowElement(element) {
    const {
      overflow,
      overflowX,
      overflowY,
      display
    } = getComputedStyle$1(element);
    return /auto|scroll|overlay|hidden|clip/.test(overflow + overflowY + overflowX) && !['inline', 'contents'].includes(display);
  }
  function isTableElement(element) {
    return ['table', 'td', 'th'].includes(getNodeName(element));
  }
  function isContainingBlock(element) {
    const safari = isSafari();
    const css = getComputedStyle$1(element);

    // https://developer.mozilla.org/en-US/docs/Web/CSS/Containing_block#identifying_the_containing_block
    return css.transform !== 'none' || css.perspective !== 'none' || !safari && (css.backdropFilter ? css.backdropFilter !== 'none' : false) || !safari && (css.filter ? css.filter !== 'none' : false) || ['transform', 'perspective', 'filter'].some(value => (css.willChange || '').includes(value)) || ['paint', 'layout', 'strict', 'content'].some(value => (css.contain || '').includes(value));
  }
  function isSafari() {
    if (typeof CSS === 'undefined' || !CSS.supports) return false;
    return CSS.supports('-webkit-backdrop-filter', 'none');
  }
  function isLastTraversableNode(node) {
    return ['html', 'body', '#document'].includes(getNodeName(node));
  }

  const min = Math.min;
  const max = Math.max;
  const round = Math.round;
  const floor = Math.floor;
  const createEmptyCoords = v => ({
    x: v,
    y: v
  });

  function getCssDimensions(element) {
    const css = getComputedStyle$1(element);
    // In testing environments, the `width` and `height` properties are empty
    // strings for SVG elements, returning NaN. Fallback to `0` in this case.
    let width = parseFloat(css.width) || 0;
    let height = parseFloat(css.height) || 0;
    const hasOffset = isHTMLElement(element);
    const offsetWidth = hasOffset ? element.offsetWidth : width;
    const offsetHeight = hasOffset ? element.offsetHeight : height;
    const shouldFallback = round(width) !== offsetWidth || round(height) !== offsetHeight;
    if (shouldFallback) {
      width = offsetWidth;
      height = offsetHeight;
    }
    return {
      width,
      height,
      $: shouldFallback
    };
  }

  function unwrapElement(element) {
    return !isElement(element) ? element.contextElement : element;
  }

  function getScale(element) {
    const domElement = unwrapElement(element);
    if (!isHTMLElement(domElement)) {
      return createEmptyCoords(1);
    }
    const rect = domElement.getBoundingClientRect();
    const {
      width,
      height,
      $
    } = getCssDimensions(domElement);
    let x = ($ ? round(rect.width) : rect.width) / width;
    let y = ($ ? round(rect.height) : rect.height) / height;

    // 0, NaN, or Infinity should always fallback to 1.

    if (!x || !Number.isFinite(x)) {
      x = 1;
    }
    if (!y || !Number.isFinite(y)) {
      y = 1;
    }
    return {
      x,
      y
    };
  }

  const noOffsets = /*#__PURE__*/createEmptyCoords(0);
  function getVisualOffsets(element, isFixed, floatingOffsetParent) {
    var _win$visualViewport, _win$visualViewport2;
    if (isFixed === void 0) {
      isFixed = true;
    }
    if (!isSafari()) {
      return noOffsets;
    }
    const win = element ? getWindow(element) : window;
    if (!floatingOffsetParent || isFixed && floatingOffsetParent !== win) {
      return noOffsets;
    }
    return {
      x: ((_win$visualViewport = win.visualViewport) == null ? void 0 : _win$visualViewport.offsetLeft) || 0,
      y: ((_win$visualViewport2 = win.visualViewport) == null ? void 0 : _win$visualViewport2.offsetTop) || 0
    };
  }

  function getBoundingClientRect(element, includeScale, isFixedStrategy, offsetParent) {
    if (includeScale === void 0) {
      includeScale = false;
    }
    if (isFixedStrategy === void 0) {
      isFixedStrategy = false;
    }
    const clientRect = element.getBoundingClientRect();
    const domElement = unwrapElement(element);
    let scale = createEmptyCoords(1);
    if (includeScale) {
      if (offsetParent) {
        if (isElement(offsetParent)) {
          scale = getScale(offsetParent);
        }
      } else {
        scale = getScale(element);
      }
    }
    const visualOffsets = getVisualOffsets(domElement, isFixedStrategy, offsetParent);
    let x = (clientRect.left + visualOffsets.x) / scale.x;
    let y = (clientRect.top + visualOffsets.y) / scale.y;
    let width = clientRect.width / scale.x;
    let height = clientRect.height / scale.y;
    if (domElement) {
      const win = getWindow(domElement);
      const offsetWin = offsetParent && isElement(offsetParent) ? getWindow(offsetParent) : offsetParent;
      let currentIFrame = win.frameElement;
      while (currentIFrame && offsetParent && offsetWin !== win) {
        const iframeScale = getScale(currentIFrame);
        const iframeRect = currentIFrame.getBoundingClientRect();
        const css = getComputedStyle(currentIFrame);
        const left = iframeRect.left + (currentIFrame.clientLeft + parseFloat(css.paddingLeft)) * iframeScale.x;
        const top = iframeRect.top + (currentIFrame.clientTop + parseFloat(css.paddingTop)) * iframeScale.y;
        x *= iframeScale.x;
        y *= iframeScale.y;
        width *= iframeScale.x;
        height *= iframeScale.y;
        x += left;
        y += top;
        currentIFrame = getWindow(currentIFrame).frameElement;
      }
    }
    return core.rectToClientRect({
      width,
      height,
      x,
      y
    });
  }

  function getDocumentElement(node) {
    return ((isNode(node) ? node.ownerDocument : node.document) || window.document).documentElement;
  }

  function getNodeScroll(element) {
    if (isElement(element)) {
      return {
        scrollLeft: element.scrollLeft,
        scrollTop: element.scrollTop
      };
    }
    return {
      scrollLeft: element.pageXOffset,
      scrollTop: element.pageYOffset
    };
  }

  function convertOffsetParentRelativeRectToViewportRelativeRect(_ref) {
    let {
      rect,
      offsetParent,
      strategy
    } = _ref;
    const isOffsetParentAnElement = isHTMLElement(offsetParent);
    const documentElement = getDocumentElement(offsetParent);
    if (offsetParent === documentElement) {
      return rect;
    }
    let scroll = {
      scrollLeft: 0,
      scrollTop: 0
    };
    let scale = createEmptyCoords(1);
    const offsets = createEmptyCoords(0);
    if (isOffsetParentAnElement || !isOffsetParentAnElement && strategy !== 'fixed') {
      if (getNodeName(offsetParent) !== 'body' || isOverflowElement(documentElement)) {
        scroll = getNodeScroll(offsetParent);
      }
      if (isHTMLElement(offsetParent)) {
        const offsetRect = getBoundingClientRect(offsetParent);
        scale = getScale(offsetParent);
        offsets.x = offsetRect.x + offsetParent.clientLeft;
        offsets.y = offsetRect.y + offsetParent.clientTop;
      }
    }
    return {
      width: rect.width * scale.x,
      height: rect.height * scale.y,
      x: rect.x * scale.x - scroll.scrollLeft * scale.x + offsets.x,
      y: rect.y * scale.y - scroll.scrollTop * scale.y + offsets.y
    };
  }

  function getWindowScrollBarX(element) {
    // If <html> has a CSS width greater than the viewport, then this will be
    // incorrect for RTL.
    return getBoundingClientRect(getDocumentElement(element)).left + getNodeScroll(element).scrollLeft;
  }

  // Gets the entire size of the scrollable document area, even extending outside
  // of the `<html>` and `<body>` rect bounds if horizontally scrollable.
  function getDocumentRect(element) {
    const html = getDocumentElement(element);
    const scroll = getNodeScroll(element);
    const body = element.ownerDocument.body;
    const width = max(html.scrollWidth, html.clientWidth, body.scrollWidth, body.clientWidth);
    const height = max(html.scrollHeight, html.clientHeight, body.scrollHeight, body.clientHeight);
    let x = -scroll.scrollLeft + getWindowScrollBarX(element);
    const y = -scroll.scrollTop;
    if (getComputedStyle$1(body).direction === 'rtl') {
      x += max(html.clientWidth, body.clientWidth) - width;
    }
    return {
      width,
      height,
      x,
      y
    };
  }

  function getParentNode(node) {
    if (getNodeName(node) === 'html') {
      return node;
    }
    const result =
      // Step into the shadow DOM of the parent of a slotted node.
      node.assignedSlot ||
      // DOM Element detected.
      node.parentNode ||
      // ShadowRoot detected.
      isShadowRoot(node) && node.host ||
      // Fallback.
      getDocumentElement(node);
    return isShadowRoot(result) ? result.host : result;
  }

  function getNearestOverflowAncestor(node) {
    const parentNode = getParentNode(node);
    if (isLastTraversableNode(parentNode)) {
      return node.ownerDocument ? node.ownerDocument.body : node.body;
    }
    if (isHTMLElement(parentNode) && isOverflowElement(parentNode)) {
      return parentNode;
    }
    return getNearestOverflowAncestor(parentNode);
  }

  function getOverflowAncestors(node, list) {
    var _node$ownerDocument;
    if (list === void 0) {
      list = [];
    }
    const scrollableAncestor = getNearestOverflowAncestor(node);
    const isBody = scrollableAncestor === ((_node$ownerDocument = node.ownerDocument) == null ? void 0 : _node$ownerDocument.body);
    const win = getWindow(scrollableAncestor);
    if (isBody) {
      return list.concat(win, win.visualViewport || [], isOverflowElement(scrollableAncestor) ? scrollableAncestor : []);
    }
    return list.concat(scrollableAncestor, getOverflowAncestors(scrollableAncestor));
  }

  function getViewportRect(element, strategy) {
    const win = getWindow(element);
    const html = getDocumentElement(element);
    const visualViewport = win.visualViewport;
    let width = html.clientWidth;
    let height = html.clientHeight;
    let x = 0;
    let y = 0;
    if (visualViewport) {
      width = visualViewport.width;
      height = visualViewport.height;
      const visualViewportBased = isSafari();
      if (!visualViewportBased || visualViewportBased && strategy === 'fixed') {
        x = visualViewport.offsetLeft;
        y = visualViewport.offsetTop;
      }
    }
    return {
      width,
      height,
      x,
      y
    };
  }

  // Returns the inner client rect, subtracting scrollbars if present.
  function getInnerBoundingClientRect(element, strategy) {
    const clientRect = getBoundingClientRect(element, true, strategy === 'fixed');
    const top = clientRect.top + element.clientTop;
    const left = clientRect.left + element.clientLeft;
    const scale = isHTMLElement(element) ? getScale(element) : createEmptyCoords(1);
    const width = element.clientWidth * scale.x;
    const height = element.clientHeight * scale.y;
    const x = left * scale.x;
    const y = top * scale.y;
    return {
      width,
      height,
      x,
      y
    };
  }
  function getClientRectFromClippingAncestor(element, clippingAncestor, strategy) {
    let rect;
    if (clippingAncestor === 'viewport') {
      rect = getViewportRect(element, strategy);
    } else if (clippingAncestor === 'document') {
      rect = getDocumentRect(getDocumentElement(element));
    } else if (isElement(clippingAncestor)) {
      rect = getInnerBoundingClientRect(clippingAncestor, strategy);
    } else {
      const visualOffsets = getVisualOffsets(element);
      rect = {
        ...clippingAncestor,
        x: clippingAncestor.x - visualOffsets.x,
        y: clippingAncestor.y - visualOffsets.y
      };
    }
    return core.rectToClientRect(rect);
  }
  function hasFixedPositionAncestor(element, stopNode) {
    const parentNode = getParentNode(element);
    if (parentNode === stopNode || !isElement(parentNode) || isLastTraversableNode(parentNode)) {
      return false;
    }
    return getComputedStyle$1(parentNode).position === 'fixed' || hasFixedPositionAncestor(parentNode, stopNode);
  }

  // A "clipping ancestor" is an `overflow` element with the characteristic of
  // clipping (or hiding) child elements. This returns all clipping ancestors
  // of the given element up the tree.
  function getClippingElementAncestors(element, cache) {
    const cachedResult = cache.get(element);
    if (cachedResult) {
      return cachedResult;
    }
    let result = getOverflowAncestors(element).filter(el => isElement(el) && getNodeName(el) !== 'body');
    let currentContainingBlockComputedStyle = null;
    const elementIsFixed = getComputedStyle$1(element).position === 'fixed';
    let currentNode = elementIsFixed ? getParentNode(element) : element;

    // https://developer.mozilla.org/en-US/docs/Web/CSS/Containing_block#identifying_the_containing_block
    while (isElement(currentNode) && !isLastTraversableNode(currentNode)) {
      const computedStyle = getComputedStyle$1(currentNode);
      const currentNodeIsContaining = isContainingBlock(currentNode);
      if (!currentNodeIsContaining && computedStyle.position === 'fixed') {
        currentContainingBlockComputedStyle = null;
      }
      const shouldDropCurrentNode = elementIsFixed ? !currentNodeIsContaining && !currentContainingBlockComputedStyle : !currentNodeIsContaining && computedStyle.position === 'static' && !!currentContainingBlockComputedStyle && ['absolute', 'fixed'].includes(currentContainingBlockComputedStyle.position) || isOverflowElement(currentNode) && !currentNodeIsContaining && hasFixedPositionAncestor(element, currentNode);
      if (shouldDropCurrentNode) {
        // Drop non-containing blocks.
        result = result.filter(ancestor => ancestor !== currentNode);
      } else {
        // Record last containing block for next iteration.
        currentContainingBlockComputedStyle = computedStyle;
      }
      currentNode = getParentNode(currentNode);
    }
    cache.set(element, result);
    return result;
  }

  // Gets the maximum area that the element is visible in due to any number of
  // clipping ancestors.
  function getClippingRect(_ref) {
    let {
      element,
      boundary,
      rootBoundary,
      strategy
    } = _ref;
    const elementClippingAncestors = boundary === 'clippingAncestors' ? getClippingElementAncestors(element, this._c) : [].concat(boundary);
    const clippingAncestors = [...elementClippingAncestors, rootBoundary];
    const firstClippingAncestor = clippingAncestors[0];
    const clippingRect = clippingAncestors.reduce((accRect, clippingAncestor) => {
      const rect = getClientRectFromClippingAncestor(element, clippingAncestor, strategy);
      accRect.top = max(rect.top, accRect.top);
      accRect.right = min(rect.right, accRect.right);
      accRect.bottom = min(rect.bottom, accRect.bottom);
      accRect.left = max(rect.left, accRect.left);
      return accRect;
    }, getClientRectFromClippingAncestor(element, firstClippingAncestor, strategy));
    return {
      width: clippingRect.right - clippingRect.left,
      height: clippingRect.bottom - clippingRect.top,
      x: clippingRect.left,
      y: clippingRect.top
    };
  }

  function getDimensions(element) {
    return getCssDimensions(element);
  }

  function getTrueOffsetParent(element, polyfill) {
    if (!isHTMLElement(element) || getComputedStyle$1(element).position === 'fixed') {
      return null;
    }
    if (polyfill) {
      return polyfill(element);
    }
    return element.offsetParent;
  }
  function getContainingBlock(element) {
    let currentNode = getParentNode(element);
    while (isHTMLElement(currentNode) && !isLastTraversableNode(currentNode)) {
      if (isContainingBlock(currentNode)) {
        return currentNode;
      } else {
        currentNode = getParentNode(currentNode);
      }
    }
    return null;
  }

  // Gets the closest ancestor positioned element. Handles some edge cases,
  // such as table ancestors and cross browser bugs.
  function getOffsetParent(element, polyfill) {
    const window = getWindow(element);
    if (!isHTMLElement(element)) {
      return window;
    }
    let offsetParent = getTrueOffsetParent(element, polyfill);
    while (offsetParent && isTableElement(offsetParent) && getComputedStyle$1(offsetParent).position === 'static') {
      offsetParent = getTrueOffsetParent(offsetParent, polyfill);
    }
    if (offsetParent && (getNodeName(offsetParent) === 'html' || getNodeName(offsetParent) === 'body' && getComputedStyle$1(offsetParent).position === 'static' && !isContainingBlock(offsetParent))) {
      return window;
    }
    return offsetParent || getContainingBlock(element) || window;
  }

  function getRectRelativeToOffsetParent(element, offsetParent, strategy) {
    const isOffsetParentAnElement = isHTMLElement(offsetParent);
    const documentElement = getDocumentElement(offsetParent);
    const isFixed = strategy === 'fixed';
    const rect = getBoundingClientRect(element, true, isFixed, offsetParent);
    let scroll = {
      scrollLeft: 0,
      scrollTop: 0
    };
    const offsets = createEmptyCoords(0);
    if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
      if (getNodeName(offsetParent) !== 'body' || isOverflowElement(documentElement)) {
        scroll = getNodeScroll(offsetParent);
      }
      if (isHTMLElement(offsetParent)) {
        const offsetRect = getBoundingClientRect(offsetParent, true, isFixed, offsetParent);
        offsets.x = offsetRect.x + offsetParent.clientLeft;
        offsets.y = offsetRect.y + offsetParent.clientTop;
      } else if (documentElement) {
        offsets.x = getWindowScrollBarX(documentElement);
      }
    }
    return {
      x: rect.left + scroll.scrollLeft - offsets.x,
      y: rect.top + scroll.scrollTop - offsets.y,
      width: rect.width,
      height: rect.height
    };
  }

  const platform = {
    getClippingRect,
    convertOffsetParentRelativeRectToViewportRelativeRect,
    isElement,
    getDimensions,
    getOffsetParent,
    getDocumentElement,
    getScale,
    async getElementRects(_ref) {
      let {
        reference,
        floating,
        strategy
      } = _ref;
      const getOffsetParentFn = this.getOffsetParent || getOffsetParent;
      const getDimensionsFn = this.getDimensions;
      return {
        reference: getRectRelativeToOffsetParent(reference, await getOffsetParentFn(floating), strategy),
        floating: {
          x: 0,
          y: 0,
          ...(await getDimensionsFn(floating))
        }
      };
    },
    getClientRects: element => Array.from(element.getClientRects()),
    isRTL: element => getComputedStyle$1(element).direction === 'rtl'
  };

  // https://samthor.au/2021/observing-dom/
  function observeMove(element, onMove) {
    let io = null;
    let timeoutId;
    const root = getDocumentElement(element);
    function cleanup() {
      clearTimeout(timeoutId);
      io && io.disconnect();
      io = null;
    }
    function refresh(skip, threshold) {
      if (skip === void 0) {
        skip = false;
      }
      if (threshold === void 0) {
        threshold = 1;
      }
      cleanup();
      const {
        left,
        top,
        width,
        height
      } = element.getBoundingClientRect();
      if (!skip) {
        onMove();
      }
      if (!width || !height) {
        return;
      }
      const insetTop = floor(top);
      const insetRight = floor(root.clientWidth - (left + width));
      const insetBottom = floor(root.clientHeight - (top + height));
      const insetLeft = floor(left);
      const rootMargin = -insetTop + "px " + -insetRight + "px " + -insetBottom + "px " + -insetLeft + "px";
      let isFirstUpdate = true;
      io = new IntersectionObserver(entries => {
        const ratio = entries[0].intersectionRatio;
        if (ratio !== threshold) {
          if (!isFirstUpdate) {
            return refresh();
          }
          if (ratio === 0) {
            timeoutId = setTimeout(() => {
              refresh(false, 1e-7);
            }, 100);
          } else {
            refresh(false, ratio);
          }
        }
        isFirstUpdate = false;
      }, {
        rootMargin,
        threshold
      });
      io.observe(element);
    }
    refresh(true);
    return cleanup;
  }

  /**
   * Automatically updates the position of the floating element when necessary.
   * Should only be called when the floating element is mounted on the DOM or
   * visible on the screen.
   * @returns cleanup function that should be invoked when the floating element is
   * removed from the DOM or hidden from the screen.
   * @see https://floating-ui.com/docs/autoUpdate
   */
  function autoUpdate(reference, floating, update, options) {
    if (options === void 0) {
      options = {};
    }
    const {
      ancestorScroll = true,
      ancestorResize = true,
      elementResize = true,
      layoutShift = typeof IntersectionObserver === 'function',
      animationFrame = false
    } = options;
    const referenceEl = unwrapElement(reference);
    const ancestors = ancestorScroll || ancestorResize ? [...(referenceEl ? getOverflowAncestors(referenceEl) : []), ...getOverflowAncestors(floating)] : [];
    ancestors.forEach(ancestor => {
      ancestorScroll && ancestor.addEventListener('scroll', update, {
        passive: true
      });
      ancestorResize && ancestor.addEventListener('resize', update);
    });
    const cleanupIo = referenceEl && layoutShift ? observeMove(referenceEl, update) : null;
    let resizeObserver = null;
    if (elementResize) {
      resizeObserver = new ResizeObserver(update);
      if (referenceEl && !animationFrame) {
        resizeObserver.observe(referenceEl);
      }
      resizeObserver.observe(floating);
    }
    let frameId;
    let prevRefRect = animationFrame ? getBoundingClientRect(reference) : null;
    if (animationFrame) {
      frameLoop();
    }
    function frameLoop() {
      const nextRefRect = getBoundingClientRect(reference);
      if (prevRefRect && (nextRefRect.x !== prevRefRect.x || nextRefRect.y !== prevRefRect.y || nextRefRect.width !== prevRefRect.width || nextRefRect.height !== prevRefRect.height)) {
        update();
      }
      prevRefRect = nextRefRect;
      frameId = requestAnimationFrame(frameLoop);
    }
    update();
    return () => {
      ancestors.forEach(ancestor => {
        ancestorScroll && ancestor.removeEventListener('scroll', update);
        ancestorResize && ancestor.removeEventListener('resize', update);
      });
      cleanupIo && cleanupIo();
      resizeObserver && resizeObserver.disconnect();
      resizeObserver = null;
      if (animationFrame) {
        cancelAnimationFrame(frameId);
      }
    };
  }

  /**
   * Computes the `x` and `y` coordinates that will place the floating element
   * next to a reference element when it is given a certain CSS positioning
   * strategy.
   */
  const computePosition = (reference, floating, options) => {
    // This caches the expensive `getClippingElementAncestors` function so that
    // multiple lifecycle resets re-use the same result. It only lives for a
    // single call. If other functions become expensive, we can add them as well.
    const cache = new Map();
    const mergedOptions = {
      platform,
      ...options
    };
    const platformWithCache = {
      ...mergedOptions.platform,
      _c: cache
    };
    return core.computePosition(reference, floating, {
      ...mergedOptions,
      platform: platformWithCache
    });
  };

  Object.defineProperty(exports, 'arrow', {
    enumerable: true,
    get: function () { return core.arrow; }
  });
  Object.defineProperty(exports, 'autoPlacement', {
    enumerable: true,
    get: function () { return core.autoPlacement; }
  });
  Object.defineProperty(exports, 'detectOverflow', {
    enumerable: true,
    get: function () { return core.detectOverflow; }
  });
  Object.defineProperty(exports, 'flip', {
    enumerable: true,
    get: function () { return core.flip; }
  });
  Object.defineProperty(exports, 'hide', {
    enumerable: true,
    get: function () { return core.hide; }
  });
  Object.defineProperty(exports, 'inline', {
    enumerable: true,
    get: function () { return core.inline; }
  });
  Object.defineProperty(exports, 'limitShift', {
    enumerable: true,
    get: function () { return core.limitShift; }
  });
  Object.defineProperty(exports, 'offset', {
    enumerable: true,
    get: function () { return core.offset; }
  });
  Object.defineProperty(exports, 'shift', {
    enumerable: true,
    get: function () { return core.shift; }
  });
  Object.defineProperty(exports, 'size', {
    enumerable: true,
    get: function () { return core.size; }
  });
  exports.autoUpdate = autoUpdate;
  exports.computePosition = computePosition;
  exports.getOverflowAncestors = getOverflowAncestors;
  exports.platform = platform;

  Object.defineProperty(exports, '__esModule', { value: true });

}));
;
((Drupal,once,_ref)=>{let {computePosition,offset,shift,flip}=_ref;Drupal.theme.ginTooltipWrapper=(dataset,title)=>`<div class="gin-tooltip ${dataset.drupalTooltipClass||""}">\n      ${dataset.drupalTooltip||title}\n    </div>`,Drupal.behaviors.ginTooltip={attach:(context)=>{Drupal.ginTooltip.init(context);}},Drupal.ginTooltip={init:function(context){once("ginTooltipInit","[data-gin-tooltip]",context).forEach(((trigger)=>{const title=trigger.title;title&&(trigger.title=""),trigger.insertAdjacentHTML("afterend",Drupal.theme.ginTooltipWrapper(trigger.dataset,title));const tooltip=trigger.nextElementSibling,updatePosition=()=>{this.computePosition(trigger,tooltip);};new ResizeObserver(updatePosition).observe(trigger),new MutationObserver(updatePosition).observe(trigger,{attributes:!0,childList:!0,subtree:!0}),trigger.addEventListener("mouseover",updatePosition),trigger.addEventListener("focus",updatePosition);}));},computePosition:function(trigger,tooltip){let placement=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"bottom-end";computePosition(trigger,tooltip,{strategy:"absolute",placement:trigger.dataset.drupalTooltipPosition||placement,middleware:[flip({padding:16}),offset(6),shift({padding:16})]}).then(((_ref2)=>{let {x,y}=_ref2;Object.assign(tooltip.style,{"inset-inline-start":`${x}px`,"inset-block-start":`${y}px`});}));}};})(Drupal,once,FloatingUIDOM);;
((Drupal)=>{Drupal.behaviors.ginSticky={attach:()=>{once("ginSticky",".region-sticky-watcher").forEach((()=>{const observer=new IntersectionObserver(((_ref)=>{let [e]=_ref;const regionSticky=document.querySelector(".region-sticky");regionSticky.classList.toggle("region-sticky--is-sticky",e.intersectionRatio<1),regionSticky.toggleAttribute("data-offset-top",e.intersectionRatio<1),Drupal.displace(!0);}),{threshold:[1]}),element=document.querySelector(".region-sticky-watcher");element&&observer.observe(element);}));}};})(Drupal);;
/* @license GPL-2.0-or-later https://raw.githubusercontent.com/ckeditor/ckeditor5/v47.6.0/LICENSE.md */
/*!
 * @license Copyright (c) 2003-2026, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md.
 */(()=>{var e={163:e=>{"use strict";e.exports=function(e){var t=document.createElement("style");return e.setAttributes(t,e.attributes),e.insert(t,e.options),t}},237:e=>{"use strict";e.exports=CKEditor5.dll},305:e=>{"use strict";e.exports=function(e){var t=[];return t.toString=function(){return this.map(function(t){var i="",o=void 0!==t[5];return t[4]&&(i+="@supports (".concat(t[4],") {")),t[2]&&(i+="@media ".concat(t[2]," {")),o&&(i+="@layer".concat(t[5].length>0?" ".concat(t[5]):""," {")),i+=e(t),o&&(i+="}"),t[2]&&(i+="}"),t[4]&&(i+="}"),i}).join("")},t.i=function(e,i,o,r,n){"string"==typeof e&&(e=[[null,e,void 0]]);var s={};if(o)for(var a=0;a<this.length;a++){var c=this[a][0];null!=c&&(s[c]=!0)}for(var l=0;l<e.length;l++){var d=[].concat(e[l]);o&&s[d[0]]||(void 0!==n&&(void 0===d[5]||(d[1]="@layer".concat(d[5].length>0?" ".concat(d[5]):""," {").concat(d[1],"}")),d[5]=n),i&&(d[2]?(d[1]="@media ".concat(d[2]," {").concat(d[1],"}"),d[2]=i):d[2]=i),r&&(d[4]?(d[1]="@supports (".concat(d[4],") {").concat(d[1],"}"),d[4]=r):d[4]="".concat(r)),t.push(d))}},t}},311:(e,t,i)=>{e.exports=i(237)("./src/ui.js")},424:e=>{"use strict";var t={};e.exports=function(e,i){var o=function(e){if(void 0===t[e]){var i=document.querySelector(e);if(window.HTMLIFrameElement&&i instanceof window.HTMLIFrameElement)try{i=i.contentDocument.head}catch(e){i=null}t[e]=i}return t[e]}(e);if(!o)throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");o.appendChild(i)}},517:e=>{"use strict";e.exports=function(e,t){Object.keys(t).forEach(function(i){e.setAttribute(i,t[i])})}},584:(e,t,i)=>{e.exports=i(237)("./src/utils.js")},719:e=>{"use strict";var t=[];function i(e){for(var i=-1,o=0;o<t.length;o++)if(t[o].identifier===e){i=o;break}return i}function o(e,o){for(var n={},s=[],a=0;a<e.length;a++){var c=e[a],l=o.base?c[0]+o.base:c[0],d=n[l]||0,u="".concat(l," ").concat(d);n[l]=d+1;var h=i(u),p={css:c[1],media:c[2],sourceMap:c[3],supports:c[4],layer:c[5]};if(-1!==h)t[h].references++,t[h].updater(p);else{var f=r(p,o);o.byIndex=a,t.splice(a,0,{identifier:u,updater:f,references:1})}s.push(u)}return s}function r(e,t){var i=t.domAPI(t);i.update(e);return function(t){if(t){if(t.css===e.css&&t.media===e.media&&t.sourceMap===e.sourceMap&&t.supports===e.supports&&t.layer===e.layer)return;i.update(e=t)}else i.remove()}}e.exports=function(e,r){var n=o(e=e||[],r=r||{});return function(e){e=e||[];for(var s=0;s<n.length;s++){var a=i(n[s]);t[a].references--}for(var c=o(e,r),l=0;l<n.length;l++){var d=i(n[l]);0===t[d].references&&(t[d].updater(),t.splice(d,1))}n=c}}},782:(e,t,i)=>{e.exports=i(237)("./src/core.js")},783:(e,t,i)=>{e.exports=i(237)("./src/engine.js")},792:e=>{"use strict";e.exports=function(e){return e[1]}},808:(e,t,i)=>{"use strict";i.d(t,{A:()=>a});var o=i(792),r=i.n(o),n=i(305),s=i.n(n)()(r());s.push([e.id,".ck.ck-editor{position:relative}.ck.ck-editor .ck-editor__top .ck-sticky-panel .ck-toolbar{z-index:var(--ck-z-panel)}.ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content{border:solid var(--ck-color-base-border);border-radius:0;border-width:1px 1px 0}.ck-rounded-corners .ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content,.ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content.ck-rounded-corners{border-radius:var(--ck-border-radius);border-bottom-left-radius:0;border-bottom-right-radius:0}.ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content.ck-sticky-panel__content_sticky{border-bottom-width:1px}.ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content .ck-menu-bar{border:0;border-bottom:1px solid var(--ck-color-base-border)}.ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content .ck-toolbar{border:0}.ck.ck-editor__main>.ck-editor__editable{background:var(--ck-color-base-background);border-radius:0}.ck-rounded-corners .ck.ck-editor__main>.ck-editor__editable,.ck.ck-editor__main>.ck-editor__editable.ck-rounded-corners{border-radius:var(--ck-border-radius);border-top-left-radius:0;border-top-right-radius:0}.ck.ck-editor__main>.ck-editor__editable:not(.ck-focused){border-color:var(--ck-color-base-border)}",""]);const a=s},863:e=>{"use strict";var t,i=(t=[],function(e,i){return t[e]=i,t.filter(Boolean).join("\n")});function o(e,t,o,r){var n;if(o)n="";else{n="",r.supports&&(n+="@supports (".concat(r.supports,") {")),r.media&&(n+="@media ".concat(r.media," {"));var s=void 0!==r.layer;s&&(n+="@layer".concat(r.layer.length>0?" ".concat(r.layer):""," {")),n+=r.css,s&&(n+="}"),r.media&&(n+="}"),r.supports&&(n+="}")}if(e.styleSheet)e.styleSheet.cssText=i(t,n);else{var a=document.createTextNode(n),c=e.childNodes;c[t]&&e.removeChild(c[t]),c.length?e.insertBefore(a,c[t]):e.appendChild(a)}}var r={singleton:null,singletonCounter:0};e.exports=function(e){if("undefined"==typeof document)return{update:function(){},remove:function(){}};var t=r.singletonCounter++,i=r.singleton||(r.singleton=e.insertStyleElement(e));return{update:function(e){o(i,t,!1,e)},remove:function(e){o(i,t,!0,e)}}}}},t={};function i(o){var r=t[o];if(void 0!==r)return r.exports;var n=t[o]={id:o,exports:{}};return e[o](n,n.exports,i),n.exports}i.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return i.d(t,{a:t}),t},i.d=(e,t)=>{for(var o in t)i.o(t,o)&&!i.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},i.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),i.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var o={};(()=>{"use strict";i.r(o),i.d(o,{ClassicEditor:()=>w,ClassicEditorUI:()=>n,ClassicEditorUIView:()=>k});var e=i(311),t=i(783),r=i(584);class n extends e.EditorUI{view;_toolbarConfig;_elementReplacer;constructor(t,i){super(t),this.view=i,this._toolbarConfig=(0,e.normalizeToolbarConfig)(t.config.get("toolbar")),this._elementReplacer=new r.ElementReplacer,this.listenTo(t.editing.view,"scrollToTheSelection",this._handleScrollToTheSelectionWithStickyPanel.bind(this))}get element(){return this.view.element}init(e){const t=this.editor,i=this.view,o=t.editing.view,r=i.editable,n=o.document.getRoot();r.name=n.rootName,i.render();const s=r.element;this.setEditableElement(r.name,s),i.editable.bind("isFocused").to(this.focusTracker),o.attachDomRoot(s),e&&this._elementReplacer.replace(e,this.element),this._initPlaceholder(),this._initToolbar(),i.menuBarView&&this.initMenuBar(i.menuBarView),this._initDialogPluginIntegration(),this._initContextualBalloonIntegration(),this.fire("ready")}destroy(){super.destroy();const e=this.view,t=this.editor.editing.view;this._elementReplacer.restore(),t.getDomRoot(e.editable.name)&&t.detachDomRoot(e.editable.name),e.destroy()}_initToolbar(){const e=this.view;e.stickyPanel.bind("isActive").to(this.focusTracker,"isFocused"),e.stickyPanel.limiterElement=e.element,e.stickyPanel.bind("viewportTopOffset").to(this,"viewportOffset",({visualTop:e})=>e||0),e.toolbar.fillFromConfig(this._toolbarConfig,this.componentFactory),this.addToolbar(e.toolbar)}_initPlaceholder(){const e=this.editor,i=e.editing.view,o=i.document.getRoot(),r=e.sourceElement;let n;const s=e.config.get("placeholder");s&&(n="string"==typeof s?s:s[this.view.editable.name]),!n&&r&&"textarea"===r.tagName.toLowerCase()&&(n=r.getAttribute("placeholder")),n&&(o.placeholder=n),(0,t.enableViewPlaceholder)({view:i,element:o,isDirectHost:!1,keepOnFocus:!0})}_initContextualBalloonIntegration(){if(!this.editor.plugins.has("ContextualBalloon"))return;const{stickyPanel:e}=this.view,t=this.editor.plugins.get("ContextualBalloon");t.on("getPositionOptions",t=>{const i=t.return;if(!i||!e.isSticky||!e.element)return;const o=new r.Rect(e.element).height,n="function"==typeof i.target?i.target():i.target,s="function"==typeof i.limiter?i.limiter():i.limiter;if(n&&s&&new r.Rect(n).height>=new r.Rect(s).height-o)return;const a={...i.viewportOffsetConfig},c=(a.top||0)+o;t.return={...i,viewportOffsetConfig:{...a,top:c}}},{priority:"low"});const i=()=>{t.visibleView&&t.updatePosition()};this.listenTo(e,"change:isSticky",i),this.listenTo(this.editor.ui,"change:viewportOffset",i)}_handleScrollToTheSelectionWithStickyPanel(e,t,i){const o=this.view.stickyPanel;if(o.isSticky){const e=new r.Rect(o.element).height;t.viewportOffset.top+=e}else{const e=()=>{this.editor.editing.view.scrollToTheSelection(i)};this.listenTo(o,"change:isSticky",e),setTimeout(()=>{this.stopListening(o,"change:isSticky",e)},20)}}_initDialogPluginIntegration(){if(!this.editor.plugins.has("Dialog"))return;const t=this.view.stickyPanel,i=this.editor.plugins.get("Dialog");i.on("show",()=>{const o=i.view;o.on("moveTo",(i,n)=>{if(!t.isSticky||o.wasMoved||o.isModal)return;const s=new r.Rect(t.contentPanelElement);n[1]<s.bottom+e.DialogView.defaultOffset&&(n[1]=s.bottom+e.DialogView.defaultOffset)},{priority:"high"})},{priority:"low"})}}var s=i(719),a=i.n(s),c=i(863),l=i.n(c),d=i(424),u=i.n(d),h=i(517),p=i.n(h),f=i(163),b=i.n(f),g=i(808),m={attributes:{"data-cke":!0}};m.setAttributes=p(),m.insert=u().bind(null,"head"),m.domAPI=l(),m.insertStyleElement=b();a()(g.A,m);g.A&&g.A.locals&&g.A.locals;class k extends e.BoxedEditorUIView{stickyPanel;toolbar;editable;constructor(t,i,o={}){super(t),this.stickyPanel=new e.StickyPanelView(t),this.toolbar=new e.ToolbarView(t,{shouldGroupWhenFull:o.shouldToolbarGroupWhenFull}),o.useMenuBar&&(this.menuBarView=new e.MenuBarView(t)),this.editable=new e.InlineEditableUIView(t,i,void 0,{label:o.label})}render(){super.render(),this.menuBarView?this.stickyPanel.content.addMany([this.menuBarView,this.toolbar]):this.stickyPanel.content.add(this.toolbar),this.top.add(this.stickyPanel),this.main.add(this.editable)}}var v=i(782);function y(e){return function(e){return"object"==typeof e&&null!==e}(e)&&1===e.nodeType&&!function(e){if("object"!=typeof e)return!1;if(null==e)return!1;if(null===Object.getPrototypeOf(e))return!0;if("[object Object]"!==Object.prototype.toString.call(e)){const t=e[Symbol.toStringTag];return null!=t&&(!!Object.getOwnPropertyDescriptor(e,Symbol.toStringTag)?.writable&&e.toString()===`[object ${t}]`)}let t=e;for(;null!==Object.getPrototypeOf(t);)t=Object.getPrototypeOf(t);return Object.getPrototypeOf(e)===t}(e)}class w extends((0,v.ElementApiMixin)(v.Editor)){static get editorName(){return"ClassicEditor"}ui;constructor(e,t={}){if(!_(e)&&void 0!==t.initialData)throw new r.CKEditorError("editor-create-initial-data",null);super(t),this.config.define("menuBar.isVisible",!1),void 0===this.config.get("initialData")&&this.config.set("initialData",function(e){return _(e)?(0,r.getDataFromElement)(e):e}(e)),_(e)&&(this.sourceElement=e),this.model.document.createRoot();const i=!this.config.get("toolbar.shouldNotGroupWhenFull"),o=this.config.get("menuBar"),s=new k(this.locale,this.editing.view,{shouldToolbarGroupWhenFull:i,useMenuBar:o.isVisible,label:this.config.get("label")});this.ui=new n(this,s),(0,v.attachToForm)(this)}destroy(){return this.sourceElement&&this.updateSourceElement(),this.ui.destroy(),super.destroy()}static create(e,t={}){return new Promise(i=>{const o=new this(e,t);i(o.initPlugins().then(()=>o.ui.init(_(e)?e:null)).then(()=>o.data.init(o.config.get("initialData"))).then(()=>o.fire("ready")).then(()=>o))})}}function _(e){return y(e)}})(),(window.CKEditor5=window.CKEditor5||{}).editorClassic=o})();;
!function(t){const e=t.en=t.en||{};e.dictionary=Object.assign(e.dictionary||{},{"HTML object":"HTML object"})}(window.CKEDITOR_TRANSLATIONS||(window.CKEDITOR_TRANSLATIONS={})),
/*!
 * @license Copyright (c) 2003-2026, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md.
 */(()=>{var t={163:t=>{"use strict";t.exports=function(t){var e=document.createElement("style");return t.setAttributes(e,t.attributes),t.insert(e,t.options),e}},237:t=>{"use strict";t.exports=CKEditor5.dll},305:t=>{"use strict";t.exports=function(t){var e=[];return e.toString=function(){return this.map(function(e){var r="",i=void 0!==e[5];return e[4]&&(r+="@supports (".concat(e[4],") {")),e[2]&&(r+="@media ".concat(e[2]," {")),i&&(r+="@layer".concat(e[5].length>0?" ".concat(e[5]):""," {")),r+=t(e),i&&(r+="}"),e[2]&&(r+="}"),e[4]&&(r+="}"),r}).join("")},e.i=function(t,r,i,o,n){"string"==typeof t&&(t=[[null,t,void 0]]);var l={};if(i)for(var s=0;s<this.length;s++){var a=this[s][0];null!=a&&(l[a]=!0)}for(var m=0;m<t.length;m++){var c=[].concat(t[m]);i&&l[c[0]]||(void 0!==n&&(void 0===c[5]||(c[1]="@layer".concat(c[5].length>0?" ".concat(c[5]):""," {").concat(c[1],"}")),c[5]=n),r&&(c[2]?(c[1]="@media ".concat(c[2]," {").concat(c[1],"}"),c[2]=r):c[2]=r),o&&(c[4]?(c[1]="@supports (".concat(c[4],") {").concat(c[1],"}"),c[4]=o):c[4]="".concat(o)),e.push(c))}},e}},424:t=>{"use strict";var e={};t.exports=function(t,r){var i=function(t){if(void 0===e[t]){var r=document.querySelector(t);if(window.HTMLIFrameElement&&r instanceof window.HTMLIFrameElement)try{r=r.contentDocument.head}catch(t){r=null}e[t]=r}return e[t]}(t);if(!i)throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");i.appendChild(r)}},507:(t,e,r)=>{t.exports=r(237)("./src/enter.js")},517:t=>{"use strict";t.exports=function(t,e){Object.keys(e).forEach(function(r){t.setAttribute(r,e[r])})}},584:(t,e,r)=>{t.exports=r(237)("./src/utils.js")},617:(t,e,r)=>{"use strict";r.d(e,{A:()=>s});var i=r(792),o=r.n(i),n=r(305),l=r.n(n)()(o());l.push([t.id,":root{--ck-html-object-embed-unfocused-outline-width:1px}.ck-widget.html-object-embed{background-color:var(--ck-color-base-foreground);font-size:var(--ck-font-size-base);min-width:calc(76px + var(--ck-spacing-standard));padding:var(--ck-spacing-small);padding-top:calc(var(--ck-font-size-tiny) + var(--ck-spacing-large))}.ck-widget.html-object-embed:not(.ck-widget_selected):not(:hover){outline:var(--ck-html-object-embed-unfocused-outline-width) dashed var(--ck-color-widget-blurred-border)}.ck-widget.html-object-embed:before{background:#999;border-radius:0 0 var(--ck-border-radius) var(--ck-border-radius);color:var(--ck-color-base-background);content:attr(data-html-object-embed-label);font-family:var(--ck-font-face);font-size:var(--ck-font-size-tiny);font-style:normal;font-weight:400;left:var(--ck-spacing-standard);padding:calc(var(--ck-spacing-tiny) + var(--ck-html-object-embed-unfocused-outline-width)) var(--ck-spacing-small) var(--ck-spacing-tiny);position:absolute;top:0;transition:background var(--ck-widget-handler-animation-duration) var(--ck-widget-handler-animation-curve)}.ck-widget.html-object-embed .ck-widget__type-around .ck-widget__type-around__button.ck-widget__type-around__button_before{margin-left:50px}.ck-widget.html-object-embed .html-object-embed__content{pointer-events:none}div.ck-widget.html-object-embed{margin:1em auto}span.ck-widget.html-object-embed{display:inline-block}",""]);const s=l},719:t=>{"use strict";var e=[];function r(t){for(var r=-1,i=0;i<e.length;i++)if(e[i].identifier===t){r=i;break}return r}function i(t,i){for(var n={},l=[],s=0;s<t.length;s++){var a=t[s],m=i.base?a[0]+i.base:a[0],c=n[m]||0,u="".concat(m," ").concat(c);n[m]=c+1;var d=r(u),h={css:a[1],media:a[2],sourceMap:a[3],supports:a[4],layer:a[5]};if(-1!==d)e[d].references++,e[d].updater(h);else{var f=o(h,i);i.byIndex=s,e.splice(s,0,{identifier:u,updater:f,references:1})}l.push(u)}return l}function o(t,e){var r=e.domAPI(e);r.update(t);return function(e){if(e){if(e.css===t.css&&e.media===t.media&&e.sourceMap===t.sourceMap&&e.supports===t.supports&&e.layer===t.layer)return;r.update(t=e)}else r.remove()}}t.exports=function(t,o){var n=i(t=t||[],o=o||{});return function(t){t=t||[];for(var l=0;l<n.length;l++){var s=r(n[l]);e[s].references--}for(var a=i(t,o),m=0;m<n.length;m++){var c=r(n[m]);0===e[c].references&&(e[c].updater(),e.splice(c,1))}n=a}}},782:(t,e,r)=>{t.exports=r(237)("./src/core.js")},783:(t,e,r)=>{t.exports=r(237)("./src/engine.js")},792:t=>{"use strict";t.exports=function(t){return t[1]}},863:t=>{"use strict";var e,r=(e=[],function(t,r){return e[t]=r,e.filter(Boolean).join("\n")});function i(t,e,i,o){var n;if(i)n="";else{n="",o.supports&&(n+="@supports (".concat(o.supports,") {")),o.media&&(n+="@media ".concat(o.media," {"));var l=void 0!==o.layer;l&&(n+="@layer".concat(o.layer.length>0?" ".concat(o.layer):""," {")),n+=o.css,l&&(n+="}"),o.media&&(n+="}"),o.supports&&(n+="}")}if(t.styleSheet)t.styleSheet.cssText=r(e,n);else{var s=document.createTextNode(n),a=t.childNodes;a[e]&&t.removeChild(a[e]),a.length?t.insertBefore(s,a[e]):t.appendChild(s)}}var o={singleton:null,singletonCounter:0};t.exports=function(t){if("undefined"==typeof document)return{update:function(){},remove:function(){}};var e=o.singletonCounter++,r=o.singleton||(o.singleton=t.insertStyleElement(t));return{update:function(t){i(r,e,!1,t)},remove:function(t){i(r,e,!0,t)}}}},901:(t,e,r)=>{t.exports=r(237)("./src/widget.js")}},e={};function r(i){var o=e[i];if(void 0!==o)return o.exports;var n=e[i]={id:i,exports:{}};return t[i](n,n.exports,r),n.exports}r.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return r.d(e,{a:e}),e},r.d=(t,e)=>{for(var i in e)r.o(e,i)&&!r.o(t,i)&&Object.defineProperty(t,i,{enumerable:!0,get:e[i]})},r.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),r.r=t=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})};var i={};(()=>{"use strict";r.r(i),r.d(i,{CodeBlockElementSupport:()=>Pt,CustomElementSupport:()=>Qt,DataFilter:()=>Et,DataSchema:()=>mt,DualContentModelElementSupport:()=>Ft,EmptyBlock:()=>oe,FullPage:()=>ee,GeneralHtmlSupport:()=>Yt,HeadingElementSupport:()=>_t,HorizontalLineElementSupport:()=>Kt,HtmlComment:()=>Zt,HtmlPageDataProcessor:()=>te,IframeElementSupport:()=>Xt,ImageElementSupport:()=>xt,ListElementSupport:()=>Wt,MediaEmbedElementSupport:()=>Tt,ScriptElementSupport:()=>Bt,StyleElementSupport:()=>Vt,TableElementSupport:()=>Ht,_HTML_SUPPORT_SCHEMA_DEFINITIONS:()=>et,_attributeToInlineHtmlSupportConverter:()=>J,_createObjectHtmlSupportView:()=>X,_emptyInlineModelElementToViewHtmlSupportConverter:()=>Y,_getHtmlSupportAttributeName:()=>q,_getHtmlSupportDescendantElement:()=>$t,_mergeHtmlSupportViewElementAttributes:()=>L,_modelToViewBlockAttributeHtmlSupportConverter:()=>tt,_modifyHtmlSupportGhsAttribute:()=>U,_removeHtmlSupportViewAttributes:()=>M,_setHtmlSupportViewAttributes:()=>N,_toHtmlSupportPascalCase:()=>W,_toObjectWidgetHtmlSupportConverter:()=>K,_updateHtmlSupportViewAttributes:()=>V,_viewToAttributeInlineHtmlSupportConverter:()=>Q,_viewToModelBlockAttributeHtmlSupportConverter:()=>Z,_viewToModelObjectContentHtmlSupportConverter:()=>G});var t=r(782),e=r(584),o=r(783),n=r(901);function l(t){return Object.getOwnPropertySymbols(t).filter(e=>Object.prototype.propertyIsEnumerable.call(t,e))}function s(t){return null==t?void 0===t?"[object Undefined]":"[object Null]":Object.prototype.toString.call(t)}const a="[object RegExp]",m="[object String]",c="[object Number]",u="[object Boolean]",d="[object Arguments]",h="[object Symbol]",f="[object Date]",g="[object Map]",b="[object Set]",p="[object Array]",w="[object ArrayBuffer]",A="[object Object]",y="[object DataView]",v="[object Uint8Array]",E="[object Uint8ClampedArray]",S="[object Uint16Array]",O="[object Uint32Array]",k="[object Int8Array]",C="[object Int16Array]",j="[object Int32Array]",P="[object Float32Array]",F="[object Float64Array]";function I(t){return null==t||"object"!=typeof t&&"function"!=typeof t}function _(t){return ArrayBuffer.isView(t)&&!(t instanceof DataView)}function $(t,e,r,i=new Map,o=void 0){const n=o?.(t,e,r,i);if(null!=n)return n;if(I(t))return t;if(i.has(t))return i.get(t);if(Array.isArray(t)){const e=new Array(t.length);i.set(t,e);for(let n=0;n<t.length;n++)e[n]=$(t[n],n,r,i,o);return Object.hasOwn(t,"index")&&(e.index=t.index),Object.hasOwn(t,"input")&&(e.input=t.input),e}if(t instanceof Date)return new Date(t.getTime());if(t instanceof RegExp){const e=new RegExp(t.source,t.flags);return e.lastIndex=t.lastIndex,e}if(t instanceof Map){const e=new Map;i.set(t,e);for(const[n,l]of t)e.set(n,$(l,n,r,i,o));return e}if(t instanceof Set){const e=new Set;i.set(t,e);for(const n of t)e.add($(n,void 0,r,i,o));return e}if("undefined"!=typeof Buffer&&Buffer.isBuffer(t))return t.subarray();if(_(t)){const e=new(Object.getPrototypeOf(t).constructor)(t.length);i.set(t,e);for(let n=0;n<t.length;n++)e[n]=$(t[n],n,r,i,o);return e}if(t instanceof ArrayBuffer||"undefined"!=typeof SharedArrayBuffer&&t instanceof SharedArrayBuffer)return t.slice(0);if(t instanceof DataView){const e=new DataView(t.buffer.slice(0),t.byteOffset,t.byteLength);return i.set(t,e),x(e,t,r,i,o),e}if("undefined"!=typeof File&&t instanceof File){const e=new File([t],t.name,{type:t.type});return i.set(t,e),x(e,t,r,i,o),e}if(t instanceof Blob){const e=new Blob([t],{type:t.type});return i.set(t,e),x(e,t,r,i,o),e}if(t instanceof Error){const e=new t.constructor;return i.set(t,e),e.message=t.message,e.name=t.name,e.stack=t.stack,e.cause=t.cause,x(e,t,r,i,o),e}if("object"==typeof t&&function(t){switch(s(t)){case d:case p:case w:case y:case u:case f:case P:case F:case k:case C:case j:case g:case c:case A:case a:case b:case m:case h:case v:case E:case S:case O:return!0;default:return!1}}(t)){const e=Object.create(Object.getPrototypeOf(t));return i.set(t,e),x(e,t,r,i,o),e}return t}function x(t,e,r=t,i,o){const n=[...Object.keys(e),...l(e)];for(let l=0;l<n.length;l++){const s=n[l],a=Object.getOwnPropertyDescriptor(t,s);(null==a||a.writable)&&(t[s]=$(e[s],s,r,i,o))}}function T(t,e){return function(t,e){return $(t,void 0,t,new Map,e)}(t,(r,i,o,n)=>{const l=e?.(r,i,o,n);if(null!=l)return l;if("object"==typeof t)switch(Object.prototype.toString.call(t)){case c:case m:case u:{const e=new t.constructor(t?.valueOf());return x(e,t),e}case d:{const e={};return x(e,t),e.length=t.length,e[Symbol.iterator]=t[Symbol.iterator],e}default:return}})}function B(t){return T(t)}const D=/\p{Lu}?\p{Ll}+|[0-9]+|\p{Lu}+(?!\p{Ll})|\p{Emoji_Presentation}|\p{Extended_Pictographic}|\p{L}+/gu;function H(t){if(null==t)return"";if("string"==typeof t)return t;if(Array.isArray(t))return t.map(H).join(",");const e=String(t);return"0"===e&&Object.is(Number(t),-0)?"-0":e}function R(t){const e=function(t){return Array.from(t.match(D)??[])}(function(t){return"string"!=typeof t&&(t=H(t)),t.replace(/['\u2019]/g,"")}(t).trim());let r="";for(let t=0;t<e.length;t++){const i=e[t];r&&(r+=" "),i===i.toUpperCase()?r+=i:r+=i[0].toUpperCase()+i.slice(1).toLowerCase()}return r}function V(t,e,r,i){e&&M(t,e,i),r&&N(t,r,i)}function N(t,e,r){if(e.attributes)for(const[i,o]of Object.entries(e.attributes))t.setAttribute(i,o,r);e.styles&&t.setStyle(e.styles,r),e.classes&&t.addClass(e.classes,r)}function M(t,e,r){if(e.attributes)for(const[i]of Object.entries(e.attributes))t.removeAttribute(i,r);if(e.styles)for(const i of Object.keys(e.styles))t.removeStyle(i,r);e.classes&&t.removeClass(e.classes,r)}function L(t,e){const r=B(t);let i="attributes";for(i in e)r[i]="classes"==i?Array.from(new Set([...t[i]||[],...e[i]])):{...t[i],...e[i]};return r}function U(t,e,r,i,o){const n=e.getAttribute(r),l={};for(const t of["attributes","styles","classes"]){if(t!=i){n&&n[t]&&(l[t]=n[t]);continue}if("classes"==i){const e=new Set(n&&n.classes||[]);o(e),e.size&&(l[t]=Array.from(e));continue}const e=new Map(Object.entries(n&&n[t]||{}));o(e),e.size&&(l[t]=Object.fromEntries(e))}Object.keys(l).length?e.is("documentSelection")?t.setSelectionAttribute(r,l):t.setAttribute(r,l,e):n&&(e.is("documentSelection")?t.removeSelectionAttribute(r):t.removeAttribute(r,e))}function z(t,e,r){for(const i of e.getItems({shallow:!0})){const e=i.getAttribute(t);e&&e.attributes&&Object.keys(e.attributes).length?Object.keys(e).length>1&&r.setAttribute(t,{attributes:e.attributes},i):r.removeAttribute(t,i)}}function W(t){return R(t).replace(/ /g,"")}function q(t){return`html${W(t)}Attributes`}function G({model:t}){return(e,r)=>r.writer.createElement(t,{htmlContent:e.getCustomProperty("$rawContent")})}function K(t,{view:e,isInline:r}){const i=t.t;return(t,{writer:o})=>{const l=i("HTML object"),s=X(e,t,o),a=t.getAttribute(q(e));o.addClass("html-object-embed__content",s),a&&N(o,a,s);const m=o.createContainerElement(r?"span":"div",{class:"html-object-embed","data-html-object-embed-label":l},s);return(0,n.toWidget)(m,o,{label:l})}}function X(t,e,r){return r.createRawElement(t,null,(t,r)=>{r.setContentOf(t,e.getAttribute("htmlContent"))})}function Q({view:t,model:e,allowEmpty:r},i){return e=>{e.on(`element:${t}`,(t,e,n)=>{let l=i.processViewAttributes(e.viewItem,n);if(l||n.consumable.test(e.viewItem,{name:!0})){if(l=l||{},n.consumable.consume(e.viewItem,{name:!0}),e.modelRange||(e=Object.assign(e,n.convertChildren(e.viewItem,e.modelCursor))),r&&e.modelRange.isCollapsed&&Object.keys(l).length){const t=n.writer.createElement("htmlEmptyElement");if(!n.safeInsert(t,e.modelCursor))return;const r=n.getSplitParts(t);return e.modelRange=n.writer.createRange(e.modelRange.start,n.writer.createPositionAfter(r[r.length-1])),n.updateConversionResult(t,e),void o(t,l,n)}for(const t of e.modelRange.getItems())o(t,l,n)}},{priority:"low"})};function o(t,r,i){if(i.schema.checkAttribute(t,e)){const o=L(r,t.getAttribute(e)||{});i.writer.setAttribute(e,o,t)}}}function Y({model:t,view:e},r){return(i,{writer:o,consumable:l})=>{if(!i.hasAttribute(t))return null;const s=o.createContainerElement(e),a=i.getAttribute(t);return l.consume(i,`attribute:${t}`),N(o,a,s),s.getFillerOffset=()=>null,r?(0,n.toWidget)(s,o):s}}function J({priority:t,view:e}){return(r,i)=>{if(!r)return;const{writer:o}=i,n=o.createAttributeElement(e,null,{priority:t});return N(o,r,n),n}}function Z({view:t},e){return r=>{r.on(`element:${t}`,(t,r,i)=>{if(!r.modelRange||r.modelRange.isCollapsed)return;const o=e.processViewAttributes(r.viewItem,i);o&&i.writer.setAttribute(q(r.viewItem.name),o,r.modelRange)},{priority:"low"})}}function tt({view:t,model:e}){return r=>{r.on(`attribute:${q(t)}:${e}`,(t,e,r)=>{if(!r.consumable.consume(e.item,t.name))return;const{attributeOldValue:i,attributeNewValue:o}=e;V(r.writer,i,o,r.mapper.toViewElement(e.item))})}}const et={block:[{model:"codeBlock",view:"pre"},{model:"paragraph",view:"p"},{model:"blockQuote",view:"blockquote"},{model:"listItem",view:"li"},{model:"pageBreak",view:"div"},{model:"rawHtml",view:"div"},{model:"table",view:"table"},{model:"tableRow",view:"tr"},{model:"tableCell",view:"td"},{model:"tableCell",view:"th"},{model:"tableColumnGroup",view:"colgroup"},{model:"tableColumn",view:"col"},{model:"caption",view:"caption"},{model:"caption",view:"figcaption"},{model:"imageBlock",view:"img"},{model:"imageInline",view:"img"},{model:"horizontalLine",view:"hr"},{model:"htmlP",view:"p",modelSchema:{inheritAllFrom:"$block"}},{model:"htmlBlockquote",view:"blockquote",modelSchema:{inheritAllFrom:"$container"}},{model:"htmlTable",view:"table",modelSchema:{allowWhere:"$block",isBlock:!0}},{model:"htmlTbody",view:"tbody",modelSchema:{allowIn:"htmlTable",isBlock:!1}},{model:"htmlThead",view:"thead",modelSchema:{allowIn:"htmlTable",isBlock:!1}},{model:"htmlTfoot",view:"tfoot",modelSchema:{allowIn:"htmlTable",isBlock:!1}},{model:"htmlCaption",view:"caption",modelSchema:{allowIn:"htmlTable",allowChildren:"$text",isBlock:!1}},{model:"htmlColgroup",view:"colgroup",modelSchema:{allowIn:"htmlTable",allowChildren:"col",isBlock:!1}},{model:"htmlCol",view:"col",modelSchema:{allowIn:"htmlColgroup",isBlock:!1}},{model:"htmlTr",view:"tr",modelSchema:{allowIn:["htmlTable","htmlThead","htmlTbody"],isLimit:!0}},{model:"htmlTd",view:"td",modelSchema:{allowIn:"htmlTr",allowContentOf:"$container",isLimit:!0,isBlock:!1}},{model:"htmlTh",view:"th",modelSchema:{allowIn:"htmlTr",allowContentOf:"$container",isLimit:!0,isBlock:!1}},{model:"htmlFigure",view:"figure",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlFigcaption",view:"figcaption",modelSchema:{allowIn:"htmlFigure",allowChildren:"$text",isBlock:!1}},{model:"htmlAddress",view:"address",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlAside",view:"aside",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlMain",view:"main",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlDetails",view:"details",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlSummary",view:"summary",modelSchema:{allowChildren:["htmlH1","htmlH2","htmlH3","htmlH4","htmlH5","htmlH6","$text"],allowIn:"htmlDetails",isBlock:!1}},{model:"htmlDiv",view:"div",paragraphLikeModel:"htmlDivParagraph",modelSchema:{inheritAllFrom:"$container"}},{model:"htmlFieldset",view:"fieldset",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlLegend",view:"legend",modelSchema:{allowIn:"htmlFieldset",allowChildren:"$text"}},{model:"htmlHeader",view:"header",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlFooter",view:"footer",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlForm",view:"form",modelSchema:{inheritAllFrom:"$container",isBlock:!0}},{model:"htmlHgroup",view:"hgroup",modelSchema:{allowIn:["$root","$container"],allowChildren:["paragraph","htmlP","htmlH1","htmlH2","htmlH3","htmlH4","htmlH5","htmlH6"],isBlock:!1}},{model:"htmlH1",view:"h1",modelSchema:{inheritAllFrom:"$block"}},{model:"htmlH2",view:"h2",modelSchema:{inheritAllFrom:"$block"}},{model:"htmlH3",view:"h3",modelSchema:{inheritAllFrom:"$block"}},{model:"htmlH4",view:"h4",modelSchema:{inheritAllFrom:"$block"}},{model:"htmlH5",view:"h5",modelSchema:{inheritAllFrom:"$block"}},{model:"htmlH6",view:"h6",modelSchema:{inheritAllFrom:"$block"}},{model:"$htmlList",modelSchema:{allowWhere:"$container",allowChildren:["$htmlList","htmlLi"],isBlock:!1}},{model:"htmlDir",view:"dir",modelSchema:{inheritAllFrom:"$htmlList"}},{model:"htmlMenu",view:"menu",modelSchema:{inheritAllFrom:"$htmlList"}},{model:"htmlUl",view:"ul",modelSchema:{inheritAllFrom:"$htmlList"}},{model:"htmlOl",view:"ol",modelSchema:{inheritAllFrom:"$htmlList"}},{model:"htmlLi",view:"li",modelSchema:{allowIn:"$htmlList",allowChildren:"$text",isBlock:!1}},{model:"htmlPre",view:"pre",modelSchema:{inheritAllFrom:"$block"}},{model:"htmlArticle",view:"article",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlSection",view:"section",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlNav",view:"nav",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlDl",view:"dl",modelSchema:{allowWhere:"$container",allowChildren:["htmlDt","htmlDd","htmlDiv"],isBlock:!1}},{model:"htmlDt",view:"dt",modelSchema:{allowChildren:"$block",allowIn:"htmlDiv",isBlock:!1}},{model:"htmlDd",view:"dd",modelSchema:{allowChildren:"$block",allowIn:"htmlDiv",isBlock:!1}},{model:"htmlCenter",view:"center",modelSchema:{inheritAllFrom:"$container",isBlock:!1}},{model:"htmlHr",view:"hr",isEmpty:!0,modelSchema:{inheritAllFrom:"$blockObject"}}],inline:[{model:"htmlLiAttributes",view:"li",appliesToBlock:!0,coupledAttribute:"listItemId"},{model:"htmlOlAttributes",view:"ol",appliesToBlock:!0,coupledAttribute:"listItemId"},{model:"htmlUlAttributes",view:"ul",appliesToBlock:!0,coupledAttribute:"listItemId"},{model:"htmlFigureAttributes",view:"figure",appliesToBlock:"table"},{model:"htmlTheadAttributes",view:"thead",appliesToBlock:"table"},{model:"htmlTbodyAttributes",view:"tbody",appliesToBlock:"table"},{model:"htmlFigureAttributes",view:"figure",appliesToBlock:"imageBlock"},{model:"htmlAcronym",view:"acronym",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlTt",view:"tt",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlFont",view:"font",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlTime",view:"time",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlVar",view:"var",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlBig",view:"big",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlSmall",view:"small",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlSamp",view:"samp",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlQ",view:"q",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlOutput",view:"output",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlKbd",view:"kbd",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlBdi",view:"bdi",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlBdo",view:"bdo",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlAbbr",view:"abbr",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlA",view:"a",priority:5,coupledAttribute:"linkHref",attributeProperties:{isFormatting:!0}},{model:"htmlStrong",view:"strong",coupledAttribute:"bold",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlB",view:"b",coupledAttribute:"bold",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlI",view:"i",coupledAttribute:"italic",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlEm",view:"em",coupledAttribute:"italic",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlS",view:"s",coupledAttribute:"strikethrough",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlDel",view:"del",coupledAttribute:"strikethrough",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlIns",view:"ins",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlU",view:"u",coupledAttribute:"underline",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlSub",view:"sub",coupledAttribute:"subscript",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlSup",view:"sup",coupledAttribute:"superscript",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlCode",view:"code",coupledAttribute:"code",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlMark",view:"mark",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlSpan",view:"span",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlCite",view:"cite",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlLabel",view:"label",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlDfn",view:"dfn",attributeProperties:{copyOnEnter:!0,isFormatting:!0}},{model:"htmlObject",view:"object",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlIframe",view:"iframe",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlInput",view:"input",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlButton",view:"button",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlTextarea",view:"textarea",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlSelect",view:"select",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlVideo",view:"video",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlEmbed",view:"embed",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlOembed",view:"oembed",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlAudio",view:"audio",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlImg",view:"img",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlCanvas",view:"canvas",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlMeter",view:"meter",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlProgress",view:"progress",isObject:!0,modelSchema:{inheritAllFrom:"$inlineObject"}},{model:"htmlScript",view:"script",modelSchema:{allowWhere:["$text","$block"],isInline:!0}},{model:"htmlStyle",view:"style",modelSchema:{allowWhere:["$text","$block"],isInline:!0}},{model:"htmlCustomElement",view:"$customElement",modelSchema:{allowWhere:["$text","$block"],allowAttributesOf:"$inlineObject",isInline:!0}}]};function rt(t){return"__proto__"===t}function it(t){return null!==t&&"object"==typeof t&&"[object Arguments]"===s(t)}function ot(t){return"object"==typeof t&&null!==t}function nt(t){if("object"!=typeof t)return!1;if(null==t)return!1;if(null===Object.getPrototypeOf(t))return!0;if("[object Object]"!==Object.prototype.toString.call(t)){const e=t[Symbol.toStringTag];if(null==e)return!1;return!!Object.getOwnPropertyDescriptor(t,Symbol.toStringTag)?.writable&&t.toString()===`[object ${e}]`}let e=t;for(;null!==Object.getPrototypeOf(e);)e=Object.getPrototypeOf(e);return Object.getPrototypeOf(t)===e}function lt(t){return _(t)}function st(t,...e){const r=e.slice(0,-1),i=e[e.length-1];let o=t;for(let t=0;t<r.length;t++){o=at(o,r[t],i,new Map)}return o}function at(t,e,r,i){if(I(t)&&(t=Object(t)),null==e||"object"!=typeof e)return t;if(i.has(e))return function(t){if(I(t))return t;if(Array.isArray(t)||_(t)||t instanceof ArrayBuffer||"undefined"!=typeof SharedArrayBuffer&&t instanceof SharedArrayBuffer)return t.slice(0);const e=Object.getPrototypeOf(t),r=e.constructor;if(t instanceof Date||t instanceof Map||t instanceof Set)return new r(t);if(t instanceof RegExp){const e=new r(t);return e.lastIndex=t.lastIndex,e}if(t instanceof DataView)return new r(t.buffer.slice(0));if(t instanceof Error){const e=new r(t.message);return e.stack=t.stack,e.name=t.name,e.cause=t.cause,e}if("undefined"!=typeof File&&t instanceof File)return new r([t],t.name,{type:t.type,lastModified:t.lastModified});if("object"==typeof t){const r=Object.create(e);return Object.assign(r,t)}return t}(i.get(e));if(i.set(e,t),Array.isArray(e)){e=e.slice();for(let t=0;t<e.length;t++)e[t]=e[t]??void 0}const o=[...Object.keys(e),...l(e)];for(let n=0;n<o.length;n++){const l=o[n];if(rt(l))continue;let s=e[l],a=t[l];if(it(s)&&(s={...s}),it(a)&&(a={...a}),"undefined"!=typeof Buffer&&Buffer.isBuffer(s)&&(s=B(s)),Array.isArray(s))if("object"==typeof a&&null!=a){const t=[],e=Reflect.ownKeys(a);for(let r=0;r<e.length;r++){const i=e[r];t[i]=a[i]}a=t}else a=[];const m=r(a,s,l,t,e,i);null!=m?t[l]=m:Array.isArray(s)||ot(a)&&ot(s)?t[l]=at(a,s,r,i):null==a&&nt(s)?t[l]=at({},s,r,i):null==a&&lt(s)?t[l]=B(s):void 0!==a&&void 0===s||(t[l]=s)}return t}class mt extends t.Plugin{_definitions=[];static get pluginName(){return"DataSchema"}static get isOfficialPlugin(){return!0}init(){for(const t of et.block)this.registerBlockElement(t);for(const t of et.inline)this.registerInlineElement(t)}registerBlockElement(t){this._definitions.push({...t,isBlock:!0})}registerInlineElement(t){this._definitions.push({...t,isInline:!0})}extendBlockElement(t){this._extendDefinition({...t,isBlock:!0})}extendInlineElement(t){this._extendDefinition({...t,isInline:!0})}getDefinitionsForView(t,e=!1){const r=new Set;for(const i of this._getMatchingViewDefinitions(t)){if(e)for(const t of this._getReferences(i.model))r.add(t);r.add(i)}return r}getDefinitionsForModel(t){return this._definitions.filter(e=>e.model==t)}_getMatchingViewDefinitions(t){return this._definitions.filter(e=>e.view&&function(t,e){if("string"==typeof t)return t===e;if(t instanceof RegExp)return t.test(e);return!1}(t,e.view))}*_getReferences(t){const r=["inheritAllFrom","inheritTypesFrom","allowWhere","allowContentOf","allowAttributesOf"],i=this._definitions.filter(e=>e.model==t);for(const{modelSchema:o}of i)if(o)for(const i of r)for(const r of(0,e.toArray)(o[i]||[])){const e=this._definitions.filter(t=>t.model==r);for(const i of e)r!==t&&(yield*this._getReferences(i.model),yield i)}}_extendDefinition(t){const e=Array.from(this._definitions.entries()).filter(([,e])=>e.model==t.model);if(0!=e.length)for(const[r,i]of e)this._definitions[r]=st({},i,t,(t,e)=>Array.isArray(t)?t.concat(e):void 0);else this._definitions.push(t)}}var ct=r(719),ut=r.n(ct),dt=r(863),ht=r.n(dt),ft=r(424),gt=r.n(ft),bt=r(517),pt=r.n(bt),wt=r(163),At=r.n(wt),yt=r(617),vt={attributes:{"data-cke":!0}};vt.setAttributes=pt(),vt.insert=gt().bind(null,"head"),vt.domAPI=ht(),vt.insertStyleElement=At();ut()(yt.A,vt);yt.A&&yt.A.locals&&yt.A.locals;class Et extends t.Plugin{_dataSchema;_allowedAttributes;_disallowedAttributes;_allowedElements;_disallowedElements;_dataInitialized;_coupledAttributes;constructor(t){super(t),this._dataSchema=t.plugins.get("DataSchema"),this._allowedAttributes=new o.Matcher,this._disallowedAttributes=new o.Matcher,this._allowedElements=new Set,this._disallowedElements=new Set,this._dataInitialized=!1,this._coupledAttributes=null,this._registerElementsAfterInit(),this._registerElementHandlers(),this._registerCoupledAttributesPostFixer(),this._registerAssociatedHtmlAttributesPostFixer()}static get pluginName(){return"DataFilter"}static get isOfficialPlugin(){return!0}static get requires(){return[mt,n.Widget]}loadAllowedConfig(t){for(const e of t){const t=e.name||/[\s\S]+/,r=jt(e);this.allowElement(t),r.forEach(t=>this.allowAttributes(t))}}loadDisallowedConfig(t){for(const e of t){const t=e.name||/[\s\S]+/,r=jt(e);0==r.length?this.disallowElement(t):r.forEach(t=>this.disallowAttributes(t))}}loadAllowedEmptyElementsConfig(t){for(const e of t)this.allowEmptyElement(e)}allowElement(t){for(const e of this._dataSchema.getDefinitionsForView(t,!0))this._addAllowedElement(e),this._coupledAttributes=null}disallowElement(t){for(const e of this._dataSchema.getDefinitionsForView(t,!1))this._disallowedElements.add(e.view)}allowEmptyElement(t){for(const e of this._dataSchema.getDefinitionsForView(t,!0))e.isInline&&this._dataSchema.extendInlineElement({...e,allowEmpty:!0})}allowAttributes(t){this._allowedAttributes.add(t)}disallowAttributes(t){this._disallowedAttributes.add(t)}processViewAttributes(t,e){const{consumable:r}=e;return St(t,this._disallowedAttributes,r),function(t,{attributes:e,classes:r,styles:i}){if(!e.length&&!r.length&&!i.length)return null;return{...e.length&&{attributes:Ot(t,e)},...i.length&&{styles:kt(t,i)},...r.length&&{classes:r}}}(t,St(t,this._allowedAttributes,r))}_addAllowedElement(t){if(!this._allowedElements.has(t)){if(this._allowedElements.add(t),"appliesToBlock"in t&&"string"==typeof t.appliesToBlock)for(const e of this._dataSchema.getDefinitionsForModel(t.appliesToBlock))e.isBlock&&this._addAllowedElement(e);this._dataInitialized&&this.editor.data.once("set",()=>{this._fireRegisterEvent(t)},{priority:e.priorities.highest+1})}}_registerElementsAfterInit(){this.editor.data.on("init",()=>{this._dataInitialized=!0;for(const t of this._allowedElements)this._fireRegisterEvent(t)},{priority:e.priorities.highest+1})}_registerElementHandlers(){this.on("register",(t,r)=>{const i=this.editor.model.schema;if(r.isObject&&!i.isRegistered(r.model))this._registerObjectElement(r);else if(r.isBlock)this._registerBlockElement(r);else{if(!r.isInline)throw new e.CKEditorError("data-filter-invalid-definition",null,r);this._registerInlineElement(r)}t.stop()},{priority:"lowest"})}_registerCoupledAttributesPostFixer(){const t=this.editor.model,e=t.document.selection;t.document.registerPostFixer(e=>{const r=t.document.differ.getChanges();let i=!1;const o=this._getCoupledAttributesMap();for(const t of r){if("attribute"!=t.type||null!==t.attributeNewValue)continue;const r=o.get(t.attributeKey);if(r)for(const{item:o}of t.range.getWalker())for(const t of r)o.hasAttribute(t)&&(e.removeAttribute(t,o),i=!0)}return i}),this.listenTo(e,"change:attribute",(r,{attributeKeys:i})=>{const o=new Set,n=this._getCoupledAttributesMap();for(const t of i){if(e.hasAttribute(t))continue;const r=n.get(t);if(r)for(const t of r)e.hasAttribute(t)&&o.add(t)}0!=o.size&&t.change(t=>{for(const e of o)t.removeSelectionAttribute(e)})})}_registerAssociatedHtmlAttributesPostFixer(){const t=this.editor.model;t.document.registerPostFixer(e=>{const r=t.document.differ.getChanges();let i=!1;for(const o of r)if("insert"===o.type&&"$text"!==o.name)for(const r of o.attributes.keys())r.startsWith("html")&&r.endsWith("Attributes")&&(t.schema.checkAttribute(o.name,r)||(e.removeAttribute(r,o.position.nodeAfter),i=!0));return i})}_getCoupledAttributesMap(){if(this._coupledAttributes)return this._coupledAttributes;this._coupledAttributes=new Map;for(const t of this._allowedElements)if(t.coupledAttribute&&t.model){const e=this._coupledAttributes.get(t.coupledAttribute);e?e.push(t.model):this._coupledAttributes.set(t.coupledAttribute,[t.model])}return this._coupledAttributes}_fireRegisterEvent(t){t.view&&this._disallowedElements.has(t.view)||this.fire(t.view?`register:${t.view}`:"register",t)}_registerObjectElement(t){const r=this.editor,i=r.model.schema,o=r.conversion,{view:n,model:l}=t;i.register(l,t.modelSchema),n&&(i.extend(t.model,{allowAttributes:[q(n),"htmlContent"]}),r.data.registerRawContentMatcher({name:n}),o.for("upcast").elementToElement({view:n,model:G(t),converterPriority:e.priorities.low+2}),o.for("upcast").add(Z(t,this)),o.for("editingDowncast").elementToStructure({model:{name:l,attributes:[q(n)]},view:K(r,t)}),o.for("dataDowncast").elementToElement({model:l,view:(t,{writer:e})=>X(n,t,e)}),o.for("dataDowncast").add(tt(t)))}_registerBlockElement(t){const r=this.editor,i=r.model.schema,o=r.conversion,{view:n,model:l}=t;if(!i.isRegistered(t.model)){if(!t.modelSchema)return;if(i.register(t.model,t.modelSchema),!n)return;o.for("upcast").elementToElement({model:l,view:n,converterPriority:e.priorities.low+2}),o.for("downcast").elementToElement({model:l,view:(e,{writer:r})=>t.isEmpty?r.createEmptyElement(n):r.createContainerElement(n)})}n&&(i.extend(t.model,{allowAttributes:q(n)}),o.for("upcast").add(Z(t,this)),o.for("downcast").add(tt(t)))}_registerInlineElement(t){const e=this.editor,r=e.model.schema,i=e.conversion,o=t.model;if(!t.appliesToBlock&&(r.extend("$text",{allowAttributes:o}),t.attributeProperties&&r.setAttributeProperties(o,t.attributeProperties),i.for("upcast").add(Q(t,this)),i.for("downcast").attributeToElement({model:o,view:J(t)}),t.allowEmpty)){if(r.setAttributeProperties(o,{copyFromObject:!1}),!r.isRegistered("htmlEmptyElement")){r.register("htmlEmptyElement",{inheritAllFrom:"$inlineObject"});const t=t=>Array.from(t.getAttributeKeys()).some(t=>t.startsWith("html"));e.model.document.registerPostFixer(r=>{const i=e.model.document.differ.getChanges(),o=new Set;for(const e of i)if("remove"!==e.type){if("attribute"===e.type&&null===e.attributeNewValue)for(const{item:r}of e.range)r.is("element","htmlEmptyElement")&&!t(r)&&o.add(r);if("insert"===e.type&&e.position.nodeAfter){const i=e.position.nodeAfter;for(const{item:e}of r.createRangeOn(i))e.is("element","htmlEmptyElement")&&!t(e)&&o.add(e)}}for(const t of o)r.remove(t);return o.size>0})}e.data.htmlProcessor.domConverter.registerInlineObjectMatcher(e=>e.name==t.view&&e.isEmpty&&Array.from(e.getAttributeKeys()).length?{name:!0}:null),i.for("editingDowncast").elementToElement({model:"htmlEmptyElement",view:Y(t,!0)}),i.for("dataDowncast").elementToElement({model:"htmlEmptyElement",view:Y(t)})}}}function St(t,e,r){const i=e.matchAll(t)||[],o=t.document.stylesProcessor;return i.reduce((e,{match:i})=>{for(const[n,l]of i.attributes||[])if("style"==n){const i=l,n=o.getRelatedStyles(i).filter(t=>t.split("-").length>i.split("-").length).sort((t,e)=>e.split("-").length-t.split("-").length);for(const i of n)r.consume(t,{styles:[i]})&&e.styles.push(i);r.consume(t,{styles:[i]})&&e.styles.push(i)}else if("class"==n){const i=l;r.consume(t,{classes:[i]})&&e.classes.push(i)}else r.consume(t,{attributes:[n]})&&e.attributes.push(n);return e},{attributes:[],classes:[],styles:[]})}function Ot(t,r){const i={};for(const o of r){const r=t.getAttribute(o);void 0!==r&&(0,e.isValidAttributeName)(o)&&(i[o]=r)}return i}function kt(t,e){const r=new o.StylesMap(t.document.stylesProcessor);for(const i of e){const e=t.getStyle(i);void 0!==e&&r.set(i,e)}return Object.fromEntries(r.getStylesEntries())}function Ct(t,e){const{name:r}=t,i=t[e];return nt(i)?Object.entries(i).map(([t,i])=>({name:r,[e]:{[t]:i}})):Array.isArray(i)?i.map(t=>({name:r,[e]:[t]})):[t]}function jt(t){const{name:e,attributes:r,classes:i,styles:o}=t,n=[];return r&&n.push(...Ct({name:e,attributes:r},"attributes")),i&&n.push(...Ct({name:e,classes:i},"classes")),o&&n.push(...Ct({name:e,styles:o},"styles")),n}class Pt extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"CodeBlockElementSupport"}static get isOfficialPlugin(){return!0}init(){if(!this.editor.plugins.has("CodeBlockEditing"))return;const t=this.editor.plugins.get(Et);t.on("register:pre",(e,r)=>{if("codeBlock"!==r.model)return;const i=this.editor,o=i.model.schema,n=i.conversion;o.extend("codeBlock",{allowAttributes:["htmlPreAttributes","htmlContentAttributes"]}),n.for("upcast").add(function(t){return e=>{e.on("element:code",(e,r,i)=>{const o=r.viewItem,n=o.parent;function l(e,o){const n=t.processViewAttributes(e,i);n&&i.writer.setAttribute(o,n,r.modelRange)}n&&n.is("element","pre")&&(l(n,"htmlPreAttributes"),l(o,"htmlContentAttributes"))},{priority:"low"})}}(t)),n.for("downcast").add(t=>{t.on("attribute:htmlPreAttributes:codeBlock",(t,e,r)=>{if(!r.consumable.consume(e.item,t.name))return;const{attributeOldValue:i,attributeNewValue:o}=e,n=r.mapper.toViewElement(e.item).parent;V(r.writer,i,o,n)}),t.on("attribute:htmlContentAttributes:codeBlock",(t,e,r)=>{if(!r.consumable.consume(e.item,t.name))return;const{attributeOldValue:i,attributeNewValue:o}=e,n=r.mapper.toViewElement(e.item);V(r.writer,i,o,n)})}),e.stop()})}}class Ft extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"DualContentModelElementSupport"}static get isOfficialPlugin(){return!0}init(){this.editor.plugins.get(Et).on("register",(t,r)=>{const i=r,o=this.editor,n=o.model.schema,l=o.conversion;if(!i.paragraphLikeModel)return;if(n.isRegistered(i.model)||n.isRegistered(i.paragraphLikeModel))return;const s={model:i.paragraphLikeModel,view:i.view};n.register(i.model,i.modelSchema),n.register(s.model,{inheritAllFrom:"$block"}),l.for("upcast").elementToElement({view:i.view,model:(t,{writer:e})=>this._hasBlockContent(t)?e.createElement(i.model):e.createElement(s.model),converterPriority:e.priorities.low+.5}),l.for("downcast").elementToElement({view:i.view,model:i.model}),this._addAttributeConversion(i),l.for("downcast").elementToElement({view:s.view,model:s.model}),this._addAttributeConversion(s),t.stop()})}_hasBlockContent(t){const e=this.editor.editing.view,r=e.domConverter.blockElements;for(const i of e.createRangeIn(t).getItems())if(i.is("element")&&r.includes(i.name))return!0;return!1}_addAttributeConversion(t){const e=this.editor,r=e.conversion,i=e.plugins.get(Et);e.model.schema.extend(t.model,{allowAttributes:q(t.view)}),r.for("upcast").add(Z(t,i)),r.for("downcast").add(tt(t))}}var It=r(507);class _t extends t.Plugin{static get requires(){return[mt,It.Enter]}static get pluginName(){return"HeadingElementSupport"}static get isOfficialPlugin(){return!0}init(){const t=this.editor;if(!t.plugins.has("HeadingEditing"))return;const e=t.config.get("heading.options");this.registerHeadingElements(t,e)}registerHeadingElements(t,e){const r=t.plugins.get(mt),i=[];for(const t of e)"model"in t&&"view"in t&&(r.registerBlockElement({view:t.view,model:t.model}),i.push(t.model));r.extendBlockElement({model:"htmlHgroup",modelSchema:{allowChildren:i}}),r.extendBlockElement({model:"htmlSummary",modelSchema:{allowChildren:i}})}}function $t(t,e,r){const i=t.createRangeOn(e);for(const{item:t}of i.getWalker())if(t.is("element",r))return t}class xt extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"ImageElementSupport"}static get isOfficialPlugin(){return!0}init(){const t=this.editor;if(!t.plugins.has("ImageInlineEditing")&&!t.plugins.has("ImageBlockEditing"))return;const e=t.model.schema,r=t.conversion,i=t.plugins.get(Et);i.on("register:figure",()=>{r.for("upcast").add(function(t){return e=>{e.on("element:figure",(e,r,i)=>{const o=r.viewItem;if(!r.modelRange||!o.hasClass("image"))return;const n=t.processViewAttributes(o,i);n&&i.writer.setAttribute("htmlFigureAttributes",n,r.modelRange)},{priority:"low"})}}(i))}),i.on("register:img",(o,n)=>{"imageBlock"!==n.model&&"imageInline"!==n.model||(e.isRegistered("imageBlock")&&e.extend("imageBlock",{allowAttributes:["htmlImgAttributes","htmlFigureAttributes","htmlLinkAttributes"]}),e.isRegistered("imageInline")&&e.extend("imageInline",{allowAttributes:["htmlA","htmlImgAttributes"]}),r.for("upcast").add(function(t){return e=>{e.on("element:img",(e,r,i)=>{if(!r.modelRange)return;const o=r.viewItem,n=t.processViewAttributes(o,i);n&&i.writer.setAttribute("htmlImgAttributes",n,r.modelRange)},{priority:"low"})}}(i)),r.for("downcast").add(t=>{function e(e){t.on(`attribute:${e}:imageInline`,(t,e,r)=>{if(!r.consumable.consume(e.item,t.name))return;const{attributeOldValue:i,attributeNewValue:o}=e,n=r.mapper.toViewElement(e.item);V(r.writer,i,o,n)},{priority:"low"})}function r(e,r){t.on(`attribute:${r}:imageBlock`,(t,r,i)=>{if(!i.consumable.test(r.item,t.name))return;const{attributeOldValue:o,attributeNewValue:n}=r,l=i.mapper.toViewElement(r.item),s=$t(i.writer,l,e);s&&(V(i.writer,o,n,s),i.consumable.consume(r.item,t.name))},{priority:"low"}),"a"===e&&t.on("attribute:linkHref:imageBlock",(t,e,r)=>{if(!r.consumable.consume(e.item,"attribute:htmlLinkAttributes:imageBlock"))return;const i=r.mapper.toViewElement(e.item),o=$t(r.writer,i,"a");N(r.writer,e.item.getAttribute("htmlLinkAttributes"),o)},{priority:"low"})}e("htmlImgAttributes"),r("img","htmlImgAttributes"),r("figure","htmlFigureAttributes"),r("a","htmlLinkAttributes")}),t.plugins.has("LinkImage")&&r.for("upcast").add(function(t,e){const r=e.plugins.get("ImageUtils");return e=>{e.on("element:a",(e,i,o)=>{const n=i.viewItem;if(!r.findViewImgElement(n))return;const l=i.modelCursor.parent;if(!l.is("element","imageBlock"))return;const s=t.processViewAttributes(n,o);s&&o.writer.setAttribute("htmlLinkAttributes",s,l)},{priority:"low"})}}(i,t)),o.stop())})}}class Tt extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"MediaEmbedElementSupport"}static get isOfficialPlugin(){return!0}init(){const t=this.editor;if(!t.plugins.has("MediaEmbed")||t.config.get("mediaEmbed.previewsInData"))return;const e=t.model.schema,r=t.conversion,i=this.editor.plugins.get(Et),o=this.editor.plugins.get(mt),n=t.config.get("mediaEmbed.elementName");o.registerBlockElement({model:"media",view:n}),i.on("register:figure",()=>{r.for("upcast").add(function(t){return e=>{e.on("element:figure",(e,r,i)=>{const o=r.viewItem;if(!r.modelRange||!o.hasClass("media"))return;const n=t.processViewAttributes(o,i);n&&i.writer.setAttribute("htmlFigureAttributes",n,r.modelRange)},{priority:"low"})}}(i))}),i.on(`register:${n}`,(t,o)=>{"media"===o.model&&(e.extend("media",{allowAttributes:[q(n),"htmlFigureAttributes"]}),r.for("upcast").add(function(t,e){const r=(r,i,o)=>{function n(e,r){const n=t.processViewAttributes(e,o);n&&o.writer.setAttribute(r,n,i.modelRange)}n(i.viewItem,q(e))};return t=>{t.on(`element:${e}`,r,{priority:"low"})}}(i,n)),r.for("dataDowncast").add(function(t){return e=>{function r(t,r){e.on(`attribute:${r}:media`,(e,r,i)=>{if(!i.consumable.consume(r.item,e.name))return;const{attributeOldValue:o,attributeNewValue:n}=r,l=i.mapper.toViewElement(r.item),s=$t(i.writer,l,t);V(i.writer,o,n,s)})}r(t,q(t)),r("figure","htmlFigureAttributes")}}(n)),t.stop())})}}class Bt extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"ScriptElementSupport"}static get isOfficialPlugin(){return!0}init(){const t=this.editor.plugins.get(Et);t.on("register:script",(e,r)=>{const i=this.editor,o=i.model.schema,n=i.conversion;o.register("htmlScript",r.modelSchema),o.extend("htmlScript",{allowAttributes:["htmlScriptAttributes","htmlContent"],isContent:!0}),i.data.registerRawContentMatcher({name:"script"}),n.for("upcast").elementToElement({view:"script",model:G(r)}),n.for("upcast").add(Z(r,t)),n.for("downcast").elementToElement({model:"htmlScript",view:(t,{writer:e})=>X("script",t,e)}),n.for("downcast").add(tt(r)),e.stop()})}}const Dt=["width","max-width","min-width","height","min-height","max-height"];class Ht extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"TableElementSupport"}static get isOfficialPlugin(){return!0}init(){const t=this.editor;if(!t.plugins.has("TableEditing"))return;const e=t.model.schema,r=t.conversion,i=t.plugins.get(Et),o=t.plugins.get("TableUtils");i.on("register:figure",()=>{r.for("upcast").add(function(t){return e=>{e.on("element:figure",(e,r,i)=>{const o=r.viewItem;if(!r.modelRange||!o.hasClass("table"))return;const n=t.processViewAttributes(o,i);n&&i.writer.setAttribute("htmlFigureAttributes",n,r.modelRange)},{priority:"low"})}}(i))}),i.on("register:table",(n,l)=>{"table"===l.model&&(e.extend("table",{allowAttributes:["htmlTableAttributes","htmlFigureAttributes","htmlTheadAttributes","htmlTbodyAttributes"]}),r.for("upcast").add(function(t){return e=>{e.on("element:table",(e,r,i)=>{if(!r.modelRange)return;const o=r.viewItem;i.consumable.consume(o,{classes:"table"}),n(o,"htmlTableAttributes");for(const t of o.getChildren())t.is("element","thead")&&n(t,"htmlTheadAttributes"),t.is("element","tbody")&&n(t,"htmlTbodyAttributes");function n(e,o){const n=t.processViewAttributes(e,i);n&&i.writer.setAttribute(o,n,r.modelRange)}},{priority:"low"})}}(i)),r.for("downcast").add(t=>{function e(e,r){t.on(`attribute:${r}:table`,(t,i,o)=>{if(!o.consumable.test(i.item,t.name))return;const n=o.mapper.toViewElement(i.item),l=$t(o.writer,n,e);if(l)if(o.consumable.consume(i.item,t.name),"htmlTableAttributes"===r&&n!==l){const t=Rt(i.attributeOldValue),e=Rt(i.attributeNewValue);V(o.writer,t.tableAttributes,e.tableAttributes,l),V(o.writer,t.figureAttributes,e.figureAttributes,n)}else V(o.writer,i.attributeOldValue,i.attributeNewValue,l)})}e("table","htmlTableAttributes"),e("figure","htmlFigureAttributes"),e("thead","htmlTheadAttributes"),e("tbody","htmlTbodyAttributes")}),t.model.document.registerPostFixer(function(t,e){return r=>{const i=t.document.differ.getChanges();let o=!1;for(const t of i){if("attribute"!=t.type||"headingRows"!=t.attributeKey)continue;const i=t.range.start.nodeAfter,n=i.getAttribute("htmlTheadAttributes"),l=i.getAttribute("htmlTbodyAttributes");n&&!t.attributeNewValue?(r.removeAttribute("htmlTheadAttributes",i),o=!0):l&&t.attributeNewValue==e.getRows(i)&&(r.removeAttribute("htmlTbodyAttributes",i),o=!0)}return o}}(t.model,o)),n.stop())})}}function Rt(t){const e={},r={...t};if(!t||!("styles"in t))return{figureAttributes:e,tableAttributes:r};r.styles={};for(const[i,o]of Object.entries(t.styles))Dt.includes(i)?e.styles={...e.styles,[i]:o}:r.styles={...r.styles,[i]:o};return{figureAttributes:e,tableAttributes:r}}class Vt extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"StyleElementSupport"}static get isOfficialPlugin(){return!0}init(){const t=this.editor.plugins.get(Et);t.on("register:style",(e,r)=>{const i=this.editor,o=i.model.schema,n=i.conversion;o.register("htmlStyle",r.modelSchema),o.extend("htmlStyle",{allowAttributes:["htmlStyleAttributes","htmlContent"],isContent:!0}),i.data.registerRawContentMatcher({name:"style"}),n.for("upcast").elementToElement({view:"style",model:G(r)}),n.for("upcast").add(Z(r,t)),n.for("downcast").elementToElement({model:"htmlStyle",view:(t,{writer:e})=>X("style",t,e)}),n.for("downcast").add(tt(r)),e.stop()})}}function Nt(t){if(!t||"object"!=typeof t)return!1;const e=Object.getPrototypeOf(t);return!(null!==e&&e!==Object.prototype&&null!==Object.getPrototypeOf(e))&&"[object Object]"===Object.prototype.toString.call(t)}function Mt(t,e,r,i,o,n,l){const s=l(t,e,r,i,o,n);if(void 0!==s)return s;if(typeof t==typeof e)switch(typeof t){case"bigint":case"string":case"boolean":case"symbol":case"undefined":case"function":return t===e;case"number":return t===e||Object.is(t,e);case"object":return Lt(t,e,n,l)}return Lt(t,e,n,l)}function Lt(t,e,r,i){if(Object.is(t,e))return!0;let o=s(t),n=s(e);if(o===d&&(o=A),n===d&&(n=A),o!==n)return!1;switch(o){case m:return t.toString()===e.toString();case c:{const r=t.valueOf(),i=e.valueOf();return(I=r)===(_=i)||Number.isNaN(I)&&Number.isNaN(_)}case u:case f:case h:return Object.is(t.valueOf(),e.valueOf());case a:return t.source===e.source&&t.flags===e.flags;case"[object Function]":return t===e}var I,_;const $=(r=r??new Map).get(t),x=r.get(e);if(null!=$&&null!=x)return $===e;r.set(t,e),r.set(e,t);try{switch(o){case g:if(t.size!==e.size)return!1;for(const[o,n]of t.entries())if(!e.has(o)||!Mt(n,e.get(o),o,t,e,r,i))return!1;return!0;case b:{if(t.size!==e.size)return!1;const o=Array.from(t.values()),n=Array.from(e.values());for(let l=0;l<o.length;l++){const s=o[l],a=n.findIndex(o=>Mt(s,o,void 0,t,e,r,i));if(-1===a)return!1;n.splice(a,1)}return!0}case p:case v:case E:case S:case O:case"[object BigUint64Array]":case k:case C:case j:case"[object BigInt64Array]":case P:case F:if("undefined"!=typeof Buffer&&Buffer.isBuffer(t)!==Buffer.isBuffer(e))return!1;if(t.length!==e.length)return!1;for(let o=0;o<t.length;o++)if(!Mt(t[o],e[o],o,t,e,r,i))return!1;return!0;case w:return t.byteLength===e.byteLength&&Lt(new Uint8Array(t),new Uint8Array(e),r,i);case y:return t.byteLength===e.byteLength&&t.byteOffset===e.byteOffset&&Lt(new Uint8Array(t),new Uint8Array(e),r,i);case"[object Error]":return t.name===e.name&&t.message===e.message;case A:{if(!(Lt(t.constructor,e.constructor,r,i)||Nt(t)&&Nt(e)))return!1;const o=[...Object.keys(t),...l(t)],n=[...Object.keys(e),...l(e)];if(o.length!==n.length)return!1;for(let n=0;n<o.length;n++){const l=o[n],s=t[l];if(!Object.hasOwn(e,l))return!1;if(!Mt(s,e[l],l,t,e,r,i))return!1}return!0}default:return!1}}finally{r.delete(t),r.delete(e)}}function Ut(){}function zt(t,e){return function(t,e,r){return Mt(t,e,void 0,void 0,void 0,void 0,r)}(t,e,Ut)}class Wt extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"ListElementSupport"}static get isOfficialPlugin(){return!0}init(){const t=this.editor;if(!t.plugins.has("ListEditing"))return;const e=t.model.schema,r=t.conversion,i=t.plugins.get(Et),o=t.plugins.get("ListEditing"),n=t.plugins.get("ListUtils"),l=["ul","ol","li"];o.registerDowncastStrategy({scope:"item",attributeName:"htmlLiAttributes",setAttributeOnDowncast:N}),o.registerDowncastStrategy({scope:"list",attributeName:"htmlUlAttributes",setAttributeOnDowncast:N}),o.registerDowncastStrategy({scope:"list",attributeName:"htmlOlAttributes",setAttributeOnDowncast:N}),i.on("register",(t,o)=>{if(!l.includes(o.view))return;if(t.stop(),e.checkAttribute("$block","htmlLiAttributes"))return;const n=l.map(t=>q(t));e.extend("$listItem",{allowAttributes:n}),r.for("upcast").add(t=>{t.on("element:ul",qt("htmlUlAttributes",i),{priority:"low"}),t.on("element:ol",qt("htmlOlAttributes",i),{priority:"low"}),t.on("element:li",qt("htmlLiAttributes",i),{priority:"low"})})}),o.on("postFixer",(t,{listNodes:e,writer:r})=>{for(const{node:i,previousNodeInList:o}of e)if(o){if(o.getAttribute("listType")==i.getAttribute("listType")){const e=Gt(o.getAttribute("listType")),n=o.getAttribute(e);!zt(i.getAttribute(e),n)&&r.model.schema.checkAttribute(i,e)&&(r.setAttribute(e,n,i),t.return=!0)}if(o.getAttribute("listItemId")==i.getAttribute("listItemId")){const e=o.getAttribute("htmlLiAttributes");!zt(i.getAttribute("htmlLiAttributes"),e)&&r.model.schema.checkAttribute(i,"htmlLiAttributes")&&(r.setAttribute("htmlLiAttributes",e,i),t.return=!0)}}}),o.on("postFixer",(t,{listNodes:e,writer:r})=>{for(const{node:i}of e){const e=i.getAttribute("listType");!n.isNumberedListType(e)&&i.getAttribute("htmlOlAttributes")&&(r.removeAttribute("htmlOlAttributes",i),t.return=!0),n.isNumberedListType(e)&&i.getAttribute("htmlUlAttributes")&&(r.removeAttribute("htmlUlAttributes",i),t.return=!0)}})}afterInit(){const t=this.editor;if(!t.commands.get("indentList"))return;const e=t.commands.get("indentList");this.listenTo(e,"afterExecute",(e,r)=>{t.model.change(e=>{for(const i of r){const r=Gt(i.getAttribute("listType"));t.model.schema.checkAttribute(i,r)&&e.setAttribute(r,{},i)}})})}}function qt(t,e){return(r,i,o)=>{const n=i.viewItem;i.modelRange||Object.assign(i,o.convertChildren(i.viewItem,i.modelCursor));const l=e.processViewAttributes(n,o);for(const e of i.modelRange.getItems({shallow:!0}))e.hasAttribute("listItemId")&&(e.hasAttribute("htmlUlAttributes")||e.hasAttribute("htmlOlAttributes")||o.writer.model.schema.checkAttribute(e,t)&&o.writer.setAttribute(t,l||{},e))}}function Gt(t){return"numbered"===t||"customNumbered"==t?"htmlOlAttributes":"htmlUlAttributes"}class Kt extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"HorizontalLineElementSupport"}static get isOfficialPlugin(){return!0}init(){const t=this.editor;if(!t.plugins.has("HorizontalLineEditing"))return;const e=t.model.schema,r=t.conversion,i=t.plugins.get(Et);i.on("register:hr",(t,o)=>{"horizontalLine"===o.model&&(e.extend("horizontalLine",{allowAttributes:["htmlHrAttributes"]}),r.for("upcast").add(Z(o,i)),r.for("downcast").add(t=>{t.on("attribute:htmlHrAttributes:horizontalLine",(t,e,r)=>{if(!r.consumable.test(e.item,t.name))return;const{attributeOldValue:i,attributeNewValue:o}=e,n=r.mapper.toViewElement(e.item),l=$t(r.writer,n,"hr");l&&(V(r.writer,i,o,l),r.consumable.consume(e.item,t.name))},{priority:"low"})}),t.stop())})}}class Xt extends t.Plugin{static get requires(){return[Et]}static get pluginName(){return"IframeElementSupport"}static get isOfficialPlugin(){return!0}init(){this.editor.config.define("htmlSupport.htmlIframeSandbox",!0),this._setupSandboxConversion()}_setupSandboxConversion(){const{plugins:t,config:e,conversion:r}=this.editor,i=t.get(Et),o=e.get("htmlSupport.htmlIframeSandbox");if(!1===o)return;const n=Array.isArray(o)?Array.from(o):[];i.on("register:iframe",(t,e)=>{r.for("editingDowncast").add(t=>{t.on(`insert:${e.model}`,(t,e,r)=>{const{mapper:i,writer:o}=r,l=i.toViewElement(e.item);for(const{item:t}of o.createRangeOn(l))if(t.is("element","iframe"))if(t.hasAttribute("sandbox")){const e=new Set,r=t.getAttribute("sandbox");for(const t of r.trim().split(/\s+/))n.includes(t)&&e.add(t);o.setAttribute("sandbox",Array.from(e).join(" "),t)}else o.setAttribute("sandbox",n.join(" "),t)},{priority:"lowest"})})})}}class Qt extends t.Plugin{static get requires(){return[Et,mt]}static get pluginName(){return"CustomElementSupport"}static get isOfficialPlugin(){return!0}init(){const t=this.editor.plugins.get(Et),e=this.editor.plugins.get(mt);t.on("register:$customElement",(r,i)=>{r.stop();const n=this.editor,l=n.model.schema,s=n.conversion,a=n.editing.view.domConverter.unsafeElements,m=n.data.htmlProcessor.domConverter.preElements;l.register(i.model,i.modelSchema),l.extend(i.model,{allowAttributes:["htmlElementName","htmlCustomElementAttributes","htmlContent"],isContent:!0}),n.data.htmlProcessor.domConverter.registerRawContentMatcher({name:"template"}),s.for("upcast").elementToElement({view:/.*/,model:(r,l)=>{if("$comment"==r.name)return null;if(!function(t){try{document.createElement(t)}catch{return!1}return!0}(r.name))return null;if(e.getDefinitionsForView(r.name).size)return null;a.includes(r.name)||a.push(r.name),m.includes(r.name)||m.push(r.name);const s=l.writer.createElement(i.model,{htmlElementName:r.name}),c=t.processViewAttributes(r,l);let u;if(c&&l.writer.setAttribute("htmlCustomElementAttributes",c,s),r.is("element","template")&&r.getCustomProperty("$rawContent"))u=r.getCustomProperty("$rawContent");else{const t=new o.ViewUpcastWriter(r.document).createDocumentFragment(r),e=n.data.htmlProcessor.domConverter.viewToDom(t),i=e.firstChild;for(;i.firstChild;)e.appendChild(i.firstChild);i.remove(),u=n.data.htmlProcessor.htmlWriter.getHtml(e)}l.writer.setAttribute("htmlContent",u,s);for(const{item:t}of n.editing.view.createRangeIn(r))l.consumable.consume(t,{name:!0});return s},converterPriority:"low"}),s.for("editingDowncast").elementToElement({model:{name:i.model,attributes:["htmlElementName","htmlCustomElementAttributes","htmlContent"]},view:(t,{writer:e})=>{const r=t.getAttribute("htmlElementName"),i=e.createRawElement(r);return t.hasAttribute("htmlCustomElementAttributes")&&N(e,t.getAttribute("htmlCustomElementAttributes"),i),i}}),s.for("dataDowncast").elementToElement({model:{name:i.model,attributes:["htmlElementName","htmlCustomElementAttributes","htmlContent"]},view:(t,{writer:e})=>{const r=t.getAttribute("htmlElementName"),i=t.getAttribute("htmlContent"),o=e.createRawElement(r,null,(t,e)=>{e.setContentOf(t,i)});return t.hasAttribute("htmlCustomElementAttributes")&&N(e,t.getAttribute("htmlCustomElementAttributes"),o),o}})})}}class Yt extends t.Plugin{static get pluginName(){return"GeneralHtmlSupport"}static get isOfficialPlugin(){return!0}static get requires(){return[Et,Pt,Ft,_t,xt,Tt,Bt,Ht,Vt,Wt,Kt,Xt,Qt]}init(){const t=this.editor,e=t.plugins.get(Et);e.loadAllowedEmptyElementsConfig(t.config.get("htmlSupport.allowEmpty")||[]),e.loadAllowedConfig(t.config.get("htmlSupport.allow")||[]),e.loadDisallowedConfig(t.config.get("htmlSupport.disallow")||[])}afterInit(){const t=this.editor.commands.get("removeFormat");t?.registerCustomAttribute(t=>t.startsWith("html")&&t.endsWith("Attributes"),z)}getGhsAttributeNameForElement(t){const e=this.editor.plugins.get("DataSchema"),r=Array.from(e.getDefinitionsForView(t,!1)),i=r.find(t=>t.isInline&&!r[0].isObject);return i?i.model:q(t)}addModelHtmlClass(t,r,i){const o=this.editor.model,n=this.getGhsAttributeNameForElement(t);o.change(t=>{for(const l of Jt(o,i,n))U(t,l,n,"classes",t=>{for(const i of(0,e.toArray)(r))t.add(i)})})}removeModelHtmlClass(t,r,i){const o=this.editor.model,n=this.getGhsAttributeNameForElement(t);o.change(t=>{for(const l of Jt(o,i,n))U(t,l,n,"classes",t=>{for(const i of(0,e.toArray)(r))t.delete(i)})})}setModelHtmlAttributes(t,e,r){const i=this.editor.model,o=this.getGhsAttributeNameForElement(t);i.change(t=>{for(const n of Jt(i,r,o))U(t,n,o,"attributes",t=>{for(const[r,i]of Object.entries(e))t.set(r,i)})})}removeModelHtmlAttributes(t,r,i){const o=this.editor.model,n=this.getGhsAttributeNameForElement(t);o.change(t=>{for(const l of Jt(o,i,n))U(t,l,n,"attributes",t=>{for(const i of(0,e.toArray)(r))t.delete(i)})})}setModelHtmlStyles(t,e,r){const i=this.editor.model,o=this.getGhsAttributeNameForElement(t);i.change(t=>{for(const n of Jt(i,r,o))U(t,n,o,"styles",t=>{for(const[r,i]of Object.entries(e))t.set(r,i)})})}removeModelHtmlStyles(t,r,i){const o=this.editor.model,n=this.getGhsAttributeNameForElement(t);o.change(t=>{for(const l of Jt(o,i,n))U(t,l,n,"styles",t=>{for(const i of(0,e.toArray)(r))t.delete(i)})})}}function*Jt(t,e,r){if(e)if(!(Symbol.iterator in e)&&e.is("documentSelection")&&e.isCollapsed)t.schema.checkAttributeInSelection(e,r)&&(yield e);else for(const i of function(t,e,r){return!(Symbol.iterator in e)&&(e.is("node")||e.is("$text")||e.is("$textProxy"))?t.schema.checkAttribute(e,r)?[t.createRangeOn(e)]:[]:t.schema.getValidRanges(t.createSelection(e).getRanges(),r)}(t,e,r))yield*i.getItems({shallow:!0})}class Zt extends t.Plugin{static get pluginName(){return"HtmlComment"}static get isOfficialPlugin(){return!0}init(){const t=this.editor,r=new Map;t.data.processor.skipComments=!1,t.model.schema.addAttributeCheck((t,e)=>{if(t.endsWith("$root")&&e.startsWith("$comment"))return!0}),t.conversion.for("upcast").elementToMarker({view:"$comment",model:t=>{const i=`$comment:${(0,e.uid)()}`,o=t.getCustomProperty("$rawContent");return r.set(i,o),i}}),t.conversion.for("dataDowncast").markerToElement({model:"$comment",view:(t,{writer:e})=>{let r;for(const e of this.editor.model.document.getRootNames())if(r=this.editor.model.document.getRoot(e),r.hasAttribute(t.markerName))break;const i=t.markerName,o=r.getAttribute(i),n=e.createUIElement("$comment");return e.setCustomProperty("$rawContent",o,n),n}}),t.model.document.registerPostFixer(e=>{let i=!1;const o=t.model.document.differ.getChangedMarkers().filter(t=>t.name.startsWith("$comment:"));for(const t of o){const{oldRange:o,newRange:n}=t.data;if(!o||!n||o.root!=n.root){if(o){const r=o.root;r.hasAttribute(t.name)&&(e.removeAttribute(t.name,r),i=!0)}if(n){const o=n.root;"$graveyard"==o.rootName?(e.removeMarker(t.name),i=!0):o.hasAttribute(t.name)||(e.setAttribute(t.name,r.get(t.name)||"",o),i=!0)}}}return i}),t.data.on("set",()=>{for(const e of t.model.markers.getMarkersGroup("$comment"))this.removeHtmlComment(e.name)},{priority:"high"}),t.model.on("deleteContent",(e,[r])=>{for(const e of r.getRanges()){const r=t.model.schema.getLimitElement(e),i=t.model.createPositionAt(r,0),o=t.model.createPositionAt(r,"end");let n;n=i.isTouching(e.start)&&o.isTouching(e.end)?this.getHtmlCommentsInRange(t.model.createRange(i,o)):this.getHtmlCommentsInRange(e,{skipBoundaries:!0});for(const t of n)this.removeHtmlComment(t)}},{priority:"high"})}createHtmlComment(t,r){const i=(0,e.uid)(),o=this.editor.model,n=o.document.getRoot(t.root.rootName),l=`$comment:${i}`;return o.change(e=>{const i=e.createRange(t);return e.addMarker(l,{usingOperation:!0,affectsData:!0,range:i}),e.setAttribute(l,r,n),l})}removeHtmlComment(t){const e=this.editor,r=e.model.markers.get(t);return!!r&&(e.model.change(t=>{t.removeMarker(r)}),!0)}getHtmlCommentData(t){const e=this.editor.model.markers.get(t);if(!e)return null;let r="";for(const e of this.editor.model.document.getRoots())if(e.hasAttribute(t)){r=e.getAttribute(t);break}return{content:r,position:e.getStart()}}getHtmlCommentsInRange(t,{skipBoundaries:e=!1}={}){const r=!e;return Array.from(this.editor.model.markers.getMarkersGroup("$comment")).filter(e=>function(t,e){const i=t.getRange().start;return(i.isAfter(e.start)||r&&i.isEqual(e.start))&&(i.isBefore(e.end)||r&&i.isEqual(e.end))}(e,t)).map(t=>t.name)}}class te extends o.HtmlDataProcessor{toView(t){if(!/<(?:html|body|head|meta)(?:\s[^>]*)?>/i.test(t.trim().slice(0,1e4)))return super.toView(t);let e="",r="";t=(t=t.trim().replace(/<\?xml\s[^?]*\?>/i,t=>(r=t,""))).trim().replace(/^<!DOCTYPE\s[^>]*?>/i,t=>(e=t,""));const i=this._toDom(t),n=this.domConverter.domToView(i,{skipComments:this.skipComments}),l=new o.ViewUpcastWriter(n.document);l.setCustomProperty("$fullPageDocument",i.ownerDocument.documentElement.outerHTML,n);const s=Array.from(i.ownerDocument.querySelectorAll("head style"));return l.setCustomProperty("$fullPageHeadStyles",s,n),e&&l.setCustomProperty("$fullPageDocType",e,n),r&&l.setCustomProperty("$fullPageXmlDeclaration",r,n),n}toData(t){let e=super.toData(t);const r=t.getCustomProperty("$fullPageDocument"),i=t.getCustomProperty("$fullPageDocType"),o=t.getCustomProperty("$fullPageXmlDeclaration");return r&&(e=r.replace(/<\/body\s*>/,e+"$&"),i&&(e=i+"\n"+e),o&&(e=o+"\n"+e)),e}}class ee extends t.Plugin{static get pluginName(){return"FullPage"}static get licenseFeatureCode(){return"FPH"}static get isOfficialPlugin(){return!0}static get isPremiumPlugin(){return!0}constructor(t){super(t),t.config.define("htmlSupport.fullPage",{allowRenderStylesFromHead:!1,sanitizeCss:t=>((0,e.logWarning)("css-full-page-provide-sanitize-function"),{css:t,hasChanged:!1})}),t.data.processor=new te(t.data.viewDocument)}init(){const t=this.editor,e=["$fullPageDocument","$fullPageDocType","$fullPageXmlDeclaration","$fullPageHeadStyles"];t.model.schema.extend("$root",{allowAttributes:e}),t.data.on("toModel",(r,[i])=>{const o=t.model.document.getRoot();t.model.change(t=>{for(const r of e){const e=i.getCustomProperty(r);e&&t.setAttribute(r,e,o)}}),re(t)&&this._renderStylesFromHead(o)},{priority:"low"}),t.data.on("toView",(t,[r])=>{if(!r.is("rootElement"))return;const i=r,n=t.return;if(!i.hasAttribute("$fullPageDocument"))return;const l=new o.ViewUpcastWriter(n.document);for(const t of e){const e=i.getAttribute(t);e&&l.setCustomProperty(t,e,n)}},{priority:"low"}),t.data.on("set",()=>{const r=t.model.document.getRoot();t.model.change(t=>{for(const i of e)r.hasAttribute(i)&&t.removeAttribute(i,r)})},{priority:"high"}),t.data.on("get",(t,e)=>{e[0]||(e[0]={}),e[0].trim=!1},{priority:"high"})}destroy(){super.destroy(),re(this.editor)&&this._removeStyleElementsFromDom()}_removeStyleElementsFromDom(){const t=Array.from(e.global.document.querySelectorAll(`[data-full-page-style-id="${this.editor.id}"]`));for(const e of t)e.remove()}_renderStyleElementsInDom(t){const r=this.editor,i=t.getAttribute("$fullPageHeadStyles");if(!i)return;const o=r.config.get("htmlSupport.fullPage.sanitizeCss");for(const t of i){t.setAttribute("data-full-page-style-id",r.id);const i=o(t.innerText);i.hasChanged&&(t.innerText=i.css),e.global.document.head.append(t)}}_renderStylesFromHead(t){this._removeStyleElementsFromDom(),this._renderStyleElementsInDom(t)}}function re(t){return t.config.get("htmlSupport.fullPage.allowRenderStylesFromHead")}const ie="htmlEmptyBlock";class oe extends t.Plugin{static get pluginName(){return"EmptyBlock"}static get isOfficialPlugin(){return!0}afterInit(){const{model:t,conversion:e,plugins:r,config:i}=this.editor,o=t.schema,n=i.get("htmlSupport.preserveEmptyBlocksInEditingView");o.extend("$block",{allowAttributes:[ie]}),o.extend("$container",{allowAttributes:[ie]}),o.isRegistered("tableCell")&&o.extend("tableCell",{allowAttributes:[ie]}),n?e.for("downcast").add(ne()):e.for("dataDowncast").add(ne()),e.for("upcast").add(function(t){return e=>{e.on("element",(e,r,i)=>{const{viewItem:o,modelRange:n}=r;if(!o.is("element")||!o.isEmpty||o.getCustomProperty("$hasBlockFiller"))return;const l=n&&n.start.nodeAfter;if(!l||!t.checkAttribute(l,ie))return;if(i.writer.setAttribute(ie,!0,l),1!=l.childCount)return;const s=l.getChild(0);s.is("element","paragraph")&&t.checkAttribute(s,ie)&&i.writer.setAttribute(ie,!0,s)},{priority:"lowest"})}}(o)),r.has("ClipboardPipeline")&&this._registerClipboardPastingHandler()}_registerClipboardPastingHandler(){const t=this.editor.plugins.get("ClipboardPipeline");this.listenTo(t,"contentInsertion",(t,e)=>{e.sourceEditorId!==this.editor.id&&this.editor.model.change(t=>{for(const{item:r}of t.createRangeIn(e.content))r.is("element")&&r.hasAttribute(ie)&&t.removeAttribute(ie,r)})})}}function ne(){return t=>{t.on(`attribute:${ie}`,(t,e,r)=>{const{mapper:i,consumable:o}=r,{item:n}=e;if(!o.consume(n,t.name))return;const l=i.toViewElement(n);l&&e.attributeNewValue&&(l.getFillerOffset=()=>null)})}}})(),(window.CKEditor5=window.CKEditor5||{}).htmlSupport=i})();;
/*!
 * @license Copyright (c) 2003-2026, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md.
 */(()=>{var e={237:e=>{"use strict";e.exports=CKEditor5.dll},311:(e,t,i)=>{e.exports=i(237)("./src/ui.js")},584:(e,t,i)=>{e.exports=i(237)("./src/utils.js")},782:(e,t,i)=>{e.exports=i(237)("./src/core.js")},783:(e,t,i)=>{e.exports=i(237)("./src/engine.js")}},t={};function i(o){var r=t[o];if(void 0!==r)return r.exports;var n=t[o]={exports:{}};return e[o](n,n.exports,i),n.exports}i.d=(e,t)=>{for(var o in t)i.o(t,o)&&!i.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},i.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),i.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var o={};(()=>{"use strict";i.r(o),i.d(o,{DecoupledEditor:()=>c,DecoupledEditorUI:()=>s,DecoupledEditorUIView:()=>l});var e=i(782),t=i(584),r=i(311),n=i(783);class s extends r.EditorUI{view;constructor(e,t){super(e),this.view=t}init(){const e=this.editor,t=this.view,i=e.editing.view,o=t.editable,r=i.document.getRoot();o.name=r.rootName,t.render();const n=o.element;this.setEditableElement(o.name,n),t.editable.bind("isFocused").to(this.focusTracker),i.attachDomRoot(n),this._initPlaceholder(),this._initToolbar(),this.initMenuBar(this.view.menuBarView),this.fire("ready")}destroy(){super.destroy();const e=this.view,t=this.editor.editing.view;t.getDomRoot(e.editable.name)&&t.detachDomRoot(e.editable.name),e.destroy()}_initToolbar(){const e=this.editor,t=this.view;t.toolbar.fillFromConfig(e.config.get("toolbar"),this.componentFactory),this.addToolbar(t.toolbar)}_initPlaceholder(){const e=this.editor,t=e.editing.view,i=t.document.getRoot(),o=e.config.get("placeholder");if(o){const e="string"==typeof o?o:o[i.rootName];e&&(i.placeholder=e)}(0,n.enableViewPlaceholder)({view:t,element:i,isDirectHost:!1,keepOnFocus:!0})}}class l extends r.EditorUIView{toolbar;editable;menuBarView;constructor(e,t,i={}){super(e),this.toolbar=new r.ToolbarView(e,{shouldGroupWhenFull:i.shouldToolbarGroupWhenFull}),this.menuBarView=new r.MenuBarView(e),this.editable=new r.InlineEditableUIView(e,t,i.editableElement,{label:i.label}),this.toolbar.extendTemplate({attributes:{class:["ck-reset_all","ck-rounded-corners"],dir:e.uiLanguageDirection}}),this.menuBarView.extendTemplate({attributes:{class:["ck-reset_all","ck-rounded-corners"],dir:e.uiLanguageDirection}})}render(){super.render(),this.registerChild([this.menuBarView,this.toolbar,this.editable])}}function a(e){return function(e){return"object"==typeof e&&null!==e}(e)&&1===e.nodeType&&!function(e){if("object"!=typeof e)return!1;if(null==e)return!1;if(null===Object.getPrototypeOf(e))return!0;if("[object Object]"!==Object.prototype.toString.call(e)){const t=e[Symbol.toStringTag];return null!=t&&(!!Object.getOwnPropertyDescriptor(e,Symbol.toStringTag)?.writable&&e.toString()===`[object ${t}]`)}let t=e;for(;null!==Object.getPrototypeOf(t);)t=Object.getPrototypeOf(t);return Object.getPrototypeOf(e)===t}(e)}class c extends((0,e.ElementApiMixin)(e.Editor)){static get editorName(){return"DecoupledEditor"}ui;constructor(i,o={}){if(!d(i)&&void 0!==o.initialData)throw new t.CKEditorError("editor-create-initial-data",null);super(o),void 0===this.config.get("initialData")&&this.config.set("initialData",function(e){return d(e)?(0,t.getDataFromElement)(e):e}(i)),d(i)&&(this.sourceElement=i,(0,e.secureSourceElement)(this,i)),this.model.document.createRoot();const r=!this.config.get("toolbar.shouldNotGroupWhenFull"),n=new l(this.locale,this.editing.view,{editableElement:this.sourceElement,shouldToolbarGroupWhenFull:r,label:this.config.get("label")});this.ui=new s(this,n)}destroy(){const e=this.getData();return this.ui.destroy(),super.destroy().then(()=>{this.sourceElement&&this.updateSourceElement(e)})}static create(e,i={}){return new Promise(o=>{if(d(e)&&"TEXTAREA"===e.tagName)throw new t.CKEditorError("editor-wrong-element",null);const r=new this(e,i);o(r.initPlugins().then(()=>r.ui.init()).then(()=>r.data.init(r.config.get("initialData"))).then(()=>r.fire("ready")).then(()=>r))})}}function d(e){return a(e)}})(),(window.CKEditor5=window.CKEditor5||{}).editorDecoupled=o})();;
/* @license GPL-2.0-or-later https://www.drupal.org/licensing/faq */
(function($,Drupal,drupalSettings){function findFieldForFormatSelector($formatSelector){const fieldId=$formatSelector.attr('data-editor-for');return $(`#${fieldId}`).get(0);}function filterXssWhenSwitching(field,format,originalFormatID,callback){if(format.editor.isXssSafe)callback(field,format);else $.ajax({url:Drupal.url(`editor/filter_xss/${format.format}`),type:'POST',data:{value:field.value,original_format_id:originalFormatID},dataType:'json',success(xssFilteredValue){if(xssFilteredValue!==false)field.value=xssFilteredValue;callback(field,format);}});}function changeTextEditor(field,newFormatID){const previousFormatID=field.getAttribute('data-editor-active-text-format');if(drupalSettings.editor.formats[previousFormatID])Drupal.editorDetach(field,drupalSettings.editor.formats[previousFormatID]);else $(field).off('.editor');if(drupalSettings.editor.formats[newFormatID]){const format=drupalSettings.editor.formats[newFormatID];filterXssWhenSwitching(field,format,previousFormatID,Drupal.editorAttach);}field.setAttribute('data-editor-active-text-format',newFormatID);}function onTextFormatChange(event){const select=event.target;const field=event.data.field;const activeFormatID=field.getAttribute('data-editor-active-text-format');const newFormatID=select.value;if(newFormatID===activeFormatID)return;const supportContentFiltering=drupalSettings.editor.formats[newFormatID]?.editorSupportsContentFiltering;const hasContent=field.value!=='';if(hasContent&&supportContentFiltering){const message=Drupal.t('Changing the text format to %text_format will permanently remove content that is not allowed in that text format.<br><br>Save your changes before switching the text format to avoid losing data.',{'%text_format':$(select).find('option:selected')[0].textContent});const confirmationDialog=Drupal.dialog(`<div>${message}</div>`,{title:Drupal.t('Change text format?'),classes:{'ui-dialog':'editor-change-text-format-modal'},resizable:false,buttons:[{text:Drupal.t('Continue'),class:'button button--primary',click(){changeTextEditor(field,newFormatID);confirmationDialog.close();}},{text:Drupal.t('Cancel'),class:'button',click(){select.value=activeFormatID;const eventChange=new Event('change');select.dispatchEvent(eventChange);confirmationDialog.close();}}],closeOnEscape:false,create(){$(this).parent().find('.ui-dialog-titlebar-close').remove();},beforeClose:false,close(event){$(event.target).remove();}});confirmationDialog.showModal();}else changeTextEditor(field,newFormatID);}Drupal.editors={};Drupal.behaviors.editor={attach(context,settings){if(!settings.editor)return;once('editor','[data-editor-for]',context).forEach((editor)=>{const $this=$(editor);const field=findFieldForFormatSelector($this);if(!field)return;const activeFormatID=editor.value;field.setAttribute('data-editor-active-text-format',activeFormatID);if(settings.editor.formats[activeFormatID])Drupal.editorAttach(field,settings.editor.formats[activeFormatID]);$(field).on('change.editor keypress.editor',()=>{field.setAttribute('data-editor-value-is-changed','true');$(field).off('.editor');});if(editor.tagName==='SELECT')$this.on('change.editorAttach',{field},onTextFormatChange);$(field.form).on('submit',(event)=>{if(event.isDefaultPrevented())return;if(settings.editor.formats[activeFormatID])Drupal.editorDetach(field,settings.editor.formats[activeFormatID],'serialize');});});},detach(context,settings,trigger){let editors;if(trigger==='serialize')editors=once.filter('editor','[data-editor-for]',context);else editors=once.remove('editor','[data-editor-for]',context);editors.forEach((editor)=>{const $this=$(editor);const activeFormatID=editor.value;const field=findFieldForFormatSelector($this);if(field&&activeFormatID in settings.editor.formats)Drupal.editorDetach(field,settings.editor.formats[activeFormatID],trigger);});}};Drupal.editorAttach=function(field,format){if(format.editor){Drupal.editors[format.editor].attach(field,format);Drupal.editors[format.editor].onChange(field,()=>{$(field).trigger('formUpdated');field.setAttribute('data-editor-value-is-changed','true');});}};Drupal.editorDetach=function(field,format,trigger){if(format.editor){Drupal.editors[format.editor].detach(field,format,trigger);if(field.getAttribute('data-editor-value-is-changed')==='false')field.value=field.getAttribute('data-editor-value-original');}};})(jQuery,Drupal,drupalSettings);;
((Drupal,debounce,CKEditor5,$,once)=>{Drupal.CKEditor5Instances=new Map();const callbacks=new Map();const required=new Set();function findFunc(scope,name){if(!scope)return null;const parts=name.includes('.')?name.split('.'):name;if(parts.length>1)return findFunc(scope[parts.shift()],parts);return typeof scope[parts[0]]==='function'?scope[parts[0]]:null;}function buildFunc(config){const {func}=config;const fn=findFunc(window,func.name);if(typeof fn==='function'){const result=func.invoke?fn(...func.args):fn;return result;}return null;}function buildRegexp(config){const {pattern}=config.regexp;const main=pattern.match(/\/(.+)\/.*/)[1];const options=pattern.match(/\/.+\/(.*)/)[1];return new RegExp(main,options);}function processConfig(config){function processArray(config){return config.map((item)=>{if(typeof item==='object')return processConfig(item);return item;});}if(config===null)return null;return Object.entries(config).reduce((processed,[key,value])=>{if(typeof value==='object'){if(!value)return processed;if(value.hasOwnProperty('func'))processed[key]=buildFunc(value);else if(value.hasOwnProperty('regexp'))processed[key]=buildRegexp(value);else if(Array.isArray(value))processed[key]=processArray(value);else processed[key]=processConfig(value);}else processed[key]=value;return processed;},{});}const setElementId=(element)=>{const id=Math.random().toString().slice(2,9);element.setAttribute('data-ckeditor5-id',id);return id;};const getElementId=(element)=>element.getAttribute('data-ckeditor5-id');function selectPlugins(plugins){return plugins.map((pluginDefinition)=>{const [build,name]=pluginDefinition.split('.');if(CKEditor5[build]&&CKEditor5[build][name])return CKEditor5[build][name];console.warn(`Failed to load ${build} - ${name}`);return null;});}function processRules(rulesGroup){try{[...rulesGroup.cssRules].forEach(ckeditor5SelectorProcessing);}catch(e){console.warn(`Stylesheet ${rulesGroup.href} not included in CKEditor reset due to the browser's CORS policy.`);}}function ckeditor5SelectorProcessing(rule){if(rule.cssRules)processRules(rule);if(!rule.selectorText)return;const offCanvasId='#drupal-off-canvas';const CKEditorClass='.ck';const styleFence='[data-drupal-ck-style-fence]';if(rule.selectorText.includes(offCanvasId)||rule.selectorText.includes(CKEditorClass))rule.selectorText=rule.selectorText.split(/,/g).map((selector)=>{if(selector.includes(offCanvasId))return `${selector.trim()}:not(${styleFence} *)`;if(selector.includes(CKEditorClass))return [selector.trim(),selector.trim().replace(CKEditorClass,`${offCanvasId} ${styleFence} ${CKEditorClass}`)];return selector;}).flat().join(', ');}function offCanvasCss(element){const fenceName='data-drupal-ck-style-fence';const editor=Drupal.CKEditor5Instances.get(element.getAttribute('data-ckeditor5-id'));editor.ui.view.element.setAttribute(fenceName,'');if(once('ckeditor5-off-canvas-reset','body').length){[...document.styleSheets].forEach(processRules);const prefix=`#drupal-off-canvas-wrapper [${fenceName}]`;const addedCss=[`${prefix} .ck.ck-content * {display:revert;background:revert;color:initial;padding:revert;}`,`${prefix} .ck.ck-content li {display:list-item}`];const prefixedCss=[...addedCss].join('\n');const offCanvasCssStyle=document.createElement('style');offCanvasCssStyle.textContent=prefixedCss;offCanvasCssStyle.setAttribute('id','ckeditor5-off-canvas-reset');document.body.appendChild(offCanvasCssStyle);}}Drupal.editors.ckeditor5={attach(element,format){const {editorClassic}=CKEditor5;const {toolbar,plugins,config,language}=format.editorSettings;const extraPlugins=selectPlugins(plugins);const pluginConfig=processConfig(config);const editorConfig={extraPlugins,toolbar,...pluginConfig,language:{...pluginConfig.language,...language}};const id=setElementId(element);const {ClassicEditor}=editorClassic;ClassicEditor.create(element,editorConfig).then((editor)=>{function calculateLineHeight(rows){const element=document.createElement('p');element.setAttribute('style','visibility: hidden;');element.innerHTML='&nbsp;';editor.ui.view.editable.element.append(element);const styles=window.getComputedStyle(element);const height=element.clientHeight;const marginTop=parseInt(styles.marginTop,10);const marginBottom=parseInt(styles.marginBottom,10);const mostMargin=marginTop>=marginBottom?marginTop:marginBottom;element.remove();return ((height+mostMargin)*(rows-1)+marginTop+height+marginBottom);}Drupal.CKEditor5Instances.set(id,editor);const rows=editor.sourceElement.getAttribute('rows');editor.ui.view.editable.element.closest('.ck-editor').style.setProperty('--ck-min-height',`${calculateLineHeight(rows)}px`);if(element.hasAttribute('required')){required.add(id);element.removeAttribute('required');}if(element.hasAttribute('disabled'))editor.enableReadOnlyMode('ckeditor5_disabled');$(document).on(`drupalViewportOffsetChange.ckeditor5.${id}`,(event,offsets)=>{editor.ui.viewportOffset=offsets;});editor.model.document.on('change:data',()=>{const callback=callbacks.get(id);if(callback)callback();});const isOffCanvas=element.closest('#drupal-off-canvas');if(isOffCanvas)offCanvasCss(element);}).catch((error)=>{console.info('Debugging can be done with an unminified version of CKEditor by installing from the source file. Consult documentation at https://www.drupal.org/node/3258901');console.error(error);});},detach(element,format,trigger){const id=getElementId(element);const editor=Drupal.CKEditor5Instances.get(id);if(!editor)return;$(document).off(`drupalViewportOffsetChange.ckeditor5.${id}`);if(trigger==='serialize')editor.updateSourceElement();else{element.removeAttribute('contentEditable');return editor.destroy().then(()=>{Drupal.CKEditor5Instances.delete(id);callbacks.delete(id);if(required.has(id)){element.setAttribute('required','required');required.delete(id);}}).catch((error)=>{console.error(error);});}},onChange(element,callback){callbacks.set(getElementId(element),debounce(callback,400,true));},attachInlineEditor(element,format,mainToolbarId){const {editorDecoupled}=CKEditor5;const {toolbar,plugins,config:pluginConfig,language}=format.editorSettings;const extraPlugins=selectPlugins(plugins);const config={extraPlugins,toolbar,language,...processConfig(pluginConfig)};const id=setElementId(element);const {DecoupledEditor}=editorDecoupled;DecoupledEditor.create(element,config).then((editor)=>{Drupal.CKEditor5Instances.set(id,editor);const toolbar=document.getElementById(mainToolbarId);toolbar.appendChild(editor.ui.view.toolbar.element);editor.model.document.on('change:data',()=>{const callback=callbacks.get(id);if(callback)callback(editor.getData());});}).catch((error)=>{console.error(error);});}};Drupal.ckeditor5={saveCallback:null,openDialog(url,saveCallback,dialogSettings){dialogSettings.classes=dialogSettings.classes||{};const classes=dialogSettings.classes['ui-dialog']?dialogSettings.classes['ui-dialog'].split(' '):[];classes.push('ui-dialog--narrow');dialogSettings.classes['ui-dialog']=classes.join(' ');dialogSettings.autoResize=window.matchMedia('(min-width: 600px)').matches;dialogSettings.width='auto';const ckeditorAjaxDialog=Drupal.ajax({dialog:dialogSettings,dialogType:'modal',selector:'.ckeditor5-dialog-loading-link',url,progress:{type:'fullscreen'},submit:{editor_object:{}}});ckeditorAjaxDialog.execute();Drupal.ckeditor5.saveCallback=saveCallback;}};Drupal.behaviors.editorStyleFix={attach(context){[...document.styleSheets].filter((sheet)=>sheet.ownerNode.hasAttribute('data-cke')).forEach((sheet)=>{[...sheet.cssRules].forEach((rule,ruleIndex)=>{if(rule?.selectorText&&(rule.selectorText.includes(' ol')||rule.selectorText.includes(' ul'))&&!rule.selectorText.includes('type'))sheet.cssRules[ruleIndex].style['list-style-type']=null;});});}};function redirectTextareaFragmentToCKEditor5Instance(){const hash=window.location.hash.substring(1);const element=document.getElementById(hash);if(element){const editorID=getElementId(element);const editor=Drupal.CKEditor5Instances.get(editorID);if(editor){editor.sourceElement.nextElementSibling.setAttribute('id',`cke_${hash}`);window.location.replace(`#cke_${hash}`);}}}$(window).on('hashchange.ckeditor',redirectTextareaFragmentToCKEditor5Instance);window.addEventListener('dialog:beforecreate',()=>{const dialogLoading=document.querySelector('.ckeditor5-dialog-loading');if(dialogLoading){dialogLoading.addEventListener('transitionend',function removeDialogLoading(){dialogLoading.remove();});dialogLoading.style.transition='top 0.5s ease';dialogLoading.style.top='-40px';}});$(window).on('editor:dialogsave',(e,values)=>{if(Drupal.ckeditor5.saveCallback)Drupal.ckeditor5.saveCallback(values);});window.addEventListener('dialog:afterclose',()=>{if(Drupal.ckeditor5.saveCallback)Drupal.ckeditor5.saveCallback=null;});})(Drupal,Drupal.debounce,CKEditor5,jQuery,once);;
(function($,Drupal){"use strict";Drupal.behaviors.imceUrlInput={attach:function(context,settings){$('.imce-url-input',context).not('.imce-url-processed').addClass('imce-url-processed').each(imceInput.processUrlInput);}};var imceInput=window.imceInput=window.imceInput||{processUrlInput:function(i,el){var button=imceInput.createUrlButton(el.id,el.getAttribute('data-imce-type'));el.parentNode.insertBefore(button,el);},createUrlButton:function(inputId,inputType){var button=document.createElement('a');button.href='#';button.className='imce-url-button';button.title=Drupal.t('Open File Browser');button.innerHTML='<span>'+button.title+'</span>';button.onclick=imceInput.urlButtonClick;button.setAttribute('data-input-id',inputId||'imce-url-input-'+(Math.random()+'').substring(2));button.setAttribute('data-input-type',inputType||'link');return button;},urlButtonClick:function(e){const inputId=this.getAttribute('data-input-id');const type=this.getAttribute('data-input-type');$('#'+inputId).trigger('focus');imceInput.openImce('imceInput.urlSendto',type,'inputId='+inputId);return false;},openImce:function(sendto,type,params){var url=imceInput.url('sendto='+sendto+'&type='+type+(params?'&'+params:''));return imceInput.openWindow(url);},url:function(params){var url=Drupal.url('imce');if(params)url+=(url.indexOf('?')===-1?'?':'&')+params;return url;},openWindow:function(url,win){var width=Math.min(1000,parseInt(screen.availWidth*0.8));var height=Math.min(800,parseInt(screen.availHeight*0.8));return (win||window).open(url,'','width='+width+',height='+height+',resizable=1');},urlSendto:function(File,win){var url=File.getUrl();var el=$('#'+win.imce.getQuery('inputId'))[0];win.close();if(el)$(el).val(url).trigger('change').trigger('focus');}};})(jQuery,Drupal);;
(function(Drupal,CKEditor5){class ImceSelector extends CKEditor5.core.Plugin{init(){this.editor.ui.on('ready',function(){const plugins=this.editor.plugins;if(plugins.has('ImageInsertViaUrlUI')){const dialog=plugins.get('Dialog');if(dialog)dialog.once('show:insertImageViaUrl',(evt,data)=>{const el=data.content.element.getElementsByClassName('ck-input-text')[0];imceInput.processCKEditor5Input(el,'image');});}if(plugins.has('ImageInsertUI')){const view=plugins.get('ImageInsertUI').dropdownView;if(view)view.once('change:isOpen',function(){const el=view.element.getElementsByClassName('ck-input-text')[0];imceInput.processCKEditor5Input(el,'image');});}if(plugins.has('LinkUI')){const ui=plugins.get('LinkUI');const process=()=>{const el=ui.formView?.urlInputView?.fieldView?.element;if(el){ui._balloon?.view?.off('change:isVisible',process);imceInput.processCKEditor5Input(el,'link');return true;}};process()||ui._balloon?.view?.on('change:isVisible',process);}});}}class ImceImage extends CKEditor5.core.Plugin{init(){const label=Drupal.t('Insert images using Imce File Manager');imceInput.ckeditor5PluginInit(this.editor,'image',label);}}class ImceLink extends CKEditor5.core.Plugin{init(){const label=Drupal.t('Insert file links using Imce File Manager');imceInput.ckeditor5PluginInit(this.editor,'link',label);}}CKEditor5.imce=CKEditor5.imce||{ImceSelector,ImceImage,ImceLink};const imceInput=window.imceInput||{};imceInput.ckeditor5PluginInit=function(editor,type,label){editor.ui.componentFactory.add('imce_'+type,function(){const button=new CKEditor5.ui.ButtonView();button.set({label,class:'ck-imce-button ck-imce-'+type+'-button',tooltip:true});button.on('execute',function(){const id=editor.sourceElement.getAttribute('data-ckeditor5-id');return imceInput.openImce('imceInput.sendtoCKEditor5',type,'ckid='+id);});return button;});};imceInput.processCKEditor5Input=function(el,type){if(!el)return;const name='sendtoCKEditor5'+(Math.random()+'').substring(2);imceInput[name]=function(File,win){el.value=File.getUrl();win.close();el.focus();el.dispatchEvent(new CustomEvent('input'));if(el.form){const button=el.form.getElementsByClassName('ck-button-save')[0];if(button)button.click();}};const button=imceInput.createUrlButton(el.id,type);button.className+=' imce-selector-button';button.onclick=function(){imceInput.openImce('imceInput.'+name,type);return false;};el.insertAdjacentElement('afterend',button);el.parentNode.className+=' ck-imce-wrp ck-imce-'+type+'-wrp';return button;};imceInput.sendtoCKEditor5=function(File,win){const imce=win.imce;const editor=Drupal.CKEditor5Instances.get(imce.getQuery('ckid'));if(!editor){win.close();return;}const type=imce.getQuery('type');const isImg=type==='image';const selected=imce.getSelection();const finish=function(){const inner=isImg?'':imceInput.ckeditor5GetSelection(editor);const html=imce.itemsHtml(selected,type,inner);imceInput.ckeditor5SetSelection(editor,html);win.close();};if(isImg)imce.loadItemUuids(selected,finish);else finish();};imceInput.ckeditor5GetSelection=function(editor){let html='';try{const model=editor.model;const content=model.getSelectedContent(model.document.selection);html=editor.data.stringify(content);}catch(err){console.error(err);}return html;};imceInput.ckeditor5SetSelection=function(editor,html,skipFocus){try{const viewFragment=editor.data.processor.toView(html);const modelFragment=editor.data.toModel(viewFragment);editor.model.insertContent(modelFragment);if(!skipFocus)editor.editing.view.focus();}catch(err){console.error(err);}};})(Drupal,CKEditor5);;
(function($,Drupal){Drupal.behaviors.menuUiDetailsSummaries={attach(context){$(context).find('.menu-link-form').drupalSetSummary((context)=>{const $context=$(context);if($context.find('.js-form-item-menu-enabled input:checked').length)return Drupal.checkPlain($context.find('.js-form-item-menu-title input')[0].value);return Drupal.t('Not in menu');});}};Drupal.behaviors.menuUiLinkAutomaticTitle={attach(context){const $context=$(context);$context.find('.menu-link-form').each(function(){const $this=$(this);const $checkbox=$this.find('.js-form-item-menu-enabled input');const $linkTitle=$context.find('.js-form-item-menu-title input');const $title=$this.closest('form').find('.js-form-item-title-0-value input');if(!($checkbox.length&&$linkTitle.length&&$title.length))return;if($checkbox[0].checked&&$linkTitle[0].value.length)$linkTitle.data('menuLinkAutomaticTitleOverridden',true);$linkTitle.on('keyup',()=>{$linkTitle.data('menuLinkAutomaticTitleOverridden',true);});$checkbox.on('change',()=>{if($checkbox[0].checked){if(!$linkTitle.data('menuLinkAutomaticTitleOverridden'))$linkTitle[0].value=$title[0].value;}else{$linkTitle[0].value='';$linkTitle.removeData('menuLinkAutomaticTitleOverridden');}$checkbox.closest('.vertical-tabs-pane').trigger('summaryUpdated');$checkbox.trigger('formUpdated');});$title.on('keyup',()=>{if(!$linkTitle.data('menuLinkAutomaticTitleOverridden')&&$checkbox[0].checked){$linkTitle[0].value=$title[0].value;$linkTitle.trigger('formUpdated');}});});}};})(jQuery,Drupal);;
(function($,Drupal){Drupal.behaviors.entityContentDetailsSummaries={attach(context){const $context=$(context);$context.find('.entity-content-form-revision-information').drupalSetSummary((context)=>{const $revisionContext=$(context);const revisionCheckbox=$revisionContext.find('.js-form-item-revision input');if((revisionCheckbox.length&&revisionCheckbox[0].checked)||(!revisionCheckbox.length&&$revisionContext.find('.js-form-item-revision-log textarea').length))return Drupal.t('New revision');return Drupal.t('No revision');});$context.find('details.entity-translation-options').drupalSetSummary((context)=>{const $translationContext=$(context);let translate;let $checkbox=$translationContext.find('.js-form-item-translation-translate input');if($checkbox.length)translate=$checkbox[0].checked?Drupal.t('Needs to be updated'):Drupal.t('Does not need to be updated');else{$checkbox=$translationContext.find('.js-form-item-translation-retranslate input');translate=$checkbox[0]?.checked?Drupal.t('Flag other translations as outdated'):Drupal.t('Do not flag other translations as outdated');}return translate;});}};})(jQuery,Drupal);;
(function($,Drupal,drupalSettings){Drupal.behaviors.nodeDetailsSummaries={attach(context){const $context=$(context);$context.find('.node-form-author').drupalSetSummary((context)=>{const nameElement=context.querySelector('.field--name-uid input');const name=nameElement?.value;const dateElement=context.querySelector('.field--name-created input');const date=dateElement?.value;if(name&&date)return Drupal.t('By @name on @date',{'@name':name,'@date':date});if(name)return Drupal.t('By @name',{'@name':name});if(date)return Drupal.t('Authored on @date',{'@date':date});});$context.find('.node-form-options').drupalSetSummary((context)=>{const $optionsContext=$(context);const values=[];if($optionsContext.find('input:checked').length){$optionsContext.find('input:checked').next('label').each(function(){values.push(Drupal.checkPlain(this.textContent.trim()));});return values.join(', ');}return Drupal.t('Not promoted');});}};})(jQuery,Drupal,drupalSettings);;
(function($,Drupal,once){'use strict';Drupal.behaviors.paragraphsActions={attach:function(context,settings){var $actionsElement=$(once('paragraphs-dropdown','.paragraphs-dropdown',context));$actionsElement.each(function(){var $this=$(this);var $toggle=$this.find('.paragraphs-dropdown-toggle');$toggle.on('click',function(e){e.preventDefault();$this.toggleClass('open');});$this.on('focusout',function(e){setTimeout(function(){if($this.has(document.activeElement).length==0)$this.removeClass('open');},1);});});}};})(jQuery,Drupal,once);;
(function($,Drupal){function DropButton(dropbutton,settings){const options=$.extend({title:Drupal.t('List additional actions')},settings);const $dropbutton=$(dropbutton);this.$dropbutton=$dropbutton;this.$list=$dropbutton.find('.dropbutton');this.$actions=this.$list.find('li').addClass('dropbutton-action');if(this.$actions.length>1){const $primary=this.$actions.slice(0,1);const $secondary=this.$actions.slice(1);$secondary.addClass('secondary-action');$primary.after(Drupal.theme('dropbuttonToggle',options));this.$dropbutton.addClass('dropbutton-multiple').on({'mouseleave.dropbutton':this.hoverOut.bind(this),'mouseenter.dropbutton':this.hoverIn.bind(this),'focusout.dropbutton':this.focusOut.bind(this),'focusin.dropbutton':this.focusIn.bind(this)});}else this.$dropbutton.addClass('dropbutton-single');}function dropbuttonClickHandler(e){e.preventDefault();$(e.target).closest('.dropbutton-wrapper').toggleClass('open');}Drupal.behaviors.dropButton={attach(context,settings){const dropbuttons=once('dropbutton','.dropbutton-wrapper',context);if(dropbuttons.length){const body=once('dropbutton-click','body');if(body.length)$(body).on('click','.dropbutton-toggle',dropbuttonClickHandler);dropbuttons.forEach((dropbutton)=>{DropButton.dropbuttons.push(new DropButton(dropbutton,settings.dropbutton));});}}};$.extend(DropButton,{dropbuttons:[]});$.extend(DropButton.prototype,{toggle(show){const isBool=typeof show==='boolean';show=isBool?show:!this.$dropbutton.hasClass('open');this.$dropbutton.toggleClass('open',show);},hoverIn(){if(this.timerID)window.clearTimeout(this.timerID);},hoverOut(){this.timerID=window.setTimeout(this.close.bind(this),500);},open(){this.toggle(true);},close(){this.toggle(false);},focusOut(e){this.hoverOut.call(this,e);},focusIn(e){this.hoverIn.call(this,e);}});$.extend(Drupal.theme,{dropbuttonToggle(options){return `<li class="dropbutton-toggle"><button type="button"><span class="dropbutton-arrow"><span class="visually-hidden">${options.title}</span></span></button></li>`;}});Drupal.DropButton=DropButton;})(jQuery,Drupal);;
((Drupal)=>{Drupal.theme.dropbuttonToggle=(options)=>`<li class="dropbutton-toggle"><button type="button" class="dropbutton__toggle"><span class="visually-hidden">${options.title}</span></button></li>`;})(Drupal);;
((Drupal,once)=>{Drupal.behaviors.ginDropbutton={attach:function(context){once("ginDropbutton",".dropbutton-multiple:has(.dropbutton--gin)",context).forEach(((el)=>{const toggle=el.querySelector(".dropbutton__toggle");toggle&&toggle.addEventListener("click",(()=>{this.positionDropdown(el);}));}));},positionDropdown:function(el){const secondaryAction=el.querySelector(".secondary-action"),dropMenu=el.querySelector(".dropbutton__items");if(!secondaryAction||!dropMenu)return;const toolbarTotalHeight=(parseFloat(getComputedStyle(document.documentElement).getPropertyValue("--gin-toolbar-y-offset"))||0)+(parseFloat(getComputedStyle(document.documentElement).getPropertyValue("--gin-toolbar-height"))||0),boundingRect=secondaryAction.getBoundingClientRect(),spaceBelow=window.innerHeight-boundingRect.bottom,halfVh=Math.floor(window.innerHeight/2),upperBound=el.closest("form")||document.querySelector("#block-gin-content")||document.body,upperTop=Math.max(upperBound.getBoundingClientRect().top,0),effectiveSpaceAbove=Math.max(boundingRect.top-upperTop,0);dropMenu.style.position="absolute",dropMenu.style.overflowY="auto",spaceBelow>=effectiveSpaceAbove?(dropMenu.style.top="100%",dropMenu.style.bottom="auto",dropMenu.style.maxHeight=`${Math.max(spaceBelow-32,120)}px`):(dropMenu.style.top="auto",dropMenu.style.bottom="100%",dropMenu.style.maxHeight=Math.max(Math.min(effectiveSpaceAbove,halfVh),0)-toolbarTotalHeight+"px");}};})(Drupal,once);;
(function($,Drupal,once){'use strict';var setUpTab=function($parWidget,$parTabs,$parContent,$parBehavior,$mainRegion){var $tabContent=$parTabs.find('.paragraphs_content_tab');var $tabBehavior=$parTabs.find('.paragraphs_behavior_tab');if($tabBehavior.hasClass('is-active')){$parWidget.removeClass('content-active').addClass('behavior-active');$tabContent.removeClass('is-active');$tabContent.find('a').removeClass('is-active');$tabBehavior.addClass('is-active');$tabBehavior.find('a').addClass('is-active');}else{if(!($mainRegion.hasClass('content-active'))&&!($mainRegion.hasClass('behavior-active'))){$tabContent.addClass('is-active');$tabContent.find('a').addClass('is-active');$parWidget.addClass('content-active');}$parTabs.removeClass('paragraphs-tabs-hide');if($parBehavior.length===0)$parTabs.addClass('paragraphs-tabs-hide');}};var switchActiveClass=function($parTabs,$clickedTab,$parWidget){var $clickedTabParent=$clickedTab.parent();$parTabs.find('li').removeClass('is-active');$parTabs.find('li').find('a').removeClass('is-active');$clickedTabParent.addClass('is-active');$clickedTabParent.find('a').addClass('is-active');$parWidget.removeClass('behavior-active content-active');if($clickedTabParent.hasClass('paragraphs_content_tab')){$parWidget.addClass('content-active');$parWidget.find('.paragraphs-add-wrapper').parent().show();}else{$parWidget.addClass('behavior-active');$parWidget.find('.paragraphs-add-wrapper').parent().hide();}};var markFirstVisibleParagraph=function(totalTopOffset){var $window=$(window);var bottomOfScreen=$window.scrollTop()+$window.height();var topOfScreen=$window.scrollTop()+totalTopOffset;var $firstParagraph=false;var $allParagraphs=$('.node-form .draggable');$allParagraphs.each(function(){var $this=$(this);var topOfElement=$this.offset().top;var bottomOfElement=$this.offset().top+$this.height();if((bottomOfScreen>topOfElement)&&(topOfScreen<bottomOfElement)){if($firstParagraph)if(topOfElement>topOfScreen){$firstParagraph=$this;return false;}else{if(topOfElement>bottomOfScreen)return false;}$firstParagraph=$this;if(topOfScreen<topOfElement)return false;}});if($firstParagraph){$('.first-paragraph').removeClass('first-paragraph');$firstParagraph.addClass('first-paragraph paragraph-hover');}return $firstParagraph;};Drupal.behaviors.bodyTabs={attach:function(context){var $topLevelParWidgets=$('.paragraphs-tabs-wrapper',context).filter(function(){return $(this).parents('.paragraphs-nested').length===0;});$(once('paragraphs-bodytabs',$topLevelParWidgets)).each(function(){var $parWidget=$(this);var $parTabs=$parWidget.find('.paragraphs-tabs');$parTabs.find('a').click(function(e){var toolbarHeight=$('.toolbar-tray-horizontal').outerHeight()||0;var adminToolbarsOffset=$('#toolbar-bar').outerHeight()+toolbarHeight;var totalTopOffset=adminToolbarsOffset+$('.paragraphs-tabs').outerHeight();var $firstParagraph;var currentParagraphOffset=0;var $window=$(window);$firstParagraph=markFirstVisibleParagraph(totalTopOffset);if($firstParagraph){currentParagraphOffset=$firstParagraph.offset().top-($window.scrollTop()+totalTopOffset);if(currentParagraphOffset<0)currentParagraphOffset=0;}e.preventDefault();switchActiveClass($parTabs,$(this),$parWidget);if($firstParagraph){$('html, body').scrollTop($firstParagraph.offset().top-totalTopOffset-currentParagraphOffset);setTimeout(function(){$('.first-paragraph').removeClass('paragraph-hover');},1000);}});});if($('.paragraphs-tabs-wrapper',context).length>0)$topLevelParWidgets.each(function(){var $parWidget=$(this);var $parTabs=$parWidget.find('.paragraphs-tabs');var $parContent=$parWidget.find('.paragraphs-content');var $parBehavior=$parWidget.find('.paragraphs-behavior');var $mainRegion=$parWidget.find('.layout-region-node-main');setUpTab($parWidget,$parTabs,$parContent,$parBehavior,$mainRegion);});}};})(jQuery,Drupal,once);;
(function($,Drupal){Drupal.behaviors.pathDetailsSummaries={attach(context){$(context).find('.path-form').drupalSetSummary((context)=>{const pathElement=document.querySelector('.js-form-item-path-0-alias input');const path=pathElement?.value;return path?Drupal.t('Alias: @alias',{'@alias':path}):Drupal.t('No alias');});}};})(jQuery,Drupal);;
(($,Drupal)=>{Drupal.behaviors.pathFieldsetSummaries={attach(context){if(typeof $.fn.drupalSetSummary==='undefined')return;$(context).find('.path-form').drupalSetSummary((pathForm)=>{const automaticInput=pathForm.querySelector('.js-form-item-path-0-pathauto input');if(automaticInput&&automaticInput.checked)return Drupal.t('Automatic alias');const pathInput=pathForm.querySelector('.js-form-item-path-0-alias input');if(pathInput&&pathInput.value)return Drupal.t('Alias: @alias',{'@alias':pathInput.value});return Drupal.t('No alias');});}};})(jQuery,Drupal);;
(function($,Drupal){Drupal.behaviors.textSummary={attach(context,settings){once('text-summary','.js-text-summary',context).forEach((summary)=>{const $widget=$(summary).closest('.js-text-format-wrapper');const $summary=$widget.find('.js-text-summary-wrapper');const $summaryLabel=$summary.find('label').eq(0);const $full=$widget.children('.js-form-type-textarea');let $fullLabel=$full.find('label').eq(0);if($fullLabel.length===0)$fullLabel=$('<label></label>').prependTo($full);if($fullLabel.hasClass('visually-hidden')){$fullLabel.html((index,oldHtml)=>`<span class="visually-hidden">${oldHtml}</span>`);$fullLabel.removeClass('visually-hidden');}const $link=$(`<span class="field-edit-link"> (<button type="button" class="link link-edit-summary">${Drupal.t('Hide summary')}</button>)</span>`);const $button=$link.find('button');let toggleClick=true;$link.on('click',(e)=>{if(toggleClick){$summary.hide();$button.html(Drupal.t('Edit summary'));$link.appendTo($fullLabel);}else{$summary.show();$button.html(Drupal.t('Hide summary'));$link.appendTo($summaryLabel);}e.preventDefault();toggleClick=!toggleClick;}).appendTo($summaryLabel);if(summary.value==='')$link.trigger('click');});}};})(jQuery,Drupal);;
(function($,Drupal,{focusable}){Drupal.behaviors.dialog={attach(context,settings){if(!document.querySelector('#drupal-modal'))document.body.insertAdjacentHTML('beforeend','<div id="drupal-modal" class="ui-front" style="display:none"></div>');if(context!==document){const dialog=context.closest('.ui-dialog-content');if(dialog){if($(dialog).dialog('option','drupalAutoButtons'))dialog.dispatchEvent(new CustomEvent('dialogButtonsChange'));setTimeout(function(){if(!dialog.contains(document.activeElement)){$(dialog).dialog('instance')._focusedElement=null;$(dialog).dialog('instance')._focusTabbable();}},0);}}const originalClose=settings.dialog.close;settings.dialog.close=function(event,...args){originalClose.apply(settings.dialog,[event,...args]);const $element=$(event.target);const ajaxContainer=$element.data('uiDialog')?$element.data('uiDialog').opener.closest('[data-drupal-ajax-container]'):[];if(ajaxContainer.length&&(document.activeElement===document.body||$(document.activeElement).not(':visible'))){const focusableChildren=focusable(ajaxContainer[0]);if(focusableChildren.length>0)setTimeout(()=>{focusableChildren[0].focus();},0);}$(event.target).remove();};},prepareDialogButtons($dialog){const buttons=[];const buttonSelectors='.form-actions input[type=submit], .form-actions a.button, .form-actions a.action-link';const buttonElements=$dialog[0].querySelectorAll(buttonSelectors);buttonElements.forEach((button)=>{button.style.display='none';buttons.push({text:button.innerHTML||button.getAttribute('value'),class:button.getAttribute('class'),'data-once':button.dataset.once,click(e){if(button.tagName==='A')button.click();else ['mousedown','mouseup','click'].forEach((event)=>button.dispatchEvent(new MouseEvent(event)));e.preventDefault();}});});return buttons;}};Drupal.AjaxCommands.prototype.openDialog=function(ajax,response,status){if(!response.selector)return false;let dialog=document.querySelector(response.selector);if(!dialog){dialog=document.createElement('div');dialog.id=response.selector.replace(/^#/,'');dialog.classList.add('ui-front');document.body.appendChild(dialog);}if(!ajax.wrapper)ajax.wrapper=dialog.id;response.command='insert';response.method='html';ajax.commands.insert(ajax,response,status);response.dialogOptions=response.dialogOptions||{};if(typeof response.dialogOptions.drupalAutoButtons==='undefined')response.dialogOptions.drupalAutoButtons=true;else if(response.dialogOptions.drupalAutoButtons==='false')response.dialogOptions.drupalAutoButtons=false;else response.dialogOptions.drupalAutoButtons=!!response.dialogOptions.drupalAutoButtons;if(!response.dialogOptions.buttons&&response.dialogOptions.drupalAutoButtons)response.dialogOptions.buttons=Drupal.behaviors.dialog.prepareDialogButtons($(dialog));const dialogButtonsChange=()=>{const buttons=Drupal.behaviors.dialog.prepareDialogButtons($(dialog));$(dialog).dialog('option','buttons',buttons);};dialog.addEventListener('dialogButtonsChange',dialogButtonsChange);dialog.addEventListener('dialog:beforeclose',(event)=>{dialog.removeEventListener('dialogButtonsChange',dialogButtonsChange);});const createdDialog=Drupal.dialog(dialog,response.dialogOptions);if(response.dialogOptions.modal)createdDialog.showModal();else createdDialog.show();dialog.parentElement?.querySelector('.ui-dialog-buttonset')?.classList.add('form-actions');};Drupal.AjaxCommands.prototype.closeDialog=function(ajax,response,status){const dialog=document.querySelector(response.selector);if(dialog){Drupal.dialog(dialog).close();if(!response.persist)dialog.remove();}};Drupal.AjaxCommands.prototype.setDialogOption=function(ajax,response,status){const dialog=document.querySelector(response.selector);if(dialog)$(dialog).dialog('option',response.optionName,response.optionValue);};window.addEventListener('dialog:aftercreate',(event)=>{const dialog=event.dialog;const cancelButton=event.target.querySelector('.dialog-cancel');const cancelClick=(e)=>{dialog.close('cancel');e.preventDefault();e.stopPropagation();};cancelButton?.removeEventListener('click',cancelClick);cancelButton?.addEventListener('click',cancelClick);});Drupal.AjaxCommands.prototype.openModalDialogWithUrl=function(ajax,response){const dialogOptions=response.dialogOptions||{};const elementSettings={progress:{type:'throbber'},dialogType:'modal',dialog:dialogOptions,url:response.url,httpMethod:'GET'};Drupal.ajax(elementSettings).execute();};})(jQuery,Drupal,window.tabbable);;
/* @license MIT https://github.com/ludo/jquery-treetable/blob/3.2.0/MIT-LICENSE.txt */
(function($){var Node,Tree,methods;Node=(function(){function Node(row,tree,settings){var parentId;this.row=row;this.tree=tree;this.settings=settings;this.id=this.row.data(this.settings.nodeIdAttr);parentId=this.row.data(this.settings.parentIdAttr);if(parentId!=null&&parentId!=="")this.parentId=parentId;this.treeCell=$(this.row.children(this.settings.columnElType)[this.settings.column]);this.expander=$(this.settings.expanderTemplate);this.indenter=$(this.settings.indenterTemplate);this.children=[];this.initialized=false;this.treeCell.prepend(this.indenter);}Node.prototype.addChild=function(child){return this.children.push(child);};Node.prototype.ancestors=function(){var ancestors,node;node=this;ancestors=[];while(node=node.parentNode())ancestors.push(node);return ancestors;};Node.prototype.collapse=function(){if(this.collapsed())return this;this.row.removeClass("expanded").addClass("collapsed");this._hideChildren();this.expander.attr("title",this.settings.stringExpand);if(this.initialized&&this.settings.onNodeCollapse!=null)this.settings.onNodeCollapse.apply(this);return this;};Node.prototype.collapsed=function(){return this.row.hasClass("collapsed");};Node.prototype.expand=function(){if(this.expanded())return this;this.row.removeClass("collapsed").addClass("expanded");if(this.initialized&&this.settings.onNodeExpand!=null)this.settings.onNodeExpand.apply(this);if($(this.row).is(":visible"))this._showChildren();this.expander.attr("title",this.settings.stringCollapse);return this;};Node.prototype.expanded=function(){return this.row.hasClass("expanded");};Node.prototype.hide=function(){this._hideChildren();this.row.hide();return this;};Node.prototype.isBranchNode=function(){if(this.children.length>0||this.row.data(this.settings.branchAttr)===true)return true;else return false;};Node.prototype.updateBranchLeafClass=function(){this.row.removeClass('branch');this.row.removeClass('leaf');this.row.addClass(this.isBranchNode()?'branch':'leaf');};Node.prototype.level=function(){return this.ancestors().length;};Node.prototype.parentNode=function(){if(this.parentId!=null)return this.tree[this.parentId];else return null;};Node.prototype.removeChild=function(child){var i=$.inArray(child,this.children);return this.children.splice(i,1);};Node.prototype.render=function(){var handler,settings=this.settings,target;if(settings.expandable===true&&this.isBranchNode()){handler=function(e){$(this).parents("table").treetable("node",$(this).parents("tr").data(settings.nodeIdAttr)).toggle();return e.preventDefault();};this.indenter.html(this.expander);target=settings.clickableNodeNames===true?this.treeCell:this.expander;target.off("click.treetable").on("click.treetable",handler);target.off("keydown.treetable").on("keydown.treetable",function(e){if(e.keyCode==13)handler.apply(this,[e]);});}this.indenter[0].style.paddingLeft=""+(this.level()*settings.indent)+"px";return this;};Node.prototype.reveal=function(){if(this.parentId!=null)this.parentNode().reveal();return this.expand();};Node.prototype.setParent=function(node){if(this.parentId!=null)this.tree[this.parentId].removeChild(this);this.parentId=node.id;this.row.data(this.settings.parentIdAttr,node.id);return node.addChild(this);};Node.prototype.show=function(){if(!this.initialized)this._initialize();this.row.show();if(this.expanded())this._showChildren();return this;};Node.prototype.toggle=function(){if(this.expanded())this.collapse();else this.expand();return this;};Node.prototype._hideChildren=function(){var child,_i,_len,_ref,_results;_ref=this.children;_results=[];for(_i=0,_len=_ref.length;_i<_len;_i++){child=_ref[_i];_results.push(child.hide());}return _results;};Node.prototype._initialize=function(){var settings=this.settings;this.render();if(settings.expandable===true&&settings.initialState==="collapsed")this.collapse();else this.expand();if(settings.onNodeInitialized!=null)settings.onNodeInitialized.apply(this);return this.initialized=true;};Node.prototype._showChildren=function(){var child,_i,_len,_ref,_results;_ref=this.children;_results=[];for(_i=0,_len=_ref.length;_i<_len;_i++){child=_ref[_i];_results.push(child.show());}return _results;};return Node;})();Tree=(function(){function Tree(table,settings){this.table=table;this.settings=settings;this.tree={};this.nodes=[];this.roots=[];}Tree.prototype.collapseAll=function(){var node,_i,_len,_ref,_results;_ref=this.nodes;_results=[];for(_i=0,_len=_ref.length;_i<_len;_i++){node=_ref[_i];_results.push(node.collapse());}return _results;};Tree.prototype.expandAll=function(){var node,_i,_len,_ref,_results;_ref=this.nodes;_results=[];for(_i=0,_len=_ref.length;_i<_len;_i++){node=_ref[_i];_results.push(node.expand());}return _results;};Tree.prototype.findLastNode=function(node){if(node.children.length>0)return this.findLastNode(node.children[node.children.length-1]);else return node;};Tree.prototype.loadRows=function(rows){var node,row,i;if(rows!=null)for(i=0;i<rows.length;i++){row=$(rows[i]);if(row.data(this.settings.nodeIdAttr)!=null){node=new Node(row,this.tree,this.settings);this.nodes.push(node);this.tree[node.id]=node;if(node.parentId!=null&&this.tree[node.parentId])this.tree[node.parentId].addChild(node);else this.roots.push(node);}}for(i=0;i<this.nodes.length;i++)node=this.nodes[i].updateBranchLeafClass();return this;};Tree.prototype.move=function(node,destination){var nodeParent=node.parentNode();if(node!==destination&&destination.id!==node.parentId&&$.inArray(node,destination.ancestors())===-1){node.setParent(destination);this._moveRows(node,destination);if(node.parentNode().children.length===1)node.parentNode().render();}if(nodeParent)nodeParent.updateBranchLeafClass();if(node.parentNode())node.parentNode().updateBranchLeafClass();node.updateBranchLeafClass();return this;};Tree.prototype.removeNode=function(node){this.unloadBranch(node);node.row.remove();if(node.parentId!=null)node.parentNode().removeChild(node);delete this.tree[node.id];this.nodes.splice($.inArray(node,this.nodes),1);return this;};Tree.prototype.render=function(){var root,_i,_len,_ref;_ref=this.roots;for(_i=0,_len=_ref.length;_i<_len;_i++){root=_ref[_i];root.show();}return this;};Tree.prototype.sortBranch=function(node,sortFun){node.children.sort(sortFun);this._sortChildRows(node);return this;};Tree.prototype.unloadBranch=function(node){var children=node.children.slice(0),i;for(i=0;i<children.length;i++)this.removeNode(children[i]);node.children=[];node.updateBranchLeafClass();return this;};Tree.prototype._moveRows=function(node,destination){var children=node.children,i;node.row.insertAfter(destination.row);node.render();for(i=children.length-1;i>=0;i--)this._moveRows(children[i],node);};Tree.prototype._sortChildRows=function(parentNode){return this._moveRows(parentNode,parentNode);};return Tree;})();methods={init:function(options,force){var settings;settings=$.extend({branchAttr:"ttBranch",clickableNodeNames:false,column:0,columnElType:"td",expandable:false,expanderTemplate:"<a href='#'>&nbsp;</a>",indent:19,indenterTemplate:"<span class='indenter'></span>",initialState:"collapsed",nodeIdAttr:"ttId",parentIdAttr:"ttParentId",stringExpand:"Expand",stringCollapse:"Collapse",onInitialized:null,onNodeCollapse:null,onNodeExpand:null,onNodeInitialized:null},options);return this.each(function(){var el=$(this),tree;if(force||el.data("treetable")===undefined){tree=new Tree(this,settings);tree.loadRows(this.rows).render();el.addClass("treetable").data("treetable",tree);if(settings.onInitialized!=null)settings.onInitialized.apply(tree);}return el;});},destroy:function(){return this.each(function(){return $(this).removeData("treetable").removeClass("treetable");});},collapseAll:function(){this.data("treetable").collapseAll();return this;},collapseNode:function(id){var node=this.data("treetable").tree[id];if(node)node.collapse();else throw new Error("Unknown node '"+id+"'");return this;},expandAll:function(){this.data("treetable").expandAll();return this;},expandNode:function(id){var node=this.data("treetable").tree[id];if(node){if(!node.initialized)node._initialize();node.expand();}else throw new Error("Unknown node '"+id+"'");return this;},loadBranch:function(node,rows){var settings=this.data("treetable").settings,tree=this.data("treetable").tree;rows=$(rows);if(node==null)this.append(rows);else{var lastNode=this.data("treetable").findLastNode(node);rows.insertAfter(lastNode.row);}this.data("treetable").loadRows(rows);rows.filter("tr").each(function(){tree[$(this).data(settings.nodeIdAttr)].show();});if(node!=null)node.render().expand();return this;},move:function(nodeId,destinationId){var destination,node;node=this.data("treetable").tree[nodeId];destination=this.data("treetable").tree[destinationId];this.data("treetable").move(node,destination);return this;},node:function(id){return this.data("treetable").tree[id];},removeNode:function(id){var node=this.data("treetable").tree[id];if(node)this.data("treetable").removeNode(node);else throw new Error("Unknown node '"+id+"'");return this;},reveal:function(id){var node=this.data("treetable").tree[id];if(node)node.reveal();else throw new Error("Unknown node '"+id+"'");return this;},sortBranch:function(node,columnOrFunction){var settings=this.data("treetable").settings,prepValue,sortFun;columnOrFunction=columnOrFunction||settings.column;sortFun=columnOrFunction;if($.isNumeric(columnOrFunction))sortFun=function(a,b){var extractValue,valA,valB;extractValue=function(node){var val=node.row.find("td:eq("+columnOrFunction+")").text();return $.trim(val).toUpperCase();};valA=extractValue(a);valB=extractValue(b);if(valA<valB)return -1;if(valA>valB)return 1;return 0;};this.data("treetable").sortBranch(node,sortFun);return this;},unloadBranch:function(node){this.data("treetable").unloadBranch(node);return this;}};$.fn.treetable=function(method){if(methods[method])return methods[method].apply(this,Array.prototype.slice.call(arguments,1));else if(typeof method==='object'||!method)return methods.init.apply(this,arguments);else return $.error("Method "+method+" does not exist on jQuery.treetable");};window.TreeTable||(window.TreeTable={});window.TreeTable.Node=Node;window.TreeTable.Tree=Tree;})(jQuery);;
/* @license GPL-2.0-or-later https://www.drupal.org/licensing/faq */
(function($,Drupal,drupalSettings){'use strict';Drupal.behaviors.tokenTree={attach:function(context,settings){$(once('token-tree','table.token-tree',context)).treetable({expandable:true});}};Drupal.behaviors.tokenInsert={attach:function(context,settings){$('textarea, input[type="text"]',context).focus(function(){drupalSettings.tokenFocusedField=this;});if(Drupal.CKEditor5Instances)Drupal.CKEditor5Instances.forEach(function(editor){editor.editing.view.document.on('change:isFocused',(event,data,isFocused)=>{if(isFocused)drupalSettings.tokenFocusedCkeditor5=editor;});});once('token-click-insert','.token-click-insert .token-key',context).forEach(function(token){var newThis=$('<a href="javascript:void(0);" title="'+Drupal.t('Insert this token into your form')+'">'+$(token).html()+'</a>').click(function(){var content=this.text;if(drupalSettings.tokenFocusedField&&(drupalSettings.tokenFocusedField.tokenDialogFocus||drupalSettings.tokenFocusedField.tokenHasFocus))insertAtCursor(drupalSettings.tokenFocusedField,content);else if(typeof (tinyMCE)!='undefined'&&tinyMCE.activeEditor)tinyMCE.activeEditor.execCommand('mceInsertContent',false,content);else if(typeof (CKEDITOR)!='undefined'&&CKEDITOR.currentInstance)CKEDITOR.currentInstance.insertHtml(content);else if(typeof (CodeMirror)!='undefined'&&drupalSettings.tokenFocusedField&&$(drupalSettings.tokenFocusedField).parents('.CodeMirror').length){var editor=$(drupalSettings.tokenFocusedField).parents('.CodeMirror')[0].CodeMirror;editor.replaceSelection(content);editor.focus();}else if(Drupal.wysiwyg&&Drupal.wysiwyg.activeId)Drupal.wysiwyg.instances[Drupal.wysiwyg.activeId].insert(content);else if(typeof (CKEDITOR)!='undefined'&&typeof (Drupal.ckeditorActiveId)!='undefined')CKEDITOR.instances[Drupal.ckeditorActiveId].insertHtml(content);else if(drupalSettings.tokenFocusedField)insertAtCursor(drupalSettings.tokenFocusedField,content);else if(drupalSettings.tokenFocusedCkeditor5){const editor=drupalSettings.tokenFocusedCkeditor5;editor.model.change((writer)=>{writer.insertText(content,editor.model.document.selection.getFirstPosition());});}else alert(Drupal.t('First click a text field to insert your tokens into.'));return false;});$(token).html(newThis);});function insertAtCursor(editor,content){var scroll=editor.scrollTop;if(document.selection){editor.focus();var sel=document.selection.createRange();sel.text=content;}else if(editor.selectionStart||editor.selectionStart=='0'){var startPos=editor.selectionStart;var endPos=editor.selectionEnd;editor.value=editor.value.substring(0,startPos)+content+editor.value.substring(endPos,editor.value.length);}else editor.value+=content;editor.scrollTop=scroll;}}};})(jQuery,Drupal,drupalSettings);;
