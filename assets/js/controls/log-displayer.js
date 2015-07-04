function LogDisplayer($container, $wrapper) {
	var that = this;
	that.default_line_number = 35;
	that.logIdentifier = null;
	that.$container = $container;
	that.$wrapper = $wrapper;
	that.$displayArea = $wrapper.find(".log-display-area");
	that.$completeLogControl = $("#complete-log", $wrapper);
	that.$completeLogControl.next("button").click(function() {
		that.refreshLineNumber();
		if (that.owner && that.logIdentifier)
			that.askRefresh(that.owner, that.logIdentifier);
	})
	that.refreshLineNumber();
	that.owner = null;
}
LogDisplayer.prototype.askRefresh = function(requerer, logIdentifier) {
	var that = this;
	that.logIdentifier = logIdentifier;
	if (that.owner != requerer)
		return;
	$.ajax({
		type : "GET",
		url : that.getUrl(logIdentifier)
	}).done(function(data) {
		that.$displayArea.text(unescape(data));
	});
}
LogDisplayer.prototype.setOwner = function(logIdentifier) {
	this.owner = logIdentifier;
}
LogDisplayer.prototype.getUrl = function(logIdentifier) {
	var that = this;
	var url = "/rest/log/" + logIdentifier + "/lines/" + that.lineNumber;
	return url;
}
LogDisplayer.prototype.refreshLineNumber = function() {
	var that = this;
	if (that.$completeLogControl.attr("checked") == "checked")
		that.lineNumber = 'all';
	else
		that.lineNumber = that.default_line_number;
}