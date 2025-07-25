/*
	Copyright (c) 2007-2011 Peter Schlömer

	Released under the following licenses (to make reuse in other Wikis
	easier):

	GNU General Public License (GPL), version 2
	GNU Free Documentatin Licence (GFDL), version 1.2 or later
	Creative Commons Attribution ShareAlike (CC-by-sa), version 2 or later
*/

/*
	Global variables
*/
var ime_areas = Array();
var ime_currentlyEditing = -1;
var ime_width;
var ime_height;
var ime_scale;
var imeEventHandlers = {
	ime_Area: ime_Area,
	ime_CircleCoord: ime_CircleCoord,
	ime_PolyCoord: ime_PolyCoord,
	ime_RectCoord: ime_RectCoord,
	ime_deleteArea: ime_deleteArea,
	ime_deletePolyCoords: ime_deletePolyCoords,
	ime_editArea: ime_editArea,
	ime_error: ime_error,
	ime_eventCircle: ime_eventCircle,
	ime_eventDummy: ime_eventDummy,
	ime_eventGetButton: ime_eventGetButton,
	ime_eventGetX: ime_eventGetX,
	ime_eventGetY: ime_eventGetY,
	ime_eventPoly: ime_eventPoly,
	ime_eventRect: ime_eventRect,
	ime_findATag: ime_findATag,
	ime_getElementsByClassName: ime_getElementsByClassName,
	ime_getEvent: ime_getEvent,
	ime_hideImport: ime_hideImport,
	ime_highlightMap: ime_highlightMap,
	ime_highlightMapCircle: ime_highlightMapCircle,
	ime_highlightMapPoly: ime_highlightMapPoly,
	ime_htmlNewDiv: ime_htmlNewDiv,
	ime_importLines: ime_importLines,
	ime_init1: ime_init1,
	ime_init2: ime_init2,
	ime_mouseEventClear: ime_mouseEventClear,
	ime_mouseEventSet: ime_mouseEventSet,
	ime_newArea: ime_newArea,
	ime_removeOtherUIElements: ime_removeOtherUIElements,
	ime_saveArea: ime_saveArea,
	ime_showImport: ime_showImport,
	ime_updateAreas: ime_updateAreas,
	ime_updateMap: ime_updateMap,
	ime_updateResult: ime_updateResult,
	ime_updateSelectArea: ime_updateSelectArea
};

/*
	Start Initialization if this is an image page and there actually is an image
*/
$(document).ready(function(){
	// Determine whether we are on an image page. Namespace must be 6 and action view
	if ( mw.config.get( 'wgNamespaceNumber' ) == 6 && mw.config.get( 'wgAction' ) == 'view' ) {
		// If we can a div with id file, we initialize
		if (document.getElementById('file')) {
			ime_init1();
		}
	}
});

/*
	Create a new div element object with an id.
*/
function ime_htmlNewDiv(id) {
	var div = document.createElement('div');
	if (id) div.id = id;
	return div;
}

var imeButton = null;
/*
	Initialization, part 1: Tries to find image and uses a XMLHttpRequest
	to download information about the image. When this is done (it's an
	asynchronous request) show a link to load the rest of ImageMapEdit
	using ime_init2().
*/
function ime_init1() {
	var divFile = document.getElementById('file');
	if (!divFile) {
		ime_error(mw.message('imagemapedit-error-imagenotfound').text() + ' (ime_init1,divFile=null)');
		return;
	}

	var a = ime_findATag(divFile);
	if (!a) {
		ime_error(mw.message('imagemapedit-error-imagenotfound').text() + ' (ime_init1,a=null)');
		return;
	}

	var img = a.firstChild;
	if (!img) {
		ime_error(mw.message('imagemapedit-error-imagenotfound').text() + ' (ime_init1,img=null)');
		return;
	}

	var url = mw.config.get( 'wgScriptPath' ) + '/api.php?format=xml&action=query&prop=imageinfo&iiprop=size&titles=' + mw.config.get( 'wgPageName' );

	imeButton = new OO.ui.ButtonWidget( {
		label: mw.message('imagemapedit-display-interface').text(),
		icon: 'imageLayoutBasic',
		disabled: true
	} );
	imeButton.on( 'click', ime_init2 );
	$( '#file' ).append( imeButton.$element );

	$.get(url, function(response) {
		var iiAttr = response.getElementsByTagName('ii')[0].attributes;
		ime_width = iiAttr.getNamedItem('width').nodeValue;
		ime_height = iiAttr.getNamedItem('height').nodeValue;

		ime_scale = img.width/ime_width;
		imeButton.setDisabled( false );
	});
}

