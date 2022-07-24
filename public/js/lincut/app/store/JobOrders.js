
Ext.define("Lincut.store.JobOrders", {
	extend: "Ext.data.Store",

	model: "Lincut.model.Order",
	
	pageSize: 500,
	
	proxy: {
		type: "rest",
		url: "/application/rest/job/orders/",
		reader: {
			type: "json",
			root: "data"
		}
	}
	
});


