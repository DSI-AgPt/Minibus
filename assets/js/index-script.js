function Index() {

	var actionName = this.getActionName();
	var controllerName = this.getControllerName();
	this.currentPageHandler = new window[controllerName.capitalize()
			+ actionName.capitalize()](this);
	this.updateActiveTab(actionName);
	this.buildJsTree();
	$('body').show();
	this.buildLayout();
	this.initializeAlerts();
	this.currentPageHandler.initialize();

}
// TODO magical value
Index.prototype.DEFAULT_SELECTED_DATA_TYPE = 'etudiants';
Index.prototype.getActionName = function() {
	var actionName = $("input#action-name").val();
	if (actionName == 'index')
		actionName = 'edition';
	return actionName;
}
Index.prototype.getControllerName = function() {
	var actionName = $("input#controller-name").val();
	return actionName;
}
Index.prototype.updateActiveTab = function(activeTab) {
	$("a[href='#" + activeTab + "']").closest('li').addClass("ui-tabs-active");
	$("#main-menu").tabs({
		activate : function(event, ui) {
			var url = ui.newTab.attr("aria-controls");
			window.location = ("/" + url);
		}
	});
}
Index.prototype.buildJsTree = function() {
	var that = this;
	$("#menu-entites").on('changed.jstree', function(e, data) {
		var selected = data.selected[0];
		if (!selected.match(/^item-/))
			return;
		that.saveSelectedDataType(selected.replace("item-", ""));
		that.currentPageHandler.updateActiveDataArea();
	}).jstree().jstree('select_node', 'item-' + that.getSelectedDataType());
}
Index.prototype.buildLayout = function() {
	$('body').layout({
		defaults : {
			fxName : "slide",
			fxSpeed : "slow",
			initClosed : false,
			closable : false
		},
		north : {
			fxName : "none",
			spacing_closed : 8,
			togglerLength_closed : "100%",
			size : 68
		},
		east : {
			fxName : "none",
			spacing_closed : 8,
			togglerLength_closed : "100%",
			size : 500
		},
		applyDemoStyles : false
	});
}
Index.prototype.getSelectedDataType = function() {
	if ($.cookie('selected-data-type'))
		return $.cookie('selected-data-type');
	return this.DEFAULT_SELECTED_DATA_TYPE;
};
Index.prototype.saveSelectedDataType = function(type) {
	$.cookie('selected-data-type', type);
};

/**
 * Freeow alerts handling
 */
Index.prototype.AlertTypes = {
	OK : [ "simple", "ok" ],
	MESSAGE : [ "simple", "message" ],
	NORMAL : [ "smokey", "pushpin" ],
	ERROR : [ "gray", "slide" ]
};
Index.prototype.initializeAlerts = function() {
	$('<div id="freeow" class="freeow freeow-top-right"></div>').appendTo(
			$('body'));
}
Index.prototype.alert = function(title, message, type, autoHide) {
	var typeExists = false;
	for ( var key in this.AlertTypes) {
		typeExists = typeExists || type == this.AlertTypes[key];
	}
	if (!typeExists)
		type = this.AlertTypes.NORMAL;
	$("#freeow").freeow(title, message, {
		classes : type,
		autoHide : autoHide,
		hideStyle : {
			opacity : 0,
			left : "400px"
		},
		showStyle : {
			opacity : 1,
			left : 0
		}
	});

};
