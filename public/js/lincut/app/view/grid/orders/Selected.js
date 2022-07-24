
Ext.define("Lincut.view.grid.orders.Selected", {
	extend: "Lincut.view.grid.abstract.Orders",
	alias: "widget.gridordersselected",
	
	store: "orders.Selected",
	
	bbar: ["->", {
		itemId: "clear",
		text: "Очистить"
	}, {
		itemId: "gotoOptimisation",
		text: "На оптимизацию"
	}],
	
	viewConfig: {
		plugins: {
			ptype: "gridviewdragdrop",
			dropGroup: "dragGroup1",
			enableDrag: false
		},
		listeners: {
			drop: function(node, data, dropRec, dropPosition) {
				//console.log(data);
			}
		}
	}

});


