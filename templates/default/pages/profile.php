<?php if($this->ci->session->userdata("user_id")!=""){
	if(empty($_POST['sendNews'])) $_POST['sendNews'] = 0;
	if(empty($_POST['sendMess'])) $_POST['sendMess'] = 0;
	if(empty($_POST['sendProd'])) $_POST['sendProd'] = 0;
	if($_POST['sbm']!=""){
		$additional_data=array(
		"email"=>$_POST['user_email'],
		"first_name"=>$_POST['user_firstname'],
		"phone"=>$_POST['user_phone'],
		"adress"=>$_POST['user_adress'],
		"city"=>$_POST['user_city'],
		"subscribe"=>$_POST['sendNews'],
		"messages"=>$_POST['sendMess'],
		"new_products"=>$_POST['sendProd'],
		"facebook"=>$_POST['user_facebook'],
		"vkontakte"=>$_POST['user_vkontakte']
	);
		$reg=$this->ci->ion_auth->update($this->ci->session->userdata("user_id"),$additional_data);
	}
	if(!empty($_FILES)){
		$this->ci->load->library("uploads");
		$this->ci->uploads->remove(array(
			"user_id"=>$this->ci->session->userdata("user_id"),
			"name"=>"user-photo"
		));
		$file_name=file_name("./uploads/shop/products/original/",$_FILES['foto']['name'],true);
		$dest_file_path="./uploads/shop/products/original/".$file_name;

		$this->ci->load->library("uploads");
		$upload_id=$this->ci->uploads->upload_file($_FILES['foto']['tmp_name'],$dest_file_path,array(
			"file_original_name"=>$_FILES['foto']['name'],
			"name"=>"user-photo",
			"extra_id"=>$this->ci->session->userdata("user_id"),
			"proc_config_var_name"=>"mod_shop[images_options]"
		));
	}
	$img=$this->db->query("SELECT * FROM `uploads` WHERE `user_id`='".$this->ci->session->userdata("user_id")."' && `name`='user-photo' ")->result();
	$res=$this->db->query("SELECT * FROM `users` WHERE `id`='".$this->ci->session->userdata("user_id")."' ")->result();
	?>
	<div class="profilePage">
		<h1>Личный кабинет</h1>
		<script type="text/javascript">
			function uploadPhoto(){
				$(".userFoto").click();
			}
		</script>
		<form method="post" enctype="multipart/form-data">
		<?php if(!empty($img)){
			?><a href="#" onclick="uploadPhoto(); return false;" class="userImg"><img src="uploads/shop/products/thumbs4/<?php print $img[0]->file_name;?>" /></a><?php
		}else{
			?><a href="#" onclick="uploadPhoto(); return false;" class="userImg"><img src="/assets/media/profile.png" /></a><?php
		} ?>
		<input type="file" name="foto" class="userFoto" />
		<div class="userInfo">
			<h3><?php print $res[0]->username; ?></h3><br />
			<span>Имя:</span><input type="text" name="user_firstname" id="user_firstname" value="<?php print $res[0]->first_name; ?>" /><br />
			<span>Адрес:</span><input type="text" name="user_adress" id="user_adress" value="<?php print $res[0]->adress; ?>" /><br />
			<span>Город:</span><input type="text" name="user_city" id="user_city" value="<?php print $res[0]->city; ?>" /><br />
			<span>Телефон:</span><input type="text" name="user_phone" id="user_phone" value="<?php print $res[0]->phone; ?>" /><br />
			<span>E-mail:</span><input type="text" name="user_email" id="user_email" value="<?php print $res[0]->email; ?>"/><br />
			<span>Facebook:</span><input type="text" name="user_facebook" id="user_facebook" value="<?php print $res[0]->facebook; ?>"/><br />
			<span>Vkontakte:</span><input type="text" name="user_vkontakte" id="user_vkontakte" value="<?php print $res[0]->vkontakte; ?>"/><br />
		</div>
		<div class="checkBlock">
			<h3>Настройки</h3>
			<br />
			<input type="checkbox" name="sendNews" value="1" <?php if($res[0]->subscribe==1){ ?>checked="checked"<?php } ?> /><span>Получать новости</span><br />
			<input type="checkbox" name="sendMess" value="1" <?php if($res[0]->messages==1){ ?>checked="checked"<?php } ?> /><span>Получать сообщения</span><br />
			<input type="checkbox" name="sendProd" value="1" <?php if($res[0]->new_products==1){ ?>checked="checked"<?php } ?> /><span>Уведомлять меня о новых поступлениях</span><br />
			<input type="submit" name="sbm" class="profileSend" value="Редактировать" />
		</div>
		</form>
		<div class="clear"><!-- --></div>
		<br />
		<div class="line"><!-- --></div>
		<?php
		include_once("./modules/shop/shop.helper.php"); 
		$shopModuleHelper=new shopModuleHelper;
		$wish=$this->db->query("SELECT `wishlist` FROM `users` WHERE `id`='".$this->ci->session->userdata("user_id")."' ")->result();
		if(!empty($wish)){
		?>
			<div class="wish">
				<h3>МОЙ WISHLIST</h3>
				<?php 
					$wishIDs = explode(",",$wish[0]->wishlist);
					$count = count($wishIDs);
					$products_query=$shopModuleHelper->products_query();
					if(empty($_POST['moreWish'])){
						$prod = $products_query->where_in("shop_products.id", $wishIDs)->limit(4)->get()->result();
					}else{
						$prod = $products_query->where_in("shop_products.id", $wishIDs)->get()->result();
					}
					foreach($prod as $p){
						$link = $shopModuleHelper->link_product_view($p);
						print '<a href="'.$link.'" class="hisRow"> <img src="/uploads/shop/products/thumbs4/'.$p->main_picture_file_name.'" border="0" /> <br />'.$p->title.'</a>';
					} 
					if($count>4 && empty($_POST['moreWish'])){ ?>
						<a href="#" class="moreBut" onclick="$('#moreWish').submit(); return false;" ></a>
						<form method="post" id="moreWish"><input name="moreWish" type="hidden" value="1" /></form>
					<?php } ?>
			<div class="clear"><!-- --></div>
			</div>
		<?php
		}
		
		$his=$this->db->query("SELECT `basket` FROM `shop_orders` WHERE `user_id`='".$this->ci->session->userdata("user_id")."' ")->result();
		$ids = array();
		foreach($his as $r){
			$bsk = json_decode($r->basket);
			foreach($bsk as $bb){
				foreach ($bb as $b){
					if(empty($b->id)) continue;
					$ids[]= $b->id;
				}
			}
		}
		$products_query=$shopModuleHelper->products_query();
		$count = count($ids);
		if(empty($_POST['historyWish'])){
			$prod = $products_query->where_in("shop_products.id", $ids)->limit(4)->get()->result();
		}else{
			$prod = $products_query->where_in("shop_products.id", $ids)->get()->result();
		}
		if(!empty($prod)){ ?>
			<br />
			<div class="line"><!-- --></div>
			<div class="historyBuy">
			<h3>МОЯ ИСТОРИЯ ПОКУПОК</h3>		
		<?php
			foreach($prod as $p){
				$link = $shopModuleHelper->link_product_view($p);
				print '<a href="'.$link.'" class="hisRow"> <img src="/uploads/shop/products/thumbs4/'.$p->main_picture_file_name.'" border="0" /> <br />'.$p->title.'</a>';
			}
			if($ids>4 && empty($_POST['historyWish'])){ ?>
				<a href="#" class="moreBut" onclick="$('#historyWish').submit(); return false;" ></a>
				<form method="post" id="historyWish"><input name="historyWish" type="hidden" value="1" /></form>
			<? }
			?><div class="clear"><!-- --></div></div><?php
		}
			?>
	</div>
<?php } ?>
