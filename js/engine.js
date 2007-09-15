function SetCookie(cookieName, cookieData) {
	var expires = new Date ();
	expires.setTime(expires.getTime() + 31 * (24 * 60 * 60 * 1000));
	document.cookie = cookieName + "=" + escape(cookieData) + "; expires=" + expires.toGMTString();
} 

function GetCookie(name) {
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1)
    {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    }
    else
    {
        begin += 2;
    }
    var end = document.cookie.indexOf(";", begin);
    if (end == -1)
    {
        end = dc.length;
    }
    return unescape(dc.substring(begin + prefix.length, end));
}

// Custom Event Handling
document.onclick = EVT_Click;

function EVT_Click(evt) {
	evt = (evt) ? evt : ((window.event) ? event : null ) ;
	var target = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null ) ;
	OBJ_Click(target) ;
	evt.cancelBubble ;
}

function OBJ_Click(target) {
	if (target.id) {
		ObjectID = target.id ;
		ObjectType = ObjectID.substring(0,5) ;
		ObjectNyyum = ObjectID.substring(6,38) ; // Asumption that len(number)<20 ... 
		switch(ObjectType) {
		case 'TITLE' :
			ItemId = getItemId(target) ;
			ItemShowHide(ItemId) ;
			return ;
			break ;
		case 'IMARK' :
			ItemId = getItemId(target) ;
			setMark(ItemId) ;
			return ;
		default:
			break ;
		}
	}
	if (!target.parentNode) {
		return ;
	} else {
		OBJ_Click(target.parentNode) ;	
	}
}

function Obj_findStyleValue(target,styleProp, IEStyleProp) {
	if (target.currentStyle) return target.currentStyle[ IEStyleProp ] ;
	else if (window.getComputedStyle) {
		compStyle = window.getComputedStyle(target,'') ;
		return compStyle.getPropertyValue(styleProp) ;
	}
}

function getItemId(obj) {
	if (obj.id && obj.id.substring(0,5)=='IITEM' ) {
		return obj.id.substring(6,38) ;
	}
	if (!obj.parentNode) return null;
	return getItemId(obj.parentNode) ;
}
function ItemShowHide(id) {
	var item = document.getElementById('IITEM-'+id) ;
	var i ;
	for (i=0; i<item.childNodes.length; i++)
	if (item.childNodes[i].id && item.childNodes[i].id.substring(0,5)=='ICONT') {
		content = item.childNodes[i] ;
		break ;
	}
	status =  Obj_findStyleValue(content,'display','display')  ;
	if (status!='block') {
		content.style['display'] = 'block' ;
		item.style['background'] = Obj_findStyleValue(document.getElementById('c2'),'color','color') ;
	} else {
		content.style['display'] = 'none' ;
		item.style['background'] = Obj_findStyleValue(document.getElementById('c1'),'color','color') ;
	}
}
function getMarkObj(element) {
	var i ;
	if (!element) return null ;
	if (element.id && element.id.substring(0,5)=='IMARK') return element ;
	if (!element.childNodes) return null ;

	for (i=0; i<element.childNodes.length; i++) {
		itm = getMarkObj(element.childNodes[i]) ;
		if (itm) return itm ;
	}
}
function getMarkById(id) {
	var item = document.getElementById('IITEM-'+id) ;
	return getMarkObj(item) ;
}
function setMark(id) {
	var item ;
	if (!markID) { markID=id ; }
	if (item = getMarkById(markID) ) {
		item.setAttribute( 'src', 'i/mark_off.gif' );
	}
	if (item = getMarkById(id) ) {
		item.setAttribute( 'src', 'i/mark_on.gif' );
	}
	markID = id ;
	SetCookie('mark', markID) ;
}
function SourcesSetShowHide(show) {
	var item = document.getElementById('sources') ;
	if (show) {
		item.style['display'] = 'block' ;
	} else {
		item.style['display'] = 'none' ;
	}
}


function ItemSetShowHide(id,show) {
	//var item = document.getElementById('IITEM-'+id) ;
	var item = id;
	var i ;
	for (i=0; i<item.childNodes.length; i++)
	if (item.childNodes[i].id && item.childNodes[i].id.substring(0,5)=='ICONT') {
		content = item.childNodes[i] ;
		break ;
	}
	if (show) {
		content.style['display'] = 'block' ;
		item.style['background'] = Obj_findStyleValue(document.getElementById('c2'),'color','color') ;
	} else {
		content.style['display'] = 'none' ;
		item.style['background'] = Obj_findStyleValue(document.getElementById('c1'),'color','color') ;
	}
}