/*
	Initialization, part 2: Triggered by an external link. Does some moving
	around of the image in the logical structure of the page, then hides the
	link and finally puts the HTML code in place.
*/
function ime_init2() {

	// Remove UI that might interfere with this code
	ime_removeOtherUIElements();

	var divFile = document.getElementById('file');
	var tempNode = divFile.firstChild;
	var a = ime_findATag(tempNode);
	var img = a.firstChild;

	var divImeContainer = ime_htmlNewDiv('imeContainer');
	divImeContainer.style.position = 'relative';

	// Move image from within link to outside
	a.removeChild(img);
	divFile.insertBefore(divImeContainer,tempNode);
	divFile.removeChild(tempNode);

	img.id = 'imeImg';
	img.style.zIndex = 99;
	img.style.border = 'none';
	img.style.opacity = '0.75';
	img.style.filter = 'alpha(opacity=75)'; // IE

	// Internet Explorer needs this differently
	if (typeof(navigator.userAgent) != 'undefined' && navigator.userAgent.match('/MSIE/')) {
		divImeContainer.style.overflow = 'none';
	}
	else {
		divImeContainer.style.overflow = 'auto';
	}

	var divImePreview = ime_htmlNewDiv('imePreview');
	divImePreview.style.position = 'absolute';
	divImePreview.style.top = '0';
	divImePreview.style.left = '0';
	divImePreview.style.width = img.width + 'px';
	divImePreview.style.height = img.height + 'px';
	divImePreview.zIndex = 0;

	divImeContainer.appendChild(divImePreview);
	divImeContainer.appendChild(img);

	var divIme = ime_htmlNewDiv('ime');
	divFile.appendChild(divIme);

	imeButton.setDisabled( true );

	// Disable image context menu so right click can be used for events
	img.oncontextmenu = ime_eventDummy;

	var template = mw.template.get( 'ext.imagemapedit', 'ImageMapEdit.mustache' );

	var messageKeys = template.getSource().match(/\{\{([a-zA-Z0-9\-_]+)\}\}/g) || [];
	var templateRenderData = {};
	messageKeys.forEach(function(match) {
		var key = match.replace(/[{}]/g, '');
		// Message keys that might be used here:
		// * imagemapedit-bottomleft
		// * imagemapedit-bottomright
		// * imagemapedit-circle
		// * imagemapedit-circlechoose1
		// * imagemapedit-circlechoose2
		// * imagemapedit-coordinates
		// * imagemapedit-deletearea
		// * imagemapedit-deletecoordinates
		// * imagemapedit-display-interface
		// * imagemapedit-editarea
		// * imagemapedit-error-imagenotfound
		// * imagemapedit-generatedwikicode
		// * imagemapedit-hidetextbox
		// * imagemapedit-imagealttext
		// * imagemapedit-imagedescription
		// * imagemapedit-imagedisplaydimensions
		// * imagemapedit-imagedisplayposition
		// * imagemapedit-imagedisplayposition-left
		// * imagemapedit-imagedisplayposition-center
		// * imagemapedit-imagedisplayposition-right
		// * imagemapedit-imagedisplaytype
		// * imagemapedit-imagedisplaytype-thumbnail
		// * imagemapedit-imagedisplaytype-border
		// * imagemapedit-imagedisplaytype-frameless
		// * imagemapedit-import
		// * imagemapedit-importareas
		// * imagemapedit-infolinkposition
		// * imagemapedit-linktarget
		// * imagemapedit-linktitle
		// * imagemapedit-newarea
		// * imagemapedit-nolink
		// * imagemapedit-notspecified
		// * imagemapedit-optional
		// * imagemapedit-poly
		// * imagemapedit-polychoose
		// * imagemapedit-preferences
		// * imagemapedit-position
		// * imagemapedit-radius
		// * imagemapedit-rect
		// * imagemapedit-rectbottom
		// * imagemapedit-rectchoose1
		// * imagemapedit-rectchoose2
		// * imagemapedit-rectleft
		// * imagemapedit-rectright
		// * imagemapedit-recttop
		// * imagemapedit-showtextbox
		// * imagemapedit-topleft
		// * imagemapedit-topright
		templateRenderData[key] = mw.message(key).escaped();
	});

	divIme.innerHTML = template.render(templateRenderData).html();

	document.ime = divIme.querySelector( 'form[name="ime"]' );

	// Setup event listeners
	if ( document.ime && document.ime.area ) {
		document.ime.area.addEventListener( 'click', function() {
			ime_editArea( this.selectedIndex );
		});
	}

	// Default image configs
	document.ime.imageDisplayDimensions.value = img.width + 'px';
}

