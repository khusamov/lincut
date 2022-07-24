
Ext.define("Lincut.view.grid.Jobs", {
	extend: "Ext.grid.Panel",
	alias: "widget.gridjobs",
	
	store: "Jobs",
	
	multiSelect: true,
	
	columns: [{
		dataIndex: "id",
		text: "Номер",
		flex: 1
	}, {
		dataIndex: "title",
		text: "Название",
		flex: 3
	/*}, {
		dataIndex: "restriction_mode",
		text: "Режим ограничения",
		flex: 1
	}, {
		dataIndex: "restriction_up_time",
		text: "Ограничение по времени",
		flex: 1
	}, {
		dataIndex: "restriction_up_count",
		text: "Ограничение числом переборов",
		flex: 1*/
	}, {
		dataIndex: "map_ready",
		text: "Карта раскроя",
		flex: 1,
		renderer: function(value) {
			return value ? "Готова" : "Не готова";
		}
	}],
	
	bbar: {
		xtype: "pagingtoolbar",
		store: "Jobs",
		displayInfo: true
	}

});


