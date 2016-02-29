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
class Confirmation extends Main {

	// GET /resource/confirmation
	public function showAction() {

		//Если указан токен, подтверждаем
		if( $this->getParam('token') ) {
			$model = $this->getModel();
			try {
				/**
				 * @var \ActiveRecord|Auth_Trait $resource
				 */
				$resource = $model::confirm($this->getParam('token'));
				if( $resource->confirmed() ) {
					$this->setFlash('notice', $this->t('auth.confirmations.confirmed'));
				} else {
					$this->setFlash('alert', $this->t('auth.confirmations.confirmed'));
				}

				//Уходим на главную
				$this->_redirect('/');
			} catch( \DB_Exception $e ) {
				$this->setFlash('alert', $e->getMessage());
			}
		}
	}

	// POST /resource/confirmation
	public function createAction() {
		$model = $this->getModel();

		/**
		 * @var \ActiveRecord|Auth_Trait $resource
		 */
		$resource = $model::where(['email' => $this->getParam(Routes::getName())['email']])->row();
		if( $resource ) {

			if( $resource->confirmed() ) {
				$this->setFlash('alert', $this->t('auth.errors.already_confirmed'));
			} else {
				//Отправляем письмо счастья
				AuthMailer::confirmation($resource->email, $resource->confirmation_token);
				$this->setFlash('notice', $this->t('auth.confirmations.send_instructions'));
			}
			$this->_redirect('/');
		} else {
			$this->setFlash('notice', $this->t('auth.errors.not_found'));
			$this->_redirect('/' . Routes::getName() . '/confirmation');
		}
	}
}