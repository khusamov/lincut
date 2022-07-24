
Ext.define("Lincut.store.OrderStatuses", {
	extend: "Ext.data.Store",

	fields: ["id", "text"],
	
	remoteSort: true,
	remoteFilter: true,
	remoteGroup: true,
	pageSize: 100,
	
	proxy: {
		type: "rest",
		url: "/application/rest/ref/",
		extraParams: {
			ref: "order-status"
		},
		reader: {
			type: "json",
			root: "data"
		}
	}
	
});


