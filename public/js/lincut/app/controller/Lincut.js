
/**
 * Общий контроллер.
 * Окно настроек, о программе и пр.
 */

Ext.define("Lincut.controller.Lincut", {
	extend: "Ext.app.Controller",
	
	views: ["About", "Options"],
	
	models: ["Options"],
	
	init: function() {
		this.control({
			"#start #options": {
				click: this.startMenu_menuItemOptions_onClick
			},
			"#start #about": {
				click: this.startMenu_menuItemAbout_onClick
			}
		});
	},
	
	windows: {},
	
	startMenu_menuItemOptions_onClick: function() {
		var me = this;
		me.getWindowOptions().show();
	},
	
	startMenu_menuItemAbout_onClick: function() {
		var me = this;
		if (!me.windows.about) me.windows.about = me.getView("About").create();
		me.windows.about.show();
	},
	
	getWindowOptions: function() {
		var me = this;
		if (!me.windows.options) me.windows.options = me.getView("Options").create();
		var win = me.windows.options;

		var form = win.down("form").getForm();
		
		win.on("show", function() {
			form.reset();
			var loading = win.setLoading("Загрузка настроек. Подождите...");
			me.getModel("Options").load(1, {
				success: function(options) {
					form.loadRecord(options);
					loading.hide();
				}
			});
		});
		
		win.down("#save").on("click", function() {
			form.updateRecord();
			var options = form.getRecord();
			var loading = win.setLoading("Сохранение настроек. Подождите...");
			options.save({
				success: function(options) {
					loading.hide();
					win.close();
				}
			});
		});
		
		return win;
	}
	
});


