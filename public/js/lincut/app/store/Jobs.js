
Ext.define("Lincut.store.Jobs", {
	extend: "Ext.data.Store",

	model: "Lincut.model.Job",
	
	autoLoad: true,
	pageSize: 300,
	
	proxy: {
		type: "rest",
		url: "/application/rest/jobs/",
		reader: {
			type: "json",
			root: "data"
		}
	}
	
});


