<?php
namespace Boot\Auth\Controller;

use Boot\Auth\Routes;
use Boot\Model\Auth_Trait;

/**
 * Date: 16.02.16
 * Time: 9:49
 * @author  Sergey Odintsov <nixx.dj@gmail.com>
 */
class Session extends Main {

	// GET /resource/sign_in
	public function sign_inAction() {
		$model = $this->getModel();
		$this->view->resource = $model::create();

		//Если уже авторизованы, уходим на главную
		if( $model::getCurrentUser() ) {
			$this->setFlash('notice', $this->t('auth.errors.already_authenticated'));
			$this->_redirect('/');
		}
	}

	// POST /resource/sign_in
	public function createAction() {
		$model = $this->getModel();

		//Если уже авторизованы, уходим на главную
		if( $model::getCurrentUser() ) {
			$this->setFlash('notice', $this->t('auth.errors.already_authenticated'));
			$this->_redirect('/');
		}

		//Авторизуем
		$resource = $model::login($this->getParam(Routes::getName())->permit(['email', 'password']));

		//Если авторизовали успешно
		if( $resource === true ) {
			$this->setFlash('notice', $this->t('auth.sessions.signed_in'));
			$this->_redirect('/');
		} elseif( $resource === null ) {
			$this->setFlash('notice', $this->t('auth.errors.not_found'));
			$this->_redirect('/' . Routes::getName() . '/sign_in');
		} else {
			$resource->errors->add('email', 'auth.errors.invalid');
			$this->view->resource = $resource;
			$this->render('sign_in');
		}
	}

	// GET /resource/sign_out
	public function sign_outAction() {
		$model = $this->getModel();

		//Если уже авторизованы, уходим на главную
		if( $model::getCurrentUser() ) {
			$model::getCurrentUser()->logout();
			$this->setFlash('notice', $this->t('auth.sessions.signed_out'));
		}
		$this->_redirect('/');
	}
}