
// Подключаем дополнительные библиотеки

Ext.Loader.setPath("Ext.ux", "/js/extjs/examples/ux");

// Приложение

Ext.application({
	name: "Lincut",
	autoCreateViewport: true, 
	appFolder: "/js/lincut/app",
	controllers: ["Lincut", "Orders", "Jobs", "Mapcuts"]
});

// Корректировка основных классов под себя

Ext.require("Ext.window.Window", function() {

	Ext.override(Ext.window.Window, {
		cls: "border-radius-2px",
		shadow: false,
		ghost: false,
		border: false,
		bodyStyle: {
			backgroundColor: "transparent" // TODO сделать классом, чтобы легко можно было отключать
		},
		maximizable: true,
		constrainHeader: true, // без этой фигни перекрывается панель задач при максимизации окна
		closeAction: "hide",
		resizable: {
			dynamic: true
		},
		initComponent: function() {
			var me = this;
			me.callParent(arguments);
			if (!me.modal) {
				// Добавляем в панель вьюпорта, чтобы при максимизации окно не перекрывало панель задач
				Ext.ComponentQuery.query("viewport > panel#desktop")[0].add(me);
			}			
		}
	});

});

Ext.require("Ext.menu.Menu", function() {

	Ext.override(Ext.menu.Menu, {
		shadow: false
	});

});

Ext.require("Ext.tab.Panel", function() {

	Ext.override(Ext.tab.Panel, {
		plain: true,
	});

});

Ext.require("Ext.data.Store", function() {

	Ext.override(Ext.data.Store, {
		/**
		 * Сервисная функция.
		 * Определение, есть ли в хранилище какие-либо изменения, 
		 * а именно: новые записи, измененные или удаленные записи.
		 */
		checkCnangeRecords: function() {
			return this.getNewRecords().length > 0 || this.getUpdatedRecords().length > 0 || this.getRemovedRecords().length > 0;
		}
	});

});




/**
 * Исправление ошибки, когда фильтр в PHP-режиме выдает одинаковые ключи для value, 
 * в итоге все выбранные value пропадают, кроме последнего значения.
 * http://php.ru/forum/viewtopic.php?f=13&t=48424&p=385424#p385424
 */

Ext.require("Ext.ux.grid.filter.ListFilter", function() {

	Ext.override(Ext.ux.grid.filter.ListFilter, {
		phpMode: true
	});

});