/*
	Finds all elements in the current document with the specified class.
*/
function ime_getElementsByClassName(className) {
	// Hopefully the browser supports this natively
	if (document.getElementsByClassName) {
		return document.getElementsByClassName(className);
	}

	// Otherwise use the function defined by MediaWiki
	return getElementsByClassName(document,'*',className)
}

/*
	Display an error message, either by putting it on the page or - if the
	place to put it does not exist - by showing an alert box.
*/
function ime_error(message) {
	var jqFile = $('#file');
	var jqIme = $('#ime');

	if (jqFile.length !== 0) {
		var jqImeError = $('<p/>')
		.css({
			'color' : 'darkred',
			'background' : 'white',
			'border' : '1px solid darkred',
			'padding' : '1ex'
		})
		.text(message)

		if (jqIme !== 0) {
			jqIme.before(jqImeError);
		} else {
			jqImeError.appendTo(jqFile);
		}
	}
	else {
		window.alert(message);
	}
}

/*
	Dummy function to intercept events
*/
function ime_eventDummy(e) {
	e.cancelBubble = true;
	return false;
}

/*
	Function to define an object containing rect(angle) coordinates.
*/
function ime_RectCoord(x1,y1,x2,y2) {
	this.left = x1;
	this.top = y1;
	this.right = x2;
	this.bottom = y2;
}

/*
	Function to define an object containing circle coordinates.
*/
function ime_CircleCoord(x,y,r) {
	this.x = x;
	this.y = y;
	this.radius = r;
}

/*
	Function to define an object containing poly(gon) coordinates.
*/
function ime_PolyCoord(x,y,r) {
	this.points = Array();
}

/*
	Function to define an object storing info on a clickable area for the
	imagemap.
*/
function ime_Area(shape) {
	if (shape=='rect') {
		this.shape = 'rect';
		this.coords = new ime_RectCoord(0,0,0,0);
	}
	else if (shape=='circle') {
		this.shape = 'circle';
		this.coords = new ime_CircleCoord(0,0,20);
	}
	else {
		this.shape = 'poly';
		this.coords = new ime_PolyCoord();
	}
	this.link = '';
	this.title = '';
}

/*
	Browser invariant function to get the event "behind" the object passed
	to event handlers.
*/
function ime_getEvent(e) {
	if (e) {
		return e;
	}
	else {
		return window.event;
	}
}