function visible_mode(display) {
	var main = document.getElementById('main') ;
	var i ;
	var j ;
	var items ;
	var feed ;
	for (i=0; i<main.childNodes.length; i++) {
		feed = main.childNodes[i] ;
		for (j=0; j<feed.childNodes.length; j++) {
			date = feed.childNodes[j];
			for (k=0; k<date.childNodes.length; k++) {
				if (date.childNodes[k].id && date.childNodes[k].id.substring(0,5)=='IITEM') {
						//itemID = date.childNodes[k].id.substring(6,38) ;
						//ItemSetShowHide(itemID,display) ;
						ItemSetShowHide(date.childNodes[k],display) ;
				}
			}
		}
	}
	showDetails = display ;
	SetCookie('showDetails', showDetails) ;
}
/*
function visible_mode(display) {
        var main = document.getElementById('main') ;
        var i ;
        var j ;
        var items ;
        for (i=0; i<main.childNodes.length; i++) {
                items = main.childNodes[i] ;
                for (j=0; j<main.childNodes.length; j++) {
                        if (main.childNodes[j].id && main.childNodes[j].id.substring(0,5)=='IITEM') {
                                itemID = main.childNodes[j].id.substring(6,38) ;
    		                    ItemSetShowHide(itemID,display) ;
                        }
                }
        }
        showDetails = display ;         SetCookie('showDetails', showDetails) ;
}*/

function toggleStyle(StyleName) {
	object = document.getElementById(StyleName) ;
	if (object.disabled==true) object.disabled=false ;
	else object.disabled=true;
}
function visible_mode_toggle() {
	visible_mode(!showDetails) ;
}

function toggle_visible(object) {
	if (document.layers)
	{
		current = (document.layers[object].display == 'none') ? 'block' : 'none';
		document.layers[object].display = current;
	}
	else if (document.all)
	{
		current = (document.all[object].style.display == 'none') ? 'block' : 'none';
		document.all[object].style.display = current;
	}
	else if (document.getElementById)
	{
		vista = (document.getElementById(object).style.display == 'none') ? 'block' : 'none';
		document.getElementById(object).style.display = vista;
	}
}
function toggle_hide_show(object) {
	if (document.all)
	{
		current = (document.all[object].src.indexOf('i/arrow_in.png')) ? 'i/arrow_out.png' : 'i/arrow_in.png';
		document.all[object].src = current;
	}
	else if (document.getElementById)
	{
		vista = document.getElementById(object).src.indexOf('i/arrow_in.png');
		if(vista >= 0) {
			vista = 'i/arrow_out.png';
		}
		else {
			vista = 'i/arrow_in.png';
		}
		document.getElementById(object).src = vista;
	}
}

function search_page(query) {
	var main = document.getElementById('main');
	var i ;
	var j ;
	var items ;
	for (i=0; i<main.childNodes.length; i++) {
		items = main.childNodes[i] ;
        for (j=0; j<main.childNodes.length; j++) {
			if (main.childNodes[j].id && main.childNodes[j].id.substring(0,5)=='IITEM') {
				itemID = main.childNodes[j].id.substring(6,38) ;
				ItemSetShowHide(itemID,display) ;
			}
		}
		var str="Web Enabling Tools is Cool!"
		var pos=str.IndexOf(query)
		if (pos>=0) {
			
		}
		else {
			
		}
	}
}
function showChange( whichId, whichName, whichUrl ) {
	var elem;
	if( document.getElementById ) {// this is the way the standards work
		elem = document.getElementById('changer');
		document.getElementById('change_id').value = whichId;
		document.getElementById('change_name').value = whichName;
		document.getElementById('change_url').value = whichUrl;
	}
	else if( document.all ) {// this is the way old msie versions work
		elem = document.all['changer'];
		document.all['change_id'].value = whichId;
		document.all['change_name'].value = whichName;
		document.all['change_url'].value = whichUrl;
	}
	else if( document.layers ) {// this is the way nn4 works
		elem = document.layers['changer'];
		document.layers['change_id'].value = whichId;
		document.layers['change_name'].value = whichName;
		document.layers['change_url'].value = whichUrl;
	}
	elem.style.display = 'block';
	Fat.fade_element('changer',null,null,'#E67373','#FFFFFF');
	return false;
}
function hideChanger() {
	var elem;
	if( document.getElementById ) {// this is the way the standards work
		elem = document.getElementById('changer');
		elem2 = document.getElementById('changer_id');
	}
	else if( document.all ) {// this is the way old msie versions work
		elem = document.all['changer'];
		elem2 = document.all['changer_id'];
	}
	else if( document.layers ) {// this is the way nn4 works
		elem = document.layers['changer'];
		elem2 = document.layers['changer_id'];
	}
	elem.style.display = 'none';
	elem2.style.display = 'none';
}
function adminLoader(page) {
	switch(page) {
		case 'feeds':
			hideChanger();
			break;
	}
}