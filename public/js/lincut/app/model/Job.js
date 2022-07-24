
Ext.define("Lincut.model.Job", {
	extend: "Ext.data.Model",
	
	fields: [{
		name: "id"
	}, {
		name: "title",
		type: "string",
		defaultValue: "Безымянное сменное задание"
	/*}, {
		name: "restriction_mode",
		type: "string",
		defaultValue: "all"
	}, {
		name: "restriction_up_time",
		type: "int",
		defaultValue: 10
	}, {
		name: "restriction_up_count",
		type: "int",
		defaultValue: 10000*/
	}, {
		name: "map_ready",
		type: "boolean",
		defaultValue: false
	}]

});


