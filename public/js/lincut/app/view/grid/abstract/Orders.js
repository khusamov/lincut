
Ext.define("Lincut.view.grid.abstract.Orders", {
	extend: "Ext.grid.Panel",
	
	columns: [{
		dataIndex: "TaskAccountNum",
		text: "Номер заказа",
		flex: 1
	}, {
		dataIndex: "ClientName",
		text: "Клиент",
		flex: 2
	}, {
		dataIndex: "TaskDate",
		renderer: Ext.util.Format.dateRenderer("d.m.Y"),
		text: "Создано",
		flex: 1
	}, {
		dataIndex: "TaskDateComplite",
		renderer: Ext.util.Format.dateRenderer("d.m.Y"),
		text: "Завершится",
		flex: 1
	}, {
		dataIndex: "TaskStatus",
		text: "Статус",
		flex: 1
	}]

});


