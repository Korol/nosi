<?php 

require('../config.php');
$link = mysql_connect($db['default']['hostname'], $db['default']['username'], $db['default']['password']);
if (!$link) {
    die('Ошибка соединения: ' . mysql_error());
}
mysql_select_db($db['default']['database'], $link) or die ('Can\'t use  : ' . mysql_error());
/*
$sql="
INSERT INTO `url_structure` ( `user_id`, `parent_id`, `name`, `url`, `is_main_page`, `title`, `route`, `type`, `module`, `action`, `in_basket`, `extra_name`, `extra_id`, `options`, `date_add`, `enabled`, `order`) VALUES
( 0, 0, 'order_process', '/order_process.html', 0, 'Обработка заказа', '', 'module_action-one', 'shop', 'order_process', 0, '', 0, '', 1372021035, 1, 53);

";
$res=mysql_query($sql);
var_dump($res);

*/
   if (isset($_POST['validate']) AND ($_POST['validate']=='validate')) {// запустим проверку  ввёдённых  данных  перед регой
	$errors[]='';
	$username= addslashes($_POST['username']);
	$email= addslashes($_POST['email']);
	//проверим занято ли имя
	
	$query = "SELECT `id` FROM `users` WHERE `username` = '".$username."'";

	$res=mysql_query($query);
$num_rows = mysql_num_rows($res);
	if($num_rows<>0){
	$errors['username']='fail';
	}
	
	//проверим занята ли почта
$query = "SELECT `id` FROM `users` WHERE `email` = '".$email."'";
	$res=mysql_query($query);

$num_rows = mysql_num_rows($res);
	if($num_rows<>0){
	$errors['email']='fail';
	}
	$json = json_encode($errors);
	echo $json;

	exit();
	}





?>

