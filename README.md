##Getting started

Заходим в консоль в корневою директорию и вводим команду:

	```console
	composer require pnixx/boot-auth
	```

После установки запускаем генератор модели:
	
	```console
	php vendor/pnixx/boot-auth/generate.php MODEL
	```
	
Замените MODEL на имя вашей модели (например `user`). После выполнения команды будет создана модель и соответствующая миграция.

Далее, добавляем инициализацию в файл `application/config/initialize.php`:

	```console
	Boot\Auth::initialize();
	```
	
Добавляем данные о созданной модели в конфиг `application/config/application.ini`:

	;;Auth module
	auth.model = "MODEL"
	auth.mailer.host = "http://localhost"

Замените MODEL на имя созданной ранее модели.

Добавляем маршруты в файл `application/config/routes.php`:

	```php
	Boot\Auth\Routes::init_for('users');
	```
	
##Фильтры в контроллере

Добавляем в переменную $before_action в каждом контроллере, в котором требуется авторизация:

	```php
	public $before_action = [
		'authenticate_user' => []
	];
	```
	
##Конфигурация контроллера

Для изменения стандартных методов или шаблонов достаточно просто наследоваться от необходимого контроллера и создать шаблоны.

1. Создаем контроллер, например в папке `application/controllers/users/session.php`:

	```php
	<?php
	namespace Boot\Users\Controller;
	
	class Session extends \Boot\Auth\Controller\Session {
	
	}
	```

2. Указываем в роутах, что мы изменили контроллер:

	```php
	Boot\Auth\Routes::init_for('users', [
		'session'      => 'Boot\Users\Controller\Session',
	]);
	```

3. Создаем шаблон `application/views/users/session/sign_in.phtml` (не обязательно, если требуется только изменить контроллер)
