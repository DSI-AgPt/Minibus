/**
 * Prototype commun IndexAcquisition / IndexExport
 */
function IndexIo($container) {
}
IndexIo.prototype.disableDataSend = true;

IndexIo.prototype.initialize = function() {
	var that = this;
	this.addLogDisplayer();
	this.buildControlScriptList();
	this.initializationIndex = new Object();
	that.buildInterface();
	that.updateActiveDataArea();
	that.pollRunningProcesses();
	$('details').details();

};
IndexIo.prototype.addLogDisplayer = function() {
	$logArea = $("div.ui-layout-east.ui-layout-pane.ui-layout-pane-east");
	this.logDisplayer = new LogDisplayer(this, $logArea);
}
IndexIo.prototype.buildControlScriptList = function() {
	var that = this;
	this.controlScripts = new Object();
	$(".control-script-name")
			.each(
					function(i, e) {
						$scriptClass = $(e).val();
						$elemWrapper = $(e).next("div");
						that.controlScripts[$elemWrapper.attr("id")] = instantiate(
								$scriptClass, [ that, $elemWrapper,
										that.logDisplayer ]);
					});
}
IndexIo.prototype.buildInterface = function() {
	var that = this;

	$(".year-select-menu").selectmenu({
		width : 'auto',
		change : function() {
			that.rememberSelectedYear();
			that.updateActiveDataArea();

		}
	});

};
// TODO factorize
IndexIo.prototype.rememberSelectedYear = function() {
	var that = this;
	var selectedDataType = this.$container.getSelectedDataType();
	that.$yearControl = $("#year-select-menu-" + selectedDataType);
	if (that.$yearControl.length == 0)
		return;
	$.cookie('selected-year', that.$yearControl.val());
};
IndexIo.prototype.updateActiveDataArea = function() {
	var that = this;
	if (!this.initializationIndex)
		return;
	var selectedDataType = this.$container.getSelectedDataType();
	// TODO factorize
	that.$yearControl = $("#year-select-menu-" + selectedDataType);
	if ($.cookie('selected-year') && that.$yearControl.length > 0) {
		that.$yearControl.val($.cookie('selected-year')).selectmenu('refresh');
	}
	var initialized = this.isAreaInitialized(selectedDataType);
	$wrapper = $("div#" + this.mode + "-" + selectedDataType);
	if (!initialized) {
		for ( var key in this.controlScripts) {
			$wrapper.find("#" + key).each(function(i, e) {
				that.controlScripts[key].initialize($(e));
				$(e).hover(function() {
					// $(this).toggleClass("ui-state-disabled");
				});
			});

		}
	}
	$(".process").hide();
	$("#" + that.mode + "-" + selectedDataType).show();
	for ( var key in this.controlScripts) {
		$wrapper.find("#" + key).each(function(i, e) {
			that.controlScripts[key].update($(e));
		});
	}
	this.initializeArea(selectedDataType);
};

IndexIo.prototype.initializeArea = function(selectedDataType) {
	this.initializationIndex[selectedDataType] = true;
};
IndexIo.prototype.isAreaInitialized = function(selectedDataType) {
	return this.initializationIndex[selectedDataType] == true;
};

IndexIo.prototype.getSelectedYear = function() {
	if (this.$yearControl.length == 0)
		return false;
	return this.$yearControl.val()
};
IndexIo.prototype.enableControlBox = function($box, bool) {
	var that = this;
	$box.toggleClass("ui-state-disabled", !bool);
	if (!bool) {
		$box.get(0).addEventListener("click mousedown mouseup", that.stopEvent,
				true);
		$box.find(".ui-button").button("disable");
	} else {
		$box.get(0).removeEventListener('click mousedown mouseup',
				that.stopEvent, true);
		$box.find(".ui-button").button("enable");
	}

};
IndexIo.prototype.stopEvent = function(e) {
	e.preventDefault();
	e.stopPropagation();
	e.stopImmediatePropagation();
	return false;
};
IndexIo.prototype.pollRunningProcesses = function() {
	var that = this;
	setInterval(function() {
		$.ajax({
			type : "GET",
			url : that.getProcessStatePollingUrl()
		})
				.done(
						function(data) {
							var process, $container;
							$(".running-icon", ".process-control").addClass(
									'ui-helper-hidden');

							for (var int = 0; int < data.length; int++) {
								process = data[int];
								$typeContainer = $("#" + that.mode + "-"
										+ process.type);
								$processBox = $(
										"#process-control-" + process.type
												+ "-" + process.endpoint,
										$typeContainer);
								$(".running-icon", $processBox).removeClass(
										'ui-helper-hidden').next('img').hide();
							}
							$(".running-icon.ui-helper-hidden",
									".process-control").next('img').show();
						}).error(
				// TODO implémenter
				function(data) {
					console.log(data)

				});
	}, 3000);

};
IndexIo.prototype.getProcessStatePollingUrl = function() {
	var year = this.getSelectedYear();
	var url = "/rest/process/running/" + this.mode + "/" + (year ? year : 0);
	return url;
}
IndexIo.prototype.handleDisplayLogEvent = function(e, logIdentifier) {
	console.log(data);
}