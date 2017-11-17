<?php
class userModule extends Cms_modules {
	function __construct()
	{
		parent::__construct();

		// подключаем библиотеку для отрисовки интерфейса (формы, таблицы и тп.)

		$this->ci->load->library("fb");
		$this->load->helper('url');
		$this->load->helper('cms');
	}

	public function login()
	{
		if($this->input->get("logout")!==false){
			// include_once("./application/libraries/facebook/facebook.php");

			// $facebook = new Facebook(array(
			//   'appId'  => 375511195873316,
			//   'secret' => "74f8f71ab79c7ca06b6487d96ffbc934",
			// ));

			$fb_key = 'fbsr_375511195873316';
  			// set_cookie($fb_key, '', '', '', '/', '');

  			setcookie($fb_key, '', time()-10);

			// $facebook->setSession(NULL);

			$this->ci->ion_auth->logout();
			redirect("/");
			
		}

		$remember=1;
		if (
			$this->input->post("login_sm")!==false && 
			$this->ci->ion_auth->login($this->input->post("email"),$this->input->post("password"),$remember)){

			if($this->input->post("ajax")!==false){
				print 1;
			}

			redirect($this->admin_url);
		}else{
			if($this->input->post("ajax")!==false){
				print $this->ci->ion_auth->errors();
			}else{
				$this->login_error=$this->ci->ion_auth->errors();
			}
		}

		// $this->ci->load->frontView("user/login",$this->d);
	}

	public function register()
	{
		$errors=array();

		if($this->input->post("register_sm")!==false){
			if($this->input->post("email")==""){
				$errors[]="Введите E-mail!";
			}

			if($this->input->post("password")=="" || ($this->input->post("password")!="" && $this->input->post("password")!=$this->input->post("password2"))){
				$errors[]="Пароль введен неверно!";
			}

			if($this->input->post("first_name")==""){
				$errors[]="Введите имя!";
			}

			if(sizeof($errors)==0){
				$salt       = $this->ci->ion_auth_model->store_salt ? $this->ci->ion_auth_model->salt() : FALSE;
				$password   = $this->ci->ion_auth_model->hash_password($this->input->post("password"), $salt);

				$additional_data=array(
					"first_name"=>$this->input->post("first_name"),
					"last_name"=>$this->input->post("last_name"),
					"active"=>1
				);

				if($this->input->post("block")==1){
					$additional_data['active']=0;
				}

				$group_id=2;

				$user_id=$this->ci->ion_auth->register($this->input->post("email"),$this->input->post("password"),$this->input->post("email"),$additional_data,array($group_id));

				if($user_id===false){
					print $this->ci->ion_auth->errors();
				}
			}

			exit;
		}

		if($this->input->post("facebook_register_sm")!==false){
			include_once("./application/libraries/facebook/facebook.php");

			$facebook = new Facebook(array(
			  'appId'  => 375511195873316,
			  'secret' => "74f8f71ab79c7ca06b6487d96ffbc934",
			));
			$userId = $facebook->getUser();

			if($userId){
				$fb_user_data=json_decode(file_get_contents("https://graph.facebook.com/me?fields=link,picture.width(999).height(999),first_name,last_name,email,birthday,updated_time&access_token=".$facebook->getAccessToken()));

				if(!isset($fb_user_data->id)){
					$register_error="Ошибка авторизации через facebook!";
				}

				$num=$this->db
				->where(array(
					"facebook_id"=>$fb_user_data->id
				))
				->count_all_results("users");
				
				if($num!=0){
					$register_error="Этот пользователь уже зарегистрирован!";
				}
			
/*
stdClass Object
(
    [id] => 100000069575222
    [name] => Евгений Масон
    [first_name] => Евгений
    [last_name] => Масон
    [link] => http://www.facebook.com/evgeny.mason
    [username] => evgeny.mason
    [birthday] => 04/26/1989
    [location] => stdClass Object
        (
            [id] => 111227078906045
            [name] => Kyiv, Ukraine
        )

    [gender] => male
    [email] => jeno.kiev@gmail.com
    [timezone] => 2
    [locale] => ru_RU
    [verified] => 1
    [updated_time] => 2012-12-02T15:10:27+0000
)
*/
				// проверяем нет ли этого пользователя в базе, если ID есть, авторизуем!
				$user=$this->db
				->get_where("users",array(
					"facebook_id"=>$fb_user_data->id
				))->row();

				if(isset($user->id)){
					if(!$this->ci->ion_auth->login_by_id($user->id)){
						$register_error="Ошибка авторизации через facebook!";
					}else{
						if(strtotime($fb_user_data->updated_time)!=$user->facebook_updated_time){
							$this->facebook_update_user_data($user->id,$fb_user_data);
						}

						print 1;
						exit;
					}
				}else{
					// создаем ремдомный пароль
					$rand_password=md5(uniqid(rand(),1));
					$salt       = $this->ci->ion_auth_model->store_salt ? $this->ci->ion_auth_model->salt() : FALSE;
					$password   = $this->ci->ion_auth_model->hash_password($rand_password, $salt);

					list($first_name,$last_name)=explode(" ",trim($fb_user_data->name));
					list($m,$d,$y)=explode("/",$fb_user_data->birthday);

					$additional_data=array(
						"facebook_id"=>$fb_user_data->id,
						"active"=>1
					);

					$group_id=2;

					$user_id=$this->ci->ion_auth->register($fb_user_data->email,$password,$fb_user_data->email,$additional_data,array($group_id));

					if($user_id===false){
						$register_error=$this->ci->ion_auth->errors();
					}else{
						$this->facebook_update_user_data($user_id,$fb_user_data);
					}
				}
			}else{
				$register_error="Ошибка авторизации через facebook!";
			}

			if(empty($register_error)){
				if(!$this->ci->ion_auth->login_by_id($user_id)){
					$register_error="Ошибка авторизации через facebook!";
				}
			}

			if(!empty($register_error)){
				print $register_error;
			}else{
				print 1;
			}
			exit;
		}
	}

	function facebook_update_user_data($user_id,&$fb_user_data)
	{
		$this->ci->load->library("uploads");
		list($m,$d,$y)=explode("/",$fb_user_data->birthday);

		$this->ci->uploads->remove(array(
			"component_type"=>"module",
			"component_name"=>"user",
			"extra_type"=>"user_id",
			"extra_id"=>$user_id
		));

		// скачиваем фотографию
		$ext=strtolower(end(explode(".",$fb_user_data->picture->data->url)));
		$avatar_file_name=md5($fb_user_data->id.".".$fb_user_data->updated_time).".".$ext;

		if(isset($fb_user_data->picture->data->url)){
			$avatar_upload_id=$this->ci->uploads->upload_file($fb_user_data->picture->data->url,"./uploads/users/".$avatar_file_name,array(
				"component_type"=>"module",
				"component_name"=>"user",
				"extra_type"=>"user_id",
				"extra_id"=>$user_id,
				"proc_config_var_name"=>"config[mod_user_images_options]"
			));
		}

		$this->db->where("id",$user_id)->update("users",array(
			"facebook_updated_time"=>strtotime($fb_user_data->updated_time),
			"facebook_link"=>$fb_user_data->link,
			"avatar_upload_id"=>$avatar_upload_id,
			"birthday"=>mktime(0,0,0,$m,$d,$y),
			"first_name"=>$fb_user_data->first_name,
			"last_name"=>$fb_user_data->last_name
		));
	}
}
?>