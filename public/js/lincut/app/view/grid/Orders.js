
// http://setup-okna.ru/images/js/khcrm/tickets/extgrid.js

Ext.define("Lincut.view.grid.Orders", {
	extend: "Lincut.view.grid.abstract.Orders",
	alias: "widget.gridorders",
	
	requires: ["Ext.ux.grid.FiltersFeature", "Lincut.store.OrderStatuses"],
	
	store: "Orders",
	
	multiSelect: true,
	
	
	/*
	loadMask: true,
	selModel: {
		pruneRemoved: false
	},
	
	//autoLoad: true,
	plugins: [{
		ptype: "bufferedrenderer",
		//variableRowHeight: true,
		//leadingBufferZone: 6000,
		//trailingBufferZone: 6000,
		//numFromEdge: 20,
		//scrollToLoadBuffer: 50,
		//synchronousRender: false
	}],
	*/
	
	
	
	features: [{
		ftype: "filters",
		filters: [{
			dataIndex: "TaskID",
			type: "string"
		}, {
			dataIndex: "TaskAccountNum",
			type: "string"
		}, {
			dataIndex: "ClientName",
			type: "string"
		}, {
			dataIndex: "TaskDate",
			type: "date"
		}, {
			dataIndex: "TaskDateComplite",
			type: "date"
		}, {
			dataIndex: "TaskStatus",
			type: "list",
			store: Ext.create("Lincut.store.OrderStatuses")
		}]
	}],
	
	bbar: {
		xtype: "pagingtoolbar",
		store: "Orders",
		displayInfo: true
	},
	
	viewConfig: {
		copy: true,
		plugins: {
			ptype: "gridviewdragdrop",
			dragGroup: "dragGroup1"
		}
	}

});


