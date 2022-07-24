
Ext.define("Lincut.controller.Jobs", {
	extend: "Ext.app.Controller",
	
	views: ["Jobs", "grid.Jobs", "Job", "grid.JobOrders"],

	models: ["Order", "Job"],
	
	stores: ["Jobs"],
	
	init: function() {
		this.control({
			"#start #jobs": {
				click: this.startMenu_menuItemJobs_onClick
			}
		});
	},
	
	/**
	 * Массив статических окон контроллера.
	 */
	windows: {},
	
	/**
	 * Массив открытых заданий (динамические окна с заданиями).
	 * При закрытии окна (окно уничтожается), задание из массива удаляется.
	 */
	jobs: [],
	
	startMenu_menuItemJobs_onClick: function() {
		var me = this;
		if (!me.windows.jobs) {
			me.windows.jobs = me.getView("Jobs").create();
			
			var window = me.windows.jobs;
			
			var menu = Ext.create("Ext.menu.Menu", {
				items: [{
					cls: "single default",
					text: "Открыть",
					handler: me.jobsWindow_ContextMenu_open,
					scope: me
				}, {
					cls: "multi",
					text: "Удалить",
					handler: me.jobsWindow_ContextMenu_delete,
					scope: me
				}]
			});
			
			window.down("grid").on("itemcontextmenu", function(grid, record, item, index, event) {
				menu.showAt(event.getXY());
				event.stopEvent();
			});
			window.down("grid").on("itemdblclick", function(grid, record, item, index, event) {
				menu.items.each(function(item) {
					if (item.hasCls("default")) item.handler.call(me);
				});
			});
			
		}
		me.windows.jobs.show();
	},
	
	jobsWindow_ContextMenu_open: function() {
		var me = this;
		var selection = me.windows.jobs.down("grid").getSelectionModel().getSelection();
		me.openJob(selection[0].get("id"));
	},
	
	jobsWindow_ContextMenu_delete: function() {
		var me = this;
		var selection = me.windows.jobs.down("grid").getSelectionModel().getSelection();
		Ext.Msg.show({
			title: "Удаление",
			msg: "Удалить?",
			buttons: Ext.Msg.YESNO,
			fn: function(button) {
				if (button == "yes") me.deleteJobs(selection);
			}
		});
	},
	
	/**
	 * Удаление заданий.
	 * @param selection Ext.data.Model / Ext.data.Model[] 
	 * где Number это индекс записи.
	 */
	deleteJobs: function(selection) {
		var me = this;
		if (selection instanceof Ext.data.Model) {
			selection = [selection];
		}
		// Закрываем окна, окна браузеров с открытыми картами
		// Составляем список заданий, которые сохранены - их и будем удалять
		// Не сохраненные задания не удаляем
		var _selection = [];
		Ext.Array.each(selection, function(select) {
			var found = false;
			Ext.Array.each(me.jobs, function(win) {
				if (win._job) {
					if (win._job.savestate == "save") {
						if (win._job.id == select.get("id")) {
							if (win._job.viewmap) win._job.viewmap.close();
							if (win._job.getpdfmap) win._job.getpdfmap.close();
							win.close();
						}
						_selection.push(select);
						found = true;
						return false;
					}
				}
			});
			if (!found) _selection.push(select);
		});
		selection = _selection;
		// Удаляем выбранные задания
		me.getStore("Jobs").remove(selection);
		me.getStore("Jobs").sync();		
	},
	
	/**
	 * Открыть существующее задание.
	 * Окно сразу показывается.
	 * @param id
	 */
	openJob: function(id) {
		var me = this;
		var window = null;

		// Ищем открытое окно (возможно задание уже открыто)
		Ext.Array.each(me.jobs, function(_window) {
			if (_window._job && _window._job.id == id) {
				window = _window;
				return false;
			}
		});
		
		// Если не нашли, то открываем заново
		if (!window) {
			// Создать окно со сменным заданием
			window = me.createJobWindow("update", id);
			// Загружаем заказы
			window._job.storeJobOrders.getProxy().setExtraParam("job_id", id);
			window._job.storeJobOrders.load();
		}
		
		// Показать окно
		window.show();
	},
	
	/**
	 * Создать новое сменное задание.
	 * На вход подается коллекция заказов.
	 * Окно сразу показывается.
	 * @param Ext.data.Store orders
	 */
	createNewJob: function(orders) {
		var me = this;
		
		// Создать окно со сменным заданием
		var window = me.createJobWindow();
		
		// Наполнить хранилище выбранными заказами
		var fields = me.getModel("Order").getFields();
		orders.data.each(function(order, index, total) {
			var data = {};
			Ext.Array.each(fields, function(field) {
				data[field.name] = order.get(field.name);
			});
			window._job.storeJobOrders.add(data);
		});
		
		// Показать окно
		window.show();
	},
	
	
	/**
	 * Создать окно "Сменное задание".
	 * @param mode (по умолчанию = insert)
	 * @param id (номер задания, если mode = update)
	 * @returns Ext.window.Window
	 */
	createJobWindow: function(mode, id) {
		var me = this;
		var modelJob = me.getModel("Job");
		var storeJobs = me.getStore("Jobs");
		mode = mode || "insert";

		var window = me.getView("Job").create();
		me.initJobWindowOptions(window, { id: id, mode: mode });
		me.jobs.push(window);

		window._job.storeJobs = storeJobs;

		var formpanel = window.down("form#options");
		var form = formpanel.getForm();

		window._job.form = form;
		
		var onRecordReady = function() {
			var isMapcutReady = form.getRecord().get("map_ready");
			if (!isMapcutReady) {
				window.getDockedComponent("tbar").down("#view").disable();
				window.getDockedComponent("tbar").down("#download").disable();
			}
		};
		
		if (mode == "insert") {
			window.getDockedComponent("tbar").down("#delete").disable();
			var record = Ext.create(modelJob);
			record.setProxy(storeJobs.getProxy());
			form.loadRecord(record);
			onRecordReady();
		} else { // mode == update
			modelJob.setProxy(storeJobs.getProxy());
			modelJob.load(id, {
				success: function(record) {
					form.loadRecord(record);
					window._job.savestate = "save";
					onRecordReady();
				}
			});
		}

		window.getDockedComponent("tbar").down("#save").on("click", me.jobWindow_menuItemJobSave_handler, window);
		window.getDockedComponent("tbar").down("#delete").on("click", me.jobWindow_menuItemJobDelete_handler, window);
		window.getDockedComponent("tbar").down("#optimize").on("click", me.jobWindow_menuItemJobOptimize_handler, window);
		window.getDockedComponent("tbar").down("#view").on("click", me.jobWindow_menuItemJobViewmap_handler, window);
		window.getDockedComponent("tbar").down("#download").on("click", me.jobWindow_menuItemJobDownloadmap_handler, window);
		window.getDockedComponent("tbar").down("#close").on("click", function() { window.close(); });
		
		window.on("beforeclose", me.jobWindow_onBeforeClose, window);
		window.on("beforedestroy", me.jobWindow_onBeforeDestroy, window);
		
		form.getFields().each(function(field) {
			field.on("change", function() {
				window._job.savestate = "change";
			});
		});
		
		// Заголовок окна на основе названия сменного задания
		var fieldTitle = formpanel.down("field[name='title']");
		var title = new Ext.Template("Сменное задание {number} «{title}»");
		var windowTitleHandler = function() {
			window.setTitle(title.apply({
				title: fieldTitle.getValue(),
				number: window._job.id ? "№ " + window._job.id : ""
			}));
		};
		fieldTitle.on("change", windowTitleHandler);
		windowTitleHandler();

		// Прицепить к окну хранилище для выбранных заказов
		var storeJobOrders = me.createStoreJobOrders();
		window.down("gridjoborders").reconfigure(storeJobOrders);
		window._job.storeJobOrders = storeJobOrders;
		
		// Соединить хранилище и скроллер
		window.down("pagingtoolbar").bindStore(storeJobOrders);
		
		// Возврат готового окна
		return window;
	},
	
	jobWindow_onBeforeClose: function() {
		var window = this;
		if (window._job.savestate == "save") return true;
		if (window._job.savestate == "issave") {
			alert("Данные в процессе сохранения. Подождите!");
			return false;
		};
		if (window._job.savestate == "change") {
			Ext.Msg.show({
				title: "Сменное задание",
				msg: "Данные не сохранены. Сохранить?",
				buttons: Ext.Msg.YESNOCANCEL,
				icon: Ext.Msg.QUESTION,
				fn: function(button) {
					if (button == "no") {
						window.destroy();
					}
					if (button == "yes") {
						window._job.controller.jobWindow_menuItemJobSave_handler
							.call(window, function() {
								window.destroy();
							});
					}
				}
			});
			return false;
		}
	},
	
	jobWindow_onBeforeDestroy: function() {
		var window = this;
		delete window._job.storeJobOrders;
		delete window._job;
		return true;
	},
	
	jobWindow_menuItemJobSave_handler: function(callback) {
		var window = this;
		var callback1 = callback;
		callback = function() { // Действия после сохранения задания
			Ext.isFunction(callback1) && callback1();
			if (window._job.savestate == "save") window.getDockedComponent("tbar").down("#delete").enable();
		};
		var record = window._job.form.getRecord();
		if (window._job.form.isValid()) {
			window._job.form.updateRecord();
			window._job.savestate = "issave";
			if (window._job.mode == "insert") {
				record.save({
					success: function(records, operation) {
						window._job.storeJobs.add(record);
						window._job.id = record.get("id");
						window._job.storeJobOrders.getProxy().setExtraParam("job_id", window._job.id);
						if (window._job.storeJobOrders.checkCnangeRecords()) {
							window._job.storeJobOrders.sync({
								success: function() {
									window._job.savestate = "save";
									callback && callback();
								}
							});
						} else {
							window._job.savestate = "save";
							callback && callback();
						}
					},
					failure: function() { 
						window._job.savestate = "change";
						callback && callback();
					}
				});
				window._job.mode = "update";
			} else { // window._job.mode == update
				record.save({
					success: function(records, operation) {
						window._job.storeJobs.reload();
						if (window._job.storeJobOrders.checkCnangeRecords()) {
							window._job.storeJobOrders.sync({
								success: function() {
									window._job.savestate = "save";
									callback && callback();
								}
							});
						} else {
							window._job.savestate = "save";
							callback && callback();
						}
					},
					failure: function() { 
						window._job.savestate = "change";
						callback && callback();
					}
				});
			}
		} else {
			alert("Форма заполнена неверно!");
		}
	},
	
	jobWindow_menuItemJobDelete_handler: function() {
		var window = this;
		var record = window._job.form.getRecord();
		window._job.controller.deleteJobs(record);
	},
	
	jobWindow_menuItemJobOptimize_handler: function(callback) {
		var window = this;
		switch (window._job.savestate) {
			case "change":
				Ext.Msg.show({
					title: "Оптимизация",
					msg: "Сменное задание перед оптимизацией следует сохранить. Сохранить?",
					maximizable: false,
					buttons: Ext.MessageBox.YESNO,
					icon: Ext.MessageBox.QUESTION,
					fn: function(button) {
						if (button == "yes") {
							window._job.controller.jobWindow_menuItemJobSave_handler.call(window, function() {
								window._job.controller.jobWindow_optimize.call(window);
							});
						} 
					}
				});
				break;
			case "save":
				window._job.controller.jobWindow_optimize.call(window);
				break;
			case "issave":
				alert("Сменное задание в процессе сохранения. Подождите и снова запустите оптимизацию.");
				break;
		}
	},
	
	jobWindow_optimize: function() {
		var window = this;
		
		var title = "Оптимизация";

		var cancel = false;
		var total = 0;
		var current = 0;
		var materials = [];
		
		var msgbox = Ext.Msg.show({
			title: title,
			msg: "Подождите, выполняется оптимизация...",
			progress: true,
			progressText: "Подготовка...",
			closable: false,
			maximizable: false,
			buttons: Ext.MessageBox.CANCEL,
			icon: Ext.MessageBox.WARNING,
			fn: function(button) {
				if (button == "cancel") {
					cancel = true;
				}
			}
		});
		
		var progressbar = msgbox.down("progressbar");
		
		Ext.Ajax.request({
			url: "/application/mapcut/optimize-phase1/",
			method: "get",
			params: {
				"job_id": window._job.id
			},
			success: function(response) {
				var result = eval("(" + response.responseText + ")");
				materials = result.data;
				total = materials.length;
				progressbar.updateProgress(0, "0 %");
				optimizePhase2();
			}
		});
		
		function optimizePhase2() {
			var material = materials.shift();
			if (!cancel) {
				if (material) {
					current++;
					Ext.Ajax.request({
						url: "/application/mapcut/optimize-phase2/",
						method: "get",
						params: {
							"material_id": material,
							"material_count": total
						},
						success: function(response) {
							var progress = current / total;
							var percent = Math.round(progress * 100);
							progressbar.updateProgress(progress, percent + " %");
							optimizePhase2();
						}
					});
				} else {
					// Завершено
					progressbar.updateProgress(1, "100 %");
					msgbox.close();
					Ext.Msg.alert(title, "Оптимизация завершена!");
					window._job.controller.jobWindow_onOptimizeComplite.call(window);
				}
			} else {
				// Отменено
				msgbox.close();
			}
		}
	},
	
	jobWindow_onOptimizeComplite: function() {
		var window = this;
		window.getDockedComponent("tbar").down("#view").enable();
		window.getDockedComponent("tbar").down("#download").enable();
		window._job.form.getRecord().set("map_ready", true);
		window._job.form.updateRecord();
		window._job.controller.jobWindow_menuItemJobSave_handler.call(window);
	},
	
	jobWindow_menuItemJobViewmap_handler: function() {
		var _window = this;
		var url = "/application/mapcut/viewmap/?job_id=";
		if (_window._job.id) _window._job.viewmap = window.open(url + _window._job.id);
	},
	
	jobWindow_menuItemJobDownloadmap_handler: function() {
		var _window = this;
		var url = "/application/mapcut/getpdfmap/?job_id=";
		if (_window._job.id) _window._job.getpdfmap = window.open(url + _window._job.id);
	},
	
	createStoreJobOrders: function(extraParams) {
		var me = this;
		var store = Ext.create("Ext.data.Store", {
			remoteFilter: true,
			model: me.getModel("Order"),
			proxy: me.createProxyJobOrders(extraParams)
		});
		return store;
	},
	
	createProxyJobOrders: function(extraParams) {
		var proxy = Ext.create("Ext.data.proxy.Rest", {
			url: "/application/rest/job-orders/",
			extraParams: extraParams,
			reader: {
				type: "json",
				root: "data"
			}
		});
		return proxy;
	},
	
	initJobWindowOptions: function(window, options) {
		var me = this;
		options = options || {};
		window._job = Ext.Object.merge({
			controller: me,
			id: null, // Номер сменного задания
			mode: null, // insert | update
			savestate: "change", // change | save | issave - изменена, сохранена, в процессе сохранения
			form: null,
			storeJobs: null,
			storeJobOrders: null,
			viewmap: null, // Окно браузера, где открыта карта раскроя
			getpdfmap: null // Окно браузера, где открыто скачивание PDF-файла с картой раскроя
		}, options);
	}

});


