<?php
if($this->input->post("send_sm")!==false){
	$name=trim(htmlspecialchars($this->input->post("name")));
	$email=trim(htmlspecialchars($this->input->post("email")));
	$text=trim(htmlspecialchars($this->input->post("text")));

	$errors=array();
	if(empty($name))$errors[]='Поле "Имя" не заполнено!';
	if(empty($email))$errors[]='Поле "Электронная почта" не заполнено!';
	if(empty($text))$errors[]='Поле "Текст сообщения" не заполнено!';

	if(sizeof($errors)==0){
		$email_html="";

		$date_add_hmn=date("d.m.Y H:i:s");

		$email_html.=<<<EOF
<p><strong>Имя:</strong> {$name}</p>
<p><strong>E-mail:</strong> {$email}</p>
<p><strong>Текст:</strong><br />{$text}</p>
<p>&nbsp;</p>
<p><strong>IP:</strong> {$_SERVER['REMOTE_ADDR']}</p>
<p><strong>Дата:</strong> {$date_add_hmn}</p>
EOF;

		// получаем список всех администраторов
		$users_res=$this->db
		->select("users.id, users.username, users.email, users.first_name, users.last_name")
		->join("users_groups","users_groups.user_id = users.id && users_groups.group_id = 1")
		->group_by("users.email")
		->get_where("users",array(
			"active"=>1
		))
		->result();

		foreach($users_res AS $r)
		{
			$this->ci->email->from($this->ci->config->config['email_from'],$this->ci->config->config['email_from_name']);
			$this->ci->email->to($r->email,trim($r->first_name." ".$r->last_name));
			$this->ci->email->subject("Форма обратной связи");
			$this->ci->email->message($email_html);
			$this->ci->email->send();
		}

		redirect("/contacts.html?send_ok=1");
	}

}
?><div class="breadcrumbsW" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
	<a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <strong itemprop="title">Контакты</strong>
</div>

<div class="contactsPageW"><div class="contactsPage" itemscope itemtype="http://schema.org/Organization">

	<h1>Контакты</h1>

	<div class="topContacts" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
		<div class="topContactsRow address">
			<i></i><div class="tit">Наш адрес</div>
			<div class="cont"><span itemprop="addressLocality">г. Киев</span>, метро «Позняки» <br /><span itemprop="streetAddress">пр-т П. Григоренко 32-Д</span></div>
		</div>
		<div class="topContactsRow phone">
			<i></i><div class="tit">Стационарный телефон</div>
			<div class="cont"><span itemprop="telephone">(044) 229–10–65</span>,  <span itemprop="telephone">(044) 229–92–91</span></div>
		</div>
		<div class="topContactsRow email">
			<i></i><div class="tit">Электронная почта</div>
			<div class="cont"><a href="mailto:2299291@ukr.net" itemprop="email">2299291@ukr.net</a></div>
		</div>
		<div class="topContactsRow phone2">
			<i></i><div class="tit">Мобильный телефон</div>
			<div class="cont"><span itemprop="telephone">(067) 948–84–68</span></div>
		</div>

		<div class="clear"></div>
	</div>

	<div class="c2cols">
		<div class="c2cols1">
			<div class="tit">Связаться с нами</div>

			<?php
			if(isset($errors) && sizeof($errors)>0){
				?><div style="text-align:center; font-weight:bolder; color:red;"><?php print implode("<br />",$errors); ?></div>
				<br /><?php
			}else
			if($this->input->get("send_ok")==1){
				?><div style="text-align:center; font-weight:bolder; color:green;">Спасибо, Ваши данные успешно отправлены!</div>
				<br /><?php
			}
			?>


			<form action="" method="post" class="form">
				<div class="formRow">
					<input type="text" name="name" placeholder="Имя" value="" />
				</div>
				<div class="formRow">
					<input type="text" name="email" placeholder="Электронная почта" value="" />
				</div>
				<div class="formRow">
					<textarea name="text" placeholder="Текст сообщение"></textarea>
				</div>
				<div class="formRow">
					<button type="submit" name="send_sm" value="1">Отправить</button>
				</div>
			</form>
		</div>
		<div class="c2cols2">
			<div class="tit">Мы на карте</div>
			<div class="sdMapW">
				<iframe width="351" height="351" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.ru/maps?f=q&amp;source=s_q&amp;hl=ru&amp;geocode=&amp;q=%D0%BA%D0%B8%D0%B5%D0%B2&amp;aq=&amp;sll=55.354135,40.297852&amp;sspn=21.413509,40.649414&amp;ie=UTF8&amp;hq=&amp;hnear=%D0%9A%D0%B8%D0%B5%D0%B2,+%D0%B3%D0%BE%D1%80%D0%BE%D0%B4+%D0%9A%D0%B8%D0%B5%D0%B2,+%D0%A3%D0%BA%D1%80%D0%B0%D0%B8%D0%BD%D0%B0&amp;t=m&amp;z=10&amp;ll=50.4501,30.5234&amp;output=embed"></iframe><br /><small><a href="http://maps.google.ru/maps?f=q&amp;source=embed&amp;hl=ru&amp;geocode=&amp;q=%D0%BA%D0%B8%D0%B5%D0%B2&amp;aq=&amp;sll=55.354135,40.297852&amp;sspn=21.413509,40.649414&amp;ie=UTF8&amp;hq=&amp;hnear=%D0%9A%D0%B8%D0%B5%D0%B2,+%D0%B3%D0%BE%D1%80%D0%BE%D0%B4+%D0%9A%D0%B8%D0%B5%D0%B2,+%D0%A3%D0%BA%D1%80%D0%B0%D0%B8%D0%BD%D0%B0&amp;t=m&amp;z=10&amp;ll=50.4501,30.5234" style="color:#0000FF;text-align:left">Просмотреть увеличенную карту</a></small>
			</div>
		</div>
	</div>

</div></div>