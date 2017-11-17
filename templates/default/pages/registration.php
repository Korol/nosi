<script type="text/javascript">
	function send_registerBlock()
	{
	var email=$("#registerBlock #register_name").val();
	var password=$("#registerBlock #register_password").val();
	var first_name=$("#registerBlock #register_first_name").val();
	var last_name=$("#registerBlock #register_last_name").val();

	if(email==""){
		alert("Введите E-mail!");
		return false;
	}

	if(!/^([a-z0-9_.-]+)@([a-z0-9_.-]+)\.([a-z.]{2,6})$/.test(email)){
		alert("E-mail введен неправильно!");
		return false;
	}

	if(password==""){
		alert("Введите пароль!");
		return false;
	}

	// if(first_name==""){
	// 	alert("Введите имя!");
	// 	return false;
	// }

	// $.post("/register.html",{
	// 	register_sm:1,
	// 	ajax:1,
	// 	email:email,
	// 	password:password,
	// 	first_name:first_name,
	// 	last_name:last_name
	// },function(d){
	// 	alert(d);
	// });

	$("#registerForm").submit();
	
}
</script>
<?php if(!empty($_POST)){
	// $salt       = $this->ci->ion_auth_model->store_salt ? $this->ci->ion_auth_model->salt() : FALSE;
	// $password   = $this->ci->ion_auth_model->hash_password($_POST['register_password'], $salt);
	// $fields = "username, password, email";
	// if($_POST['register_phone']!=""){ 
	// 	$_POST['register_phone']=", '".$_POST['register_phone']."'"; 
	// 	$fields.=", phone";
	// }
	// if($_POST['register_address']!=""){
	// 	$_POST['register_address']=", '".$_POST['register_address']."'"; 
	// 	$fields.=", adress";
	// }
	// if($_POST['register_city']!=""){ 
	// 	$_POST['register_city']=", '".$_POST['register_city']."'"; 
	// 	$fields.=", city";
	// }
	// $res=$this->db->query("INSERT INTO `users` (".$fields.") VALUES ('".$_POST['register_name']."'".$password."'".$_POST['register_name']."'".$_POST['register_phone'].$_POST['register_address'].$_POST['register_city'].")");
	// $user_id=$this->ci->ion_auth->register($this->input->post("username"),$this->input->post("password"),$this->input->post("email"),$additional_data,array($this->input->post("group_id")));
	$additional_data=array(
		"first_name"=>$_POST['register_first_name'],
		"phone"=>$_POST['register_phone'],
		"adress"=>$_POST['register_address'],
		"city"=>$_POST['register_city'],
		"active"=>1,
		"subscribe"=>$_POST['register_news'],
		"messages"=>$_POST['register_messages'],
		"new_products"=>$_POST['register_new_products'],
		"vkontakte"=>$_POST['register_vkontakte'],
		"facebook"=>$_POST['register_facebook']
	);
	$reg=$this->ci->ion_auth->register($_POST['register_login'],$_POST['register_password'],$_POST['register_name'],$additional_data,array("group_id"=>2));
	if($reg===false){
		print "error";
	}
	
	print '<span style="text-align:center;color:#880568;font-size:20px;display:block;margin-bottom:20px;">Вы успешно зарегистрированы</span>';
} ?>
<h1 class="regH">Регистрация</h1>
<div id="registerBlock">
		<form method="post" id="registerForm">
			<label for="register_login">Логин:<sup>*</sup></label><input type="text" name="register_login" id="register_login" />
			<br />
			<label for="register_name">E-mail:<sup>*</sup></label><input type="text" name="register_name" id="register_name" />
			<br />
			<label for="register_password">Пароль:<sup>*</sup></label><input type="password" name="register_password" id="register_password" />
			<br />
			<label for="register_first_name">Имя:</label><input type="text" name="register_first_name" id="register_first_name" />
			<br />
			<label for="register_phone">Телефон:</label><input type="text" name="register_phone" id="register_phone" />
			<br />
			<!-- <label for="register_first_name">Ваше имя</label><input type="text" name="register_first_name" id="register_first_name" />
			<br /> -->
			<label for="register_city">Город:</label><input type="text" name="register_city" id="register_city" />
			<br />
			<label for="register_address">Адрес:</label><input type="text" name="register_address" id="register_address" />
			<br />
			<label for="register_facebook">Facebook:</label><input type="text" name="register_facebook" id="register_facebook" />
			<br />
			<label for="register_vkontakte">Vkontakte:</label><input type="text" name="register_vkontakte" id="register_vkontakte" />
			<br />
			<input type="checkbox" name="register_news" id="register_news" value="1" /><span class="subNews">Получать новости</span><br />
			<input type="checkbox" name="register_messages" id="register_messages" value="1" /><span class="subNews">Получать сообщения</span><br />
			<input type="checkbox" name="register_new_products" id="register_new_products" value="1" /><span class="subNews">Уведомлять меня о новых поступлениях</span><br />
			<br /><br />
			<span class="regMessage">Для получения выбраного Вами товара при пересылке<br /> почтой, Вы должны указать адрес доставки, а также<br /> свое настоящее имя и номер телефона.</span>
		</form>
		<a href="#" class="regSub" onclick="send_registerBlock();">Зарегистрироваться</a>
		<span class="underReg">* – Все поля, отмеченные звездочкой, обязательны для заполнения</span>
</div>