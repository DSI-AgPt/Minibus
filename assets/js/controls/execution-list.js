function ExecutionList($container, $wrapper, mode, logDisplayer,
		forceSelectedId) {
	var that = this;
	this.mode = mode;
	this.logDisplayer = logDisplayer;
	this.forcedSelectionId = forceSelectedId || false;
	that.$container = $container;
	that.$wrapper = $wrapper;
	that.init = false;
	$wrapper.on({
		'open.details' : function() {
			that.openList();

		},
		'close.details' : function() {
			that.disable();
		}
	});
	if ($("html").data("execution-lists-index") == undefined)
		$("html").data("execution-lists-index", new Array());
	$("html").data("execution-lists-index").push(this);
}

ExecutionList.prototype.openList = function() {
	if (this.init == false)
		this.initialize();
	this.disableOthers();
	this.disabled = false;
	this.logDisplayer.setOwner(this);
	this.refresh();
}
ExecutionList.prototype.disableOthers = function() {
	var others = $("html").data("execution-lists-index");
	var other;
	for (var int = 0; int < others.length; int++) {
		other = others[int];
		if (other == this)
			continue;
		other.disable();
	}

}
ExecutionList.prototype.initialize = function() {
	var that = this;
	that.$wrapper.find("table").styleTable({
		interactive : true
	});
	that.init = true;
}
ExecutionList.prototype.isInitialized = function() {
	return this.init;

}
ExecutionList.prototype.refresh = function(forceSelectionOfNewLine) {
	if (this.disabled)
		return;
	var that = this;
	var annee = that.$container.$container.getSelectedYear();
	$.ajax(
			{
				type : "GET",
				url : that.getUrl(that.mode, that.$wrapper.attr("data-type"),
						that.$wrapper.attr("data-endpoint"), annee)
			}).done(function(data) {
		that.refreshList(data, forceSelectionOfNewLine || false);
	});
}
ExecutionList.prototype.refreshList = function(data, forceSelectionOfNewLine) {
	var that = this;
	$container = that.$wrapper.find('tbody');
	$container.find('.ligne-execution').remove();
	var length = data.length;
	var $firstRow = false;
	var $selectedRow = false;
	for (var int = 0; int < length; int++) {
		$row = $(document.createElement('tr')).addClass("ligne-execution")
				.attr("data-id", data[int].id).attr("data-logidentifier",
						data[int].logidentifier).attr("data-state",
						data[int].state);
		if (that.forcedSelectionId && that.forcedSelectionId == data[int].id) {
			that.selectedExecutionId = that.forcedSelectionId;
			forceSelectionOfNewLine = false;
		}
		var $cell = $(document.createElement('td')).text(data[int].id);
		$row.append($cell);
		$cell = $(document.createElement('td')).text(data[int].state);
		$row.append($cell);
		$cell = $(document.createElement('td')).text(data[int].information);
		$row.append($cell);
		$cell = $(document.createElement('td')).text(data[int].logidentifier);
		$row.append($cell);
		$cell = $(document.createElement('td')).text(data[int].start_date);
		$row.append($cell);
		$cell = $(document.createElement('td'));
		$row.append($cell);
		$container.append($row);
		if (!$firstRow)
			$firstRow = $row;
		if (data[int].id == that.selectedExecutionId)
			$selectedRow = $row;

	}

	if (!$selectedRow || forceSelectionOfNewLine)
		$selectedRow = $firstRow;
	that.$wrapper.find('tr#empty-line').toggle(length == 0);

	if ($selectedRow) {
		$container.find('tr').bind('click',
				$.proxy(that.handleRowSelection, that));
		that.selectRow($selectedRow);

		that.logDisplayer.askRefresh(that, $selectedRow
				.attr('data-logidentifier'));
		that.launchPoll($selectedRow);

	}

}
ExecutionList.prototype.launchPoll = function($selectedRow) {
	var that = this;
	if ($selectedRow.attr("data-state") == "Running") {
		that.timeout = setTimeout(function() {
			that.refresh()
		}, 1000);

	}
}
ExecutionList.prototype.disable = function() {
	var that = this;
	if (that.timeout)
		clearTimeout(that.timeout);
	this.disabled = true;

}
ExecutionList.prototype.handleRowSelection = function(event) {
	var that = this;
	this.disableOthers();
	this.disabled = false;
	$selectedRow = $(event.currentTarget);
	that.selectRow($selectedRow);
	var logIdentifier = $selectedRow.attr("data-logidentifier");
	that.logDisplayer.setOwner(that);
	that.logDisplayer.askRefresh(that, logIdentifier);
	that.launchPoll($selectedRow);
}
ExecutionList.prototype.selectRow = function($selectedRow) {
	var that = this;
	$('html').find('tr.ligne-execution').removeClass("ui-state-highlight");
	this.selectedExecutionId = $selectedRow.attr("data-id");
	$running = $selectedRow.attr("data-state") == "Running";
	$selectedRow.addClass("ui-state-highlight");
	$selectedRow.find('td').removeClass("ui-state-hover");

}

ExecutionList.prototype.getUrl = function(mode, type, endpoint, annee) {
	var url = "/rest/process/" + mode + "/" + type + "/" + endpoint;
	if (annee)
		url += "/" + annee;
	else
		url += "/" + 0;
	url += "/execution"
	return url;
}