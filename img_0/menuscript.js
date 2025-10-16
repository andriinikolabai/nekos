(function() {
var submenu		= 'submenu_';
var menuItem	= 'menu_';
var lPrev		= '';
var lParent		= '';
var lTimeout	= false;
var lHref		= '';
var lFirstTime	= true;
var lCurEl;
var lCurOpenMenuEl;
var lIndex1;

var menuIdPattern = /\b(submenu_[\d]+|menu_[\d]+)\b/i;
var menuClassPattern = /\b(menu(?:[\d]*)?td|(?:sub)?current(?:[\d]*)?td|dmenu(?:[\d]*)?td|submenu(?:[\d]*)?td)\b/ig;
var menuOverClassPattern = /\b(menu(?:[\d]*)?td|(?:sub)?current(?:[\d]*)?td|dmenu(?:[\d]*)?td|submenu(?:[\d]*)?td)Ov\b/ig;

var currentMenuItemClass = /\b(?:sub)?current(?:[\d]*)?td\b/i;
var currentMenuItemOverClass = /\b(?:sub)?current(?:[\d]*)?tdOv\b/i;

function addEvent(elm, evType, fn, useCapture) {
	if (elm.addEventListener) {
		elm.addEventListener(evType, fn, useCapture);
		return true;
	}
	else if (elm.attachEvent) {
		var r = elm.attachEvent('on' + evType, fn);
		return r;
	}
	else {
		elm['on' + evType] = fn;
	}
}

function init() {
	addEvent(document, 'mouseover', menuOver, true);
	addEvent(document, 'click', menuClick, true);
}

function getEventSrcElement(evt) {
	evt = (evt) ? evt : window.event;
	return (evt.srcElement) ? evt.srcElement : evt.target;
}

function menuClick(pEv) {
	var anchor = null;
	
	if (!parent.document.getElementById('toolbarframe')){		
		lEl = getEventSrcElement(pEv);
			
		if(lElem = getEl(lEl)) {
			lIndex1=lElem.id.substr(5);
		
			if (lTextEl = document.getElementById('textLink'+lIndex1)) {
				anchor = lTextEl;
				lHref = lTextEl.href;
			}
		} else if(lElem = getOpenMenuEl(lEl)) {
			anchor = getOpenMenuLink(lElem);
			lHref = anchor.getAttribute('href');
		}
		if(lHref) {
			
			if(anchor.getAttribute('target') == '_blank') {
				window.open(lHref, '_blank');
			} else {
				location.href = lHref;
			}
			//location.href = lHref;
			//window.open('https://www.google.com', '_blank'
		}		
	}
}

function menuOver(pEv) {
	lEl = getEventSrcElement(pEv);
	
	if(lTimeout) window.clearTimeout(lTimeout);
	lTimeout = setTimeout(function(){changeClass(lEl)}, 100);
	lTimeout = setTimeout(function(){menu(lEl)}, 500);
}

function changeClass(pEl) {
	var lEl = getElToClassChange(pEl);
	
	if(lEl && lEl.className && !/\bblank\b/.test(lEl.className)) {
		
		if (lCurEl && lEl.id != lCurEl.id) {
			lCurEl.className = lCurEl.className.replace(menuOverClassPattern, "$1");
		}
		
		lCurEl = lEl;
		lIndex1 = lCurEl.id.substr(5);
		
		if (document.getElementById(submenu+lIndex1) || doHrefExist(lIndex1) || currentMenuItemClass.test(lCurEl.className)) {
			lCurEl.className = lCurEl.className.replace(menuClassPattern, "$1Ov");
		}
	} else {
		if (lCurEl) {
			lCurEl.className = lCurEl.className.replace(menuOverClassPattern, "$1");
		}
	}
	
	/*Open menu*/
	lEl = getOpenMenuEl(pEl);
	
	if(lCurOpenMenuEl && lCurOpenMenuEl != lEl) {
		lCurOpenMenuEl.className = lCurOpenMenuEl.className.replace(menuOverClassPattern, "$1");
	}
	
	//if(lEl && !/\bblank\b/.test(lEl.className) && (getOpenMenuHref(lEl) || currentMenuItemClass.test(lEl.className) )) {
	if(lEl && (isLinkedMenuItem(lEl) || isVoidSwitchMenuItem(lEl) || isCurrentMenuItem(lEl))) {
		lCurOpenMenuEl = lEl;
		lCurOpenMenuEl.className = lCurOpenMenuEl.className.replace(menuClassPattern, "$1Ov");
	}/* else {
		
		if(lCurOpenMenuEl) {
			lCurOpenMenuEl.className = lCurOpenMenuEl.className.replace(menuOverClassPattern, "$1");
		}
	}*/
}

function isCurrentMenuItem(el) {
	return currentMenuItemClass.test(el.className);
}

function isLinkedMenuItem(el) {
	return !/\bblank\b/.test(el.className) && getOpenMenuHref(el);
}

function isVoidNoChildrenMenuItem(el) {
	return /\bblank\b/.test(el.className);
}

function isVoidMenuItem(el) {
	return !/\bblank\b/.test(el.className) && !hasLink(el) && !isCurrentMenuItem(el);
}

function isVoidSwitchMenuItem(el) {
	return isVoidMenuItem(el) && !!el.id;
}

function getOpenMenuEl(el) {
	if(el && el.tagName) {
		while (el && el.tagName && el.tagName.toLowerCase() != "body") {
			
			//hasMenuClass = menuOverClassPattern.test(el) || menuClassPattern.test(el);
			//isOpenMenuItem = !!el.id || menuIdPattern.test(el.id);
			isOpenMenuClass = !!el.className && (menuOverClassPattern.test(el.className) || menuClassPattern.test(el.className));
			
			if (isOpenMenuClass/* && isOpenMenuItem*/) {
				return el;
			} else {
				el = el.parentNode;
			}
		}
	} return false;
}

function getOpenMenuHref(pEl) {
	var children = pEl.childNodes;
	var len = children.length;
	var href;
	
	for(var i = 0; i < len; i++) {
		el = children[i];
		
		if(el.nodeType != 1) {
			continue;
		}
		
		if(el.tagName == 'A') {
			return el.getAttribute('href');
		}
		
		if(href = getOpenMenuHref(el)) {
			return href;
		}
	}
	
	return false;
	//nodeType == 1
	
	//return !!el.getElementsByTagNamne('A');
}

function getOpenMenuLink(pEl) {
	var children = pEl.childNodes;
	var len = children.length;
	var anchor;
	
	for(var i = 0; i < len; i++) {
		el = children[i];
		
		if(el.nodeType != 1) {
			continue;
		}
		
		if(el.tagName == 'A') {
			return el;
		}
		
		if(anchor = getOpenMenuLink(el)) {
			return anchor;
		}
	}
	
	return false;
	//nodeType == 1
	
	//return !!el.getElementsByTagNamne('A');
}

function hasLink(el) {
	return !!getOpenMenuHref(el);
}

function doHrefExist (pIndex) {
	if (lTextEl = document.getElementById('textLink'+pIndex)) {
		return true;
	}
	return false;	
}

function menu(pEl) {
	if(lEl = getEl(pEl)) {
		var lBr = new Array();
		var lIndex = lEl.id.substr(5);
		var lIndexFull = false;
		
		lIndexFull = lIndex;
		lIndex = lIndex.substr(0, lIndex.lastIndexOf('_') );
		
		lParent = document.getElementById('submenu_'+lIndex); 
		if (lPrev != '') {
			closeNode(lPrev, 1);
		}
		
		if (lParent && lParent.style.display == 'block')
			closeNode(lIndex, -1);
		
		
		lIndex = lIndexFull || lIndex;
		openParentNode('_'+lIndex);
		
		if( strIn(lIndex, '_') == 1 && lIndex != lPrev && document.getElementById(submenu+lIndex) ) {
			lPrev = lIndex;
		}
		
		
		/*закрывает выпадающее меню следующего уровня при переходе на другой элемент меню текущего уровня*/
		// At this point we need to have TABLE reference.
		if(lParent.tagName == 'DIV') {
			lParent = lParent.getElementsByTagName('TABLE')[0];
		}
		
		lCh = lParent.rows; /*lCh - массив, который состоит елементов подменю*/
		for (var i = 0; i < lCh.length; i++) {
			lC = lCh[i].cells; /*lC - массив, который состоит из набора ячеек текущего елемента подменю*/
			for (var j = 0; j < lC.length; j++)
				if (lC[j].id.substr(0, 5) == menuItem && lC[j].id != lEl.id)
					lBr[lBr.length] = lC[j].id.substr(5);
		}

		for(var z = 0; z < lBr.length; z++) {
			if(document.getElementById(submenu+lBr[z]) && document.getElementById(submenu+lBr[z]).style.display == 'block') {
				closeNode(lBr[z], 1);
			}
		}
	}
	else if (lPrev != '') {
		closeNode(lPrev, 1);
		lPrev = '';
	}
}

// Переделать без replace :)
function strIn(haystack, needle) {
	var rExp = new RegExp(needle);	
	var i = 0;
	
	while(rExp.test(haystack)) {
	    haystack = haystack.replace('_', '');
	    i++;
	}
	return i;
}

function closeNode(pIndex, pF) {
	if (pF == 1) {
		/*закрывает текущий элемент*/
		el = document.getElementById(submenu+pIndex);
		el.style.display	= 'none';				
		el.style.visibility	= 'hidden';
	}
	
	/*рекурсивно закрывает дочерние элементы*/
	for (var i = 1; document.getElementById(menuItem+pIndex+'_'+i); i++) {
		el = document.getElementById(submenu+pIndex+'_'+i) || null;
		if(el && el.style.display == 'block') {
			closeNode(pIndex+'_'+i, 1);
		}
	}
}

function openParentNode(pIndex) {
	while( strIn(pIndex, '_') > 1) {
		if(el = document.getElementById(submenu.substr(0, 7) + pIndex)) {
			el.style.display	= 'block';
			el.style.visibility	= 'visible';
		}
		pIndex = pIndex.substr(0, pIndex.lastIndexOf('_'));
	}
}

function getEl(el) {
	if(el && el.tagName) {
		while (el && el.tagName && el.tagName.toLowerCase() != "body") {
			if (el.id.substr(0, 5) == menuItem)	return el;
			if ((el.id.substr(0, 8) == submenu) && el.id.length > 9)
				el = document.getElementById(menuItem+el.id.substr(8));
			else	el = el.parentNode;
		}
	} return false;
}

function getElToClassChange(el) {
	if(el && el.tagName) {
		while (el && el.tagName && el.tagName.toLowerCase() != "body") {
			if (el.id.substr(0, 5) == menuItem)	return el;
			if ((el.id.substr(0, 8) == submenu) && el.id.length > 9)
				return false;
			else	el = el.parentNode;
		}
	} return false;
}


addEvent(window, 'load', init, true);

})();

