<?php
if($this->input->post("send_calc_results_sm")!==false
	&& $this->input->post("data")!==false
	&& $this->input->post("sum")!==false
	&& $this->input->post("phone")!==false){

	$sum=htmlspecialchars($this->input->post("sum"));

	$name=trim(htmlspecialchars($this->input->post("name")));
	$email=trim(htmlspecialchars($this->input->post("email")));
	$phone=trim(htmlspecialchars($this->input->post("phone")));

	$message_text="";
	$message_text.=<<<EOF
<p><h2>Новый просчет стоимости кухни.</h2></p>
<p>&nbsp;</p>
<p><strong>Имя:</strong> {$name}</p>
<p><strong>E-mail:</strong> {$email}</p>
<p><strong>Телефон:</strong> {$phone}</p>
<p><strong>Сумма:</strong> {$sum} грн.</p>
<p>&nbsp;</p>
<p><strong>Данные:</strong></p>
EOF;
	if(is_array($this->input->post("data")) && sizeof($this->input->post("data"))>0){
		foreach($this->input->post("data") AS $r)
		{
			list($name,$val)=$r;
			
			$name=htmlspecialchars($name);
			$val=htmlspecialchars($val);

			$message_text.=<<<EOF
<p><strong>{$name}:</strong> {$val}</p>
EOF;
		}
	}		$message_text.=<<<EOF
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>IP: {$_SERVER['REMOTE_ADDR']}</p>
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
		$this->ci->email->subject("Новый просчет стоимости кухни");
		$this->ci->email->message($message_text);
		$this->ci->email->send();
	}

	print 1;
	exit;
}
?><div class="breadcrumbsW" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
	<a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <strong itemprop="title">Калькулятор стоимости кухни</strong>
</div>

<div class="calculatorW calculatorWkitchen"><div class="calculatorI">
	<h1>Стоимость кухни</h1>
	<div class="pic2"></div>
	
<div class="caption">
<?php
if(!empty($this->ci->module->d['page_res']->content)){
  print $this->ci->module->d['page_res']->content;
  ?><br /><br /><?php
}
?>
</div>

	<script type="text/javascript">
	$(document).ready(function(){
		$("select").selectbox({
			speed: 400
		});
	});

 function calc1(ret)
 {
 m0=eval($("select[name='material']").val());
 f0=eval($("select[name='furnitura']").val());
 f1=eval($("select[name='suhlad']").val());
 s0=eval($("select[name='fasad']").val());
 t0=eval($("select[name='stol']").val());
 g0=eval($("select[name='vitrag']").val());
 x1=eval($("input[name='sx']").val()/1000);
 y1=eval($("input[name='sy']").val()/1000);
 z1=eval($("select[name='sizez']").val());
 sv=eval($("select[name='svet']").val());
 bar=eval($("select[name='bar']").val());
 w0=0;
 if (y1>0)
 w0=0.6;

 sum1=(((x1+y1-w0)*(m0+f0*f1+s0+t0)+g0)*z1+sv+bar)*1.6*8.2;
 sum11=parseInt(sum1);

 if (x1<0.6) {
 	alert("Значение X не может быть меньше 600 мм!");
 }

 sum11=(sum11/100)*<?php print $this->ci->config->config['calc']['kitchen']; ?>;
 
 
 	if(ret){
 		return sum11;
 	}else{
 		$("#results_modal .total_amount").text(sum11);
 		$("#results_modal").modal();
 		// alert('Цена вашей конфигурации: '+sum11+' грн.');
 	}
 }

 function sendCalcResults()
 {
 	var data=[];
	$("#calcForm .formRow").each(function(){
		if($("input:text",this).length){
			var val=$("input:text",this).val();
		}else{
			if($("select option:selected",this).length==0){
				var val=$("select option:first",this).text();
			}else{
				var val=$("select option:selected",this).text();
			}
		}
		data[data.length]=[$("label:first",this).text(),val];
	});

	if($.trim($(".modal input#phone").val())==""){
		alert('Введите телефон!');
		return false;
	}

	$.post(document.location.href,{
		send_calc_results_sm:1,
		data:data,
		sum:calc1(true),
		name:$.trim($(".modal input#name").val()),
		email:$.trim($(".modal input#email").val()),
		phone:$.trim($(".modal input#phone").val())
	},function(d){
		if(parseInt(d)!=1){
			alert(d);
		}else{
			$(".modal button.close").click();
			alert('Ваши данные успешно отправлены!');
		}
	});
 }
</script>

<div id="results_modal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Результат расчета</h3>
  </div>
  <div class="modal-body" style='width:100%;'>
  <center>
   	<strong>Цена вашей конфигурации: <span class="total_amount">100</span> грн.</strong>
   	</center>
   	<br />
    <p>
    <input placeholder="Имя" type="text" name="name" id="name" value="" /><br />
    <input placeholder="E-mail" type="text" name="email" id="email" value="" /><br />
    <input placeholder="Телефон *" name="phone" id="phone" type="text" value="" />
    </p>
  </div>
  <div class="modal-footer">
    <a href="#" onclick="$(this).parents('.modal:eq(0)').find('button.close:first').click(); return false;" class="btn">Отмена</a>
    <a href="#" onclick="sendCalcResults(); return false;" class="btn btn-primary">Отправить заявку</a>
  </div>
</div>

	<form id="calcForm" action="" class="form" method="post">
		<div class="formRow">
			<div class="sw"><input type="text" name="sx" id="" value="1000" placeholder="mm" /></div>
			<label for="">Левая сторона</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="sy" id="" placeholder="mm" /></div>
			<label for="">Правая сторона</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="sizez">
				<option value="1">720мм</option>
				<option value="1.3">900мм</option>
			</select></div>
			<label for="">Высота верхних ящиков</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="material">
				<option value="100">ДСП Swisspan</option>
				<option value="110">ДСП Egger</option>
			</select></div>
			<label for="">Материал</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="stol">
				<option value="26">28мм </option>
				<option value="35">38мм </option>
			</select></div>
			<label for="">Столешница</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="furnitura">
				<option value="12">Linken</option>
				<option value="16">Muller</option>
				<option value="85">Blum</option>
			</select></div>
			<label for="">Фурнитура</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="suhlad">
				<option value="1">нет</option>
				<option value="1,2">от 1 до 3</option>
				<option value="1,3">oт 3 до 6</option>
				<option value="1,5">от 6 до 9</option>
			</select></div>
			<label for="">Количество выдвижных ящиков</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="vitrag">
				<option value="0">нет</option>
				<option value="25">от 0 до 2</option>
				<option value="45">от 3 до 5</option>
				<option value="70">от 6 до 8</option>
			</select></div>
			<label for="">Витражи</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="fasad">
				<option value="70">ДСП в ПВХ кромке</option>
				<option value="108">МДФ Пленочный</option>
				<option value="120">МДФ Крашенный</option>
				<option value="118">МДФ Пластик</option>
				<option value="125">Кашированые</option>
			</select></div>
			<label for="">Фасады</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="svet">
				<option value="0">нет</option>
				<option value="5">есть</option>
			</select></div>
			<label for="">Подсветка</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="bar">
				<option value="0">нет</option>
				<option value="80">есть</option>
			</select></div>
			<label for="">Барная стойка</label>
		</div>

		<div class="clear"></div>
		<br />

		<center><button type="button" onclick="calc1(false); return false;">Рассчитать стоимость</button></center>
	</form>
</div></div>