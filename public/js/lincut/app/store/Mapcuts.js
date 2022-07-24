
Ext.define("Lincut.store.Mapcuts", {
	extend: "Ext.data.Store",

	model: "Lincut.model.Job",
	
	autoLoad: true,
	pageSize: 300,
	
	filters: [function(record) { return record.get("map_ready"); }],
	
	proxy: {
		type: "rest",
		url: "/application/rest/jobs/",
		reader: {
			type: "json",
			root: "data"
		}
	}
	
});


