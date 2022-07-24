
/**
 * Окно со списком всех сменных заданий.
 */

Ext.define("Lincut.view.Jobs", {
	extend: "Ext.window.Window",
	alias: "widget.windowjobs",
	
	title: "Сменные задания",
	
	width: 750,
	height: 400,
	border: false,
	
	layout: "fit",
	
	items: [{
		region: "center",
		xtype: "gridjobs"
	}]
	
});

