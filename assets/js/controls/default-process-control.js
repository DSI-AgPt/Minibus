function DefaultProcessControl($container, $wrapper, logDisplayer) {
	this.logDisplayer = logDisplayer;
	this.$container = $container;
	this.$wrapper = $wrapper;
}

DefaultProcessControl.prototype.initialize = function() {
	var that = this;
	// empêcher les boucles de redirection
	this.disableDataSend = true;

	$("form", that.$wrapper).submit(function() {
		return false;
	});

	$(".bouton-synchronisation", that.$wrapper).button({
		icons : {
			primary : 'ui-icon-refresh-special'
		},
		text : true
	}).click(function() {
		that.askExecution('sync');
	});
	$(".bouton-resynchronisation", that.$wrapper).button({
		icons : {
			primary : 'ui-icon-refresh-special'
		},
		text : true
	}).click(function() {
		that.askExecution('resync');
	});
	$(".bouton-controle", that.$wrapper).button({
		icons : {
			primary : 'ui-icon-list-check'
		},
		text : true
	}).click(function() {
		that.askExecution('control');
	});
	$(".bouton-force-execution-stop", that.$wrapper).button({
		icons : {
			primary : 'ui-icon-list-stop'
		},
		text : true
	}).click(function() {
		that.forceProcessExecutionStop();
	});
	$('.frequency-control', that.$wrapper).each(function(i, e) {
		$(e).cron({
			onChange : function() {
				if (!that.disableDataSend)
					that.sendProcessdata($(e).closest('.process-control'));
			},
			useGentleSelect : true
		});
	});
	$("input[name=shedule]+button", that.$wrapper).click(function(e) {
		that.refreshActivationControlsState();
		that.sendProcessdata($(e.target).closest('.process-control'));
	});
	$("input.active-control+button", that.$wrapper).click(function(e) {
		that.refreshActivationControlsState();
		that.sendProcessdata($(e.target).closest('.process-control'));
	});
	this.addExecutionList();
};
DefaultProcessControl.prototype.addExecutionList = function() {
	var that = this;
	var $form = that.$wrapper.find('form');
	var mode = $form.find('input[name=mode]').val();
	var forceSelectedId = false;
	var $alertRedirectionSelectExecution = $("input#select-execution");
	if ($alertRedirectionSelectExecution.length > 0) {
		forceSelectedId = $alertRedirectionSelectExecution.val();
		$alertRedirectionSelectExecution.remove();
	}
	that.executionList = new ExecutionList(this, this.$wrapper.find("details"),
			mode, that.logDisplayer, forceSelectedId);

};
DefaultProcessControl.prototype.forceOpenIfAlertRedirection = function() {
	var that = this;
	$alertRedirectionForceOpen = $("input#open-process");

	var idProcess = that.$wrapper.attr("id");

	if ($alertRedirectionForceOpen.length > 0) {
		if ("process-control-" + $alertRedirectionForceOpen.val() == idProcess) {
			that.$wrapper.find('summary').first().click();
			$alertRedirectionForceOpen.remove();
		}

	}

};
DefaultProcessControl.prototype.refreshActivationControlsState = function() {
	var that = this;
	$("input[name=shedule]", that.$wrapper).each(
			function(i, e) {
				$(e).closest("div").find(".frequency-control").css(
						"visibility",
						($(e).attr("checked") == "checked") ? "visible"
								: "hidden");
			});
	$("input.active-control", that.$wrapper).each(
			function(i, e) {
				var checked = ($(e).attr("checked") == "checked");
				$(e).closest('form').find(".bouton-synchronisation").button(
						checked ? 'enable' : 'disable');
				$(e).closest('form').find(".bouton-resynchronisation").button(
						checked ? 'enable' : 'disable');
				$(e).closest('form').find("input[name=shedule]+button").button(
						checked ? 'enable' : 'disable');
			})
};
DefaultProcessControl.prototype.getUrl = function(mode, type, endpoint, annee) {
	var url = "/rest/process/" + mode + "/" + type + "/" + endpoint;
	if (annee)
		url += "/" + annee;
	else
		url += "/" + 0;
	return url;
}
DefaultProcessControl.prototype.getExecutionUrl = function(mode, type,
		endpoint, annee) {
	var url = "/rest/process/execute/" + mode + "/" + type + "/" + endpoint;
	if (annee)
		url += "/" + annee;
	else
		url += "/" + 0;
	return url;
}
DefaultProcessControl.prototype.update = function() {
	var that = this;
	that.$container.enableControlBox(that.$wrapper, false);
	var $form = that.$wrapper.find('form');
	var mode = $form.find('input[name=mode]').val();
	var type = $form.find('input[name=type]').val();
	var endpoint = $form.find('input[name=endpoint]').val();
	var annee = that.$container.getSelectedYear();
	var url = that.getUrl(mode, type, endpoint, annee);
	$.ajax({
		type : "GET",
		url : url,
	}).done(function(data) {
		that.$container.enableControlBox(that.$wrapper, true);
		that.refresh(data);
		if (that.executionList.isInitialized()) {
			that.executionList.refresh(true);
		} else
			that.forceOpenIfAlertRedirection();
	});

}
DefaultProcessControl.prototype.sendProcessdata = function($processControlArea) {
	var that = this;
	that.$container.enableControlBox(that.$wrapper, false);
	var $form = that.$wrapper.find('form');
	var mode = $form.find('input[name=mode]').val();
	var type = $form.find('input[name=type]').val();
	var endpoint = $form.find('input[name=endpoint]').val();
	var annee = that.$container.getSelectedYear();
	var data = {
		'cron' : $form.find(".frequency-control").cron("value"),
		'active' : $form.find(".active-control").attr("checked") == "checked",
		'shedule' : $form.find("input[name=shedule][type=checkbox]").attr(
				"checked") == "checked",
	};
	$.ajax({
		type : "PUT",
		data : data,
		url : that.getUrl(mode, type, endpoint, annee)
	}).done(function(data) {
		that.$container.enableControlBox(that.$wrapper, true);
		that.refresh(data);
	}).error(
			// TODO factoriser
			function(data) {
				switch (data.status) {
				case 400:
					for ( var key in data.responseJSON) {
						that.$container.$container.alert(key,
								data.responseJSON[key],
								that.$container.$container.AlertTypes.ERROR,
								false)

					}
					break;

				default:
					// TODO gérer les autres erreurs
					break;
				}

			});
}
DefaultProcessControl.prototype.forceProcessExecutionStop = function() {
	var that = this;
	that.$container.enableControlBox(that.$wrapper, false);
	var $form = that.$wrapper.find('form');
	var mode = $form.find('input[name=mode]').val();
	var type = $form.find('input[name=type]').val();
	var endpoint = $form.find('input[name=endpoint]').val();
	var annee = that.$container.getSelectedYear();
	var data = {
		'force-execution-stop' : true
	};
	$.ajax({
		type : "PUT",
		data : data,
		url : that.getUrl(mode, type, endpoint, annee)
	}).done(function(data) {
		that.$container.enableControlBox(that.$wrapper, true);
		that.refresh(data);
	}).error(
			// TODO factorize
			function(data) {
				switch (data.status) {
				case 400:
					for ( var key in data.responseJSON) {
						that.$container.$container.alert(key,
								data.responseJSON[key],
								that.$container.$container.AlertTypes.ERROR,
								false)

					}
					break;

				default:
					// TODO gérer les autres erreurs
					break;
				}

			});
}
DefaultProcessControl.prototype.askExecution = function($mode) {
	var that = this;
	that.$container.enableControlBox(that.$wrapper, false);
	var $form = that.$wrapper.find('form');
	var mode = $form.find('input[name=mode]').val();
	var type = $form.find('input[name=type]').val();
	var endpoint = $form.find('input[name=endpoint]').val();
	var annee = that.$container.getSelectedYear();
	var data = {
		'mode' : $mode
	};
	$
			.ajax({
				type : "POST",
				data : data,
				url : that.getExecutionUrl(mode, type, endpoint, annee)
			})
			.done(
					function(data) {
						that.$container.enableControlBox(that.$wrapper, true);
						that.refresh(data);

						$
								.ajax({
									type : "POST",
									url : "/execution"
								})
								.error(
										// TODO factoriser
										function(data) {
											switch (data.status) {
											case 400:
												for ( var key in data.responseJSON) {
													that.$container.$container
															.alert(
																	key,
																	data.responseJSON[key],
																	that.$container.$container.AlertTypes.ERROR,
																	false)

												}
												break;

											default:
												// TODO gérer les autres erreurs
												break;
											}

										});
						setTimeout(function() {
							if (that.executionList.isInitialized())
								that.executionList.refresh(true);
						}, 1000);

					})
			.error(
					function(data) {
						switch (data.status) {
						case 400:
							for ( var key in data.responseJSON) {
								that.$container.$container
										.alert(
												key,
												data.responseJSON[key],
												that.$container.$container.AlertTypes.ERROR,
												false)

							}
							break;

						default:
							// TODO handle other errors
							break;
						}
						that.$container.enableControlBox(that.$wrapper, true);

					});
}
DefaultProcessControl.prototype.refresh = function(data) {
	var that = this;
	var $form = that.$wrapper.find('form');
	$form.find('input.active-control').check(data.active);
	$form.find('input[name=shedule][type=checkbox]').check(data.shedule);
	// avoid update infinite loop
	that.disableDataSend = true;
	$form.find(".frequency-control").cron("value", data.cron);
	that.refreshActivationControlsState();
	that.disableDataSend = false;
}
