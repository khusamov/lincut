
Ext.define("Lincut.model.Order", {
	extend: "Ext.data.Model",
	
	fields: [{
		name: "TaskID",
		type: "int"
	}, {
		name: "TaskAccountNum",
		type: "string"
	}, {
		name: "ClientName",
		type: "string"
	}, {
		name: "TaskDate",
		type: "date"
	}, {
		name: "TaskDateComplite",
		type: "date"
	}, {
		name: "TaskStatus",
		type: "string"
	}]

});


