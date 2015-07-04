$(document).ready(function() {
	$('html').addClass($.fn.details.support ? 'details' : 'no-details');
	new Index();
})
// utilities
String.prototype.capitalize = function() {
	return this.charAt(0).toUpperCase() + this.slice(1);
};
(function($) {
	$.fn.check = function(bool) {
		return this.each(function() {
			$this = $(this);
			if (bool)
				$this.attr('checked', 'checked');
			else
				$this.removeAttr('checked');
			if ($this.hasClass('ui-helper-hidden-accessible'))
				$this.checkbox('refresh');
		});
	};
	$.fn.styleTable = function(options) {
		var defaults = {
			css : 'ui-styled-table',
			interactive : false
		};
		options = $.extend(defaults, options);

		return this.each(function() {
			$this = $(this);
			$this.addClass(options.css);
			if (options.interactive)
				$this.on('mouseover mouseout', 'tbody tr', function(event) {
					$(this).toggleClass("ui-state-hover",
							event.type == 'mouseover');
				});
			$this.find("tr.ui-state-error td").addClass("ui-state-error");
			$this.find("th").addClass("ui-widget");
			$this.find("td").addClass("ui-widget");
			$this.find("tr:last-child").addClass("last-child");

		});
	};
})(jQuery);

/**
 * héritage
 */
var extendClass = function(child, parent) {
	var Surrogate = function() {
	};
	Surrogate.prototype = parent.prototype;
	child.prototype = new Surrogate();

};
/**
 * Instanciation dynamique
 */
var instantiate = function(className, args) {
	var o, f, c;
	c = window[className]; // get reference to class constructor function
	f = function() {
	}; // dummy function
	f.prototype = c.prototype; // reference same prototype
	o = new f(); // instantiate dummy function to copy prototype properties
	c.apply(o, args); // call class constructor, supplying new object as
	// context
	o.constructor = c; // assign correct constructor (not f)
	return o;
}
/**
 * définition des relations d'héritage
 */
if (typeof IndexAcquisition == 'function')
	extendClass(IndexAcquisition, IndexIo);
if (typeof IndexExport == 'function')
	extendClass(IndexExport, IndexIo);
if (typeof CdmCdmFileAcquisitionControl == 'function')
	extendClass(CdmCdmFileAcquisitionControl, DefaultProcessControl);