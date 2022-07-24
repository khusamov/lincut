
Ext.define("Lincut.view.Orders", {
	extend: "Ext.window.Window",
	alias: "widget.windoworders",
	
	title: "Заказы",
	
	width: 1000,
	height: 500,
	border: false,
	
	layout: "border",
	
	defaults: {
		split: true
	},
	
	items: [{
		title: "Все заказы",
		region: "center",
		xtype: "gridorders",
		flex: 3
	}, {
		title: "Выбранные заказы для оптимизации",
		region: "east",
		xtype: "gridordersselected",
		flex: 2
	}]/*,
	
	tools: [{
		type: "refresh",
		tooltip: "Обновить окно",
		handler: function(event, toolel, header) {
			header.up("window").down("grid").getStore().load();
		}
	}]*/
	
});

