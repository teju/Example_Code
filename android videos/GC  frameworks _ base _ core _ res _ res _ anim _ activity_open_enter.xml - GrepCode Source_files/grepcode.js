function isBrowserMSIE() {
    return YAHOO.env.ua.ie > 0;
}

function isBrowserMSIE6() {
    return (navigator.appVersion.indexOf('MSIE 6.')==-1) ? false : true;
}

function isBrowserMSIE7() {
    return (navigator.appVersion.indexOf('MSIE 7.')==-1) ? false : true;
}

function getPageOffsetLeft(el) {
	var x;

	x = el.offsetLeft;
	if (el.offsetParent != null)
		x += getPageOffsetLeft(el.offsetParent);

	return x;
}

function getPageOffsetTop(el) {
	var y;
	y = el.offsetTop;
	if (el.offsetParent != null)
		y += getPageOffsetTop(el.offsetParent);

	return y;
}

function getPageOffsetRight(el) {
	return el.offsetWidth + getPageOffsetLeft(el);
}

function getPageOffsetBottom(el) {
	return el.offsetHeight + getPageOffsetTop(el);
}

function getWindowWidth() {
	if (typeof (window.innerWidth) == 'number') {
		// Non-IE
		return window.innerWidth;
	} else if (document.documentElement
			&& (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
		// IE 6+ in 'standards compliant mode'
		return document.documentElement.clientWidth;
	} else if (document.body
			&& (document.body.clientWidth || document.body.clientHeight)) {
		// IE 4 compatible
		return document.body.clientWidth;
	}
}

function getWindowHeight() {
	if (typeof (window.innerWidth) == 'number') {
		// Non-IE
		return window.innerHeight;
	} else if (document.documentElement
			&& (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
		// IE 6+ in 'standards compliant mode'
		return document.documentElement.clientHeight;
	} else if (document.body
			&& (document.body.clientWidth || document.body.clientHeight)) {
		// IE 4 compatible
		return document.body.clientHeight;
	}
}

function toggleStructuralViews(message) {
	// toggle
	var mysheet = document.styleSheets[0]
	var myrules = mysheet.cssRules ? mysheet.cssRules : mysheet.rules
	for (i = 0; i < myrules.length; i++) {
		selectorText = myrules[i].selectorText;
		if (!selectorText)
			continue;

		selectorText = selectorText.toLowerCase()
		if (selectorText == ".type-hierarchy") {
			typeHierarchyRule = myrules[i];
		} else if (selectorText == ".jtoutline") {
			outlineRule = myrules[i];
		} else if (selectorText == ".outline-view-controller") {
			outlineViewController = myrules[i];
		} else if (selectorText == ".type-hierarchy-view-controller") {
			typeHierarchyViewController = myrules[i];
		}
	}

	if (typeHierarchyRule.style.display == "none" && message == 2) {
		typeHierarchyRule.style.display = "inline"
		outlineRule.style.display = "none"

		typeHierarchyViewController.style.color = "#fff"
		typeHierarchyViewController.style.background = "#009"
		outlineViewController.style.color = "#999"
		outlineViewController.style.background = "#fff"
	} else if (typeHierarchyRule.style.display == "inline" && message == 1) {
		typeHierarchyRule.style.display = "none"
		outlineRule.style.display = "inline"

		typeHierarchyViewController.style.color = "#999"
		typeHierarchyViewController.style.background = "#fff"
		outlineViewController.style.color = "#fff"
		outlineViewController.style.background = "#009"
	}
}

function getEntityType() {
    var eTypeDiv = document.getElementById('entitytype_hidden');
    if (!eTypeDiv) {
        return null;
    }
    return eTypeDiv.innerHTML;
}

function isFileStagingPage() {
    return getEntityType() == 'file';
}

function isProjectStagingPage() {
    return getEntityType() == 'project';
}

function isSnapshotStagingPage() {
    return getEntityType() == 'snapshot';
}

function createShortEntityIdLabel(entityid) {
	var parts = entityid.split('@');
	parts.shift();
	var result = "";
	var first = true;
	for(var i = 0; i < parts.length; ++i) {
	   if (!first) {
	       result = result + '/';
	   }
	   first = false;
	   result = result + parts[i].replace(/[^\$]+\$/, '');
	}
	return result;
}

function strrev(str) {
	splitext = str.split("");
	revertext = splitext.reverse();
	reversed = revertext.join("");
	return reversed;
}

function mailAntiSpam(user, domain) {
	var ea = strrev(user) + '@' + strrev(domain);
	document.write('<a href="mailto:');
	document.write(ea);
	document.write('">' + ea + '</a>');
}

function mailAntiSpamInfo() {
	mailAntiSpam('ofni', 'moc.edocperg');
}

function mailAntiSpamSales() {
	mailAntiSpam('selas', 'moc.edocperg');
}

function entDeclCtxMenu(toggleEl, entityType, entityId, derivable, links) {
    var menuDiv = document.getElementById("entity-decl-context-menu");
    var menuDataDiv = document.getElementById("entity-decl-context-menu-data");
    if (menuDiv) {
        var entityIdEsc = escape(entityId);
        if (menuDiv.style.visibility == "visible" && menuDataDiv.innerHTML == entityIdEsc) {
            menuDiv.style.visibility = "hidden";
        } else {
            var x = (isBrowserMSIE() ? toggleEl.children[0] : toggleEl).offsetLeft;
            var y = toggleEl.offsetTop + toggleEl.offsetHeight;
            menuDiv.style.left = x + "px";
            menuDiv.style.top = y + "px";
            menuDiv.style.visibility = "visible";

            var menuHtml;
            menuHtml  = "<a href='/search/usages?type="+entityType+"&id="+entityIdEsc+"&k=u'>Find usages</a>";
            if (derivable) {
                menuHtml += "<br/>";
                menuHtml += "<a href='/search/usages?type="+entityType+"&id="+entityIdEsc+"&k=d'>Find derived "+entityType+"s</a>";
            }

            if (links) {
                for (i=0; i<links.length; i++) {
                    var link = links[i];
                    menuHtml += "<br/>";
                    menuHtml += link;
                }
            }

            menuDiv.innerHTML = menuHtml;
            menuDataDiv.innerHTML = entityIdEsc;
            toggleEl.title = '';
        }
    }
    else {
        //alert()??
    }
}

function contextMenuDropDown(toggleEl, dataId) {
    var menuDiv = document.getElementById("context-menu-panel");
    var menuDataIdDiv = document.getElementById("context-menu-panel-data");

    var parentEl = toggleEl.parentNode;
    var children = parentEl.childNodes;
    var dataDiv = null;
    for (var i=0; i<children.length; i++) {
        var n = children[i];
        if (n.nodeType == 1 && n.tagName.toLowerCase() == 'div') { /* element */
            dataDiv = n;
            break;
        }
    }

    if (menuDiv && menuDataIdDiv && dataDiv) {
        if (menuDiv.style.visibility == "visible" && menuDataIdDiv.innerHTML == dataId) {
            menuDiv.style.visibility = "hidden";
        } else {
            var x = getPageOffsetLeft(toggleEl) - 7;
            var y = getPageOffsetTop(toggleEl) + toggleEl.offsetHeight + 5;
            
            menuDiv.style.left = x + "px";
            menuDiv.style.top = y + "px";
            menuDiv.innerHTML = dataDiv.innerHTML;
            menuDiv.style.visibility = "visible";
            menuDataIdDiv.innerHTML = dataId;
        }
    }
}

function getRequestParameter(name) {
    var searchString = top.location.search;
    searchString = searchString.substring(1);

    var paramValue = '';
    var params = searchString.split('&');
    for (i=0; i<params.length;i++) {
        var paramPair = params[i];
        var eqlIndex = paramPair.indexOf('=');
        var paramName = paramPair.substring(0,eqlIndex);

        if (paramName == name) {
            paramValue = unescape(paramPair.substring(eqlIndex+1));
            return paramValue;
        }
    }
    return paramValue;
}

function getClassAttributeName() {
    return (isBrowserMSIE() && (YAHOO.env.ua.ie < 8)) ? "className" : "class";
}

function getClassAttribute(el) {
	return el.getAttribute(getClassAttributeName());
}

function setClassAttribute(el, v) {
    el.setAttribute(getClassAttributeName(), v);
}

function get120x240Ad() {
    return '<scr' + 'ipt type="text/javascript">'
    + 'google_ad_client = "pub-8503126240019483";'
    + '/* 120x240, created 10/18/09 */'
    + 'google_ad_slot = "7201643226";'
    + 'google_ad_width = 120;'
    + 'google_ad_height = 240;'
    + '</scr' + 'ipt>'
    + '<scr' + 'ipt type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></scr' + 'ipt>';
}

function get160x600Ad() {
	return '<scr' + 'ipt type="text/javascript">'
	+ 'google_ad_client = "pub-8503126240019483";'
	+ '/* 160x600, created 10/22/09 */'
	+ 'google_ad_slot = "1913415170";'
	+ 'google_ad_width = 160;'
	+ 'google_ad_height = 600;'
	+ '</scr' + 'ipt>'
	+ '<scr' + 'ipt type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></scr' + 'ipt>';
}

function get468x60Ad() {
	return '<scr' + 'ipt type="text/javascript">'
	+ 'google_ad_client = "pub-8503126240019483";'
	+ '/* 468x60, created 11/2/09 */'
	+ 'google_ad_slot = "5407434204";'
	+ 'google_ad_width = 468;'
	+ 'google_ad_height = 60;'
	+ '</scr' + 'ipt>'
	+ '<scr' + 'ipt type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></scr' + 'ipt>';
}

function get160x90LinkUnitAd() {
    return '<scr' + 'ipt type="text/javascript">'
    + 'google_ad_client = "ca-pub-8503126240019483";'
    + '/* 160x90 link unit */'
    + 'google_ad_slot = "4179388590";'
    + 'google_ad_width = 160;'
    + 'google_ad_height = 90;'
    + '</scr' + 'ipt>'
    + '<scr' + 'ipt type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></scr' + 'ipt>';
}
