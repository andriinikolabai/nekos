(function() {
var submenu		= 'submenu_';
var menuItem	= 'menu_';
var lPrev		= '';
var lParent		= '';
var lTimeout	= false;
var lHref		= '';
var lFirstTime	= true;
var lCurEl;
var lIndex1;

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
	if (!parent.document.getElementById('toolbarframe')){		
		lEl = getEventSrcElement(pEv);
			
		if(lElem = getEl(lEl)) {
			lIndex1=lElem.id.substr(5);
		
			if (lTextEl = document.getElementById('textLink'+lIndex1)){
				lHref=lTextEl.href;
			}
		}
		if(lHref != '')
			location.href = lHref;		
	}
}

function menuOver(pEv) {
	lEl = getEventSrcElement(pEv);
	
	if(lTimeout) window.clearTimeout(lTimeout);
	lTimeout = setTimeout(function(){changeClass(lEl)}, 100);
	lTimeout = setTimeout(function(){menu(lEl)}, 500);
}

function changeClass(pEl) {
	if((lEl = getEl(pEl)) && lFirstTime) {
		lCurEl = lEl;
		lIndex1 = lCurEl.id.substr(5);
		
		if(/\bblank\b/.test(lCurEl.className)) {
			return false;
		}
		
		if (document.getElementById(submenu+lIndex1) || doHrefExist(lIndex1) || lCurEl.className.indexOf('current') != -1) {
			switch(lCurEl.className){
				case "menutd":
						lCurEl.className='menutdOv';
						break;
				case "current":
						lCurEl.className='current over';
						break;						
				case "dmenutd21":
						lCurEl.className='dmenutd21a';
						break;
				case "menu2td":
						lCurEl.className='menu2tdOv';
						break;
				case "current2":
						lCurEl.className='current2 over';
						break;						
				case "dmenu2td21":
						lCurEl.className='dmenu2td21a';
						break;												
			}
			lFirstTime=false;
		}
	} else {
		if(!lFirstTime) {
			if (lEl.id != lCurEl.id) {
				switch(lCurEl.className) {
					case "menu2tdOv":
							lCurEl.className='menu2td';
							break;							
					case "current2 over":
							lCurEl.className='current2';
							break;							
					case "dmenu2td21a":
							lCurEl.className='dmenu2td21';
							break;	
					case "menutdOv":
							lCurEl.className='menutd';
							break;
					case "current over":
							lCurEl.className='current';
							break;						
					case "dmenutd21a":
							lCurEl.className='dmenutd21';
							break;													
				}
				lFirstTime = true;
			}
		}
	}
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


addEvent(window, 'load', init, true);

})();
