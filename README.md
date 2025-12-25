# Рабочая заготовка формы авторизации

Полная авторизации, реализована на php

4 поля

никнейм
почта
пароль
подтверждение пароля

сделано 25.12.2025

htaccess правила написаны под apache 2.2

выше папки сайта нужно создать файл .env и положить внутрь

DB_HOST=localhost

DB_NAME=Имя вашей базы данных

DB_USER=Ваш логин

DB_PASS=Ваш пароль


так же нужен .htaccess c содержимым

RewriteEngine On

RewriteCond %{REQUEST_METHOD} GET
RewriteCond %{REQUEST_URI} ^/api/
RewriteCond %{REQUEST_URI} !/api/csrf\.php$
RewriteCond %{REQUEST_URI} !/api/check_auth\.php$
RewriteRule ^ - [F,L]
