<?php
/**
 * Date: 20.02.16
 * Time: 10:53
 * @author  Sergey Odintsov <nixx.dj@gmail.com>
 */
namespace Boot\Auth\Controller;
use Boot\Model\Auth_Trait;

class Main extends \Boot_Abstract_Controller {

	/**
	 * Строим имя модели
	 * @return \ActiveRecord|Auth_Trait
	 */
	protected function getModel() {
		return "Model_" . ucfirst(\Boot::getInstance()->config->auth->model);
	}
}