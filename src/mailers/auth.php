<?php
/**
 * Date: 18.02.16
 * Time: 20:26
 * @author  Sergey Odintsov <nixx.dj@gmail.com>
 */
namespace Boot\Auth;
class AuthMailer extends \Boot_Mailer_Abstract {

	//Подтверждение регистрации
	static public function confirmation($email, $confirmation_token) {
		self::$params = [
			'token' => $confirmation_token,
			'email' => $email,
		];
		self::mail($email, self::t('auth.mailer.confirmation.subject'));
	}

	//Запрос изменения пароля
	static public function reset_password($email, $token) {
		self::$params = [
			'token' => $token,
			'email' => $email,
		];
		self::mail($email, self::t('auth.mailer.reset_password.subject'));
	}
}