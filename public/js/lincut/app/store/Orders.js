
Ext.define("Lincut.store.Orders", {
	extend: "Ext.data.Store",

	model: "Lincut.model.Order",
	
	autoLoad: true,
	remoteSort: true,
	remoteFilter: true,
	remoteGroup: true,
	pageSize: 500,
	
	//buffered: true,
	//leadingBufferZone: 300,
	//trailingBufferZone: 400,
	//purgePageCount: 5,
	
	
	
	proxy: {
		type: "rest",
		url: "/application/rest/orders/",
		reader: {
			type: "json",
			root: "data"
		}
	}
	
});


