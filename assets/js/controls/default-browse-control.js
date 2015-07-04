function DefaultBrowseControl($container, $wrapper, url) {
	this.$wrapper = $wrapper;
	this.$container = $container;
	this.$table = this.$wrapper.find("table");
	this.url = url;
}

DefaultBrowseControl.prototype.initialize = function() {
	var that = this;

	if (that.$table.length > 0) {
		var columnDefs = that.getColumnDefs();

		that.dataTable = that.$table.DataTable({

			"processing" : true,
			"serverSide" : true,
			"ajax" : {
				"url" : that.getCurrentUrl()
			},
			"columnDefs" : columnDefs
		});
	}

};
DefaultBrowseControl.prototype.getCurrentUrl = function() {
	var that = this;
	var parametreAnneeScolaire = that.$container.getSelectedYear();
	return "../" + that.url
			+ (parametreAnneeScolaire ? '/' + parametreAnneeScolaire : '')
}
DefaultBrowseControl.prototype.getColumnDefs = function() {
	var that = this;
	var columnDefs = new Array();
	that.$table.find("thead tr th").each(function(i, e) {
		columnDefs.push({
			"name" : $(e).attr("data-key-name"),
			"targets" : i
		})
	})
	return columnDefs;
}
DefaultBrowseControl.prototype.refresh = function(data) {
	var that = this;
}
DefaultBrowseControl.prototype.update = function(data) {
	var that = this;
	if (that.dataTable)
		that.dataTable.ajax.url(that.getCurrentUrl()).load();
}