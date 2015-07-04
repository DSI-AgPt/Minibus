function IndexAlerts($container) {
	this.$container = $container;
}
IndexAlerts.prototype.initialize = function() {
	var that = this;
	that.$selectProcess = $("select#alerts-structure");
	that.$selectProcess.multiselect({
		'minWidth' : 400,
		'checkAllText' : $("input#checkAllTextSelectProcess.i18n").val(),
		'uncheckAllText' : $("input#uncheckAllTextSelectProcess.i18n").val(),
		'noneSelectedText' : $("input#noneSelectedTextSelectProcess.i18n")
				.val(),
		'selectedText' : $("input#selectedTextSelectProcess.i18n").val(),
		'close' : $.proxy(that.refreshAlertsList, that)
	});
	that.$selectLevels = $("select#alerts-levels");
	that.$selectLevels
			.multiselect({
				'minWidth' : 400,
				'checkAllText' : $("input#checkAllTextSelectLevels.i18n").val(),
				'uncheckAllText' : $("input#uncheckAllTextSelectLevels.i18n")
						.val(),
				'noneSelectedText' : $(
						"input#noneSelectedTextSelectLevels.i18n").val(),
				'selectedText' : function(a, b, selecteds) {
					var text = "";
					for ( var key in selecteds) {
						if (text != "")
							text += ", ";
						$input = $(selecteds[key])
						var val = $input.val();
						var label = that.$selectLevels.find(
								"option[value=" + val + "]").text();
						text += label;
					}
					return text;
				},
				'close' : $.proxy(that.refreshAlertsList, that)
			});
	that.$selectLevels.multiselect("checkAll");
	$("span.delete-alerts").button({
		icons : {
			primary : 'ui-icon-delete-mini'
		},
		text : false
	}).click($.proxy(that.removeAlerts, this));
	var $selectAllAlerts = $(".select-all-alerts");
	$selectAllAlerts.closest('label').click(
			function(event, ui) {
				var checked = ($selectAllAlerts.attr("checked") == "checked");
				$("div.alert input[type=checkbox]", "div#alert-list-container")
						.each(function(i, e) {
							if (checked) {
								$(e).prop("checked", "checked");
							} else {
								$(e).prop('checked', false);
							}
						})
			});

	that.makeUserWait(true);

};
IndexAlerts.prototype.makeUserWait = function(bool) {
	var that = this;
	$("#alert-list-preloader").toggle(bool);
	$("label.select-all-container").toggle(!bool);
	$("span.delete-alerts").toggle(!bool);
	$("span.nb-alerts").toggle(!bool);
}
IndexAlerts.prototype.removeAlerts = function() {
	var that = this;
	var selectedAlertIds = new Array();
	var deleteAll = $(".select-all-alerts").attr("checked") == "checked";
	var url = "/rest/alerts"
	if (!deleteAll)
		$("div.alert input[type=checkbox]", "div#alert-list-container").each(
				function(i, e) {
					if ($(e).is(":checked"))
						selectedAlertIds.push(parseInt($(e).val()))

				});
	if (deleteAll) {
		var selectedProcess = that.$selectProcess.val();
		var selectedLevels = that.$selectLevels.val();
		url = url + '?process=' + selectedProcess + '&levels=' + selectedLevels;
	} else
		url = url + "/" + JSON.stringify(selectedAlertIds);
	$.ajax({
		type : "DELETE",
		url : url
	}).done(function(data) {
		console.log(data);
		that.refreshAlertsList();
	}).error(function(data) {
		// TODO implémenter
	});
};
IndexAlerts.prototype.updateActiveDataArea = function() {
	var that = this;
	that.makeUserWait(true);
	that.refreshAlertStructure();

};
IndexAlerts.prototype.refreshAlertStructure = function() {
	var that = this;
	var selectedDataType = this.$container.getSelectedDataType();
	that.getAlertStructure(selectedDataType);

};
IndexAlerts.prototype.refreshAlertsList = function() {
	var that = this;
	var selectedProcess = that.$selectProcess.val();
	var selectedLevels = that.$selectLevels.val();
	that.getAlerts(selectedProcess, selectedLevels);

};
IndexAlerts.prototype.getAlertStructure = function(selectedDataType) {
	var that = this;
	$.ajax({
		type : "GET",
		url : "/rest/alerts/" + selectedDataType + "/structure",
	}).done(
			function(data) {

				$("#alerts-structure-imports").empty();
				$("#alerts-structure-exports").empty();
				var sources = data['sources'];
				var cibles = data['cibles'];
				for ( var key in sources)
					$("#alerts-structure-imports").append(
							$(document.createElement('option')).text(key).attr(
									"value", sources[key]));
				for ( var key in cibles)
					$("#alerts-structure-exports").append(
							$(document.createElement('option')).text(key).attr(
									"value", cibles[key]));
				that.$selectProcess.multiselect("refresh").multiselect(
						"checkAll");
				that.refreshAlertsList();
			}).error(function(data) {
		// TODO
		// implémenter

	});

};
IndexAlerts.prototype.getAlerts = function(selectedProcess, selectedLevels) {
	var that = this;
	$.ajax({
		type : "GET",
		url : "/rest/alerts",
		data : {
			'process' : selectedProcess,
			'levels' : selectedLevels,
		}
	}).done($.proxy(that.refreshAlertsListView, that)).error(function(data) {
		// TODO implémenter
	});

};
IndexAlerts.prototype.unCheckAllAlertsCheckBox = function() {
	$(".select-all-alerts").removeAttr("checked").checkbox("refresh");
};
IndexAlerts.prototype.refreshAlertsListView = function(alertList) {
	var that = this;
	var $alertListeContainer = $("#alert-list-container");
	$alertListeContainer.empty();
	that.unCheckAllAlertsCheckBox();
	var alertData, $alert, $titleContainer, $checkBox, identifiant, $processLink, $colon, $executionLink, $messageContainer;
	var $alertModel = $(document.createElement("div")).addClass("alert");
	var $modelLink = $(document.createElement("a"));
	var $colonModel = $(document.createElement("span")).text(" : ");
	var $titleContainerModel = $(document.createElement("h3"));
	var $checkBoxModel = $(document.createElement("input")).attr("type",
			"checkbox");
	var $messageContainerModel = $(document.createElement("span")).addClass(
			"ui-widget");
	var nbAlertes = alertList.length;
	// TODO internationaliser
	$("span.nb-alerts").text(
			"" + (nbAlertes == 0 ? "Aucune" : nbAlertes)
					+ (nbAlertes > 1 ? " alertes" : " alerte"));
	for ( var key in alertList) {
		alertData = alertList[key];
		$alert = $alertModel.clone();

		switch (alertData.level) {
		case "WARNING":
			$alert.addClass("ui-widget-content");
			break;
		case "ERROR":
			$alert.addClass("ui-state-highlight");
			break;
		case "ALERT":
			$alert.addClass("ui-state-error");
			break;
		}
		identifiant = alertData.mode + "-" + alertData.type + "-"
				+ alertData.endpoint;
		$processLink = $modelLink.clone().attr(
				"href",
				"alerts-redirection/process/" + alertData.mode + "/"
						+ alertData.type + "-" + alertData.endpoint).text(
				identifiant);
		$colon = $colonModel.clone();
		$executionLink = $modelLink.clone().attr(
				"href",
				"alerts-redirection/process/" + alertData.mode + "/"
						+ alertData.type + "-" + alertData.endpoint
						+ "/execution/" + alertData.execution_id).text(
				"n° " + alertData.execution_id + " (" + alertData.date + ") ");
		$titleContainer = $titleContainerModel.clone();
		$checkBox = $checkBoxModel.clone().attr("value", alertData.id);
		$titleContainer.append($processLink).append($colon).append(
				$executionLink);
		$messageContainer = $messageContainerModel.clone().text(
				alertData.message);
		$alert.append($titleContainer).append($messageContainer).append(
				$checkBox);
		$alertListeContainer.append($alert);
	}
	$("div.alert input[type=checkbox]", "div#alert-list-container").change(
			$.proxy(that.unCheckAllAlertsCheckBox, that));
	that.makeUserWait(false);
};