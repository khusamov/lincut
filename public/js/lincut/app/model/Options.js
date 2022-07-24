
Ext.define("Lincut.model.Options", {
	extend: "Ext.data.Model",
	
	fields: [{
		name: "db_wcad_servername",
		type: "string"
	}, {
		name: "db_wcad_database",
		type: "string"
	}, {
		name: "db_wcad_username",
		type: "string"
	}, {
		name: "db_wcad_password",
		type: "string"
	}, {
		name: "wkhtmltopdf_path",
		type: "string"
	}, {
		name: "restriction_mode",
		type: "string"
	}, {
		name: "restriction_up_time",
		type: "int"
	}, {
		name: "restriction_up_count",
		type: "int"
	}, {
		name: "saw",
		type: "int"
	}, {
		name: "waste",
		type: "int"
	}],
	
	proxy: {
		type: "rest",
		url: "/application/rest/config/",
		reader: {
			type: "json",
			root: "data",
			writeRecordId: false
		}
	}

});