function ime_eventGetX(e) {
	if (typeof(e.layerX)!='undefined') {
		var x = Math.round( e.layerX / ime_scale );
		return Math.max( 0, x );
	}
	if (typeof(e.offsetX)!='undefined') {
		return Math.round(e.offsetX / ime_scale);
	}
	else {
		return Math.round(e.x / ime_scale);
	}
}

function ime_eventGetY(e) {
	if (typeof(e.layerY)!='undefined') {
		var y = Math.round( e.layerY / ime_scale );
		return Math.max( 0, y );
	}
	if (typeof(e.offsetY)!='undefined') {
		return Math.round(e.offsetY / ime_scale);
	}
	else {
		return Math.round(e.y / ime_scale);
	}
}

function ime_eventGetButton(e) {
	if (typeof(e.which)!='undefined') {
		return e.which;
	}
	else {
		return e.button;
	}
}

function ime_mouseEventClear() {
	var img = document.getElementById('imeImg');
	img.onmousedown = null;
	img.style.cursor = '';
}

function ime_mouseEventSet(func) {
	var img = document.getElementById('imeImg');
	img.onmousedown = func;
	img.style.cursor = 'crosshair';
}

function ime_eventRect(e) {
	e = ime_getEvent(e);
	var button = ime_eventGetButton(e);
	if (button==1) {
		document.ime.areaRectLeft.value = ime_eventGetX(e);
		document.ime.areaRectTop.value = ime_eventGetY(e);
	}
	else if (button==2 || button==3) {
		document.ime.areaRectRight.value = ime_eventGetX(e);
		document.ime.areaRectBottom.value = ime_eventGetY(e);
	}
	ime_saveArea();
	return false;
}

function ime_eventCircle(e) {
	e = ime_getEvent(e);
	var button = ime_eventGetButton(e);
	if (button==1) {
		document.ime.areaCircleX.value = ime_eventGetX(e);
		document.ime.areaCircleY.value = ime_eventGetY(e);
	}
	else if (button==2 || button==3) {
		var a = (ime_eventGetX(e) - parseInt(document.ime.areaCircleX.value));
		var b = (ime_eventGetY(e) - parseInt(document.ime.areaCircleY.value));
		document.ime.areaCircleRadius.value = Math.round(Math.sqrt(a*a + b*b));
	}
	ime_saveArea();
	return false;
}

function ime_eventPoly(e) {
	e = ime_getEvent(e);
	var button = ime_eventGetButton(e);
	if (button==1) {
		area = ime_areas[ime_currentlyEditing];
		area.coords.points.push(ime_eventGetX(e));
		area.coords.points.push(ime_eventGetY(e));
		ime_saveArea();
	}
	return false;
}

function ime_newArea(shape) {
	var area = new ime_Area(shape);
	area.shape = shape;
	ime_areas.push(area);

	ime_currentlyEditing = ime_areas.length-1;
	ime_updateAreas();
	ime_editArea(ime_currentlyEditing);
}

function ime_updateAreas() {
	ime_updateSelectArea();
	ime_updateMap();
	ime_updateResult();
}

