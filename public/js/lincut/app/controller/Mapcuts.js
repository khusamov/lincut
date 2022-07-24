
Ext.define("Lincut.controller.Mapcuts", {
	extend: "Ext.app.Controller",
	
	views: ["Mapcuts", "grid.Mapcuts"],
	
	stores: ["Mapcuts"],
	
	init: function() {
		this.control({
			"#start #mapcuts": {
				click: this.startMenu_menuItemMapcuts_onClick
			}
		});
	},
	
	windows: {},
	
	startMenu_menuItemMapcuts_onClick: function() {
		var me = this;
		me.getWindowMapcuts().show();
	},
	
	getWindowMapcuts: function() {
		var me = this;
		if (!me.windows.mapcuts) me.windows.mapcuts = me.getView("Mapcuts").create();
		var win = me.windows.mapcuts;
		
		var menu = Ext.create("Ext.menu.Menu", {
			items: [{
				cls: "single default",
				text: "Просмотр карты оптимизации",
				handler: me.mapcutsWindow_ContextMenu_view,
				scope: me
			}, {
				cls: "multi",
				text: "Скачать карту оптимизации (*.pdf)",
				handler: me.mapcutsWindow_ContextMenu_download,
				scope: me
			}]
		});

		win.down("grid").on("itemcontextmenu", function(grid, record, item, index, event) {
			menu.showAt(event.getXY());
			event.stopEvent();
		});
		win.down("grid").on("itemdblclick", function(grid, record, item, index, event) {
			menu.items.each(function(item) {
				if (item.hasCls("default")) item.handler.call(me);
			});
		});
		
		return win;
	},
	
	mapcutsWindow_ContextMenu_view: function() {
		var me = this;
		var jobId = me.windows.mapcuts.down("gridmapcuts gridview").getSelectionModel().selected.first().get("id");
		var url = "/application/mapcut/viewmap/?job_id=";
		window.open(url + jobId);
	},
	
	mapcutsWindow_ContextMenu_download: function() {
		var me = this;
		var jobId = me.windows.mapcuts.down("gridmapcuts gridview").getSelectionModel().selected.first().get("id");
		var url = "/application/mapcut/getpdfmap/?job_id=";
		window.open(url + jobId);
	}
	
});


