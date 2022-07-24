
Ext.define("Lincut.view.About", {
	extend: "Ext.window.Window",
	alias: "widget.windowabout",
	
	title: "О программе Lincut",
	
	width: 500,
	
	constrain: true,
	constrainHeader: false,
	modal: true,
	maximizable: false,
	resizable: false,
	bodyPadding: "10px",
	
	html: "Программа для раскроя линейных материалов «Lincut», " +
			"<br/>" +
			"версия 1.00 (май 2014 год), " +
			"<br/>" +
			"из базы данных программы WinCAD 5 " +
			"на базе СУБД MS SQL Express 2008. " +
			"<br/><br/>" +
			"Установленные компоненты:" +
			"<br/>" +
			"1) Sencha Ext JS, версия " + Ext.getVersion() + ", лицензия GPLv3. " +
			"<br/>" +
			"2) Zend Framework, версия 2.3.1, лицензия New BSD. " +
			"<br/>" +
			"3) Open Server, версия 5.00, бесплатная лицензия http://open-server.ru/license.html (содержимое комплекса бесплатное). " +
			"<br/>" +
			"4) Утилита создания PDF-файлов wkhtmltopdf.org, версия 0.12.0, лицензия LGPL. " +
			"<br/><br/>" +
			"Разработчики: Тимофей Хусамов, Святослав Хусамов, Сергей Федоренко. " +
			"<br/>" +
			"Контакты: khusamov@yandex.ru <nobr>+7 (965) 391-14-87.</nobr> ",
	
	buttons: [{
		text: "Закрыть",
		handler: function() {
			this.up("window").close();
		}
	}]
	
});

