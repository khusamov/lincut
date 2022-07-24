
Ext.define("Lincut.controller.Orders", {
	extend: "Ext.app.Controller",
	
	views: ["Orders", "grid.Orders", "grid.orders.Selected"],

	models: ["Order"],
	
	stores: ["Orders", "orders.Selected"],
	
	init: function() {
		this.control({
			"#start #orders": {
				click: this.startMenu_menuItemOrders_onClick
			},
			"windoworders #gotoOptimisation": {
				click: this.onButtonGotoOptimization
			},
			"windoworders #clear": {
				click: this.onButtonClear
			}
		});
	},
	
	windows: {},
	
	startMenu_menuItemOrders_onClick: function() {
		var me = this;
		if (!me.windows.orders) me.windows.orders = me.getView("Orders").create();
		me.windows.orders.show();
	},
	
	onButtonGotoOptimization: function() {
		var me = this;
		var selected = me.getStore("orders.Selected");
		me.getApplication().getController("Jobs").createNewJob(selected);
		selected.removeAll();
	},
	
	onButtonClear: function() {
		var me = this;
		me.getStore("orders.Selected").removeAll();
	}
	
});


