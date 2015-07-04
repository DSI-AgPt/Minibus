function IndexConfiguration($container) {
	this.$container = $container;
}
IndexConfiguration.prototype.initialize = function() {
	this.buildInterface();
	this.enableInterface(false);
	this.askForConfiguration();
};
IndexConfiguration.prototype.buildInterface = function() {
	var that = this;
	this.firstYearSpinner = this.createSpinner($("input[name='first_year']")
			.removeAttr("type"));
	this.lastYearSpinner = this.createSpinner($("input[name='last_year']")
			.removeAttr("type"));
	this.saveButton = $(".save-button").button({
		icons : {
			primary : 'ui-icon-enregistrer'
		},
		text : true
	}).button("disable").click(function() {
		that.sendData();
	});
	this.launchExecutorButton = $(".launch-executor-button").button({
		icons : {
			primary : 'ui-icon-launch-executor'
		},
		text : true
	}).click(
			function() {
				that
						.launchExecution($("input.verbose-mode")
								.attr("checked") == "checked");
			});
}
IndexConfiguration.prototype.enableInterface = function(bool) {
	this.lastYearSpinner.spinner(bool ? "enable" : "disable");
	this.firstYearSpinner.spinner(bool ? "enable" : "disable");
	if (!bool)
		this.enableSaveButton(false);
}
IndexConfiguration.prototype.enableSaveButton = function(bool) {
	this.saveButton.button(bool ? "enable" : "disable");
}
IndexConfiguration.prototype.enableLaunchExecutorButton = function(bool) {
	this.launchExecutorButton.button(bool ? "enable" : "disable");
}
IndexConfiguration.prototype.createSpinner = function($input) {
	var that = this;
	return $input.spinner({
		step : 1,
	}).on('spin change keyup', function(event, ui) {
		that.saveButton.button('enable');
	});
}
IndexConfiguration.prototype.askForConfiguration = function() {
	var that = this;
	$.ajax({
		type : "GET",
		url : "/rest/configuration",
	}).done(function(data) {
		that.refreshInterface(data);
		that.enableInterface(true);
	});
};
IndexConfiguration.prototype.refreshInterface = function(data) {
	this.firstYearSpinner.val(data.first_year);
	this.lastYearSpinner.val(data.last_year);
};
IndexConfiguration.prototype.launchExecution = function(verbose) {
	var that = this;
	that.enableLaunchExecutorButton(false);
	var query = verbose ? "?verbose=true" : "";
	$.ajax({
		type : "POST",
		url : "/execution" + query,
	}).done(function(data) {
		$(".log-display-area").text(data.responseText);
		that.enableLaunchExecutorButton(true);
	}).error(function(data) {
		$(".log-display-area").text(data.responseText);
		that.enableLaunchExecutorButton(true);
	});
}
IndexConfiguration.prototype.sendData = function() {
	var that = this;
	this.removeAllErrors();
	// TODO contrôle de validité des données

	var data = $("#Configuration").serialize();

	$.ajax({
		type : "POST",
		url : "/rest/configuration",
		data : data
	}).done(function(data) {
		that.enableSaveButton(false);
	}).error(
			function(data) {
				switch (data.status) {

				case 400:
					for ( var key in data.responseJSON) {
						$("input[name='" + key + "']").addClass(
								"ui-state-error");
						for ( var key2 in data.responseJSON[key]) {
							that.$container.alert("Champ " + key,
									data.responseJSON[key][key2],
									that.$container.AlertTypes.ERROR, false)
						}
					}
					break;

				default:
					break;
				}
			});
};
IndexConfiguration.prototype.removeAllErrors = function() {
	$("#Configuration").find('input').removeClass("ui-state-error");
};
IndexConfiguration.prototype.updateActiveDataArea = function() {

};
