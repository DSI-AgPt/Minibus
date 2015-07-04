function UserLogin($container) {
	this.$container = $container;
}
UserLogin.prototype.initialize = function() {
	$("#main-menu li+li+li").hide();
};
UserLogin.prototype.updateActiveDataArea = function() {

};
