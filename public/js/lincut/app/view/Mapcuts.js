
/**
 * Окно со списком сменных заданий, у которых карта раскроя готова.
 */

Ext.define("Lincut.view.Mapcuts", {
	extend: "Ext.window.Window",
	alias: "widget.windowmapcuts",
	
	title: "Карты раскроя",
	
	width: 500,
	height: 300,
	border: false,
	
	layout: "fit",
	
	items: [{
		region: "center",
		xtype: "gridmapcuts"
	}]
	
});

