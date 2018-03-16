<?php
/**
 * Date: 16.02.16
 * Time: 12:06
 * @author  Sergey Odintsov <nixx.dj@gmail.com>
 */
namespace Boot\Auth;

class Routes {

	/**
	 * Имя пути
	 * @var string
	 */
	private static $name;

	/**
	 * Инициализация роутов для модуля
	 * @param       $name
	 * @param array $controllers
	 */
	public static function init_for($name, $controllers = []) {
		self::$name = $name;

		//Контроллер
		$prefix = 'Boot\Auth\Controller\\';
		$controllers = array_merge([
			'session'      => $prefix . 'Session',
			'registration' => $prefix . 'Registration',
			'confirmation' => $prefix . 'Confirmation',
			'password' => $prefix . 'Password',
		], $controllers);

		//Добавляем роуты
		\Boot\Routes::get([$name  . '/sign_in' => $controllers['session'] . '#sign_in']);
		\Boot\Routes::post([$name . '/login' => $controllers['session'] . '#create']);
		\Boot\Routes::get([$name  . '/sign_out' => $controllers['session'] . '#sign_out']);
		\Boot\Routes::get([$name  . '/sign_up' => $controllers['registration'] . '#sign_up']);
		\Boot\Routes::post([$name . '/register' => $controllers['registration'] . '#create']);
		\Boot\Routes::get([$name  . '/confirmation' => $controllers['confirmation'] . '#show']);
		\Boot\Routes::post([$name . '/confirmed' => $controllers['confirmation'] . '#create']);
		\Boot\Routes::get([$name  . '/password' => $controllers['password'] . '#new']);
		\Boot\Routes::post([$name . '/password/reset' => $controllers['password'] . '#create']);
		\Boot\Routes::get([$name  . '/password/edit' => $controllers['password'] . '#edit']);
		\Boot\Routes::post([$name . '/password/update' => $controllers['password'] . '#update']);
	}

	/**
	 * Получает имя для пути
	 * @return string
	 */
	public static function getName() {
		return self::$name;
	}
}