function ime_updateResult() {
	var imageDescriptionPos = document.ime.imageDescriptionPos.value;
	var imeDisplayConfigs = '';
	[
		'imageDisplayDimensions',
		'imageDisplayPosition',
		'imageDisplayType'
	].forEach( function( prop ) {
		if ( document.ime[prop].value !== '' ) {
			imeDisplayConfigs += '|' + document.ime[prop].value;
		}
	});

	var result = Array();
	result.push('<imagemap>');
	result.push(
		mw.config.get( 'wgPageName' ) + imeDisplayConfigs +
		'|alt=' + document.ime.imageAltText.value +
		'|' + document.ime.imageDescription.value
	);
	result.push('');
	for (var i=0; i<ime_areas.length; i++) {
		var coords = ime_areas[i].coords;
		var s = '';
		if (ime_areas[i].shape=='rect') {
			s = coords.left + ' ' + coords.top + ' ' + coords.right + ' ' + coords.bottom;
		}
		else if (ime_areas[i].shape=='circle') {
			s = coords.x + ' ' + coords.y + ' ' + coords.radius;
		}
		else if (ime_areas[i].shape=='poly') {
			s = coords.points.join(' ');
		}
		result.push(ime_areas[i].shape + ' ' + s + (((ime_areas[i].link).match(/(http(s)?.)?\:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)/g)) ? (' [' + ime_areas[i].link + (ime_areas[i].title ? ' ' + ime_areas[i].title : '') + ']') :(' [[' + ime_areas[i].link + (ime_areas[i].title ? '|' + ime_areas[i].title : '') + ']]')));
	}
	result.push('');
	result.push('desc ' + imageDescriptionPos);
	result.push('</imagemap>');

	var preResult = document.getElementById('imeResult');

	while (preResult.lastChild) {
		preResult.removeChild(preResult.lastChild);
	}

	for (var i=0; i<result.length; i++) {
		preResult.appendChild(document.createTextNode(result[i]));
		preResult.appendChild(document.createElement('br'));
	}
}

function ime_updateMap() {
	var preview = document.getElementById('imePreview');
	var img = document.getElementById('imeImg');

	// Remove areas from map which are out of range
	for (var i=0; i<preview.childNodes.length; i++) {
		var child = preview.childNodes[i];
		var id = parseInt(child.id.substring(10));
		if (id>=ime_areas.length) {
			preview.removeChild(child);
			i--;
		}
	}

	for (var i=0; i<ime_areas.length; i++) {
		// Get existing DIV
		var area = ime_areas[i];
		var div = document.getElementById('imePreview' + i);

		// If it does not exist exists, create a new one and set style
		if (!div) {
			var div = ime_htmlNewDiv('imePreview' + i)
			preview.appendChild(div);
			div.style.zIndex = 0;
			div.style.position = 'absolute';
			div.style.opacity = 0.5;
			div.style.filter = 'alpha(opacity=50)';
		}

		var coords = area.coords;
		if (area.shape == 'rect') {
			div.className = 'previewRect';
			// Only if valid coordinates were given, draw
			if (coords.left<coords.right && coords.top<coords.bottom) {
				div.style.left = Math.round(ime_scale * coords.left) + 'px';
				div.style.top = Math.round(ime_scale * coords.top) + 'px';
				div.style.width = (Math.round(ime_scale * coords.right) - Math.round(ime_scale * coords.left)) + 'px';
				div.style.height = (Math.round(ime_scale * coords.bottom) - Math.round(ime_scale * coords.top)) + 'px';
			}
			else {
				div.style.left = '0';
				div.style.top = '0';
				div.style.width = '0';
				div.style.height = '0';
			}
		}
		else if (area.shape == 'circle') {
			div.className = 'previewCircle';
			div.style.backgroundRepeat = 'no-repeat';
			var left = Math.round(ime_scale * coords.x) - Math.round(ime_scale * coords.radius);
			var top = Math.round(ime_scale * coords.y) - Math.round(ime_scale * coords.radius);
			var size = Math.round(ime_scale * coords.radius * 2) + 1;

			div.style.left = left + 'px';
			div.style.top = top + 'px';

			if (left + size > img.width) {
				div.style.width = (img.width - left) + 'px';
			}
			else {
				div.style.width = size + 'px';
			}

			if (top + size > img.height) {
				div.style.height = (img.height - top) + 'px';
			}
			else {
				div.style.height = size + 'px';
			}
		}
		else if (area.shape == 'poly') {
			// Determine maximum coordinates (this is how big the image is)
			div.className = 'previewPoly';
			div.style.backgroundRepeat = 'no-repeat';

			var points = coords.points;
			var minX=0; var maxX=0; var minY=0; var maxY=0;
			if (points.length>0) {
				minX = points[0];
				maxX = points[0];
				minY = points[1];
				maxY = points[1];
				for (var j=2; j<points.length; j+=2) {
					var x = points[j];
					var y = points[j+1];
					if (x<minX) minX = x;
					if (x>maxX) maxX = x;
					if (y<minY) minY = y;
					if (y>maxY) maxY = y;
				}
			}

			div.style.left = Math.round(ime_scale * minX) + 'px';
			div.style.top = Math.round(ime_scale * minY) + 'px';
			div.style.width = (Math.round(ime_scale * maxX) - Math.round(ime_scale * minX)) + 'px';
			div.style.height = (Math.round(ime_scale * maxY) - Math.round(ime_scale * minY)) + 'px';
		}
	}

	ime_highlightMap();
}

