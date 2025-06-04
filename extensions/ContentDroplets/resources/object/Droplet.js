ext.contentdroplets.object.Droplet = function ( cfg ) {
	this.key = cfg.key;
	this.name = cfg.name || '';
	this.description = cfg.description || '';
	this.icon = cfg.icon;
	this.categories = cfg.categories || [];
	this.content = cfg.content;
	this.veCommand = cfg.veCommand;

	this.preload = cfg.preload || false;
};

OO.initClass( ext.contentdroplets.object.Droplet );

ext.contentdroplets.object.Droplet.prototype.getKey = function () {
	return this.key;
};

ext.contentdroplets.object.Droplet.prototype.getName = function () {
	return this.name;
};

ext.contentdroplets.object.Droplet.prototype.getDescription = function () {
	return this.description;
};

ext.contentdroplets.object.Droplet.prototype.getIcon = function () {
	return this.icon;
};

ext.contentdroplets.object.Droplet.prototype.getCategories = function () {
	return this.categories;
};

ext.contentdroplets.object.Droplet.prototype.getContent = function () {
	return this.content;
};

ext.contentdroplets.object.Droplet.prototype.getSearchText = function () {
	return this.getName().toLocaleLowerCase() + '|' +
		this.getDescription().toLocaleLowerCase();
};

ext.contentdroplets.object.Droplet.prototype.getVeInsertCommand = function () {
	return this.veCommand;
};
