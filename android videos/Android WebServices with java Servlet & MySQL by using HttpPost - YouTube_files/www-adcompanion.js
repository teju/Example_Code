(function(){var l,n=this;function q(a){a=a.split(".");for(var b=n,c;c=a.shift();)if(null!=b[c])b=b[c];else return null;return b}
function aa(){}
function ba(a){a.getInstance=function(){return a.fa?a.fa:a.fa=new a}}
function ca(a){var b=typeof a;if("object"==b)if(a){if(a instanceof Array)return"array";if(a instanceof Object)return b;var c=Object.prototype.toString.call(a);if("[object Window]"==c)return"object";if("[object Array]"==c||"number"==typeof a.length&&"undefined"!=typeof a.splice&&"undefined"!=typeof a.propertyIsEnumerable&&!a.propertyIsEnumerable("splice"))return"array";if("[object Function]"==c||"undefined"!=typeof a.call&&"undefined"!=typeof a.propertyIsEnumerable&&!a.propertyIsEnumerable("call"))return"function"}else return"null";
else if("function"==b&&"undefined"==typeof a.call)return"object";return b}
function da(a){var b=ca(a);return"array"==b||"object"==b&&"number"==typeof a.length}
function r(a){return"string"==typeof a}
function ea(a){var b=typeof a;return"object"==b&&null!=a||"function"==b}
function fa(a){return a[ga]||(a[ga]=++ha)}
var ga="closure_uid_"+(1E9*Math.random()>>>0),ha=0;function ia(a,b,c){return a.call.apply(a.bind,arguments)}
function ka(a,b,c){if(!a)throw Error();if(2<arguments.length){var d=Array.prototype.slice.call(arguments,2);return function(){var c=Array.prototype.slice.call(arguments);Array.prototype.unshift.apply(c,d);return a.apply(b,c)}}return function(){return a.apply(b,arguments)}}
function t(a,b,c){t=Function.prototype.bind&&-1!=Function.prototype.bind.toString().indexOf("native code")?ia:ka;return t.apply(null,arguments)}
function la(a,b){var c=Array.prototype.slice.call(arguments,1);return function(){var b=c.slice();b.push.apply(b,arguments);return a.apply(this,b)}}
var ma=Date.now||function(){return+new Date};
function u(a,b){var c=a.split("."),d=n;c[0]in d||!d.execScript||d.execScript("var "+c[0]);for(var e;c.length&&(e=c.shift());)c.length||void 0===b?d[e]?d=d[e]:d=d[e]={}:d[e]=b}
function v(a,b){function c(){}
c.prototype=b.prototype;a.B=b.prototype;a.prototype=new c;a.prototype.constructor=a;a.base=function(a,c,f){for(var g=Array(arguments.length-2),h=2;h<arguments.length;h++)g[h-2]=arguments[h];return b.prototype[c].apply(a,g)}}
;var na;var oa=String.prototype.trim?function(a){return a.trim()}:function(a){return a.replace(/^[\s\xa0]+|[\s\xa0]+$/g,"")};
function pa(a,b){return a<b?-1:a>b?1:0}
function qa(a){return String(a).replace(/\-([a-z])/g,function(a,c){return c.toUpperCase()})}
function ra(a){var b=r(void 0)?"undefined".replace(/([-()\[\]{}+?*.$\^|,:#<!\\])/g,"\\$1").replace(/\x08/g,"\\x08"):"\\s";return a.replace(new RegExp("(^"+(b?"|["+b+"]+":"")+")([a-z])","g"),function(a,b,e){return b+e.toUpperCase()})}
;var sa=Array.prototype.indexOf?function(a,b,c){return Array.prototype.indexOf.call(a,b,c)}:function(a,b,c){c=null==c?0:0>c?Math.max(0,a.length+c):c;
if(r(a))return r(b)&&1==b.length?a.indexOf(b,c):-1;for(;c<a.length;c++)if(c in a&&a[c]===b)return c;return-1},w=Array.prototype.forEach?function(a,b,c){Array.prototype.forEach.call(a,b,c)}:function(a,b,c){for(var d=a.length,e=r(a)?a.split(""):a,f=0;f<d;f++)f in e&&b.call(c,e[f],f,a)},ta=Array.prototype.filter?function(a,b,c){return Array.prototype.filter.call(a,b,c)}:function(a,b,c){for(var d=a.length,e=[],f=0,g=r(a)?a.split(""):a,h=0;h<d;h++)if(h in g){var k=g[h];
b.call(c,k,h,a)&&(e[f++]=k)}return e},ua=Array.prototype.some?function(a,b,c){return Array.prototype.some.call(a,b,c)}:function(a,b,c){for(var d=a.length,e=r(a)?a.split(""):a,f=0;f<d;f++)if(f in e&&b.call(c,e[f],f,a))return!0;
return!1};
function va(a,b){var c;a:{c=a.length;for(var d=r(a)?a.split(""):a,e=0;e<c;e++)if(e in d&&b.call(void 0,d[e],e,a)){c=e;break a}c=-1}return 0>c?null:r(a)?a.charAt(c):a[c]}
function wa(a,b){return 0<=sa(a,b)}
function xa(a){return Array.prototype.concat.apply(Array.prototype,arguments)}
function ya(a){var b=a.length;if(0<b){for(var c=Array(b),d=0;d<b;d++)c[d]=a[d];return c}return[]}
function za(a,b){for(var c=1;c<arguments.length;c++){var d=arguments[c];if(da(d)){var e=a.length||0,f=d.length||0;a.length=e+f;for(var g=0;g<f;g++)a[e+g]=d[g]}else a.push(d)}}
function Aa(a,b,c,d){return Array.prototype.splice.apply(a,Ba(arguments,1))}
function Ba(a,b,c){return 2>=arguments.length?Array.prototype.slice.call(a,b):Array.prototype.slice.call(a,b,c)}
;function Ca(a){if(a.classList)return a.classList;a=a.className;return r(a)&&a.match(/\S+/g)||[]}
function x(a,b){return a.classList?a.classList.contains(b):wa(Ca(a),b)}
function y(a,b){a.classList?a.classList.add(b):x(a,b)||(a.className+=0<a.className.length?" "+b:b)}
function Da(a,b){if(a.classList)w(b,function(b){y(a,b)});
else{var c={};w(Ca(a),function(a){c[a]=!0});
w(b,function(a){c[a]=!0});
a.className="";for(var d in c)a.className+=0<a.className.length?" "+d:d}}
function z(a,b){a.classList?a.classList.remove(b):x(a,b)&&(a.className=ta(Ca(a),function(a){return a!=b}).join(" "))}
function A(a,b,c){c?y(a,b):z(a,b)}
function Ea(a,b,c){x(a,b)&&(z(a,b),y(a,c))}
;function Fa(a,b){for(var c in a)b.call(void 0,a[c],c,a)}
function Ga(a){var b=Ha,c;for(c in b)if(a.call(void 0,b[c],c,b))return c}
var Ia="constructor hasOwnProperty isPrototypeOf propertyIsEnumerable toLocaleString toString valueOf".split(" ");function Ja(a,b){for(var c,d,e=1;e<arguments.length;e++){d=arguments[e];for(c in d)a[c]=d[c];for(var f=0;f<Ia.length;f++)c=Ia[f],Object.prototype.hasOwnProperty.call(d,c)&&(a[c]=d[c])}}
;var Ka;a:{var La=n.navigator;if(La){var Ma=La.userAgent;if(Ma){Ka=Ma;break a}}Ka=""}function F(a){return-1!=Ka.indexOf(a)}
;function Na(){this.f="";this.h=Oa}
Na.prototype.ea=!0;Na.prototype.da=function(){return this.f};
var Pa=/^(?:(?:https?|mailto|ftp):|[^&:/?#]*(?:[/?#]|$))/i,Oa={};function Qa(a){var b=new Na;b.f=a;return b}
Qa("about:blank");function Ra(){this.f="";this.h=Sa;this.l=null}
Ra.prototype.ea=!0;Ra.prototype.da=function(){return this.f};
var Sa={};function Ta(a,b){var c=new Ra;c.f=a;c.l=b;return c}
Ta("<!DOCTYPE html>",0);Ta("",0);Ta("<br>",0);function G(a,b){this.x=void 0!==a?a:0;this.y=void 0!==b?b:0}
G.prototype.clone=function(){return new G(this.x,this.y)};
function Ua(a,b){return new G(a.x-b.x,a.y-b.y)}
G.prototype.ceil=function(){this.x=Math.ceil(this.x);this.y=Math.ceil(this.y);return this};
G.prototype.floor=function(){this.x=Math.floor(this.x);this.y=Math.floor(this.y);return this};
G.prototype.round=function(){this.x=Math.round(this.x);this.y=Math.round(this.y);return this};function Va(a,b){this.width=a;this.height=b}
l=Va.prototype;l.clone=function(){return new Va(this.width,this.height)};
l.isEmpty=function(){return!(this.width*this.height)};
l.ceil=function(){this.width=Math.ceil(this.width);this.height=Math.ceil(this.height);return this};
l.floor=function(){this.width=Math.floor(this.width);this.height=Math.floor(this.height);return this};
l.round=function(){this.width=Math.round(this.width);this.height=Math.round(this.height);return this};var Wa=F("Opera")||F("OPR"),H=F("Trident")||F("MSIE"),Xa=F("Edge"),Ya=Xa||H,Za=F("Gecko")&&!(-1!=Ka.toLowerCase().indexOf("webkit")&&!F("Edge"))&&!(F("Trident")||F("MSIE"))&&!F("Edge"),$a=-1!=Ka.toLowerCase().indexOf("webkit")&&!F("Edge"),ab=F("Windows");function bb(){var a=n.document;return a?a.documentMode:void 0}
var cb;a:{var db="",eb=function(){var a=Ka;if(Za)return/rv\:([^\);]+)(\)|;)/.exec(a);if(Xa)return/Edge\/([\d\.]+)/.exec(a);if(H)return/\b(?:MSIE|rv)[: ]([^\);]+)(\)|;)/.exec(a);if($a)return/WebKit\/(\S+)/.exec(a);if(Wa)return/(?:Version)[ \/]?(\S+)/.exec(a)}();
eb&&(db=eb?eb[1]:"");if(H){var fb=bb();if(null!=fb&&fb>parseFloat(db)){cb=String(fb);break a}}cb=db}var gb=cb,ib={};
function jb(a){var b;if(!(b=ib[a])){b=0;for(var c=oa(String(gb)).split("."),d=oa(String(a)).split("."),e=Math.max(c.length,d.length),f=0;0==b&&f<e;f++){var g=c[f]||"",h=d[f]||"",k=RegExp("(\\d*)(\\D*)","g"),m=RegExp("(\\d*)(\\D*)","g");do{var p=k.exec(g)||["","",""],E=m.exec(h)||["","",""];if(0==p[0].length&&0==E[0].length)break;b=pa(0==p[1].length?0:parseInt(p[1],10),0==E[1].length?0:parseInt(E[1],10))||pa(0==p[2].length,0==E[2].length)||pa(p[2],E[2])}while(0==b)}b=ib[a]=0<=b}return b}
var kb=n.document,lb=kb&&H?bb()||("CSS1Compat"==kb.compatMode?parseInt(gb,10):5):void 0;!Za&&!H||H&&9<=Number(lb)||Za&&jb("1.9.1");var nb=H&&!jb("9");function ob(a){return a?new pb(qb(a)):na||(na=new pb)}
function I(a){var b=document;return r(a)?b.getElementById(a):a}
function rb(a){var b=document;return b.querySelectorAll&&b.querySelector?b.querySelectorAll("."+a):sb(a,void 0)}
function tb(a,b){var c=b||document,d=null;c.getElementsByClassName?d=c.getElementsByClassName(a)[0]:c.querySelectorAll&&c.querySelector?d=c.querySelector("."+a):d=sb(a,b)[0];return d||null}
function sb(a,b){var c,d,e,f;c=document;c=b||c;if(c.querySelectorAll&&c.querySelector&&a)return c.querySelectorAll(""+(a?"."+a:""));if(a&&c.getElementsByClassName){var g=c.getElementsByClassName(a);return g}g=c.getElementsByTagName("*");if(a){f={};for(d=e=0;c=g[d];d++){var h=c.className;"function"==typeof h.split&&wa(h.split(/\s+/),a)&&(f[e++]=c)}f.length=e;return f}return g}
function ub(a){Fa({"aria-pressed":"true"},function(b,c){"style"==c?a.style.cssText=b:"class"==c?a.className=b:"for"==c?a.htmlFor=b:vb.hasOwnProperty(c)?a.setAttribute(vb[c],b):0==c.lastIndexOf("aria-",0)||0==c.lastIndexOf("data-",0)?a.setAttribute(c,b):a[c]=b})}
var vb={cellpadding:"cellPadding",cellspacing:"cellSpacing",colspan:"colSpan",frameborder:"frameBorder",height:"height",maxlength:"maxLength",nonce:"nonce",role:"role",rowspan:"rowSpan",type:"type",usemap:"useMap",valign:"vAlign",width:"width"};function wb(a){a=a.document;a=xb(a)?a.documentElement:a.body;return new Va(a.clientWidth,a.clientHeight)}
function yb(a){var b=zb(a);a=Ab(a);return H&&jb("10")&&a.pageYOffset!=b.scrollTop?new G(b.scrollLeft,b.scrollTop):new G(a.pageXOffset||b.scrollLeft,a.pageYOffset||b.scrollTop)}
function zb(a){return a.scrollingElement?a.scrollingElement:!$a&&xb(a)?a.documentElement:a.body||a.documentElement}
function Ab(a){return a.parentWindow||a.defaultView}
function xb(a){return"CSS1Compat"==a.compatMode}
function Bb(a){a&&a.parentNode&&a.parentNode.removeChild(a)}
function Cb(a,b){if(!a||!b)return!1;if(a.contains&&1==b.nodeType)return a==b||a.contains(b);if("undefined"!=typeof a.compareDocumentPosition)return a==b||!!(a.compareDocumentPosition(b)&16);for(;b&&a!=b;)b=b.parentNode;return b==a}
function qb(a){return 9==a.nodeType?a:a.ownerDocument||a.document}
function Db(a,b){if("textContent"in a)a.textContent=b;else if(3==a.nodeType)a.data=b;else if(a.firstChild&&3==a.firstChild.nodeType){for(;a.lastChild!=a.firstChild;)a.removeChild(a.lastChild);a.firstChild.data=b}else{for(var c;c=a.firstChild;)a.removeChild(c);c=qb(a);a.appendChild(c.createTextNode(String(b)))}}
var Eb={SCRIPT:1,STYLE:1,HEAD:1,IFRAME:1,OBJECT:1},Fb={IMG:" ",BR:"\n"};function Gb(a){if(nb&&null!==a&&"innerText"in a)a=a.innerText.replace(/(\r\n|\r|\n)/g,"\n");else{var b=[];Hb(a,b,!0);a=b.join("")}a=a.replace(/ \xAD /g," ").replace(/\xAD/g,"");a=a.replace(/\u200B/g,"");nb||(a=a.replace(/ +/g," "));" "!=a&&(a=a.replace(/^\s*/,""));return a}
function Hb(a,b,c){if(!(a.nodeName in Eb))if(3==a.nodeType)c?b.push(String(a.nodeValue).replace(/(\r\n|\r|\n)/g,"")):b.push(a.nodeValue);else if(a.nodeName in Fb)b.push(Fb[a.nodeName]);else for(a=a.firstChild;a;)Hb(a,b,c),a=a.nextSibling}
function Ib(a,b,c,d){if(!b&&!c)return null;var e=b?b.toUpperCase():null;return Jb(a,function(a){return(!e||a.nodeName==e)&&(!c||r(a.className)&&wa(a.className.split(/\s+/),c))},!0,d)}
function Jb(a,b,c,d){c||(a=a.parentNode);for(c=0;a&&(null==d||c<=d);){if(b(a))return a;a=a.parentNode;c++}return null}
function pb(a){this.f=a||n.document||document}
pb.prototype.createElement=function(a){return this.f.createElement(a)};
pb.prototype.isElement=function(a){return ea(a)&&1==a.nodeType};
pb.prototype.contains=Cb;function Kb(a,b,c){a&&(a.dataset?a.dataset[Lb(b)]=c:a.setAttribute("data-"+b,c))}
function Mb(a,b){return a?a.dataset?a.dataset[Lb(b)]:a.getAttribute("data-"+b):null}
function Nb(a,b){a&&(a.dataset?delete a.dataset[Lb(b)]:a.removeAttribute("data-"+b))}
var Ob={};function Lb(a){return Ob[a]||(Ob[a]=String(a).replace(/\-([a-z])/g,function(a,c){return c.toUpperCase()}))}
;var Pb=$a?"webkit":Za?"moz":H?"ms":Wa?"o":"";function Qb(a){var b=a.__yt_uid_key;b||(b=Rb(),a.__yt_uid_key=b);return b}
var Rb=q("yt.dom.getNextId_");if(!Rb){Rb=function(){return++Sb};
u("yt.dom.getNextId_",Rb);var Sb=0}function Tb(){var a=document,b;ua(["fullscreenElement","fullScreenElement"],function(c){c in a?b=a[c]:(c=Pb+c.charAt(0).toUpperCase()+c.substr(1),b=c in a?a[c]:void 0);return!!b});
return b}
;var Ub=window.yt&&window.yt.config_||window.ytcfg&&window.ytcfg.data_||{};u("yt.config_",Ub);u("yt.tokens_",window.yt&&window.yt.tokens_||{});var Vb=window.yt&&window.yt.msgs_||q("window.ytcfg.msgs")||{};u("yt.msgs_",Vb);function Wb(a){var b=arguments;if(1<b.length){var c=b[0];Ub[c]=b[1]}else for(c in b=b[0],b)Ub[c]=b[c]}
function J(a,b){return a in Ub?Ub[a]:b}
function K(a,b){"function"==ca(a)&&(a=Xb(a));return window.setTimeout(a,b)}
function Xb(a){return a&&window.yterr?function(){try{return a.apply(this,arguments)}catch(b){throw Yb(b),b;}}:a}
function Yb(a){var b=q("yt.logging.errors.log");b?b(a,void 0):(b=J("ERRORS",[]),b.push([a,void 0]),Wb("ERRORS",b))}
function Zb(a){var b={};if(a=a in Vb?Vb[a]:void 0)for(var c in b)a=a.replace(new RegExp("\\$"+c,"gi"),function(){return b[c]});
return a}
;function $b(a){this.type="";this.source=this.data=this.currentTarget=this.relatedTarget=this.target=null;this.charCode=this.keyCode=0;this.shiftKey=this.ctrlKey=this.altKey=!1;this.clientY=this.clientX=0;this.changedTouches=null;if(a=a||window.event){this.event=a;for(var b in a)b in ac||(this[b]=a[b]);(b=a.target||a.srcElement)&&3==b.nodeType&&(b=b.parentNode);this.target=b;if(b=a.relatedTarget)try{b=b.nodeName?b:null}catch(c){b=null}else"mouseover"==this.type?b=a.fromElement:"mouseout"==this.type&&
(b=a.toElement);this.relatedTarget=b;this.clientX=void 0!=a.clientX?a.clientX:a.pageX;this.clientY=void 0!=a.clientY?a.clientY:a.pageY;this.keyCode=a.keyCode?a.keyCode:a.which;this.charCode=a.charCode||("keypress"==this.type?this.keyCode:0);this.altKey=a.altKey;this.ctrlKey=a.ctrlKey;this.shiftKey=a.shiftKey}}
$b.prototype.preventDefault=function(){this.event&&(this.event.returnValue=!1,this.event.preventDefault&&this.event.preventDefault())};
$b.prototype.stopPropagation=function(){this.event&&(this.event.cancelBubble=!0,this.event.stopPropagation&&this.event.stopPropagation())};
$b.prototype.stopImmediatePropagation=function(){this.event&&(this.event.cancelBubble=!0,this.event.stopImmediatePropagation&&this.event.stopImmediatePropagation())};
var ac={stopImmediatePropagation:1,stopPropagation:1,preventMouseEvent:1,preventManipulation:1,preventDefault:1,layerX:1,layerY:1,scale:1,rotation:1,webkitMovementX:1,webkitMovementY:1};var Ha=q("yt.events.listeners_")||{};u("yt.events.listeners_",Ha);var bc=q("yt.events.counter_")||{count:0};u("yt.events.counter_",bc);function cc(a,b,c,d){return Ga(function(e){return e[0]==a&&e[1]==b&&e[2]==c&&e[4]==!!d})}
function L(a,b,c,d){if(!a||!a.addEventListener&&!a.attachEvent)return"";d=!!d;var e=cc(a,b,c,d);if(e)return e;var e=++bc.count+"",f=!("mouseenter"!=b&&"mouseleave"!=b||!a.addEventListener||"onmouseenter"in document),g;g=f?function(d){d=new $b(d);if(!Jb(d.relatedTarget,function(b){return b==a},!0))return d.currentTarget=a,d.type=b,c.call(a,d)}:function(b){b=new $b(b);
b.currentTarget=a;return c.call(a,b)};
g=Xb(g);Ha[e]=[a,b,c,g,d];a.addEventListener?"mouseenter"==b&&f?a.addEventListener("mouseover",g,d):"mouseleave"==b&&f?a.addEventListener("mouseout",g,d):"mousewheel"==b&&"MozBoxSizing"in document.documentElement.style?a.addEventListener("MozMousePixelScroll",g,d):a.addEventListener(b,g,d):a.attachEvent("on"+b,g);return e}
function dc(a,b,c){return ec(a,b,c,function(a){return x(a,"sign-in-link")})}
function ec(a,b,c,d){var e=a||document;return L(e,b,function(a){var b=Jb(a.target,function(a){return a===e||d(a)},!0);
b&&b!==e&&!b.disabled&&(a.currentTarget=b,c.call(b,a))})}
function fc(a){a&&("string"==typeof a&&(a=[a]),w(a,function(a){if(a in Ha){var c=Ha[a],d=c[0],e=c[1],f=c[3],c=c[4];d.removeEventListener?d.removeEventListener(e,f,c):d.detachEvent&&d.detachEvent("on"+e,f);delete Ha[a]}}))}
;function gc(a,b,c,d){this.top=a;this.right=b;this.bottom=c;this.left=d}
l=gc.prototype;l.getHeight=function(){return this.bottom-this.top};
l.clone=function(){return new gc(this.top,this.right,this.bottom,this.left)};
l.contains=function(a){return this&&a?a instanceof gc?a.left>=this.left&&a.right<=this.right&&a.top>=this.top&&a.bottom<=this.bottom:a.x>=this.left&&a.x<=this.right&&a.y>=this.top&&a.y<=this.bottom:!1};
l.ceil=function(){this.top=Math.ceil(this.top);this.right=Math.ceil(this.right);this.bottom=Math.ceil(this.bottom);this.left=Math.ceil(this.left);return this};
l.floor=function(){this.top=Math.floor(this.top);this.right=Math.floor(this.right);this.bottom=Math.floor(this.bottom);this.left=Math.floor(this.left);return this};
l.round=function(){this.top=Math.round(this.top);this.right=Math.round(this.right);this.bottom=Math.round(this.bottom);this.left=Math.round(this.left);return this};function hc(a,b,c,d){this.left=a;this.top=b;this.width=c;this.height=d}
l=hc.prototype;l.clone=function(){return new hc(this.left,this.top,this.width,this.height)};
l.contains=function(a){return a instanceof hc?this.left<=a.left&&this.left+this.width>=a.left+a.width&&this.top<=a.top&&this.top+this.height>=a.top+a.height:a.x>=this.left&&a.x<=this.left+this.width&&a.y>=this.top&&a.y<=this.top+this.height};
l.ceil=function(){this.left=Math.ceil(this.left);this.top=Math.ceil(this.top);this.width=Math.ceil(this.width);this.height=Math.ceil(this.height);return this};
l.floor=function(){this.left=Math.floor(this.left);this.top=Math.floor(this.top);this.width=Math.floor(this.width);this.height=Math.floor(this.height);return this};
l.round=function(){this.left=Math.round(this.left);this.top=Math.round(this.top);this.width=Math.round(this.width);this.height=Math.round(this.height);return this};function ic(a){ic[" "](a);return a}
ic[" "]=aa;function jc(a,b,c){if(r(b))(b=kc(a,b))&&(a.style[b]=c);else for(var d in b){c=a;var e=b[d],f=kc(c,d);f&&(c.style[f]=e)}}
var lc={};function kc(a,b){var c=lc[b];if(!c){var d=qa(b),c=d;void 0===a.style[d]&&(d=($a?"Webkit":Za?"Moz":H?"ms":Wa?"O":null)+ra(d),void 0!==a.style[d]&&(c=d));lc[b]=c}return c}
function M(a,b){var c=qb(a);return c.defaultView&&c.defaultView.getComputedStyle&&(c=c.defaultView.getComputedStyle(a,null))?c[b]||c.getPropertyValue(b)||"":""}
function mc(a,b){return M(a,b)||(a.currentStyle?a.currentStyle[b]:null)||a.style&&a.style[b]}
function nc(a){var b;try{b=a.getBoundingClientRect()}catch(c){return{left:0,top:0,right:0,bottom:0}}H&&a.ownerDocument.body&&(a=a.ownerDocument,b.left-=a.documentElement.clientLeft+a.body.clientLeft,b.top-=a.documentElement.clientTop+a.body.clientTop);return b}
function oc(a){if(H&&!(8<=Number(lb)))return a.offsetParent;var b=qb(a),c=mc(a,"position"),d="fixed"==c||"absolute"==c;for(a=a.parentNode;a&&a!=b;a=a.parentNode)if(11==a.nodeType&&a.host&&(a=a.host),c=mc(a,"position"),d=d&&"static"==c&&a!=b.documentElement&&a!=b.body,!d&&(a.scrollWidth>a.clientWidth||a.scrollHeight>a.clientHeight||"fixed"==c||"absolute"==c||"relative"==c))return a;return null}
function pc(a){for(var b=new gc(0,Infinity,Infinity,0),c=ob(a),d=c.f.body,e=c.f.documentElement,f=zb(c.f);a=oc(a);)if(!(H&&0==a.clientWidth||$a&&0==a.clientHeight&&a==d)&&a!=d&&a!=e&&"visible"!=mc(a,"overflow")){var g=qc(a),h=new G(a.clientLeft,a.clientTop);g.x+=h.x;g.y+=h.y;b.top=Math.max(b.top,g.y);b.right=Math.min(b.right,g.x+a.clientWidth);b.bottom=Math.min(b.bottom,g.y+a.clientHeight);b.left=Math.max(b.left,g.x)}d=f.scrollLeft;f=f.scrollTop;b.left=Math.max(b.left,d);b.top=Math.max(b.top,f);c=
wb(Ab(c.f)||window);b.right=Math.min(b.right,d+c.width);b.bottom=Math.min(b.bottom,f+c.height);return 0<=b.top&&0<=b.left&&b.bottom>b.top&&b.right>b.left?b:null}
function qc(a){var b=qb(a),c=new G(0,0),d;d=b?qb(b):document;d=!H||9<=Number(lb)||xb(ob(d).f)?d.documentElement:d.body;if(a==d)return c;a=nc(a);b=yb(ob(b).f);c.x=a.left+b.x;c.y=a.top+b.y;return c}
function rc(a){a=nc(a);return new G(a.left,a.top)}
function sc(a){"number"==typeof a&&(a=a+"px");return a}
function tc(a){var b=uc;if("none"!=mc(a,"display"))return b(a);var c=a.style,d=c.display,e=c.visibility,f=c.position;c.visibility="hidden";c.position="absolute";c.display="inline";a=b(a);c.display=d;c.position=f;c.visibility=e;return a}
function uc(a){var b=a.offsetWidth,c=a.offsetHeight,d=$a&&!b&&!c;return(void 0===b||d)&&a.getBoundingClientRect?(a=nc(a),new Va(a.right-a.left,a.bottom-a.top)):new Va(b,c)}
function vc(a){var b=qc(a);a=tc(a);return new hc(b.x,b.y,a.width,a.height)}
function wc(a){return"rtl"==mc(a,"direction")}
function xc(a,b){if(/^\d+px?$/.test(b))return parseInt(b,10);var c=a.style.left,d=a.runtimeStyle.left;a.runtimeStyle.left=a.currentStyle.left;a.style.left=b;var e=a.style.pixelLeft;a.style.left=c;a.runtimeStyle.left=d;return e}
function yc(a,b){var c=a.currentStyle?a.currentStyle[b]:null;return c?xc(a,c):0}
var zc={thin:2,medium:4,thick:6};function Ac(a,b){if("none"==(a.currentStyle?a.currentStyle[b+"Style"]:null))return 0;var c=a.currentStyle?a.currentStyle[b+"Width"]:null;return c in zc?zc[c]:xc(a,c)}
;function Bc(a,b,c,d,e,f,g){var h,k;if(h=c.offsetParent){var m="HTML"==h.tagName||"BODY"==h.tagName;m&&"static"==mc(h,"position")||(k=qc(h),m||(m=(m=wc(h))&&Za?-h.scrollLeft:!m||Ya&&jb("8")||"visible"==mc(h,"overflowX")?h.scrollLeft:h.scrollWidth-h.clientWidth-h.scrollLeft,k=Ua(k,new G(m,h.scrollTop))))}h=k||new G;k=vc(a);if(m=pc(a)){var p=new hc(m.left,m.top,m.right-m.left,m.bottom-m.top),m=Math.max(k.left,p.left),E=Math.min(k.left+k.width,p.left+p.width);if(m<=E){var C=Math.max(k.top,p.top),p=Math.min(k.top+
k.height,p.top+p.height);C<=p&&(k.left=m,k.top=C,k.width=E-m,k.height=p-C)}}m=ob(a);C=ob(c);if(m.f!=C.f){var E=m.f.body,D;var C=Ab(C.f),p=new G(0,0),B;B=(B=qb(E))?Ab(B):window;b:{try{ic(B.parent);D=!0;break b}catch(Ff){}D=!1}if(D){D=E;do{var ed=B==C?qc(D):rc(D);p.x+=ed.x;p.y+=ed.y}while(B&&B!=C&&B!=B.parent&&(D=B.frameElement)&&(B=B.parent))}D=Ua(p,qc(E));!H||9<=Number(lb)||xb(m.f)||(D=Ua(D,yb(m.f)));k.left+=D.x;k.top+=D.y}a=Cc(a,b);b=k.left;a&4?b+=k.width:a&2&&(b+=k.width/2);b=new G(b,k.top+(a&1?
k.height:0));b=Ua(b,h);e&&(b.x+=(a&4?-1:1)*e.x,b.y+=(a&1?-1:1)*e.y);var V;g&&(V=pc(c))&&(V.top-=h.y,V.right-=h.x,V.bottom-=h.y,V.left-=h.x);return Dc(b,c,d,f,V,g,void 0)}
function Dc(a,b,c,d,e,f,g){a=a.clone();var h=Cc(b,c);c=tc(b);g=g?g.clone():c.clone();a=a.clone();g=g.clone();var k=0;if(d||0!=h)h&4?a.x-=g.width+(d?d.right:0):h&2?a.x-=g.width/2:d&&(a.x+=d.left),h&1?a.y-=g.height+(d?d.bottom:0):d&&(a.y+=d.top);if(f){if(e){d=a;h=g;k=0;65==(f&65)&&(d.x<e.left||d.x>=e.right)&&(f&=-2);132==(f&132)&&(d.y<e.top||d.y>=e.bottom)&&(f&=-5);d.x<e.left&&f&1&&(d.x=e.left,k|=1);if(f&16){var m=d.x;d.x<e.left&&(d.x=e.left,k|=4);d.x+h.width>e.right&&(h.width=Math.min(e.right-d.x,
m+h.width-e.left),h.width=Math.max(h.width,0),k|=4)}d.x+h.width>e.right&&f&1&&(d.x=Math.max(e.right-h.width,e.left),k|=1);f&2&&(k=k|(d.x<e.left?16:0)|(d.x+h.width>e.right?32:0));d.y<e.top&&f&4&&(d.y=e.top,k|=2);f&32&&(m=d.y,d.y<e.top&&(d.y=e.top,k|=8),d.y+h.height>e.bottom&&(h.height=Math.min(e.bottom-d.y,m+h.height-e.top),h.height=Math.max(h.height,0),k|=8));d.y+h.height>e.bottom&&f&4&&(d.y=Math.max(e.bottom-h.height,e.top),k|=2);f&8&&(k=k|(d.y<e.top?64:0)|(d.y+h.height>e.bottom?128:0));e=k}else e=
256;k=e}f=new hc(0,0,0,0);f.left=a.x;f.top=a.y;f.width=g.width;f.height=g.height;e=k;if(e&496)return e;a=new G(f.left,f.top);a instanceof G?(g=a.x,a=a.y):(g=a,a=void 0);b.style.left=sc(g);b.style.top=sc(a);g=new Va(f.width,f.height);c==g||c&&g&&c.width==g.width&&c.height==g.height||(c=g,g=qb(b),a=xb(ob(g).f),!H||jb("10")||a&&jb("8")?(b=b.style,Za?b.MozBoxSizing="border-box":$a?b.WebkitBoxSizing="border-box":b.boxSizing="border-box",b.width=Math.max(c.width,0)+"px",b.height=Math.max(c.height,0)+"px"):
(g=b.style,a?(H?(a=yc(b,"paddingLeft"),f=yc(b,"paddingRight"),d=yc(b,"paddingTop"),h=yc(b,"paddingBottom"),a=new gc(d,f,h,a)):(a=M(b,"paddingLeft"),f=M(b,"paddingRight"),d=M(b,"paddingTop"),h=M(b,"paddingBottom"),a=new gc(parseFloat(d),parseFloat(f),parseFloat(h),parseFloat(a))),!H||9<=Number(lb)?(f=M(b,"borderLeftWidth"),d=M(b,"borderRightWidth"),h=M(b,"borderTopWidth"),b=M(b,"borderBottomWidth"),b=new gc(parseFloat(h),parseFloat(d),parseFloat(b),parseFloat(f))):(f=Ac(b,"borderLeft"),d=Ac(b,"borderRight"),
h=Ac(b,"borderTop"),b=Ac(b,"borderBottom"),b=new gc(h,d,b,f)),g.pixelWidth=c.width-b.left-a.left-a.right-b.right,g.pixelHeight=c.height-b.top-a.top-a.bottom-b.bottom):(g.pixelWidth=c.width,g.pixelHeight=c.height)));return e}
function Cc(a,b){return(b&8&&wc(a)?b^4:b)&-9}
;function Ec(a){n.setTimeout(function(){throw a;},0)}
var Fc;
function Gc(){var a=n.MessageChannel;"undefined"===typeof a&&"undefined"!==typeof window&&window.postMessage&&window.addEventListener&&!F("Presto")&&(a=function(){var a=document.createElement("IFRAME");a.style.display="none";a.src="";document.documentElement.appendChild(a);var b=a.contentWindow,a=b.document;a.open();a.write("");a.close();var c="callImmediate"+Math.random(),d="file:"==b.location.protocol?"*":b.location.protocol+"//"+b.location.host,a=t(function(a){if(("*"==d||a.origin==d)&&a.data==
c)this.port1.onmessage()},this);
b.addEventListener("message",a,!1);this.port1={};this.port2={postMessage:function(){b.postMessage(c,d)}}});
if("undefined"!==typeof a&&!F("Trident")&&!F("MSIE")){var b=new a,c={},d=c;b.port1.onmessage=function(){if(void 0!==c.next){c=c.next;var a=c.Z;c.Z=null;a()}};
return function(a){d.next={Z:a};d=d.next;b.port2.postMessage(0)}}return"undefined"!==typeof document&&"onreadystatechange"in document.createElement("SCRIPT")?function(a){var b=document.createElement("SCRIPT");
b.onreadystatechange=function(){b.onreadystatechange=null;b.parentNode.removeChild(b);b=null;a();a=null};
document.documentElement.appendChild(b)}:function(a){n.setTimeout(a,0)}}
;function Hc(a,b,c){this.m=c;this.l=a;this.o=b;this.h=0;this.f=null}
Hc.prototype.get=function(){var a;0<this.h?(this.h--,a=this.f,this.f=a.next,a.next=null):a=this.l();return a};function Ic(){this.h=this.f=null}
var Kc=new Hc(function(){return new Jc},function(a){a.reset()},100);
Ic.prototype.remove=function(){var a=null;this.f&&(a=this.f,this.f=this.f.next,this.f||(this.h=null),a.next=null);return a};
function Jc(){this.next=this.h=this.f=null}
Jc.prototype.reset=function(){this.next=this.h=this.f=null};function Lc(a){Mc||Nc();Oc||(Mc(),Oc=!0);var b=Pc,c=Kc.get();c.f=a;c.h=void 0;c.next=null;b.h?b.h.next=c:b.f=c;b.h=c}
var Mc;function Nc(){if(n.Promise&&n.Promise.resolve){var a=n.Promise.resolve(void 0);Mc=function(){a.then(Qc)}}else Mc=function(){var a=Qc;
"function"!=ca(n.setImmediate)||n.Window&&n.Window.prototype&&!F("Edge")&&n.Window.prototype.setImmediate==n.setImmediate?(Fc||(Fc=Gc()),Fc(a)):n.setImmediate(a)}}
var Oc=!1,Pc=new Ic;function Qc(){for(var a=null;a=Pc.remove();){try{a.f.call(a.h)}catch(c){Ec(c)}var b=Kc;b.o(a);b.h<b.m&&(b.h++,a.next=b.f,b.f=a)}Oc=!1}
;function Rc(){this.h=this.h;this.l=this.l}
Rc.prototype.h=!1;Rc.prototype.isDisposed=function(){return this.h};
Rc.prototype.dispose=function(){this.h||(this.h=!0,this.R())};
Rc.prototype.R=function(){if(this.l)for(;this.l.length;)this.l.shift()()};function N(a){Rc.call(this);this.C=1;this.m=[];this.o=0;this.f=[];this.v={};this.ma=!!a}
v(N,Rc);l=N.prototype;l.subscribe=function(a,b,c){var d=this.v[a];d||(d=this.v[a]=[]);var e=this.C;this.f[e]=a;this.f[e+1]=b;this.f[e+2]=c;this.C=e+3;d.push(e);return e};
l.unsubscribe=function(a,b,c){if(a=this.v[a]){var d=this.f;if(a=va(a,function(a){return d[a+1]==b&&d[a+2]==c}))return this.L(a)}return!1};
l.L=function(a){var b=this.f[a];if(b){var c=this.v[b];if(0!=this.o)this.m.push(a),this.f[a+1]=aa;else{if(c){var d=sa(c,a);0<=d&&Array.prototype.splice.call(c,d,1)}delete this.f[a];delete this.f[a+1];delete this.f[a+2]}}return!!b};
l.N=function(a,b){var c=this.v[a];if(c){for(var d=Array(arguments.length-1),e=1,f=arguments.length;e<f;e++)d[e-1]=arguments[e];if(this.ma)for(e=0;e<c.length;e++){var g=c[e];Sc(this.f[g+1],this.f[g+2],d)}else{this.o++;try{for(e=0,f=c.length;e<f;e++)g=c[e],this.f[g+1].apply(this.f[g+2],d)}finally{if(this.o--,0<this.m.length&&0==this.o)for(;c=this.m.pop();)this.L(c)}}return 0!=e}return!1};
function Sc(a,b,c){Lc(function(){a.apply(b,c)})}
l.clear=function(a){if(a){var b=this.v[a];b&&(w(b,this.L,this),delete this.v[a])}else this.f.length=0,this.v={}};
function Tc(a,b){if(b){var c=a.v[b];return c?c.length:0}var c=0,d;for(d in a.v)c+=Tc(a,d);return c}
l.R=function(){N.B.R.call(this);this.clear();this.m.length=0};var Uc=q("yt.pubsub.instance_")||new N;N.prototype.subscribe=N.prototype.subscribe;N.prototype.unsubscribeByKey=N.prototype.L;N.prototype.publish=N.prototype.N;N.prototype.clear=N.prototype.clear;u("yt.pubsub.instance_",Uc);var Vc=q("yt.pubsub.subscribedKeys_")||{};u("yt.pubsub.subscribedKeys_",Vc);var Wc=q("yt.pubsub.topicToKeys_")||{};u("yt.pubsub.topicToKeys_",Wc);var Xc=q("yt.pubsub.isSynchronous_")||{};u("yt.pubsub.isSynchronous_",Xc);var Yc=q("yt.pubsub.skipSubId_")||null;
u("yt.pubsub.skipSubId_",Yc);function Zc(a,b,c){var d=$c();if(d){var e=d.subscribe(a,function(){if(!Yc||Yc!=e){var d=arguments,g=function(){Vc[e]&&b.apply(c||window,d)};
try{Xc[a]?g():K(g,0)}catch(h){Yb(h)}}},c);
Vc[e]=!0;Wc[a]||(Wc[a]=[]);Wc[a].push(e);return e}return 0}
function ad(a){var b=$c();b&&("number"==typeof a?a=[a]:"string"==typeof a&&(a=[parseInt(a,10)]),w(a,function(a){b.unsubscribeByKey(a);delete Vc[a]}))}
function bd(a,b){var c=$c();return c?c.publish.apply(c,arguments):!1}
function $c(){return q("yt.pubsub.instance_")}
;function cd(a,b){(a=I(a))&&a.style&&(a.style.display=b?"":"none",A(a,"hid",!b))}
function dd(a){w(arguments,function(a){!da(a)||a instanceof Element?cd(a,!0):w(a,function(a){dd(a)})})}
function fd(a){w(arguments,function(a){!da(a)||a instanceof Element?cd(a,!1):w(a,function(a){fd(a)})})}
;function gd(a){var b=void 0;isNaN(b)&&(b=void 0);var c=q("yt.scheduler.instance.addJob");c?c(a,0,b):void 0===b?a():K(a,b||0)}
;function O(a,b){this.version=a;this.args=b}
function hd(a){if(!a.na){var b={};a.call(b);a.na=b.version}return a.na}
function id(a,b){function c(){a.apply(this,b.args)}
if(!b.args||!b.version)throw Error("yt.pubsub2.Data.deserialize(): serializedData is incomplete.");var d;try{d=hd(a)}catch(e){}if(!d||b.version!=d)throw Error("yt.pubsub2.Data.deserialize(): serializedData version is incompatible.");c.prototype=a.prototype;try{return new c}catch(e){throw e.message="yt.pubsub2.Data.deserialize(): "+e.message,e;}}
function P(a,b){this.h=a;this.f=b}
P.prototype.toString=function(){return this.h};var jd=q("yt.pubsub2.instance_")||new N;N.prototype.subscribe=N.prototype.subscribe;N.prototype.unsubscribeByKey=N.prototype.L;N.prototype.publish=N.prototype.N;N.prototype.clear=N.prototype.clear;u("yt.pubsub2.instance_",jd);var kd=q("yt.pubsub2.subscribedKeys_")||{};u("yt.pubsub2.subscribedKeys_",kd);var ld=q("yt.pubsub2.topicToKeys_")||{};u("yt.pubsub2.topicToKeys_",ld);var md=q("yt.pubsub2.isAsync_")||{};u("yt.pubsub2.isAsync_",md);u("yt.pubsub2.skipSubKey_",null);
function Q(a,b){var c=nd();c&&c.publish.call(c,a.toString(),a,b)}
function R(a,b,c){var d=nd();if(!d)return 0;var e=d.subscribe(a.toString(),function(d,g){if(!window.yt.pubsub2.skipSubKey_||window.yt.pubsub2.skipSubKey_!=e){var h=function(){if(kd[e])try{if(g&&a instanceof P&&a!=d)try{g=id(a.f,g)}catch(k){throw k.message="yt.pubsub2 cross-binary conversion error for "+a.toString()+": "+k.message,k;}b.call(c||window,g)}catch(k){Yb(k)}};
md[a.toString()]?q("yt.scheduler.instance")?gd(h):K(h,0):h()}});
kd[e]=!0;ld[a.toString()]||(ld[a.toString()]=[]);ld[a.toString()].push(e);return e}
function od(a){var b=nd();b&&("number"==typeof a&&(a=[a]),w(a,function(a){b.unsubscribeByKey(a);delete kd[a]}))}
function nd(){return q("yt.pubsub2.instance_")}
;var S={},pd="ontouchstart"in document;function qd(a,b,c){b in S||(S[b]=new N);S[b].subscribe(a,c)}
function rd(a,b,c){var d;switch(a){case "mouseover":case "mouseout":d=3;break;case "mouseenter":case "mouseleave":d=9}return Jb(c,function(a){return x(a,b)},!0,d)}
function T(a){var b="mouseover"==a.type&&"mouseenter"in S||"mouseout"==a.type&&"mouseleave"in S,c=a.type in S||b;if("HTML"!=a.target.tagName&&c){if(b){var b="mouseover"==a.type?"mouseenter":"mouseleave",c=S[b],d;for(d in c.v){var e=rd(b,d,a.target);e&&!Jb(a.relatedTarget,function(a){return a==e},!0)&&c.N(d,e,b,a)}}if(b=S[a.type])for(d in b.v)(e=rd(a.type,d,a.target))&&b.N(d,e,a.type,a)}}
L(document,"blur",T,!0);L(document,"change",T,!0);L(document,"click",T);L(document,"focus",T,!0);L(document,"mouseover",T);L(document,"mouseout",T);L(document,"mousedown",T);L(document,"keydown",T);L(document,"keyup",T);L(document,"keypress",T);L(document,"cut",T);L(document,"paste",T);pd&&(L(document,"touchstart",T),L(document,"touchend",T),L(document,"touchcancel",T));function sd(a){this.l=a;this.o={};this.K=[];this.C=[]}
l=sd.prototype;l.F=function(a){return Ib(a,null,U(this),void 0)};
function U(a,b){return"yt-uix"+(a.l?"-"+a.l:"")+(b?"-"+b:"")}
l.unregister=function(){ad(this.K);this.K.length=0;od(this.C);this.C.length=0};
l.init=aa;l.dispose=aa;function td(a,b,c){a.C.push(R(b,c,a))}
function W(a,b,c,d){d=U(a,d);var e=t(c,a);qd(d,b,e);a.o[c]=e}
function X(a,b,c,d){if(b in S){var e=S[b];e.unsubscribe(U(a,d),a.o[c]);0>=Tc(e)&&(e.dispose(),delete S[b])}delete a.o[c]}
l.ya=function(a,b,c){var d=this.j(a,b);if(d&&(d=q(d))){var e=Ba(arguments,2);Aa(e,0,0,a);d.apply(null,e)}};
l.j=function(a,b){return Mb(a,b)};
function ud(a,b){Kb(a,"tooltip-text",b)}
;function vd(a){sd.call(this,a);this.m=null}
v(vd,sd);l=vd.prototype;l.F=function(a){var b=sd.prototype.F.call(this,a);return b?b:a};
l.register=function(){this.K.push(Zc("yt-uix-kbd-nav-move-out-done",this.G,this))};
l.dispose=function(){vd.B.dispose.call(this);this.m&&this.G(this.m)};
l.j=function(a,b){var c=vd.B.j.call(this,a,b);return c?c:(c=vd.B.j.call(this,a,"card-config"))&&(c=q(c))&&c[b]?c[b]:null};
l.U=function(a){var b=this.F(a);if(b){y(b,U(this,"active"));var c=wd(this,a,b);if(c){c.cardTargetNode=a;c.cardRootNode=b;xd(this,a,c);var d=U(this,"card-visible"),e=this.j(a,"card-delegate-show")&&this.j(b,"card-action");this.ya(b,"card-action",a);this.m=a;fd(c);K(t(function(){e||(dd(c),bd("yt-uix-card-show",b,a,c));yd(c);y(c,d);bd("yt-uix-kbd-nav-move-in-to",c)},this),10)}}};
function wd(a,b,c){var d=c||b,e=U(a,"card");c=zd(a,d);var f=I(U(a,"card")+Qb(d));if(f)return a=tb(U(a,"card-body"),f),Cb(a,c)||(Bb(c),a.appendChild(c)),f;f=document.createElement("div");f.id=U(a,"card")+Qb(d);f.className=e;(d=a.j(d,"card-class"))&&Da(f,d.split(/\s+/));d=document.createElement("div");d.className=U(a,"card-border");b=a.j(b,"orientation")||"horizontal";e=document.createElement("div");e.className="yt-uix-card-border-arrow yt-uix-card-border-arrow-"+b;var g=document.createElement("div");
g.className=U(a,"card-body");a=document.createElement("div");a.className="yt-uix-card-body-arrow yt-uix-card-body-arrow-"+b;Bb(c);g.appendChild(c);d.appendChild(a);d.appendChild(g);f.appendChild(e);f.appendChild(d);document.body.appendChild(f);return f}
function xd(a,b,c){var d=a.j(b,"orientation")||"horizontal",e=a.j(b,"position"),f=!!a.j(b,"force-position"),g=a.j(b,"position-fixed"),d="horizontal"==d,h="bottomright"==e||"bottomleft"==e,k="topright"==e||"bottomright"==e,m,p;k&&h?(p=13,m=8):k&&!h?(p=12,m=9):!k&&h?(p=9,m=12):(p=8,m=13);var E=wc(document.body),e=wc(b);E!=e&&(p^=4);var C;d?(e=b.offsetHeight/2-12,C=new G(-12,b.offsetHeight+6)):(e=b.offsetWidth/2-6,C=new G(b.offsetWidth+6,-12));var D=tc(c),e=Math.min(e,(d?D.height:D.width)-24-6);6>e&&
(e=6,d?C.y+=12-b.offsetHeight/2:C.x+=12-b.offsetWidth/2);var B=null;f||(B=10);D=U(a,"card-flip");a=U(a,"card-reverse");A(c,D,k);A(c,a,h);B=Bc(b,p,c,m,C,null,B);!f&&B&&(B&48&&(k=!k,p^=4,m^=4),B&192&&(h=!h,p^=1,m^=1),A(c,D,k),A(c,a,h),Bc(b,p,c,m,C));g&&(b=parseInt(c.style.top,10),f=yb(document).y,jc(c,"position","fixed"),jc(c,"top",b-f+"px"));E&&(c.style.right="",b=vc(c),b.left=b.left||parseInt(c.style.left,10),f=wb(window),c.style.left="",c.style.right=f.width-b.left-b.width+"px");b=tb("yt-uix-card-body-arrow",
c);f=tb("yt-uix-card-border-arrow",c);d=d?h?"top":"bottom":!E&&k||E&&!k?"left":"right";b.setAttribute("style","");f.setAttribute("style","");b.style[d]=e+"px";f.style[d]=e+"px";h=tb("yt-uix-card-arrow",c);k=tb("yt-uix-card-arrow-background",c);h&&k&&(c="right"==d?tc(c).width-e-13:e+11,e=c/Math.sqrt(2),h.style.left=c+"px",h.style.marginLeft="1px",k.style.marginLeft=-e+"px",k.style.marginTop=e+"px")}
l.G=function(a){if(a=this.F(a)){var b=I(U(this,"card")+Qb(a));b&&(z(a,U(this,"active")),z(b,U(this,"card-visible")),fd(b),this.m=null,b.cardTargetNode=null,b.cardRootNode=null,b.cardMask&&(Bb(b.cardMask),b.cardMask=null))}};
l.Ga=function(a,b){var c=this.F(a);if(c){if(b){var d=zd(this,c);if(!d)return;if(b instanceof Ra){var e;b instanceof Ra&&b.constructor===Ra&&b.h===Sa?e=b.f:(ca(b),e="type_error:SafeHtml");d.innerHTML=e}else Db(d,b)}x(c,U(this,"active"))&&(c=wd(this,a,c),xd(this,a,c),dd(c),yd(c))}};
function zd(a,b){var c=b.cardContentNode;if(!c){var d=U(a,"content"),e=U(a,"card-content");(c=(c=a.j(b,"card-id"))?I(c):tb(d,b))||(c=document.createElement("div"));var f=c;z(f,d);y(f,e);b.cardContentNode=c}return c}
function yd(a){var b=a.cardMask;b||(b=document.createElement("iframe"),b.src='javascript:""',Da(b,["yt-uix-card-iframe-mask"]),a.cardMask=b);b.style.position=a.style.position;b.style.top=a.style.top;b.style.left=a.offsetLeft+"px";b.style.height=a.clientHeight+"px";b.style.width=a.clientWidth+"px";document.body.appendChild(b)}
;function Ad(){vd.call(this,"clickcard");this.f={};this.h={}}
v(Ad,vd);ba(Ad);l=Ad.prototype;l.register=function(){Ad.B.register.call(this);W(this,"click",this.aa,"target");W(this,"click",this.$,"close")};
l.unregister=function(){Ad.B.unregister.call(this);X(this,"click",this.aa,"target");X(this,"click",this.$,"close");for(var a in this.f)fc(this.f[a]);this.f={};for(a in this.h)fc(this.h[a]);this.h={}};
l.aa=function(a,b,c){c.preventDefault();b=Ib(c.target,"button");b&&b.disabled||(a=(b=this.j(a,"card-target"))?I(b):a,b=this.F(a),this.j(b,"disabled")||(x(b,U(this,"active"))?(this.G(a),z(b,U(this,"active"))):(this.U(a),y(b,U(this,"active")))))};
l.U=function(a){Ad.B.U.call(this,a);var b=this.F(a);if(!Mb(b,"click-outside-persists")){var c=fa(a);if(this.f[c])return;var b=L(document,"click",t(this.ba,this,a)),d=L(window,"blur",t(this.ba,this,a));this.f[c]=[b,d]}a=L(window,"resize",t(this.Ga,this,a,void 0));this.h[c]=a};
l.G=function(a){Ad.B.G.call(this,a);a=fa(a);var b=this.f[a];b&&(fc(b),this.f[a]=null);if(b=this.h[a])fc(b),this.h[a]=null};
l.ba=function(a,b){Ib(b.target,null,"yt-uix"+(this.l?"-"+this.l:"")+"-card",void 0)||this.G(a)};
l.$=function(a){(a=Ib(a,null,U(this,"card"),void 0))&&(a=a.cardTargetNode)&&this.G(a)};var Y=/^(?:([^:/?#.]+):)?(?:\/\/(?:([^/?#]*)@)?([^/#?]*?)(?::([0-9]+))?(?=[/#?]|$))?([^?#]+)?(?:\?([^#]*))?(?:#(.*))?$/;function Bd(a){return a?decodeURI(a):a}
function Cd(a){if(a[1]){var b=a[0],c=b.indexOf("#");0<=c&&(a.push(b.substr(c)),a[0]=b=b.substr(0,c));c=b.indexOf("?");0>c?a[1]="?":c==b.length-1&&(a[1]=void 0)}return a.join("")}
function Dd(a,b,c){if("array"==ca(b))for(var d=0;d<b.length;d++)Dd(a,String(b[d]),c);else null!=b&&c.push("&",a,""===b?"":"=",encodeURIComponent(String(b)))}
function Ed(a,b,c){for(c=c||0;c<b.length;c+=2)Dd(b[c],b[c+1],a);return a}
function Fd(a,b){for(var c in b)Dd(c,b[c],a);return a}
function Gd(a){a=Fd([],a);a[0]="";return a.join("")}
function Hd(a,b){return Cd(2==arguments.length?Ed([a],arguments[1],0):Ed([a],arguments,1))}
;var Id={},Jd=0,Kd=q("yt.net.ping.workerUrl_")||null;u("yt.net.ping.workerUrl_",Kd);function Ld(a){var b=new Image,c=""+Jd++;Id[c]=b;b.onload=b.onerror=function(){delete Id[c]};
b.src=a}
;function Md(a){"?"==a.charAt(0)&&(a=a.substr(1));a=a.split("&");for(var b={},c=0,d=a.length;c<d;c++){var e=a[c].split("=");if(1==e.length&&e[0]||2==e.length){var f=decodeURIComponent((e[0]||"").replace(/\+/g," ")),e=decodeURIComponent((e[1]||"").replace(/\+/g," "));f in b?"array"==ca(b[f])?za(b[f],e):b[f]=[b[f],e]:b[f]=e}}return b}
function Nd(a,b){var c=a.split("#",2);a=c[0];var c=1<c.length?"#"+c[1]:"",d=a.split("?",2);a=d[0];var d=Md(d[1]||""),e;for(e in b)d[e]=b[e];return Cd(Fd([a],d))+c}
;function Od(a){O.call(this,1,arguments);this.f=a}
v(Od,O);function Z(a){O.call(this,1,arguments);this.f=a}
v(Z,O);function Pd(a,b){O.call(this,1,arguments);this.f=a;this.h=b}
v(Pd,O);function Qd(a,b,c,d,e){O.call(this,2,arguments);this.h=a;this.f=b;this.m=c||null;this.l=d||null;this.o=e||null}
v(Qd,O);function Rd(a,b,c){O.call(this,1,arguments);this.f=a;this.h=b}
v(Rd,O);function Sd(a,b,c,d,e,f,g){O.call(this,1,arguments);this.h=a;this.C=b;this.f=c;this.ma=d||null;this.m=e||null;this.l=f||null;this.o=g||null}
v(Sd,O);
var Td=new P("subscription-batch-subscribe",Od),Ud=new P("subscription-batch-unsubscribe",Od),Vd=new P("subscription-pref-email",Pd),Wd=new P("subscription-subscribe",Qd),Xd=new P("subscription-subscribe-loading",Z),Yd=new P("subscription-subscribe-loaded",Z),Zd=new P("subscription-subscribe-success",Rd),$d=new P("subscription-subscribe-external",Qd),ae=new P("subscription-unsubscribe",Sd),be=new P("subscription-unsubscirbe-loading",Z),ce=new P("subscription-unsubscribe-loaded",Z),de=new P("subscription-unsubscribe-success",Z),
ee=new P("subscription-external-unsubscribe",Sd),fe=new P("subscription-enable-ypc",Z),ge=new P("subscription-disable-ypc",Z);function he(a){var b=document.location.protocol+"//"+document.domain+"/post_login",b=Hd(b,"mode","subscribe"),b=Hd("/signin?context=popup","next",b),b=Hd(b,"feature","sub_button");if(b=window.open(b,"loginPopup","width=375,height=440,resizable=yes,scrollbars=yes",!0)){var c=Zc("LOGGED_IN",function(b){ad(J("LOGGED_IN_PUBSUB_KEY",void 0));Wb("LOGGED_IN",!0);a(b)});
Wb("LOGGED_IN_PUBSUB_KEY",c);b.moveTo((screen.width-375)/2,(screen.height-440)/2)}}
u("yt.pubsub.publish",bd);function ie(a){return eval("("+a+")")}
;var je=null;"undefined"!=typeof XMLHttpRequest?je=function(){return new XMLHttpRequest}:"undefined"!=typeof ActiveXObject&&(je=function(){return new ActiveXObject("Microsoft.XMLHTTP")});function ke(a,b,c,d,e,f,g){function h(){4==(k&&"readyState"in k?k.readyState:0)&&b&&Xb(b)(k)}
var k=je&&je();if(!("open"in k))return null;"onloadend"in k?k.addEventListener("loadend",h,!1):k.onreadystatechange=h;c=(c||"GET").toUpperCase();d=d||"";k.open(c,a,!0);f&&(k.responseType=f);g&&(k.withCredentials=!0);f="POST"==c;if(e=le(a,e))for(var m in e)k.setRequestHeader(m,e[m]),"content-type"==m.toLowerCase()&&(f=!1);f&&k.setRequestHeader("Content-Type","application/x-www-form-urlencoded");k.send(d);return k}
function le(a,b){b=b||{};var c;c||(c=window.location.href);var d=a.match(Y)[1]||null,e=Bd(a.match(Y)[3]||null);d&&e?(d=c,c=a.match(Y),d=d.match(Y),c=c[3]==d[3]&&c[1]==d[1]&&c[4]==d[4]):c=e?Bd(c.match(Y)[3]||null)==e&&(Number(c.match(Y)[4]||null)||null)==(Number(a.match(Y)[4]||null)||null):!0;for(var f in me){if((e=d=J(me[f]))&&!(e=c)){var e=f,g=J("CORS_HEADER_WHITELIST")||{},h=Bd(a.match(Y)[3]||null);e=h?(g=g[h])?wa(g,e):!1:!0}e&&(b[f]=d)}return b}
function ne(a,b){var c=J("XSRF_FIELD_NAME",void 0),d;b.headers&&(d=b.headers["Content-Type"]);return!b.Qa&&(!Bd(a.match(Y)[3]||null)||b.withCredentials||Bd(a.match(Y)[3]||null)==document.location.hostname)&&"POST"==b.method&&(!d||"application/x-www-form-urlencoded"==d)&&!(b.D&&b.D[c])}
function oe(a,b){var c=b.format||"JSON";b.Ta&&(a=document.location.protocol+"//"+document.location.hostname+(document.location.port?":"+document.location.port:"")+a);var d=J("XSRF_FIELD_NAME",void 0),e=J("XSRF_TOKEN",void 0),f=b.S;f&&(f[d]&&delete f[d],a=Nd(a,f||{}));var g=b.Va||"",f=b.D;ne(a,b)&&(f||(f={}),f[d]=e);f&&r(g)&&(d=Md(g),Ja(d,f),g=Gd(d));var h=!1,k,m=ke(a,function(a){if(!h){h=!0;k&&window.clearTimeout(k);var d;a:switch(a&&"status"in a?a.status:-1){case 200:case 201:case 202:case 203:case 204:case 205:case 206:case 304:d=
!0;break a;default:d=!1}var e=null;if(d||400<=a.status&&500>a.status)e=pe(c,a,b.Pa);if(d)a:{switch(c){case "XML":d=0==parseInt(e&&e.return_code,10);break a;case "RAW":d=!0;break a}d=!!e}var e=e||{},f=b.context||n;d?b.A&&b.A.call(f,a,e):b.onError&&b.onError.call(f,a,e);b.J&&b.J.call(f,a,e)}},b.method,g,b.headers,b.responseType,b.withCredentials);
b.Ea&&0<b.timeout&&(k=K(function(){h||(h=!0,m.abort(),window.clearTimeout(k),b.Ea.call(b.context||n,m))},b.timeout))}
function pe(a,b,c){var d=null;switch(a){case "JSON":a=b.responseText;b=b.getResponseHeader("Content-Type")||"";a&&0<=b.indexOf("json")&&(d=ie(a));break;case "XML":if(b=(b=b.responseXML)?qe(b):null)d={},w(b.getElementsByTagName("*"),function(a){d[a.tagName]=re(a)})}c&&se(d);
return d}
function se(a){if(ea(a))for(var b in a){var c;(c="html_content"==b)||(c=b.length-5,c=0<=c&&b.indexOf("_html",c)==c);if(c){c=b;var d;d=Ta(a[b],null);a[c]=d}else se(a[b])}}
function qe(a){return a?(a=("responseXML"in a?a.responseXML:a).getElementsByTagName("root"))&&0<a.length?a[0]:null:null}
function re(a){var b="";w(a.childNodes,function(a){b+=a.nodeValue});
return b}
var me={"X-YouTube-Client-Name":"INNERTUBE_CONTEXT_CLIENT_NAME","X-YouTube-Client-Version":"INNERTUBE_CONTEXT_CLIENT_VERSION","X-YouTube-Page-CL":"PAGE_CL","X-YouTube-Page-Label":"PAGE_BUILD_LABEL","X-YouTube-Variants-Checksum":"VARIANTS_CHECKSUM"};var te=H?"focusout":"DOMFocusOut";function ue(){sd.call(this,"tooltip");this.f=0;this.h={}}
v(ue,sd);ba(ue);l=ue.prototype;l.register=function(){W(this,"mouseover",this.M);W(this,"mouseout",this.H);W(this,"focus",this.ca);W(this,"blur",this.Y);W(this,"click",this.H);W(this,"touchstart",this.la);W(this,"touchend",this.O);W(this,"touchcancel",this.O)};
l.unregister=function(){X(this,"mouseover",this.M);X(this,"mouseout",this.H);X(this,"focus",this.ca);X(this,"blur",this.Y);X(this,"click",this.H);X(this,"touchstart",this.la);X(this,"touchend",this.O);X(this,"touchcancel",this.O);this.dispose();ue.B.unregister.call(this)};
l.dispose=function(){for(var a in this.h)this.H(this.h[a]);this.h={}};
l.M=function(a){if(!(this.f&&1E3>ma()-this.f)){var b=parseInt(this.j(a,"tooltip-hide-timer"),10);b&&(Nb(a,"tooltip-hide-timer"),window.clearTimeout(b));var b=t(function(){ve(this,a);Nb(a,"tooltip-show-timer")},this),c=parseInt(this.j(a,"tooltip-show-delay"),10)||0,b=K(b,c);
Kb(a,"tooltip-show-timer",b.toString());a.title&&(ud(a,we(this,a)),a.title="");b=fa(a).toString();this.h[b]=a}};
l.H=function(a){var b=parseInt(this.j(a,"tooltip-show-timer"),10);b&&(window.clearTimeout(b),Nb(a,"tooltip-show-timer"));b=t(function(){if(a){var b=I(xe(this,a));b&&(ye(b),Bb(b),Nb(a,"content-id"));b=I(xe(this,a,"arialabel"));Bb(b)}Nb(a,"tooltip-hide-timer")},this);
b=K(b,50);Kb(a,"tooltip-hide-timer",b.toString());if(b=this.j(a,"tooltip-text"))a.title=b;b=fa(a).toString();delete this.h[b]};
l.ca=function(a){this.f=0;this.M(a)};
l.Y=function(a){this.f=0;this.H(a)};
l.la=function(a,b,c){c.changedTouches&&(this.f=0,a=rd(b,U(this),c.changedTouches[0].target),this.M(a))};
l.O=function(a,b,c){c.changedTouches&&(this.f=ma(),a=rd(b,U(this),c.changedTouches[0].target),this.H(a))};
function ze(a,b,c){ud(b,c);a=a.j(b,"content-id");(a=I(a))&&Db(a,c)}
function we(a,b){return a.j(b,"tooltip-text")||b.title}
function ve(a,b){if(b){var c=we(a,b);if(c){var d=I(xe(a,b));if(!d){d=document.createElement("div");d.id=xe(a,b);d.className=U(a,"tip");var e=document.createElement("div");e.className=U(a,"tip-body");var f=document.createElement("div");f.className=U(a,"tip-arrow");var g=document.createElement("div");g.setAttribute("aria-hidden","true");g.className=U(a,"tip-content");var h=Ae(a,b),k=xe(a,b,"content");g.id=k;Kb(b,"content-id",k);e.appendChild(g);h&&d.appendChild(h);d.appendChild(e);d.appendChild(f);
var k=Gb(b),m=xe(a,b,"arialabel"),f=document.createElement("div");y(f,U(a,"arialabel"));f.id=m;"rtl"==document.body.getAttribute("dir")?Db(f,c+" "+k):Db(f,k+" "+c);b.setAttribute("aria-labelledby",m);k=Tb()||document.body;k.appendChild(f);k.appendChild(d);ze(a,b,c);(c=parseInt(a.j(b,"tooltip-max-width"),10))&&e.offsetWidth>c&&(e.style.width=c+"px",y(g,U(a,"normal-wrap")));g=x(b,U(a,"reverse"));Be(a,b,d,e,h,g)||Be(a,b,d,e,h,!g);var p=U(a,"tip-visible");K(function(){y(d,p)},0)}}}}
function Be(a,b,c,d,e,f){A(c,U(a,"tip-reverse"),f);var g=0;f&&(g=1);var h=tc(b);f=new G((h.width-10)/2,f?h.height:0);var k=qc(b);Dc(new G(k.x+f.x,k.y+f.y),c,g);f=wb(window);var m;1==c.nodeType?m=rc(c):(c=c.changedTouches?c.changedTouches[0]:c,m=new G(c.clientX,c.clientY));c=tc(d);var p=Math.floor(c.width/2),g=!!(f.height<m.y+h.height),h=!!(m.y<h.height),k=!!(m.x<p);f=!!(f.width<m.x+p);m=(c.width+3)/-2- -5;a=a.j(b,"force-tooltip-direction");if("left"==a||k)m=-5;else if("right"==a||f)m=20-c.width-3;
a=Math.floor(m)+"px";d.style.left=a;e&&(e.style.left=a,e.style.height=c.height+"px",e.style.width=c.width+"px");return!(g||h)}
function xe(a,b,c){a=U(a)+Qb(b);c&&(a+="-"+c);return a}
function Ae(a,b){var c=null;ab&&x(b,U(a,"masked"))&&((c=I("yt-uix-tooltip-shared-mask"))?(c.parentNode.removeChild(c),dd(c)):(c=document.createElement("iframe"),c.src='javascript:""',c.id="yt-uix-tooltip-shared-mask",c.className=U(a,"tip-mask")));return c}
function ye(a){var b=I("yt-uix-tooltip-shared-mask"),c=b&&Jb(b,function(b){return b==a},!1,2);
b&&c&&(b.parentNode.removeChild(b),fd(b),document.body.appendChild(b))}
;function Ce(){sd.call(this,"subscription-button")}
v(Ce,sd);ba(Ce);Ce.prototype.register=function(){W(this,"click",this.P);td(this,Xd,this.ha);td(this,Yd,this.ga);td(this,Zd,this.Da);td(this,be,this.ha);td(this,ce,this.ga);td(this,de,this.Fa);td(this,fe,this.Ca);td(this,ge,this.Ba)};
Ce.prototype.unregister=function(){X(this,"click",this.P);Ce.B.unregister.call(this)};
var De={V:"hover-enabled",oa:"yt-uix-button-subscribe",pa:"yt-uix-button-subscribed",Ha:"ypc-enabled",qa:"yt-uix-button-subscription-container",ra:"yt-subscription-button-disabled-mask-container"},Ee={Ia:"channel-external-id",sa:"subscriber-count-show-when-subscribed",ta:"subscriber-count-tooltip",ua:"subscriber-count-title",Ja:"href",W:"is-subscribed",Ka:"parent-url",La:"clicktracking",va:"style-type",X:"subscription-id",Ma:"target",wa:"ypc-enabled"};l=Ce.prototype;
l.P=function(a){var b=this.j(a,"href"),c;c=(c=J("PLAYER_CONFIG"))&&c.args&&void 0!==c.args.authuser?!0:!(!J("SESSION_INDEX")&&!J("LOGGED_IN"));if(b)a=this.j(a,"target")||"_self",window.open(b,a);else if(c){b=this.j(a,"channel-external-id");c=this.j(a,"clicktracking");var d;if(this.j(a,"ypc-enabled")){d=this.j(a,"ypc-item-type");var e=this.j(a,"ypc-item-id");d={itemType:d,itemId:e,subscriptionElement:a}}else d=null;e=this.j(a,"parent-url");if(this.j(a,"is-subscribed")){var f=this.j(a,"subscription-id");
Q(ae,new Sd(b,f,d,a,c,e))}else Q(Wd,new Qd(b,d,c,e))}else Fe(this,a)};
l.ha=function(a){this.I(a.f,this.ia,!0)};
l.ga=function(a){this.I(a.f,this.ia,!1)};
l.Da=function(a){this.I(a.f,this.ka,!0,a.h)};
l.Fa=function(a){this.I(a.f,this.ka,!1)};
l.Ca=function(a){this.I(a.f,this.Aa)};
l.Ba=function(a){this.I(a.f,this.za)};
l.ka=function(a,b,c){b?(Kb(a,Ee.W,"true"),c&&Kb(a,Ee.X,c)):(Nb(a,Ee.W),Nb(a,Ee.X));Ge(this,a)};
l.ia=function(a,b){var c=Ib(a,null,De.qa,void 0);A(c,De.ra,b);a.setAttribute("aria-busy",b?"true":"false");a.disabled=b};
function Ge(a,b){var c=a.j(b,Ee.va),d=!!a.j(b,"is-subscribed"),c="-"+c,e=De.pa+c;A(b,De.oa+c,!d);A(b,e,d);a.j(b,Ee.ta)&&!a.j(b,Ee.sa)&&(c=U(ue.getInstance()),A(b,c,!d),b.title=d?"":a.j(b,Ee.ua));d?K(function(){y(b,De.V)},1E3):z(b,De.V)}
l.Aa=function(a){var b=!!this.j(a,"ypc-item-type"),c=!!this.j(a,"ypc-item-id");!this.j(a,"ypc-enabled")&&b&&c&&(y(a,"ypc-enabled"),Kb(a,Ee.wa,"true"))};
l.za=function(a){this.j(a,"ypc-enabled")&&(z(a,"ypc-enabled"),Nb(a,"ypc-enabled"))};
function He(a,b){var c=rb(U(a));return ta(c,function(a){return b==this.j(a,"channel-external-id")},a)}
l.xa=function(a,b,c){var d=Ba(arguments,2);w(a,function(a){b.apply(this,xa(a,d))},this)};
l.I=function(a,b,c){var d=He(this,a),d=xa([d],Ba(arguments,1));this.xa.apply(this,d)};
function Fe(a,b){var c=t(function(a){a.discoverable_subscriptions&&Wb("SUBSCRIBE_EMBED_DISCOVERABLE_SUBSCRIPTIONS",a.discoverable_subscriptions);this.P(b)},a);
he(c)}
;var Ie=window.yt&&window.yt.uix&&window.yt.uix.widgets_||{};u("yt.uix.widgets_",Ie);function Je(a){a=a.getInstance();var b=U(a);b in Ie||(a.register(),a.K.push(Zc("yt-uix-init-"+b,a.init,a)),a.K.push(Zc("yt-uix-dispose-"+b,a.dispose,a)),Ie[b]=a)}
;var Ke="";
function Le(){function a(a){var c=decodeURIComponent(a.data.replace(/,.*/,""));a=decodeURIComponent(a.data.replace(/.*,/,""));if(c){var d=document.getElementById("bottom-bar-link");d&&!d.href&&(c instanceof Na||c instanceof Na||(c=c.ea?c.da():String(c),Pa.test(c)||(c="about:invalid#zClosurez"),c=Qa(c)),c instanceof Na&&c.constructor===Na&&c.h===Oa?c=c.f:(ca(c),c="type_error:SafeUrl"),d.href=c)}a.match(/&label=.*&/)?a=a.replace(/&label=.*&/,""):a.match(/&label=.*/)&&(a=a.replace(/&label=.*/,""));Ke=
a}
try{window.addEventListener("message",a,!1),window.parent.postMessage("companion-setup-complete","*")}catch(b){}}
function Me(a){var b=Ke;/^[\s\xa0]*$/.test(null==b?"":String(b))||(a=Nd(Ke,{label:a}))&&Ld(a)}
function Ne(a,b){a&&Me(b)}
;function Oe(a){for(var b=0;b<a.length;b++){var c=a[b];"send_follow_on_ping_action"==c.name&&c.data&&c.data.follow_on_url&&(c=c.data.follow_on_url)&&Ld(c)}}
;function Pe(a){Qe("delete_from_watch_later_list",a)}
function Qe(a,b){oe("/playlist_video_ajax?action_"+a+"=1",{method:"POST",S:{feature:b.Ra||null,authuser:b.Oa||null,pageid:b.Ua||null},D:{video_ids:b.T||null,source_playlist_id:b.Xa||null,full_list_id:b.Sa||null,delete_from_playlists:b.Wa||null,add_to_playlists:b.Na||null,plid:J("PLAYBACK_ID")||null},context:b.context,onError:b.onError,A:function(a,d){var e=d.result;e&&e.actions&&Oe(e.actions);b.A.call(this,a,d)},
J:b.J,withCredentials:!1})}
;var Re=[],Se="";function Te(a){Se=Mb(a,"video-ids");var b=tb("sign-in-link",I("shared-addto-watch-later-login"));b&&(y(a,"addto-wl-focused"),K(function(){b.focus()},0))}
function Ue(){var a=tb("addto-wl-focused");a&&(z(a,"addto-wl-focused"),K(function(){a.focus()},0))}
function Ve(a){var b;b=Nd("/addto_ajax",{action_redirect_to_signin_with_add:1,video_ids:Se,next_url:document.location});var c=document.createElement("form");c.action=b;c.method="POST";b=document.createElement("input");b.type="hidden";b.name=J("XSRF_FIELD_NAME",void 0);b.value=J("XSRF_TOKEN",void 0);c.appendChild(b);document.body.appendChild(c);c.submit();a.preventDefault()}
function We(a){Ea(a,"addto-watch-later-button","addto-watch-later-button-loading");ub(a);var b=Mb(a,"video-ids");Qe("add_to_watch_later_list",{T:b,A:function(c,d){var e=d.list_id;Xe(e,b,a);bd("playlist-addto",b,e)},
onError:function(c,d){6==d.return_code?Xe(d.list_id,b,a):Ye(a,d)}})}
function Ze(a){Ea(a,"addto-watch-later-button-success","addto-watch-later-button-loading");var b=Mb(a,"video-ids");Pe({T:b,A:function(){Ea(a,"addto-watch-later-button-loading","addto-watch-later-button");var b=Zb("ADDTO_WATCH_LATER");ze(ue.getInstance(),a,b);bd("WATCH_LATER_VIDEO_REMOVED")},
onError:function(b,d){Ye(a,d)}})}
function $e(a){var b=Mb(a,"video-ids");Pe({T:b,A:function(b,d){bd("WATCH_LATER_VIDEO_REMOVED",a,d.result.video_count)},
onError:function(b,d){Ye(a,d)}})}
function Xe(a,b,c){Ea(c,"addto-watch-later-button-loading","addto-watch-later-button-success");var d=Zb("ADDTO_WATCH_LATER_ADDED");ze(ue.getInstance(),c,d);bd("WATCH_LATER_VIDEO_ADDED",a,b.split(","))}
function Ye(a,b){Ea(a,"addto-watch-later-button-loading","addto-watch-later-button-error");var c=b.error_message||Zb("ADDTO_WATCH_LATER_ERROR");ze(ue.getInstance(),a,c)}
;function af(a){O.call(this,1,arguments)}
v(af,O);function bf(a,b){O.call(this,2,arguments);this.h=a;this.f=b}
v(bf,O);function cf(a,b,c,d){O.call(this,1,arguments);this.f=b;this.l=c||null;this.h=d||null}
v(cf,O);function df(a,b){O.call(this,1,arguments);this.h=a;this.f=b||null}
v(df,O);function ef(a){O.call(this,1,arguments)}
v(ef,O);var ff=new P("ypc-core-load",af),gf=new P("ypc-guide-sync-success",bf),hf=new P("ypc-purchase-success",cf),jf=new P("ypc-subscription-cancel",ef),kf=new P("ypc-subscription-cancel-success",df),lf=new P("ypc-init-subscription",ef);var mf=!1,nf=[],of=[];function pf(a){a.f?mf?Q($d,a):Q(ff,new af(function(){Q(lf,new ef(a.f))})):qf(a.h,a.m,a.l,a.o)}
function rf(a){a.f?mf?Q(ee,a):Q(ff,new af(function(){Q(jf,new ef(a.f))})):sf(a.h,a.C,a.m,a.l,a.o)}
function tf(a){uf(ya(a.f))}
function vf(a){wf(ya(a.f))}
function xf(a){yf(a.f,a.h,null)}
function zf(a,b,c,d){yf(a,b,c,d)}
function Af(a){var b=a.h,c=a.f.subscriptionId;b&&c&&Q(Zd,new Rd(b,c,a.f.channelInfo))}
function Bf(a){var b=a.f;Fa(a.h,function(a,d){Q(Zd,new Rd(d,a,b[d]))})}
function Cf(a){Q(de,new Z(a.h.itemId));a.f&&a.f.length&&(Df(a.f,de),Df(a.f,fe))}
function qf(a,b,c,d){var e=new Z(a);Q(Xd,e);var f={};f.c=a;c&&(f.eurl=c);d&&(f.source=d);c={};(d=J("PLAYBACK_ID"))&&(c.plid=d);(d=J("EVENT_ID"))&&(c.ei=d);b&&Ef(b,c);oe("/subscription_ajax?action_create_subscription_to_channel=1",{method:"POST",S:f,D:c,A:function(b,c){var d=c.response;Q(Zd,new Rd(a,d.id,d.channel_info));d.show_feed_privacy_dialog&&bd("SHOW-FEED-PRIVACY-SUBSCRIBE-DIALOG",a);d.actions&&Oe(d.actions)},
J:function(){Q(Yd,e)}})}
function sf(a,b,c,d,e){var f=new Z(a);Q(be,f);var g={};d&&(g.eurl=d);e&&(g.source=e);d={};d.c=a;d.s=b;(a=J("PLAYBACK_ID"))&&(d.plid=a);(a=J("EVENT_ID"))&&(d.ei=a);c&&Ef(c,d);oe("/subscription_ajax?action_remove_subscriptions=1",{method:"POST",S:g,D:d,A:function(a,b){var c=b.response;Q(de,f);c.actions&&Oe(c.actions)},
J:function(){Q(ce,f)}})}
function yf(a,b,c,d){if(null!==b||null!==c){var e={};a&&(e.channel_id=a);null===b||(e.email_on_upload=b);null===c||(e.receive_no_updates=c);oe("/subscription_ajax?action_update_subscription_preferences=1",{method:"POST",D:e,onError:function(){d&&d()}})}}
function uf(a){if(a.length){var b=Aa(a,0,40);Q("subscription-batch-subscribe-loading");Df(b,Xd);var c={};c.a=b.join(",");var d=function(){Q("subscription-batch-subscribe-loaded");Df(b,Yd)};
oe("/subscription_ajax?action_create_subscription_to_all=1",{method:"POST",D:c,A:function(c,f){d();var g=f.response,h=g.id;if("array"==ca(h)&&h.length==b.length){var k=g.channel_info_map;w(h,function(a,c){var d=b[c];Q(Zd,new Rd(d,a,k[d]))});
a.length?uf(a):Q("subscription-batch-subscribe-finished")}},
onError:function(){d();Q("subscription-batch-subscribe-failure")}})}}
function wf(a){if(a.length){var b=Aa(a,0,40);Q("subscription-batch-unsubscribe-loading");Df(b,be);var c={};c.c=b.join(",");var d=function(){Q("subscription-batch-unsubscribe-loaded");Df(b,ce)};
oe("/subscription_ajax?action_remove_subscriptions=1",{method:"POST",D:c,A:function(){d();Df(b,de);a.length&&wf(a)},
onError:function(){d()}})}}
function Df(a,b){w(a,function(a){Q(b,new Z(a))})}
function Ef(a,b){var c=Md(a),d;for(d in c)b[d]=c[d]}
;u("yt.setConfig",Wb);u("yt.www.ads.companion.pauseAndTrackClickWithLabel",function(a){try{window.parent.postMessage("pause-video","*")}catch(b){}Me(a)});
u("ytbin.www.adcompanion.init",function(a,b){qd("addto-watch-later-button","click",We);qd("addto-watch-later-button-success","click",Ze);qd("addto-watch-later-button-remove","click",$e);qd("addto-watch-later-button-sign-in","click",Te);var c=I("shared-addto-watch-later-login");Re.push(dc(c,"click",Ve));Re.push(dc(c,te,Ue));mf=!0;of.push(R(Wd,pf),R(ae,rf));mf||(of.push(R($d,pf),R(ee,rf),R(Td,tf),R(Ud,vf),R(Vd,xf)),nf.push(Zc("subscription-prefs",zf)),of.push(R(hf,Af),R(kf,Cf),R(gf,Bf)));Je(Ad);Je(Ce);
Le();Me(a);b&&(R(Zd,la(Ne,!0,b)),R(de,la(Ne,!1,b)))});})();
