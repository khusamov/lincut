
Ext.define("Lincut.store.orders.Selected", {
	extend: "Ext.data.Store",

	model: "Lincut.model.Order",
	pageSize: 500
		
		/*,
	
	proxy: {
		type: "rest",
		url: "/application/index/orders/",
		appendId: false,
		extraParams: {
			format: "json"
		},
		reader: {
			type: "json",
			root: "data"
		}
	}*/
	
});