function toggleSubmenuVis(pNumber) {
	if (parent.document.getElementById('templateframe') || pNumber == 0) {
		return false;
	}
	
	if((obj = document.getElementById('submenu_div' + pNumber)).style.display == "block" || obj.style.visibility == "visible") {
		obj.style.display = "none";
		obj.style.visibility = "hidden";
		
		//_opener = document.getElementById('submenu_div_opener_' + pNumber);
		//_opener.className = _opener.className.replace(/submenu\_opener\_open/i, 'submenu_opener_closed');
		
		obj.parentNode.className = obj.parentNode.className.replace('submenu1_cell_open', '');
		obj.parentNode.className = obj.parentNode.className + ' submenu1_cell_closed';
		//obj.parentNode.className = obj.parentNode.className.replace(/submenu1_cell_open/i, 'submenu1_cell_closed');
		//document.getElementById('arrow'+pNumber).className = "arrow";
	} else {
		obj.style.display = "block";
		obj.style.visibility = "visible";
		
		//obj.parentNode.className = obj.parentNode.className.replace(/submenu1_cell_closed/i, 'submenu1_cell_open');
		
		
		obj.parentNode.className = obj.parentNode.className.replace('submenu1_cell_closed', '');
		obj.parentNode.className = obj.parentNode.className + ' submenu1_cell_open';
		
		//_opener = document.getElementById('submenu_div_opener_' + pNumber);
		//_opener.className = _opener.className.replace(/submenu\_opener\_closed/i, 'submenu_opener_open');
		//document.getElementById('arrow'+pNumber).className = "arrowOpen";	
	}
}
