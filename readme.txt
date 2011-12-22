
Разворачиванию сайта

Требования к серверу:
- Веб-сервер с Rewrite модулем (Apache, IIS, др.)
- PHP >= 5.1.0 (+PDO,PEAR)
- MySQL >= 3.x

Инструкция:
- Скопировать файлы в директорию сайта на сервере
- Установить права записи на папки:
	1 - /public/upload
	2 - /var
- Создать базу данных "ataswild" и выполнить SQL-скрипт в архиве "/sql/ataswild.sql.zip"
- Установить настройки в файле "/engine/config/engine.config.php"
- Зайти в админку сайта http://<SITE.COM>/admin/
	E-mail - sysadmin@sysadmin.com
	Пароль - sysadmin
