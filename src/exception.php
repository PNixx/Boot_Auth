<?php
namespace Boot;
/**
 * Date: 15.02.16
 * Time: 16:48
 * @author  Sergey Odintsov <nixx.dj@gmail.com>
 */
class AuthException extends \Exception {
	public function __construct($message) {
		parent::__construct($message, 401);
	}
}