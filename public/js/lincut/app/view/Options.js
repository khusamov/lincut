
Ext.define("Lincut.view.Options", {
	extend: "Ext.window.Window",
	alias: "widget.windowoptions",
	
	title: "Настройки программы",
	
	width: 500,
	
	constrain: true,
	constrainHeader: false,
	modal: true,
	maximizable: false,
	resizable: false,
	
	layout: "fit",
	
	items: [{
		xtype: "form",
		border: false,
		itemId: "options",
		bodyStyle: {
			backgroundColor: "transparent" 
		},
		items: [{
			xtype: "tabpanel",
			items: [{
				title: "Основные настройки",
				layout: "anchor",
				bodyPadding: 10,
				items: [{
					xtype: "combobox",
					name: "restriction_mode",
					fieldLabel: "Режим ограничения оптимизации",
					labelAlign: "top",
					anchor: "100%",
					editable: false,
					queryMode: "local",
					valueField: "value",
					store: Ext.create("Ext.data.Store", {
						fields: ["text", "value"],
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
					maxValue: 100,
					minValue: 10,
					step: 10
				}, {
					xtype: "numberfield",
					name: "restriction_up_count",
					fieldLabel: "Количество переборов",
					labelAlign: "top",
					anchor: "100%",
					maxValue: 500000,
					minValue: 10000,
					step: 10000
				}, {
					xtype: "numberfield",
					name: "saw",
					fieldLabel: "Ширина пила, мм",
					labelAlign: "top",
					anchor: "100%",
					maxValue: 20,
					minValue: 0,
					step: 1
				}, {
					xtype: "numberfield",
					name: "waste",
					fieldLabel: "Отход с краев хлыста, мм",
					labelAlign: "top",
					anchor: "100%",
					maxValue: 100,
					minValue: 0,
					step: 5
				}, {
					xtype: "textfield",
					name: "wkhtmltopdf_path",
					fieldLabel: "Путь к программе wkhtmltopdf.exe",
					labelAlign: "top",
					anchor: "100%"
				}]
			}, {
				title: "Подключение к базе данных WinCAD",
				layout: "anchor",
				bodyPadding: 10,
				items: [{
					xtype: "textfield",
					name: "db_wcad_servername",
					fieldLabel: "Имя сервера",
					labelAlign: "top",
					anchor: "100%"
				}, {
					xtype: "textfield",
					name: "db_wcad_database",
					fieldLabel: "Имя базы данных",
					labelAlign: "top",
					anchor: "100%"
				}, {
					xtype: "textfield",
					name: "db_wcad_username",
					fieldLabel: "Логин",
					labelAlign: "top",
					anchor: "100%"
				}, {
					xtype: "textfield",
					name: "db_wcad_password",
					fieldLabel: "Пароль",
					labelAlign: "top",
					anchor: "100%"
				}]
			}]
		}]
	}],
	
	buttons: [{
		itemId: "save",
		text: "Сохранить"
	}, {
		itemId: "cancel",
		text: "Отмена",
		handler: function() {
			this.up("window").close();
		}
	}]
	
});

