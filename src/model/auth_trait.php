<?php
namespace Boot\Model;
use Boot\AuthException;

/**
 * Класс авторизации
 * Date: 15.02.16
 * Time: 15:54
 * @author  Sergey Odintsov <nixx.dj@gmail.com>
 *
 * @property array $_row
 * @property array $_row_update
 * @property \ActiveRecordErrors $errors
 *
 * @property $email
 * @property $encrypted_password
 * @property $reset_password_token
 * @property $reset_password_sent_at
 * @property $confirmation_token
 * @property $confirmed_at
 * @property $confirmation_sent_at
 *
 * @method bool isNew()
 * @method Auth_Trait where($where)
 * @method \ActiveRecord|Auth_Trait row()
 * @method void validator_presence_of($column)
 * @method void validator_uniqueness_of($column)
 * @method bool valid()
 *
 * @use ValidateTrait
 */
trait Auth_Trait {

	/**
	 * Объект текущего пользователя
	 * @var \ActiveRecord|self
	 */
	private static $current_user;

	/**
	 * Генерируем токен подтверждения перед созданием пользователя
	 */
	protected function before_create() {
		parent::before_create();

		//Добавляем валидацию
		$this->validator_presence_of('password');
		$this->validator_presence_of('confirm_password');
		$this->validator_presence_of('email');
		$this->validator_uniqueness_of('email');

		//Валидируем email
		if( !filter_var($this->email, FILTER_VALIDATE_EMAIL) ) {
			$this->errors->add('email', 'auth.invalid.email');
		}

		//Проверяем пароли
		if( $this->password != $this->confirm_password ) {
			$this->errors->add('password', 'auth.invalid.passwords.confirmation');
		}

		//Если есть ошибки, пользователь не может быть создан
		if( !$this->valid() ) {
			throw new AuthException('Invalid model row');
		}
		$this->createPassword($this->password);
		$this->createConfirmationToken();
	}

	/**
	 * Проверяем пароли перед сохранением
	 */
	protected function before_save() {
		parent::before_save();

		//Если есть обновление пароля
		if( $this->password ) {

			//Проверяем пароли
			if( $this->password != $this->confirm_password ) {
				$this->errors->add('password', 'auth.invalid.passwords.confirmation');
			}

			//Если есть ошибки, пользователь не может быть создан
			if( $this->valid() ) {
				$this->createPassword($this->password);
			}
		}
	}

	/**
	 * Получаем данные
	 * @param $name
	 * @param $value
	 * @return string
	 */
	public function __set($name, $value) {
		if( $name == 'password' ) {
			$this->createPassword($value);
		} else {
			parent::__set($name, $value);
		}
	}

	/**
	 * Генерируем токен подтверждения
	 */
	public function createConfirmationToken() {
		$this->confirmation_token = uniqid('c') . '_' . md5(microtime(true));
		$this->confirmation_sent_at = date('Y-m-d H:i:s');
		$this->confirmed_at = null;
	}

	/**
	 * Генерируем токен смены пароля
	 */
	public function createResetPasswordToken() {
		$this->reset_password_token = uniqid('p') . '_' . md5(microtime(true));
		$this->reset_password_sent_at = date('Y-m-d H:i:s');
	}

	/**
	 * @param $password
	 */
	private function createPassword($password) {
		$this->encrypted_password = self::password_encrypt($password);
		unset($this->_row->password);
		unset($this->_row_update['password']);
		unset($this->_row->confirm_password);
		unset($this->_row_update['confirm_password']);
	}

	//Авторизуем пользователя
	public function sign_in() {

		//Получаем секретный ключ
		$skey = \Boot_Skey::get();

		//Записываем токен в куки
		$token = $this->id . "#" . $skey . "#" . md5($this->id . $skey . $this->encrypted_password);
		\Boot_Cookie::set("auth_token", $token);
	}

	/**
	 * Пользователь подтвержден?
	 * @return bool
	 */
	public function confirmed() {
		return (bool) $this->confirmed_at;
	}

	//Удаляет авторизацию
	static public function logout() {
		\Boot_Cookie::clear("auth_token");
	}

	/**
	 * Авторизация пользователя по параметрам
	 * @param $params
	 * @return \ActiveRecord|bool|Auth_Trait
	 * @throws AuthException
	 */
	static public function login($params) {

		//Ищем ресурс
		$resource = static::where(['email' => $params['email']])->row();
		if( !$resource ) {
			throw new AuthException('auth.errors.not_found');
		}

		//Если нашли
		if( password_verify($params['password'], $resource->encrypted_password) ) {

			//Если пользователь подтвержден
			if( $resource->confirmed() ) {
				$resource->sign_in();
			} else {
				throw new AuthException('auth.errors.unconfirmed');
			}
			return true;
		} else {
			$resource->errors->add('email', 'auth.invalid.password');
		}
		return $resource;
	}

	/**
	 * Шифрует пароль
	 * @param $password
	 * @return bool|string
	 */
	static public function password_encrypt($password) {
		return password_hash($password, PASSWORD_BCRYPT);
	}

	/**
	 * Возвращаем авторизацию
	 * @return \ActiveRecord|Auth_Trait|static
	 */
	static public function getCurrentUser() {
		if( self::$current_user === null ) {

			try {
				//Читаем токен
				$token = \Boot_Cookie::get("auth_token");
				if( empty($token) ) {
					return null;
				}

				//Разбиваем токен
				$token = explode("#", $token);
				if( count($token) != 3 ) {
					throw new AuthException('User incorrect token');
				}
				list($id, $skey, $sig) = $token;

				if( !$id || !$skey || !$sig ) {
					throw new AuthException('User incorrect token data');
				}

				//Получаем юзера
				try {
					self::$current_user = static::find($id);
				} catch( \DB_Exception $e ) {
					throw new AuthException('User not found');
				}

				//Проверяем корректность
				if( self::$current_user == false || $skey != \Boot_Skey::get() || $sig != md5($id . $skey . self::$current_user->encrypted_password) ) {
					throw new AuthException('User incorrect signature');
				}
			} catch(AuthException $e) {
				self::$current_user = false;
				\Boot_Cookie::clear("auth_token");
				\Boot::getInstance()->debug($e->getMessage(), true);
				return null;
			}
		}

		return self::$current_user;
	}

	/**
	 * Ищет пользователя в базе по токену и подтверждает email
	 * @param $token
	 * @return \ActiveRecord
	 * @throws \DB_Exception
	 */
	static public function confirm($token) {

		//Ищем пользователя
		$resource = static::where(['confirmation_token' => $token])->row();
		if( $resource ) {
			$resource->update([
				'confirmation_token'   => null,
				'confirmation_sent_at' => null,
				'confirmed_at'         => date('Y-m-d H:i:s'),
			]);
			return $resource;
		} else {
			throw new \DB_Exception('Not found', 404);
		}
	}
}