(function(){var n=this,aa=function(a,b,c){return a.call.apply(a.bind,arguments)},ba=function(a,b,c){if(!a)throw Error();if(2<arguments.length){var d=Array.prototype.slice.call(arguments,2);return function(){var c=Array.prototype.slice.call(arguments);Array.prototype.unshift.apply(c,d);return a.apply(b,c)}}return function(){return a.apply(b,arguments)}},u=function(a,b,c){u=Function.prototype.bind&&-1!=Function.prototype.bind.toString().indexOf("native code")?aa:ba;return u.apply(null,arguments)};var v=(new Date).getTime();var x=function(a){a=parseFloat(a);return isNaN(a)||1<a||0>a?0:a},ca=function(a,b){var c=parseInt(a,10);return isNaN(c)?b:c},z=function(a,b){return/^true$/.test(a)?!0:/^false$/.test(a)?!1:b},da=/^([\w-]+\.)*([\w-]{2,})(\:[0-9]+)?$/,ea=function(a,b){if(!a)return b;var c=a.match(da);return c?c[0]:b};var fa=x("0.0"),A=ca("101",-1),B=ca("98",0),ga=x("0.05"),ha=x("0.001"),ia=x("0.001"),ja=z("true",!0),ka=x("0.01"),la=x("0.03"),ma=x("0.001"),na=x("0.01"),
oa=x("");var C=function(){return"r20160223"},D=z("false",!1),pa=z("false",!1),qa=z("true",!1),ra=qa||!pa;var sa=String.prototype.trim?function(a){return a.trim()}:function(a){return a.replace(/^[\s\xa0]+|[\s\xa0]+$/g,"")},ta=/&/g,ua=/</g,va=/>/g,wa=/"/g,xa=/'/g,ya=/\x00/g,za={"\x00":"\\0","\b":"\\b","\f":"\\f","\n":"\\n","\r":"\\r","\t":"\\t","\x0B":"\\x0B",'"':'\\"',"\\":"\\\\","<":"<"},E={"'":"\\'"},Aa=function(a,b){return a<b?-1:a>b?1:0};var Ba=Array.prototype.forEach?function(a,b,c){Array.prototype.forEach.call(a,b,c)}:function(a,b,c){for(var d=a.length,e="string"==typeof a?a.split(""):a,f=0;f<d;f++)f in e&&b.call(c,e[f],f,a)};var F;a:{var Ca=n.navigator;if(Ca){var Da=Ca.userAgent;if(Da){F=Da;break a}}F=""}var G=function(a){return-1!=F.indexOf(a)};var Ea=function(){return G("Opera")||G("OPR")};var I=function(a){I[" "](a);return a};I[" "]=function(){};var Fa=Ea(),J=G("Trident")||G("MSIE"),Ga=G("Edge"),K=G("Gecko")&&!(-1!=F.toLowerCase().indexOf("webkit")&&!G("Edge"))&&!(G("Trident")||G("MSIE"))&&!G("Edge"),Ha=-1!=F.toLowerCase().indexOf("webkit")&&!G("Edge"),Ia=function(){var a=n.document;return a?a.documentMode:void 0},Ja;
a:{var Ka="",La=function(){var a=F;if(K)return/rv\:([^\);]+)(\)|;)/.exec(a);if(Ga)return/Edge\/([\d\.]+)/.exec(a);if(J)return/\b(?:MSIE|rv)[: ]([^\);]+)(\)|;)/.exec(a);if(Ha)return/WebKit\/(\S+)/.exec(a);if(Fa)return/(?:Version)[ \/]?(\S+)/.exec(a)}();La&&(Ka=La?La[1]:"");if(J){var Ma=Ia();if(null!=Ma&&Ma>parseFloat(Ka)){Ja=String(Ma);break a}}Ja=Ka}
var Na=Ja,Oa={},Pa=function(a){if(!Oa[a]){for(var b=0,c=sa(String(Na)).split("."),d=sa(String(a)).split("."),e=Math.max(c.length,d.length),f=0;0==b&&f<e;f++){var g=c[f]||"",k=d[f]||"",h=RegExp("(\\d*)(\\D*)","g"),l=RegExp("(\\d*)(\\D*)","g");do{var m=h.exec(g)||["","",""],q=l.exec(k)||["","",""];if(0==m[0].length&&0==q[0].length)break;b=Aa(0==m[1].length?0:parseInt(m[1],10),0==q[1].length?0:parseInt(q[1],10))||Aa(0==m[2].length,0==q[2].length)||Aa(m[2],q[2])}while(0==b)}Oa[a]=0<=b}},Qa=n.document,
Ra=Qa&&J?Ia()||("CSS1Compat"==Qa.compatMode?parseInt(Na,10):5):void 0;var Sa;if(!(Sa=!K&&!J)){var Ta;if(Ta=J)Ta=9<=Number(Ra);Sa=Ta}Sa||K&&Pa("1.9.1");J&&Pa("9");var Ua=function(a){try{var b;if(b=!!a&&null!=a.location.href)a:{try{I(a.foo);b=!0;break a}catch(c){}b=!1}return b}catch(c){return!1}},L=function(a,b){if(!(1E-4>Math.random())){var c=Math.random();if(c<b)return c=Va(window),a[Math.floor(c*a.length)]}return null},Va=function(a){try{var b=new Uint32Array(1);a.crypto.getRandomValues(b);return b[0]/65536/65536}catch(c){return Math.random()}},Wa=function(a,b){for(var c in a)Object.prototype.hasOwnProperty.call(a,c)&&b.call(void 0,a[c],c,a)},Xa=function(a){var b=
a.length;if(0==b)return 0;for(var c=305419896,d=0;d<b;d++)c^=(c<<5)+(c>>2)+a.charCodeAt(d)&4294967295;return 0<c?c:4294967296+c};var Ya=function(a,b,c){a.addEventListener?a.addEventListener(b,c,!1):a.attachEvent&&a.attachEvent("on"+b,c)};var ab=function(a,b,c,d,e,f){try{if((d?a.X:Math.random())<(e||a.M)){var g=a.L+b+("&"+Za(c,1)),g=g.substring(0,2E3);"undefined"===typeof f?$a(g):$a(g,f)}}catch(k){}},Za=function(a,b){var c=[];Wa(a,function(a,e){var f=null,g=typeof a;if(("object"==g&&null!=a||"function"==g)&&2>b)f=Za(a,b+1);else if(0===a||a)f=String(a);f&&c.push(e+"="+encodeURIComponent(f))});return c.join("&")},$a=function(a,b){n.google_image_requests||(n.google_image_requests=[]);var c=n.document.createElement("img");if(b){var d=
function(a){b(a);a=d;c.removeEventListener?c.removeEventListener("load",a,!1):c.detachEvent&&c.detachEvent("onload",a);a=d;c.removeEventListener?c.removeEventListener("error",a,!1):c.detachEvent&&c.detachEvent("onerror",a)};Ya(c,"load",d);Ya(c,"error",d)}c.src=a;n.google_image_requests.push(c)};var bb=document,M=window,cb,db=null,N=bb.getElementsByTagName("script");N&&N.length&&(db=N[N.length-1]);cb=db;var eb=Object.prototype.hasOwnProperty,fb=function(a,b){for(var c in a)eb.call(a,c)&&b.call(void 0,a[c],c,a)},O=function(a){return!(!a||!a.call)&&"function"===typeof a},gb=function(a,b){for(var c=1,d=arguments.length;c<d;++c)a.push(arguments[c])},hb=function(a,b){if(a.indexOf){var c=a.indexOf(b);return 0<c||0===c}for(c=0;c<a.length;c++)if(a[c]===b)return!0;return!1},ib=function(a){"google_onload_fired"in a||(a.google_onload_fired=!1,Ya(a,"load",function(){a.google_onload_fired=!0}))},jb=function(a){a=
a.google_unique_id;return"number"===typeof a?a:0},kb=!!window.google_async_iframe_id;var lb=function(a){return(a=a.google_ad_modifications)?a.loeids||[]:[]},mb=function(a,b,c){if(!a)return null;for(var d=0;d<a.length;++d)if((a[d].ad_slot||b)==b&&(a[d].ad_tag_origin||c)==c)return a[d];return null};var nb=function(a,b,c){this.T=a;this.O=b;this.w=c;this.v=null;this.N=this.o;this.ca=!1},ob=function(a,b,c){this.message=a;this.fileName=b||"";this.lineNumber=c||-1},qb=function(a,b,c,d){var e;try{e=c()}catch(k){var f=a.w;try{var g=pb(k),f=(d||a.N).call(a,b,g,void 0,void 0)}catch(h){a.o("pAR",h)}if(!f)throw k;}finally{}return e},rb=function(a,b){var c=P;return function(){var d=arguments;return qb(c,a,function(){return b.apply(void 0,d)})}};
nb.prototype.o=function(a,b,c,d,e){var f={};f.context=a;b instanceof ob||(b=pb(b));f.msg=b.message.substring(0,512);b.fileName&&(f.file=b.fileName);0<b.lineNumber&&(f.line=b.lineNumber.toString());a=n.document;f.url=a.URL.substring(0,512);f.ref=a.referrer.substring(0,512);if(this.v)try{this.v(f)}catch(g){}if(d)try{d(f)}catch(g){}ab(this.T,e||this.O,f,this.ca,c);return this.w};
var pb=function(a){var b=a.toString();a.name&&-1==b.indexOf(a.name)&&(b+=": "+a.name);a.message&&-1==b.indexOf(a.message)&&(b+=": "+a.message);if(a.stack){var c=a.stack,d=b;try{-1==c.indexOf(d)&&(c=d+"\n"+c);for(var e;c!=e;)e=c,c=c.replace(/((https?:\/..*\/)[^\/:]*:\d+(?:.|\n)*)\2/,"$1");b=c.replace(/\n */g,"\n")}catch(f){b=d}}return new ob(b,a.fileName,a.lineNumber)};var sb,P;sb=new function(){this.L="http"+("http:"===M.location.protocol?"":"s")+"://pagead2.googlesyndication.com/pagead/gen_204?id=";this.M=.01;this.X=Math.random()};P=new nb(sb,"jserror",!0);var zb=function(a,b){qb(P,a,b,yb)},yb=P.o,Ab=function(a,b){return rb(a,b)};var Bb={client:"google_ad_client",format:"google_ad_format",slotname:"google_ad_slot",output:"google_ad_output",ad_type:"google_ad_type",async_oa:"google_async_for_oa_experiment",peri:"google_top_experiment",pse:"google_pstate_expt"};P.w=!D;var Cb=function(a,b){this.start=a<b?a:b;this.end=a<b?b:a};var Db=function(a){var b;try{b=parseInt(a.localStorage.getItem("google_experiment_mod"),10)}catch(c){return null}if(0<=b&&1E3>b)return b;b=Math.floor(1E3*Va(a));try{return a.localStorage.setItem("google_experiment_mod",""+b),b}catch(c){return null}};var Eb=null,Fb=function(){if(!Eb){for(var a=window,b=a,c=0;a&&a!=a.parent;)if(a=a.parent,c++,Ua(a))b=a;else break;Eb=b}return Eb};var Q={ia:{},Da:{i:"453848100",j:"453848101"},ta:{i:"24819308",j:"24819309",fa:"24819320",la:"24819321"},sa:{i:"24819330",j:"24819331"},pa:{i:"86724438",j:"86724439"},xa:{i:"10573505",j:"10573506"},J:{i:"10573595",j:"10573596"},Ba:{i:"10573511",j:"10573512"},K:{i:"10573581",j:"10573582"},wa:{i:"312815006",j:"312815007"},I:{i:"312815106",j:"312815107"},ja:{i:"26835105",j:"26835106"},oa:{i:"35923720",j:"35923721"},A:{i:"35923760",j:"35923761"},H:{i:"20040000",j:"20040001"},ga:{i:"20040016",j:"20040017"},
ua:{i:"19188000",j:"19188001"},va:{i:"20040030",j:"20040031"},ha:{ea:"314159230",ra:"314159231"},qa:{ya:"27285692",Aa:"27285712",za:"27285713"},Ca:{i:"111849357",na:"111849358",ma:"111849359"},ka:{i:"29222046",j:"29222047"}};var Gb=new function(){this.U=new Cb(100,199)};var R=function(a,b,c,d){return a.location&&a.location.hash=="#google_plle_"+d?d:L([c,d],b)};var Hb=function(a,b,c){zb("files::getSrc",function(){if("https:"==M.location.protocol&&"http"==c)throw c="https",Error("Requested url "+a+b);});return[c,"://",a,b].join("")},Ib=function(a,b,c){c||(c=ra?"https":"http");return Hb(a,b,c)};var Jb=function(){},Lb=function(a,b,c){switch(typeof b){case "string":Kb(b,c);break;case "number":c.push(isFinite(b)&&!isNaN(b)?String(b):"null");break;case "boolean":c.push(String(b));break;case "undefined":c.push("null");break;case "object":if(null==b){c.push("null");break}if(b instanceof Array||void 0!=b.length&&b.splice){var d=b.length;c.push("[");for(var e="",f=0;f<d;f++)c.push(e),Lb(a,b[f],c),e=",";c.push("]");break}c.push("{");d="";for(e in b)b.hasOwnProperty(e)&&(f=b[e],"function"!=typeof f&&
(c.push(d),Kb(e,c),c.push(":"),Lb(a,f,c),d=","));c.push("}");break;case "function":break;default:throw Error("Unknown type: "+typeof b);}},Mb={'"':'\\"',"\\":"\\\\","/":"\\/","\b":"\\b","\f":"\\f","\n":"\\n","\r":"\\r","\t":"\\t","\x0B":"\\u000b"},Nb=/\uffff/.test("\uffff")?/[\\\"\x00-\x1f\x7f-\uffff]/g:/[\\\"\x00-\x1f\x7f-\xff]/g,Kb=function(a,b){b.push('"');b.push(a.replace(Nb,function(a){if(a in Mb)return Mb[a];var b=a.charCodeAt(0),e="\\u";16>b?e+="000":256>b?e+="00":4096>b&&(e+="0");return Mb[a]=
e+b.toString(16)}));b.push('"')};var Ob=G("Safari")&&!((G("Chrome")||G("CriOS"))&&!Ea()&&!G("Edge")||G("Coast")||Ea()||G("Edge")||G("Silk")||G("Android"))&&!(G("iPhone")&&!G("iPod")&&!G("iPad")||G("iPad")||G("iPod"));var S=null,Pb=K||Ha&&!Ob||Fa||"function"==typeof n.btoa;var T="google_ad_block google_ad_channel google_ad_client google_ad_format google_ad_height google_ad_host google_ad_host_channel google_ad_host_tier_id google_ad_modifications google_ad_output google_ad_region google_ad_section google_ad_slot google_ad_type google_ad_unit_key google_ad_dom_fingerprint google_ad_width google_adtest google_allow_expandable_ads google_alternate_ad_url google_alternate_color google_analytics_domain_name google_analytics_uacct google_analytics_url_parameters google_available_width google_captcha_token google_city google_color_bg google_color_border google_color_line google_color_link google_color_text google_color_url google_container_id google_content_recommendation_ui_type google_contents google_core_dbp google_country google_cpm google_ctr_threshold google_cust_age google_cust_ch google_cust_criteria google_cust_gender google_cust_id google_cust_interests google_cust_job google_cust_l google_cust_lh google_cust_u_url google_disable_video_autoplay google_delay_requests_count google_delay_requests_delay google_ed google_eids google_enable_content_recommendations google_enable_ose google_encoding google_floating_ad_position google_font_face google_font_size google_frame_id google_gl google_hints google_is_split_slot google_image_size google_kw google_kw_type google_lact google_language google_loeid google_max_num_ads google_max_radlink_len google_mtl google_nofo google_num_radlinks google_num_radlinks_per_unit google_only_ads_with_video google_only_pyv_ads google_only_userchoice_ads google_override_format google_page_url google_pgb_reactive google_previous_watch google_previous_searches google_referrer_url google_region google_responsive_formats google_reuse_colors google_rl_dest_url google_rl_filtering google_rl_mode google_rt google_safe google_scs google_source_type google_sui google_skip google_tag_for_child_directed_treatment google_tag_info google_tag_origin google_tdsma google_tfs google_tl google_ui_features google_video_doc_id google_video_product_type google_video_url_to_fetch google_webgl_support google_with_pyv_ads google_yt_pt google_yt_up".split(" "),
Qb={google_ad_modifications:!0,google_analytics_domain_name:!0,google_analytics_uacct:!0},Rb=function(a){return(a=a.innerText||a.innerHTML)&&(a=a.replace(/^\s+/,"").split(/\r?\n/,1)[0].match(/^\x3c!--+(.*?)(?:--+>)?\s*$/))&&/google_ad_client/.test(a[1])?a[1]:null},Sb=function(a){if(a=a.innerText||a.innerHTML)if(a=a.replace(/^\s+/,"").split(/\r?\n/,1)[0],(a=a.match(/^\x3c!--+(.*?)(?:--+>)?\s*$/)||a.match(/^\/*\s*<!\[CDATA\[(.*?)(?:\/*\s*\]\]>)?\s*$/i))&&/google_ad_client/.test(a[1]))return a[1];return null},
Tb=function(a){if(a=a.innerText||a.innerHTML)if(a=a.replace(/^\s+|\s+$/g,"").replace(/\s*(\r?\n)+\s*/g,";"),(a=a.match(/^\x3c!--+(.*?)(?:--+>)?$/)||a.match(/^\/*\s*<!\[CDATA\[(.*?)(?:\/*\s*\]\]>)?$/i))&&/google_ad_client/.test(a[1]))return a[1];return null},Wb=function(a,b){var c;try{a:{var d=a.document.getElementsByTagName("script"),e=Rb,f;"undefined"!=typeof a.google_pubvars_recovery_regexp_experiment?f=a.google_pubvars_recovery_regexp_experiment:(f=L(["C","E","EM"],la),a.google_pubvars_recovery_regexp_experiment=
f);b.google_pubvars_recovery_regexp_experiment=f;switch(f){case "E":e=Sb;break;case "EM":var g=a.navigator&&a.navigator.userAgent||"",k;if(!(k=/appbankapppuzdradb|daumapps|fban|fbios|fbav|fb_iab|gsa\/|messengerforios|naver|niftyappmobile|nonavigation|pinterest|twitter|ucbrowser|yjnewsapp|youtube/i.test(g))){var h;if(h=/i(phone|pad|pod)/i.test(g)){var l;if(l=/applewebkit/i.test(g)&&!/version|safari/i.test(g)){var m;try{m=!(!M.navigator.W&&!(D&&M.google_top_window||M.top).navigator.W)}catch(r){m=!1}l=
!m}h=l}k=h}e=k?Rb:Tb}for(var q=d.length-1;0<=q;q--){var w=d[q];if(!w.google_parsed_script){w.google_parsed_script=!0;var p=e(w);if(p){c=p;break a}}}c=null}}catch(r){return!1}if(!c)return!1;try{for(var d=/(google_\w+) *= *(['"]?[\w.-]+['"]?) *(?:;|$)/gm,e={},t;t=d.exec(c);)e[t[1]]=Ub(t[2]);Vb(e,a)}catch(r){return!1}return!!a.google_ad_client},Xb=function(a){a.google_page_url&&(a.google_page_url=String(a.google_page_url));var b=[];fb(a,function(a,d){if(null!=a){var e;try{var f=[];Lb(new Jb,a,f);e=f.join("")}catch(g){}e&&
(e=e.replace(/\//g,"\\$&"),gb(b,d,"=",e,";"))}});return b.join("")},Yb=function(a){for(var b=0,c=T.length;b<c;b++){var d=T[b];Qb[d]||(a[d]=null)}},Ub=function(a){switch(a){case "true":return!0;case "false":return!1;case "null":return null;case "undefined":break;default:try{var b=a.match(/^(?:'(.*)'|"(.*)")$/);if(b)return b[1]||b[2]||"";if(/^[-+]?\d*(\.\d+)?$/.test(a)){var c=parseFloat(a);return c===c?c:void 0}}catch(d){}}},Vb=function(a,b){for(var c=0;c<T.length;c++){var d=T[c];null==b[d]&&null!=
a[d]&&(b[d]=a[d])}};var U=function(a){this.m=a;a.google_iframe_oncopy||(a.google_iframe_oncopy={handlers:{},upd:u(this.ba,this)});this.Z=a.google_iframe_oncopy},Zb;var V="var i=this.id,s=window.google_iframe_oncopy,H=s&&s.handlers,h=H&&H[i],w=this.contentWindow,d;try{d=w.document}catch(e){}if(h&&d&&(!d.body||!d.body.firstChild)){if(h.call){setTimeout(h,0)}else if(h.match){try{h=s.upd(h,i)}catch(e){}w.location.replace(h)}}";
/[\x00&<>"']/.test(V)&&(-1!=V.indexOf("&")&&(V=V.replace(ta,"&amp;")),-1!=V.indexOf("<")&&(V=V.replace(ua,"&lt;")),-1!=V.indexOf(">")&&(V=V.replace(va,"&gt;")),-1!=V.indexOf('"')&&(V=V.replace(wa,"&quot;")),-1!=V.indexOf("'")&&(V=V.replace(xa,"&#39;")),-1!=V.indexOf("\x00")&&(V=V.replace(ya,"&#0;")));Zb=V;U.prototype.set=function(a,b){this.Z.handlers[a]=b;this.m.addEventListener&&this.m.addEventListener("load",u(this.P,this,a),!1)};
U.prototype.P=function(a){a=this.m.document.getElementById(a);try{var b=a.contentWindow.document;if(a.onload&&b&&(!b.body||!b.body.firstChild))a.onload()}catch(c){}};U.prototype.ba=function(a,b){var c=$b("rx",a),d;a:{if(a&&(d=a.match("dt=([^&]+)"))&&2==d.length){d=d[1];break a}d=""}d=(new Date).getTime()-d;c=c.replace(/&dtd=(\d+|-?M)/,"&dtd="+(1E5<=d?"M":0<=d?d:"-M"));this.set(b,c);return c};
var $b=function(a,b){var c=new RegExp("\\b"+a+"=(\\d+)"),d=c.exec(b);d&&(b=b.replace(c,a+"="+(+d[1]+1||1)));return b};K||Ha||J&&Pa(11);var ac=/MSIE [2-7]|PlayStation|Gecko\/20090226|Android 2\.|Opera/i,bc=/Android/,cc=!1;var dc=function(a){if(!a)return"";(a=a.toLowerCase())&&"ca-"!=a.substring(0,3)&&(a="ca-"+a);return a};var W=null;var ec={"120x90":!0,"160x90":!0,"180x90":!0,"200x90":!0,"468x15":!0,"728x15":!0};var X="google_ad_client google_ad_format google_ad_height google_ad_width google_city google_country google_encoding google_language google_page_url".split(" "),fc=function(a){try{var b=a.top.google_ads_params_store;if(b)return b;b=a.top.google_ads_params_store={};if(b===a.top.google_ads_params_store)return b}catch(c){}return null};var Y,Z=function(a){this.u=[];this.m=a||window;this.l=0;this.s=null;this.G=0},gc=function(a,b){this.R=a;this.da=b};Z.prototype.enqueue=function(a,b){0!=this.l||0!=this.u.length||b&&b!=window?this.C(a,b):(this.l=2,this.F(new gc(a,window)))};Z.prototype.C=function(a,b){this.u.push(new gc(a,b||this.m));hc(this)};Z.prototype.S=function(a){this.l=1;if(a){var b=Ab("sjr::timeout",u(this.D,this,!0));this.s=this.m.setTimeout(b,a)}};
Z.prototype.D=function(a){a&&++this.G;1==this.l&&(null!=this.s&&(this.m.clearTimeout(this.s),this.s=null),this.l=0);hc(this)};Z.prototype.Y=function(){return!(!window||!Array)};Z.prototype.$=function(){return this.G};Z.prototype.nq=Z.prototype.enqueue;Z.prototype.nqa=Z.prototype.C;Z.prototype.al=Z.prototype.S;Z.prototype.rl=Z.prototype.D;Z.prototype.sz=Z.prototype.Y;Z.prototype.tc=Z.prototype.$;var hc=function(a){var b=Ab("sjr::tryrun",u(a.aa,a));a.m.setTimeout(b,0)};
Z.prototype.aa=function(){if(0==this.l&&this.u.length){var a=this.u.shift();this.l=2;var b=Ab("sjr::run",u(this.F,this,a));a.da.setTimeout(b,0);hc(this)}};Z.prototype.F=function(a){this.l=0;a.R()};
var ic=function(a){try{return a.sz()}catch(b){return!1}},jc=function(a){return!!a&&("object"===typeof a||"function"===typeof a)&&ic(a)&&O(a.nq)&&O(a.nqa)&&O(a.al)&&O(a.rl)},kc=function(){if(Y&&ic(Y))return Y;var a=Fb(),b=a.google_jobrunner;return jc(b)?Y=b:a.google_jobrunner=Y=new Z(a)},lc=function(a,b){kc().nq(a,b)},mc=function(a,b){kc().nqa(a,b)};var nc=kb?1==jb(M):!jb(M),oc=function(){var a=qa?"https":"http",b=I("script"),c;(c=D&&window.google_cafe_host)||(c=ea("","pagead2.googlesyndication.com"));return["<",b,' src="',Ib(c,["/pagead/js/",C(),"/r20151006/show_ads_impl.js"].join(""),a),'"></',b,">"].join("")},pc=function(a,b,c,d){return function(){var e=!1;d&&kc().al(3E4);try{var f=
a.document.getElementById(b).contentWindow;if(Ua(f)){var g=a.document.getElementById(b).contentWindow,k=g.document;k.body&&k.body.firstChild||(k.open(),g.google_async_iframe_close=!0,k.write(c))}else{for(var h=a.document.getElementById(b).contentWindow,f=c,f=String(f),g=['"'],k=0;k<f.length;k++){var l=f.charAt(k),m=l.charCodeAt(0),q=k+1,w;if(!(w=za[l])){var p;if(31<m&&127>m)p=l;else{var t=l;if(t in E)p=E[t];else if(t in za)p=E[t]=za[t];else{var r=t,y=t.charCodeAt(0);if(31<y&&127>y)r=t;else{if(256>
y){if(r="\\x",16>y||256<y)r+="0"}else r="\\u",4096>y&&(r+="0");r+=y.toString(16).toUpperCase()}p=E[t]=r}}w=p}g[q]=w}g.push('"');h.location.replace("javascript:"+g.join(""))}e=!0}catch(H){h=Fb().google_jobrunner,jc(h)&&h.rl()}e&&(e=$b("google_async_rrc",c),(new U(a)).set(b,pc(a,b,e,!1)))}},qc=function(a){var b=["<iframe"];fb(a,function(a,d){null!=a&&b.push(" "+d+'="'+a+'"')});b.push("></iframe>");return b.join("")},rc=function(a){if(!W)a:{for(var b=[n.top],c=[],d=0,e;e=b[d++];){c.push(e);try{if(e.frames)for(var f=
e.frames.length,g=0;g<f&&1024>b.length;++g)b.push(e.frames[g])}catch(h){}}for(b=0;b<c.length;b++)try{var k=c[b].frames.google_esf;if(k){W=k;break a}}catch(h){}W=null}return W?"":(c={style:"display:none"},c["data-ad-client"]=dc(a),c.id="google_esf",c.name="google_esf",a=Ib(ea("","googleads.g.doubleclick.net"),["/pagead/html/",C(),"/r20151006/zrt_lookup.html"].join("")),c.src=a,qc(c))},sc=function(a,b){var c=b.google_ad_output,
d=b.google_ad_format;d||"html"!=c&&null!=c||(d=b.google_ad_width+"x"+b.google_ad_height,b.google_ad_format_suffix&&(d+=b.google_ad_format_suffix));c=!b.google_ad_slot||b.google_override_format||!ec[b.google_ad_width+"x"+b.google_ad_height]&&"aa"==b.google_loader_used;d=d&&c?d.toLowerCase():"";b.google_ad_format=d;for(var d=[b.google_ad_slot,b.google_ad_format,b.google_ad_type,b.google_ad_width,b.google_ad_height],c=[],e=0,f=cb.parentElement;f&&25>e;f=f.parentNode,++e)c.push(9!==f.nodeType&&f.id||
"");(c=c.join())&&d.push(c);b.google_ad_unit_key=Xa(d.join(":")).toString();d=a.google_adk2_experiment=a.google_adk2_experiment||L(["C","E"],ia)||"N";if("E"==d){d=cb;c=[];for(e=0;d&&25>e;++e){var f="",f=(f=9!==d.nodeType&&d.id)?"/"+f:"",g;a:{if(d&&d.nodeName&&d.parentElement){g=d.nodeName.toString().toLowerCase();for(var k=d.parentElement.childNodes,h=0,l=0;l<k.length;++l){var m=k[l];if(m.nodeName&&m.nodeName.toString().toLowerCase()===g){if(d===m){g="."+h;break a}++h}}}g=""}c.push((d.nodeName&&d.nodeName.toString().toLowerCase())+
f+g);d=d.parentElement}d=c.join()+":";c=a;e=[];if(c)try{for(var q=c.parent,f=0;q&&q!==c&&25>f;++f){var w=q.frames;for(g=0;g<w.length;++g)if(c===w[g]){e.push(g);break}c=q;q=c.parent}}catch(p){}b.google_ad_dom_fingerprint=Xa(d+e.join()).toString()}else"C"==d&&(b.google_ad_dom_fingerprint="ctrl")};(function(a){P.v=function(b){Ba(a,function(a){a(b)})}})([function(a){a.shv=C()},function(a){Wa(Bb,function(b,c){try{null!=n[b]&&(a[c]=n[b])}catch(d){}})}]);
zb("sa::main",function(){var a=window,b=a.google_ad_modifications=a.google_ad_modifications||{};if(!b.plle){b.plle=!0;var b=b.loeids=b.loeids||[],c=Q.J,d=c.j;if(a.location&&a.location.hash=="#google_plle_"+d)c=d;else{var c=[c.i,d],d=new Cb(A,A+B-1),e;(e=0>=B||B%c.length)||(e=Gb.U,e=!(e.start<=d.start&&e.end>=d.end));e?c=null:(e=Db(a),c=null!==e&&d.start<=e&&d.end>=e?c[(e-A)%c.length]:null)}c&&b.push(c);c=Q.I;(c=R(a,ga,c.i,c.j))&&b.push(c);c=Q.K;(c=R(a,ha,c.i,c.j))&&b.push(c);c=Q.A;(c=R(a,na,c.i,c.j))&&
b.push(c);bb.body||(c=Q.H,(c=R(a,oa,c.i,c.j))&&b.push(c))}d=a.google_ad_slot;b=a.google_ad_modifications;!b||mb(b.ad_whitelist,d,void 0)?b=null:(c=b.space_collapsing||"none",b=(d=mb(b.ad_blacklist,d))?{B:!0,V:d.space_collapsing||c}:b.remove_ads_by_default?{B:!0,V:c}:null);if(b&&b.B)Yb(a);else if(ib(a),(b=!1===window.google_enable_async)||(b=navigator.userAgent,ac.test(b)?b=!1:(void 0!==window.google_async_for_oa_experiment||!bc.test(navigator.userAgent)||ac.test(navigator.userAgent)||(window.google_async_for_oa_experiment=
L(["C","E"],fa)),b=bc.test(b)?"E"!==window.google_async_for_oa_experiment:!0),b=!b||window.google_container_id||window.google_ad_output&&"html"!=window.google_ad_output),b)a.google_loader_used="sb",a.google_start_time=v,sc(a,a),document.write(rc(a.google_ad_client)+oc());else{nc&&(c=a.google_ad_client,b=navigator,ja&&a&&c&&b&&(b=a.document,c=dc(c),(d=sa("r20160212"))&&(d+="/"),d=Ib("pagead2.googlesyndication.com","/pub-config/"+d+c+".js"),c=b.createElement("script"),c.src=d,(b=b.getElementsByTagName("script")[0])&&
b.parentNode&&b.parentNode.insertBefore(c,b)));a.google_unique_id?++a.google_unique_id:a.google_unique_id=1;c={};null==a.google_ad_client&&Wb(a,c)&&(c.google_loader_features_used=2048);Vb(a,c);c.google_loader_used="sa";Yb(a);var b=I("script"),f,g;a:{try{var k=a.top.google_pubvars_reuse_experiment;if("undefined"!==typeof k){g=k;break a}k=L(["C","E"],ka)||null;a.top.google_pubvars_reuse_experiment=k;if(a.top.google_pubvars_reuse_experiment===k){g=k;break a}}catch(tb){}g=null}if("E"===g){f=null!=c.google_ad_client;
g=null!=c.google_ad_width;k=null!=c.google_ad_height;if(d=fc(a)){for(e=0;e<X.length;e++){var h=X[e];null!=c[h]&&(d[h]=c[h])}if(d=fc(a)){e=d.google_ad_width;var h=d.google_ad_height,l=d.google_ad_format;e&&h&&l&&(l=(l=l&&l.match(/(\d+)x(\d+)/))?{width:l[1],height:l[2]}:null,!l||l.width==e&&l.height==h||delete d.google_ad_format)}}if(d=fc(a))for(e=0;e<X.length;e++)h=X[e],null==c[h]&&null!=d[h]&&(c[h]=d[h]);d=null!=c.google_ad_client;e=null!=c.google_ad_width;h=null!=c.google_ad_height;f=[f?"c2":d?"c1":
"c0",g?"w2":e?"w1":"w0",k?"h2":h?"h1":"h0"].join()}g={};k=c.google_ad_height;g.width='"'+c.google_ad_width+'"';g.height='"'+k+'"';g.frameborder='"0"';g.marginwidth='"0"';g.marginheight='"0"';g.vspace='"0"';g.hspace='"0"';g.allowtransparency='"true"';g.scrolling='"no"';g.allowfullscreen='"true"';g.onload='"'+Zb+'"';var m,k=a.document,d=g.id;for(e=0;!d||k.getElementById(d);)d="aswift_"+e++;g.id=d;g.name=d;d=c.google_ad_width;e=c.google_ad_height;var h=Q.A,l=h.i,q=h.j,w=c.google_active_plles=c.google_active_plles||
[];hb(lb(a),l)?w.push(l):hb(lb(a),q)&&w.push(q);cc=hb(lb(a),h.j);h=["<iframe"];for(m in g)g.hasOwnProperty(m)&&gb(h,m+"="+g[m]);m="left:0;position:absolute;top:0;";cc&&(m=m+"width:"+d+"px;height:"+e+"px;");h.push('style="'+m+'"');h.push("></iframe>");m=g.id;d="border:none;height:"+e+"px;margin:0;padding:0;position:relative;visibility:visible;width:"+d+"px;background-color:transparent";k.write(['<ins id="',m+"_expand",'" style="display:inline-table;',d,'"><ins id="',m+"_anchor",'" style="display:block;',
d,'">',h.join(" "),"</ins></ins>"].join(""));m=g.id;sc(a,c);g=Xb(c);k=null;d=L(["C","E"],ma);if("E"==d){a:{try{if(window.JSON&&window.JSON.stringify&&window.encodeURIComponent){var p=encodeURIComponent(window.JSON.stringify(c)),t;if(Pb)t=n.btoa(p);else{e=[];for(l=h=0;l<p.length;l++){for(var r=p.charCodeAt(l);255<r;)e[h++]=r&255,r>>=8;e[h++]=r}if(!S)for(S={},p=0;65>p;p++)S[p]="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=".charAt(p);p=S;r=[];for(h=0;h<e.length;h+=3){var y=e[h],
H=h+1<e.length,ub=H?e[h+1]:0,vb=h+2<e.length,wb=vb?e[h+2]:0,l=y>>2,q=(y&3)<<4|ub>>4,w=(ub&15)<<2|wb>>6,xb=wb&63;vb||(xb=64,H||(w=64));r.push(p[l],p[q],p[w],p[xb])}t=r.join("")}k=t;break a}ab(sb,"sblob",{json:window.JSON?1:0,eURI:window.encodeURIComponent?1:0},!0,void 0,void 0)}catch(tb){P.o("sblob",tb,void 0,void 0)}k=""}k||(k="{X}")}else"C"==d&&(k="{C}");t=rc(c.google_ad_client);y=(new Date).getTime();if(H=a.google_async_for_oa_experiment)a.google_async_for_oa_experiment=void 0;t=["<!doctype html><html><body>",
t,"<",b,">",g,"google_show_ads_impl=true;google_unique_id=",jb(a),';google_async_iframe_id="',m,'";google_start_time=',v,";",d?'google_pub_vars = "'+k+'";':"",f?'google_pubvars_reuse_experiment_result = "'+f+'";':"",H?'google_async_for_oa_experiment="'+H+'";':"","google_bpp=",y>v?y-v:1,";google_async_rrc=0;google_iframe_start_time=new Date().getTime();</",b,">",oc(),"</body></html>"].join("");(a.document.getElementById(m)?lc:mc)(pc(a,m,t,!0))}});}).call(this);