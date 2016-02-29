<?php
/**
 * Date: 24.02.16
 * Time: 17:42
 * @author  Sergey Odintsov <nixx.dj@gmail.com>
 */
namespace Boot;

use Boot\Model\Auth_Trait;

class Auth {

	/**
	 * Инициализация модуля
	 */
	static public function initialize() {
		\Boot_Controller::register_call('authenticate_user', '\Boot\Auth');
	}

	/**
	 * Проверяет авторизацию пользователя
	 */
	static public function authenticate_user() {

		/**
		 * Получаем модель
		 * @var \ActiveRecord|Auth_Trait $model
		 */
		$model = "Model_" . ucfirst(\Boot::getInstance()->config->auth->model);

		//Если пользователь не найден, редиректим на вторизацию
		if( $model::getCurrentUser() == null ) {
			\Boot_Controller::getInstance()->_redirect(\Boot_View::getInstance()->users_sign_in_path());
		}
	}
}