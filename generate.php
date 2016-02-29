<?php
if( empty($argv[1]) ) {
	echo 'Use: generate.php MODEL' . PHP_EOL;
	exit(127);
}
$model = $argv[1];
$directory = realpath('.');

//Проверяем существование файла создания модели
if( !file_exists($directory . '/console/create/migrate.php') ) {
	echo 'Please run script from root project directory' . PHP_EOL;
	exit;
}
passthru("php {$directory}/console/create/migrate.php create_table_{$model} email:string:false encrypted_password:string:false reset_password_token:string reset_password_sent_at:timestamp confirmation_token:string confirmed_at:timestamp confirmation_sent_at:timestamp :UKEY=email", $return);
if( $return != 0 ) {
	exit($return);
}
sleep(1);
passthru("php {$directory}/console/create/migrate.php create_index_{$model} email reset_password_token confirmation_token", $return);
if( $return != 0 ) {
	exit($return);
}

//Читаем файл модели
$file_model = $directory . '/application/models/' . $model . '.php';
if( !file_exists($file_model) ) {
	echo 'Error open file model' . PHP_EOL;
	exit(127);
}
$model_content = file_get_contents($file_model);

//Добавляем данные треда
$model_content = preg_replace(['/(class Model_' . ucfirst($model) . '.*?{)/', '/\$encrypted_password/'], ["$1\n\tuse Boot\\Model\\Auth_Trait;", '$password'], $model_content);
file_put_contents($file_model, $model_content);
