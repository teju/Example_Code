(function(){var f=this;function g(a){a=a.split(".");for(var b=f,c;c=a.shift();)if(null!=b[c])b=b[c];else return null;return b}
function h(a,b,c){return a.call.apply(a.bind,arguments)}
function k(a,b,c){if(!a)throw Error();if(2<arguments.length){var d=Array.prototype.slice.call(arguments,2);return function(){var c=Array.prototype.slice.call(arguments);Array.prototype.unshift.apply(c,d);return a.apply(b,c)}}return function(){return a.apply(b,arguments)}}
function l(a,b,c){l=Function.prototype.bind&&-1!=Function.prototype.bind.toString().indexOf("native code")?h:k;return l.apply(null,arguments)}
var m=Date.now||function(){return+new Date};
function n(a,b){var c=a.split("."),d=f;c[0]in d||!d.execScript||d.execScript("var "+c[0]);for(var e;c.length&&(e=c.shift());)c.length||void 0===b?d[e]?d=d[e]:d=d[e]={}:d[e]=b}
;function q(){this.f=this.f;this.i=this.i}
q.prototype.f=!1;q.prototype.isDisposed=function(){return this.f};
q.prototype.dispose=function(){this.f||(this.f=!0,this.l())};
q.prototype.l=function(){if(this.i)for(;this.i.length;)this.i.shift()()};function r(){q.call(this);this.a=[];this.a[3]=[];this.a[2]=[];this.a[1]=[];this.a[0]=[];this.b={};this.g=r.a;this.o=this.h=0;this.m=this.j=!1;this.c=[];this.u=l(this.w,this)}
(function(){var a=r;function b(){}
b.prototype=q.prototype;a.v=q.prototype;a.prototype=new b;a.prototype.constructor=a;a.A=function(a,b,e){for(var w=Array(arguments.length-2),p=2;p<arguments.length;p++)w[p-2]=arguments[p];return q.prototype[b].apply(a,w)}})();
r.f="hidden";r.b=1E3/60;r.c=3;r.a=r.b-3;function t(a,b,c,d){++a.o;var e=a.o;a.b[e]=b;a.j&&!d?a.c.push({id:e,s:c}):(a.a[c].push(e),a.m||a.j||u(a));return e}
function v(a){a.c.length=0;for(var b=3;0<=b;b--)a.a[b].length=0;a.b={};a.stop()}
function x(a){try{a()}catch(b){(a=g("yt.logging.errors.log"))&&a(b)}}
r.prototype.w=function(){this.stop();this.j=!0;for(var a=m()+this.g,b=this.a[3];b.length;){var c=b.shift(),d=this.b[c];delete this.b[c];d&&x(d)}if(!(m()>=a)){do{a:{for(b=2;0<=b;b--)for(c=this.a[b];c.length;){var d=c.shift(),e=this.b[d];delete this.b[d];if(e){b=e;break a}}b=null}b&&x(b)}while(b&&m()<a)}this.j=!1;b=0;for(c=this.c.length;b<c;b++)d=this.c[b],this.a[d.s].push(d.id);this.g=r.a;(a<=m()||this.c.length)&&u(this);this.c.length=0};
function u(a){a.m=!1;0==a.h&&(a.h=window.setTimeout(a.u,1))}
r.prototype.pause=function(){this.stop();this.m=!0};
r.prototype.stop=function(){window.clearTimeout(this.h);this.h=0};
r.prototype.l=function(){v(this);this.stop();r.v.l.call(this)};function y(){var a=g("yt.scheduler.instance.instance_");if(!a||a.isDisposed())a=new r,n("yt.scheduler.instance.instance_",a);return a}
function z(){var a=g("yt.scheduler.instance.instance_");a&&(a&&"function"==typeof a.dispose&&a.dispose(),n("yt.scheduler.instance.instance_",null))}
function A(){v(y())}
var B=g("yt.scheduler.instance.timerIdMap_")||{};function C(a,b,c){if(0==c||void 0===c)return c=void 0===c,-t(y(),a,b,c);var d=window.setTimeout(function(){var c=t(y(),a,b);B[d]=c},c);
return d}
function D(a){var b=y(),c=m();x(a);a=m()-c;b.g-=a}
function E(a){var b=y();if(0>a)delete b.b[-a];else{var c=B[a];c?(delete b.b[c],delete B[a]):window.clearTimeout(a)}}
function F(){u(y())}
function G(){y().pause()}
;function H(){}
H.getInstance=function(){return H.a?H.a:H.a=new H};
H.prototype.addTask=function(a){return C(a,2)};
H.prototype.cancelTask=function(a){E(a)};if(!g("yt.scheduler.initialized")){n("yt.scheduler.instance.dispose",z);n("yt.scheduler.instance.addJob",C);n("yt.scheduler.instance.addImmediateJob",D);n("yt.scheduler.instance.cancelJob",E);n("yt.scheduler.instance.cancelAllJobs",A);n("yt.scheduler.instance.start",F);n("yt.scheduler.instance.pause",G);n("yt.scheduler.SpfScheduler.instance",H.getInstance());var I=H.getInstance(),J=H.getInstance().addTask;I.addTask=J;var K=H.getInstance(),L=H.getInstance().cancelTask;K.cancelTask=L;n("yt.scheduler.initialized",
!0)};})();
