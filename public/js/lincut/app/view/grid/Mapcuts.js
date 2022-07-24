
Ext.define("Lincut.view.grid.Mapcuts", {
	extend: "Ext.grid.Panel",
	alias: "widget.gridmapcuts",
	
	store: "Mapcuts",
	
	columns: [{
		dataIndex: "id",
		text: "Номер",
		flex: 1
	}, {
		dataIndex: "title",
		text: "Название",
		flex: 3
	}],
	
	bbar: {
		xtype: "pagingtoolbar",
		store: "Mapcuts",
		displayInfo: true
	}

});