function ime_highlightMapCircle(div,radius,highlight) {
	div.style.borderRadius = '50%';
	div.style.backgroundColor = highlight ? 'red' : 'black';
	div.style.backgroundImage = 'none'; // Clear any old background image if present
}

function ime_highlightMapPoly(div,points,highlight) {
	var minX=0; var minY=0;
	if (points.length>0) {
		minX = points[0];
		minY = points[1];
		for (var j=2; j<points.length; j+=2) {
			var x = points[j];
			var y = points[j+1];
			if (x<minX) minX = x;
			if (y<minY) minY = y;
		}
	}
	var convpoints = Array();
	var clipPathCoords = [];
	for(var j=0; j<points.length; j+=2) {
		// Points for clip-path are relative to the div's own bounding box
		var relX = Math.round(ime_scale * points[j]) - Math.round(ime_scale * minX);
		var relY = Math.round(ime_scale * points[j+1]) - Math.round(ime_scale * minY);
		convpoints[j] = relX; // This array was used for the PHP script, might not be needed now
		convpoints[j+1] = relY;
		clipPathCoords.push(relX + 'px ' + relY + 'px');
	}

	if (clipPathCoords.length > 0) {
		div.style.clipPath = 'polygon(' + clipPathCoords.join(', ') + ')';
	} else {
		// If no points, make it invisible or a tiny dot
		div.style.clipPath = 'polygon(0px 0px, 0px 0px, 0px 0px)'; // Effectively hides it
	}
	div.style.backgroundColor = highlight ? 'red' : 'black';
	div.style.backgroundImage = 'none'; // Clear any old background image
}

function ime_highlightMap() {
	for (var i=0; i<ime_areas.length; i++) {
		var div = document.getElementById('imePreview' + i);
		var area = ime_areas[i];
		if (div && area) {
			if (i==ime_currentlyEditing) {
				div.style.opacity = '0.9';
				div.style.filter = 'alpha(opacity=90)'; // IE
			} else {
				div.style.opacity = '0.75';
				div.style.filter = 'alpha(opacity=75)'; // IE
			}
			if (area.shape == 'rect') {
				var backgroundColor = (i==ime_currentlyEditing) ? 'red' : 'black';
				if (div.style.backgroundColor != backgroundColor) div.style.backgroundColor = backgroundColor;
			}
			else if (area.shape == 'circle') {
				ime_highlightMapCircle(div,area.coords.radius,(i==ime_currentlyEditing));
			}
			else if (area.shape == 'poly') {
				ime_highlightMapPoly(div,area.coords.points,(i==ime_currentlyEditing));
			}
		}
	}
}

function ime_updateSelectArea() {
	var selectArea = document.ime.area;

	while (selectArea.childNodes.length>0) {
		selectArea.removeChild(selectArea.lastChild);
	}

	for (var i=0; i<ime_areas.length; i++) {
		var option = document.createElement('option');
		var area = ime_areas[i];
			option.value = i;
		while (option.childNodes.length>0) {
			option.removeChild(option.lastChild);
		}
		var text = (area.title ? area.title : area.link) + ' [' + area.shape + ']';
		option.appendChild(document.createTextNode(text));
		if (i == ime_currentlyEditing) {
			option.selected = 'selected';
		}
		selectArea.appendChild(option);
	}
}

