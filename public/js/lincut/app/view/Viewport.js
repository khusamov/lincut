
/**
 * Основа рабочего стола
 */

Ext.define("Lincut.view.Viewport", {
    extend: "Ext.container.Viewport",

    layout: "fit",
    
    items: [{
  		itemId: "desktop",
    	xtype: "panel",
      border: false,
      
    	tbar: [{
    		itemId: "start",
    		text: "Старт",
    		menu: {
    			items: [{
    				itemId: "orders",
		    		text: "Заказы"
    			}, {
    				itemId: "jobs",
		    		text: "Сменные задания"
    			}, {
    				itemId: "mapcuts",
		    		text: "Карты раскроя"
    			},
    				/*text: "Настройки",
    				hideOnClick: false,
    				menu: {
    					items: [{
    			    		itemId: "db",
    						text: "Подключения к базам данных"
    					}, {
    						itemId: "configsets",
    						text: "Конфигурационные файлы"
    					}]
    				}*/
		    	"-", {
		    		itemId: "options",
		    		text: "Настройки программы"
    			}, {
		    		itemId: "about",
		    		text: "О программе"
    			}, "-", {
		    		itemId: "exit",
		    		text: "Выход",
		    		handler: function() {
		    			window.close();
		    		}
    			}]
    		}
    	/*}, "->", {
    		xtype: "tbtext",
    		text: "Линейный раскрой, версия 1.00"
    	}, "-", {
    		xtype: "tbtext",
    		text: "Sencha Ext JS, версия " + Ext.getVersion()*/
    	}]
    
    }]
    
});

