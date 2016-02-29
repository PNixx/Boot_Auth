<?php
namespace Boot\Auth\Controller;
use Boot\Auth\AuthMailer;
use Boot\Auth\Routes;
use Boot\AuthException;
use Boot\Model\Auth_Trait;

/**
 * Date: 16.02.16
 * Time: 9:49
 * @author  Sergey Odintsov <nixx.dj@gmail.com>
 */
class Registration extends Main {

	// GET /resource/sign_up
	public function sign_upAction() {
		$model = $this->getModel();
		$this->view->resource = $model::create();
	}

	// POST /resource/sign_up
	public function createAction() {
		$model = $this->getModel();

		/**
		 * Инициализируем пользователя
		 * @var \ActiveRecord|Auth_Trait $resource
		 */
		$resource = $model::create($this->getParam(Routes::getName())->permit(['email', 'password', 'confirm_password']));
		try {

			//Сохраняем
			$resource->save();

			//Отправляем письмо счастья
			AuthMailer::confirmation($resource->email, $resource->confirmation_token);

			//Уходим на главную
			$this->setFlash('notice', $this->t('auth.confirmations.send_instructions'));
			$this->_redirect('/');
		} catch(AuthException $e) {
			$this->view->resource = $resource;
			$this->render('sign_up');
		}
	}
}