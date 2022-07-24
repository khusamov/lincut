Установка
=========

Установить wkhtmltopdf версии 0.12.0.

1) Open Server настроить следующим образом:
- Apache 2.2
- PHP 5.3
- PostgreSQL 9.3
Остальные модули выключить

Возможно поменять порт Apache на 81 или любой свободный. В последних версиях Windows порт 80 занят обычно.

2) PHP настроить следующим образом:

extension=sqlsrv20\php_sqlsrv_53_ts_vc9.dll

в папку sqlsrv20 установить драйвер Microsoft Drivers for PHP for SQL Server
http://go.microsoft.com/fwlink/?LinkId=123025
http://sqlsrvphp.codeplex.com/

Важно: для PHP 5.3 связь с SQL Server 2008
для PHP 5.4 связь с SQL Server 2012

3) Установить утилиту wkhtmltopdf.org и прописать путь к утилите в настройках программы.

4) Установить postgresql_dump_*.sql последней версии.

5) Прописать доступ к базе WinCAD

Можно работать.

Внимание:
Пункт 1 можно не выполнять (кроме настройки порта), так как в сборке Open Server уже все настроено.
Пункт 2 можно не выполнять, так как в сборке Open Server уже все настроено.
Пункт 4 можно не выполнять, так как в сборке Open Server уже все настроено.
