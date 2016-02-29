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
class Password extends Main {

	// GET /resource/password
	public function newAction() {
		$model = $this->getModel();
		$this->view->resource = $model::create();
	}

	// POST /resource/password
	public function createAction() {
		$model = $this->getModel();

		/**
		 * @var \ActiveRecord|Auth_Trait $resource
		 */
		$resource = $model::where(['email' => $this->getParam(Routes::getName())['email']])->row();
		if( $resource ) {
			$resource->createResetPasswordToken();
			if( $resource->save() ) {
				//Отправляем письмо счастья
				AuthMailer::reset_password($resource->email, $resource->reset_password_token);
				$this->setFlash('notice', $this->t('auth.reset_password_instructions'));
			}
			$this->_redirect('/');
		} else {
			$this->setFlash('notice', $this->t('auth.errors.not_found'));
			$this->_redirect('/' . Routes::getName() . '/password');
		}
	}

	// GET /resource/password/edit?token=token
	public function editAction() {
		$model = $this->getModel();

		/**
		 * Ищем ресурс
		 * @var \ActiveRecord|Auth_Trait $resource
		 */
		$resource = $model::where(['reset_password_token' => $this->getParam('token')])->row();
		if( $resource ) {
			$this->view->resource = $resource;
		} else {
			$this->setFlash('notice', $this->t('auth.errors.not_found'));
			$this->_redirect('/' . Routes::getName() . '/password');
		}
	}

	// POST /resource/password/update
	public function updateAction() {
		$model = $this->getModel();

		//Достаем параметры
		$params = $this->getParam(Routes::getName());

		/**
		 * Ищем ресурс
		 * @var \ActiveRecord|Auth_Trait $resource
		 */
		$resource = $model::where(['reset_password_token' => $params['reset_password_token']])->row();
		if( $resource ) {
			if( $resource->update($params->permit(['password', 'confirm_password'])) ) {
				$this->setFlash('notice', $this->t('auth.passwords.updated'));
				$this->_redirect('/' . Routes::getName() . '/sign_in');
			} else {
				$this->view->resource = $resource;
				$this->render('edit');
			}
		} else {
			$this->setFlash('notice', $this->t('auth.errors.not_found'));
			$this->_redirect('/' . Routes::getName() . '/password');
		}
	}
}