function ime_editArea(index) {
	document.getElementById('imeProps').style.display = 'none';

	var area = ime_areas[index];

	if (area) {
		ime_currentlyEditing = index;

		document.getElementById('imeProps').style.display = '';
		document.getElementById('imePropsRect').style.display = 'none';
		document.getElementById('imePropsCircle').style.display = 'none';
		document.getElementById('imePropsPoly').style.display= 'none';
		ime_mouseEventClear();

		if (area.shape == 'rect') {
			document.getElementById('imePropsRect').style.display = '';
			ime_mouseEventSet(ime_eventRect);
		}
		else if (area.shape == 'circle') {
			document.getElementById('imePropsCircle').style.display = '';
			ime_mouseEventSet(ime_eventCircle);
		}
		else if (area.shape == 'poly') {
			document.getElementById('imePropsPoly').style.display = '';
			ime_mouseEventSet(ime_eventPoly);
		}

		document.ime.areaLink.value = area.link;
		document.ime.areaTitle.value = area.title;

		var coords = area.coords;
		if (area.shape == 'rect') {
			document.ime.areaRectLeft.value = coords.left;
			document.ime.areaRectTop.value = coords.top;
			document.ime.areaRectRight.value = coords.right;
			document.ime.areaRectBottom.value = coords.bottom;
		}
		else if (area.shape == 'circle') {
			document.ime.areaCircleX.value = coords.x;
			document.ime.areaCircleY.value = coords.y;
			document.ime.areaCircleRadius.value = coords.radius;
		}
		else if (area.shape == 'poly') {
			var propsPolyCoords = document.getElementById('imePropsPolyCoords');
			if (propsPolyCoords.childNodes.length > 0) propsPolyCoords.removeChild(propsPolyCoords.lastChild);
			propsPolyCoords.appendChild(document.createTextNode(area.coords.points.join(", ")));
		}

		ime_highlightMap();
	}
}

function ime_deletePolyCoords() {
	ime_areas[ime_currentlyEditing].coords.points = Array();
	ime_saveArea();
}

function ime_saveArea() {
	var area = ime_areas[ime_currentlyEditing];
	area.link = document.ime.areaLink.value;
	area.title = document.ime.areaTitle.value;

	var coords = area.coords;
	if (area.shape=='rect') {
		coords.left = parseInt(document.ime.areaRectLeft.value);
		coords.top = parseInt(document.ime.areaRectTop.value);
		coords.right = parseInt(document.ime.areaRectRight.value);
		coords.bottom = parseInt(document.ime.areaRectBottom.value);
	}
	else if (area.shape=='circle') {
		if (parseInt(document.ime.areaCircleRadius.value) < 0) document.ime.areaCircleRadius.value = 0;
		coords.x = parseInt(document.ime.areaCircleX.value);
		coords.y = parseInt(document.ime.areaCircleY.value);
		coords.radius = parseInt(document.ime.areaCircleRadius.value);
	}
	else if (area.shape == 'poly') {
		var propsPolyCoords = document.getElementById('imePropsPolyCoords');
		if (propsPolyCoords.childNodes.length > 0) propsPolyCoords.removeChild(propsPolyCoords.lastChild);
		propsPolyCoords.appendChild(document.createTextNode(coords.points.join(", ")));
	}

	ime_updateAreas();
}

