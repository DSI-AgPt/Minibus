function IndexBrowse($container) {
	this.$container = $container;
}
IndexBrowse.prototype.initialize = function() {
	var that = this;
	this.buildControlScriptList();
	this.initializationIndex = new Object();
	that.buildInterface();
	that.updateActiveDataArea();
	$('details').details();
};
IndexBrowse.prototype.buildControlScriptList = function() {
	var that = this;
	this.controlScripts = new Object();
	$(".control-script-name").each(
			function(i, e) {
				var $scriptClass = $(e).val();
				var $elemWrapper = $(e).closest('div').find(
						"div.browse-control");
				var url = $(e).closest('div').find("input.control-script-url")
						.val();
				that.controlScripts[$elemWrapper.attr("id")] = instantiate(
						$scriptClass, [ that, $elemWrapper, url ]);
			});
}
IndexBrowse.prototype.updateActiveDataArea = function() {
	var that = this;

	if (!this.initializationIndex)
		return;
	var selectedDataType = this.$container.getSelectedDataType();
	that.$yearControl = $("#year-select-menu-" + selectedDataType);
	var initialized = this.isAreaInitialized(selectedDataType);
	$wrapper = $("div#browse-" + selectedDataType);
	if (!initialized) {
		for ( var key in this.controlScripts) {
			$wrapper.find("#" + key).each(function(i, e) {
				that.controlScripts[key].initialize($(e));

			});

		}
	}
	$(".process").hide();
	$("#browse-" + selectedDataType).show();
	for ( var key in this.controlScripts) {
		$wrapper.find("#" + key).each(function(i, e) {
			that.controlScripts[key].update($(e));
		});
	}
	this.initializeArea(selectedDataType);
};
IndexBrowse.prototype.buildInterface = function() {
	var that = this;
	$(".year-select-menu").selectmenu({
		width : 'auto',
		change : function() {
			that.updateActiveDataArea();
		}
	});
};
IndexBrowse.prototype.initializeArea = function(selectedDataType) {
	this.initializationIndex[selectedDataType] = true;
};
IndexBrowse.prototype.isAreaInitialized = function(selectedDataType) {
	return this.initializationIndex[selectedDataType] == true;
};

IndexBrowse.prototype.getSelectedYear = function() {
	if (this.$yearControl.length == 0)
		return false;
	return this.$yearControl.val()
};