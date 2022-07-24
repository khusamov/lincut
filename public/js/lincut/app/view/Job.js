
Ext.define("Lincut.view.Job", {
	extend: "Ext.window.Window",
	alias: "widget.windowjob",
	
	title: "Сменное задание",
	
	width: 1000,
	height: 400,
	bodyPadding: "5px 0px 0px 0px",
	
	closeAction: "destroy",
	
	layout: "border",
	
	defaults: {
		split: true
	},
	
	items: [{
		xtype: "gridjoborders",
		title: "Выбранные заказы для оптимизации",
		region: "center",
		flex: 3
	}, {
		region: "east",
		flex: 2,
		xtype: "form",
		itemId: "options",
		title: "Параметры сменного задания",
		bodyPadding: 5,
		layout: "anchor",
		items: [{
			xtype: "textfield",
			name: "title",
			fieldLabel: "Название сменного задания",
			labelAlign: "top",
			anchor: "100%"
		/*}, {
			xtype: "combobox",
			name: "restriction_mode",
			fieldLabel: "Режим ограничения оптимизации",
			labelAlign: "top",
			anchor: "100%",
			value: "all",
			editable: false,
			queryMode: "local",
			valueField: "value",
			store: Ext.create("Ext.data.Store", {
				fields: [ "text", "value" ],
				data: [{
					text: "Ограничить по времени и по количеству переборов",
					value: "all"
				}, {
					text: "Ограничить только по времени",
					value: "time"
				}, {
					text: "Ограничить только по количеству переборов",
					value: "count"
				}]
			})
		}, {
			xtype: "numberfield",
			name: "restriction_up_time",
			fieldLabel: "Ограничение времени оптимизации, сек.",
			labelAlign: "top",
			anchor: "100%",
			value: 10,
			maxValue: 100,
			minValue: 10,
			step: 10
		}, {
			xtype: "numberfield",
			name: "restriction_up_count",
			fieldLabel: "Количество переборов",
			labelAlign: "top",
			anchor: "100%",
			value: 10000,
			maxValue: 500000,
			minValue: 10000,
			step: 10000*/
		}]
	}],
	
	tbar: {
		itemId: "tbar",
		items: [{
			itemId: "job",
			text: "Cменное задание",
			menu: {
				items: [{
					itemId: "save",
					text: "Сохранить изменения"
				}, {
					itemId: "delete",
					text: "Удалить задание и карту оптимизации"
				}, "-", {
					itemId: "close",
					text: "Закрыть"
				}]
			}
		}, {
			itemId: "map",
			text: "Карта оптимизации",
			menu: {
				items: [{
					itemId: "optimize",
					text: "Выполнить оптимизацию"
				}, "-", {
					itemId: "view",
					text: "Просмотр карты оптимизации",
					hrefTarget: "_blank"
				}, {
					itemId: "download",
					text: "Скачать карту оптимизации (*.pdf)"
				}]
			}
		}]
	} 
	
});