function ime_deleteArea() {
	ime_mouseEventClear();

	// Remove element from ime_areas array
	ime_areas.splice(ime_currentlyEditing,1);

	// Remove preview div of the deleted area
	var div = document.getElementById('imePreview' + ime_currentlyEditing);
	if (div) {
		div.parentNode.removeChild(div);
	}

	// Move ids of preview divs to fill the hole
	for (var i=ime_currentlyEditing+1; i<ime_areas.length; i++) {
		div = document.getElementById('imePreview' + i);
		if (div) {
			div.id = 'imePreview' + (i-1);
		}
	}

	// If we deleted the last area, correct currently editing
	if (ime_currentlyEditing>=ime_areas.length) {
		ime_currentlyEditing = ime_areas.length-1;
	}

	ime_updateAreas();
	ime_editArea(ime_currentlyEditing);
}

function ime_importLines() {
	var text = document.ime.importText.value;
	var lines = text.split("\n");

	for (var i=0; i<lines.length; i++) {
		var rectMatch = /rect +(\d+) +(\d+) +(\d+) +(\d+) +\[\[([^|]*)(|(.*))?\]\]/i;
		var circleMatch = /circle +(\d+) +(\d+) +(\d+) +\[\[([^|]*)(|(.*))?\]\]/i;
		var polyMatch = /poly +(.*?) +\[\[([^|]*)(|(.*))?\]\]/i;

		var line = lines[i];

		if (rectMatch.test(line)) {
			var results = rectMatch.exec(line);
			var area = new ime_Area("rect");
			area.coords.left = parseInt(results[1]);
			area.coords.top = parseInt(results[2]);
			area.coords.right = parseInt(results[3]);
			area.coords.bottom = parseInt(results[4]);
			area.link = results[5];
			if (results[6]) area.title = results[6].substring(1);
			ime_areas.push(area);
		}
		else if (circleMatch.test(line)) {
			var results = circleMatch.exec(line);
			var area = new ime_Area("circle");
			area.coords.x = parseInt(results[1]);
			area.coords.y = parseInt(results[2]);
			area.coords.radius = parseInt(results[3]);
			area.link = results[4];
			if (results[5]) area.title = results[5].substring(1);
			ime_areas.push(area);
		}
		else if (polyMatch.test(line)) {
			var results = polyMatch.exec(line);
			var area = new ime_Area("poly");
			area.coords.points = results[1].replace(/ +/," ").split(" ");
			for (var j=0; j<area.coords.points.length; j++) {
				area.coords.points[j] = parseInt(area.coords.points[j]);
			}
			area.link = results[2];
			if (results[3]) area.title = results[3].substring(1);
			ime_areas.push(area);
		}
	}
	ime_updateAreas();
	ime_hideImport();
}

function ime_showImport() {
	$('#imeImport').show();
	$('#imeImportShow').hide();
	$('#imeImportHide').show();
}

function ime_hideImport() {
	$('#imeImport').hide();
	$('#imeImportShow').show();
	$('#imeImportHide').hide();
}

/*
	Remove all UI elements that might interfere with ImageMapEdit.
*/
function ime_removeOtherUIElements() {
	// Remove all UI elements of the 'annotations' feature used on Wikimedia
	// Commons.
	$('#ImageAnnotationAddButton').remove();
}

/*
	Try to find an <a> tag within the specified HTML document node.
*/
function ime_findATag(node) {
	// We just look at the first child until there is none or it is an <a> tag
	var a = node;
	while (a != null && a.nodeName.toUpperCase() != 'A') {
		a = a.firstChild;
	}
	return a;
}

function ime_callHandler( element ) {
	var handlerName = $( element ).data( 'handler-name' );
	var handlerArgs = $( element ).data( 'handler-args' ) || [];
	var handler = imeEventHandlers[ handlerName ];
	if ( typeof handler === 'function' ) {
		handler.apply( element, handlerArgs );
	}
	return false;
}

$(document).on( 'click', '#ime *[data-handler-type=click]', function ( e ) {
	e.defaultPrevented = true;
	return ime_callHandler( this );
});
$(document).on( 'change', '#ime *[data-handler-type=change]', function ( e ) {
	e.defaultPrevented = true;
	return ime_callHandler( this );
});
