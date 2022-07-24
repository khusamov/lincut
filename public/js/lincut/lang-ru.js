
/**
 * Русификация.
 */

Ext.onReady(function() {
	
	
	
	Ext.define("Ext.locale.ru.ux.grid.FiltersFeature", {
		override: "Ext.ux.grid.FiltersFeature",
		menuFilterText: "Фильтровать"
	});
	
	Ext.define("Ext.locale.ru.ux.grid.filter.DateFilter", {
		override: "Ext.ux.grid.filter.DateFilter",
		beforeText: "Начиная с даты",
		afterText: "Заканчивая датой",
		onText: "Ровно по дате",
		dateFormat: "Y-m-d"
	});
	
	// Ext.LoadMask

	Ext.require("Ext.LoadMask", function() {

		Ext.override(Ext.LoadMask, {
			msg: "Загрузка..."
		});
		
		Ext.override(Ext.view.AbstractView, {
			loadingText: "Загрузка..."
		});

	});
	
	
	
	
	// Ext.grid.feature.Grouping

	/*Ext.define("Ext.locale.ru.grid.feature.Grouping", {
		override : "Ext.grid.feature.Grouping",
		emptyGroupText : "(Пусто)",
		groupByText : "Группировать по этому полю",
		showGroupsText : "Отображать по группам"
	});*/
	
});


