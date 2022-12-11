# inoice.fluid-line.ru

Код для отображения заказов клиентов, приходящих из 1С.

## dev установка
- Выполнить SQL миграции из `./migrations`
- `docker-compose exec php5.6 bash` - Войдите в docker контейнер php
- `composer install` - установите пакеты composer
- `cp ./Src/config.php.example ./Src/config.php` - Скопируйте файл конигурации
- Заполните `./Src/config.php`, значениями текущего окружения. Для `cert_path` укажите `NULL`, так как локальная база данных не требует обязательного подключения по SSL

## prod установка
- Выполнить SQL миграции из `./migrations`
- `docker-compose exec php5.6 bash` - Войдите в docker контейнер php
- `composer install` - установите пакеты composer
- `cp ./Src/config.php.example ./Src/config.php` - Скопируйте файл конигурации
- Заполните `./Src/config.php`, значениями текущего окружения. Для `cert_path` укажите сертификат `ca_certificate` прокинутого в папку `/var/www/cert/mysql`