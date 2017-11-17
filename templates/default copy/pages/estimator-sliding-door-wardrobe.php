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
<p><h2>Новый просчет стоимости шкафа-купе.</h2></p>
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
  }   $message_text.=<<<EOF
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
    $this->ci->email->subject("Новый просчет стоимости шкафа-купе");
    $this->ci->email->message($message_text);
    $this->ci->email->send();
  }

  print 1;
  exit;
}
?>

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

<div class="breadcrumbsW" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
  <a href="/" itemprop="url"><span itemprop="title">Главная</span></a> → <strong itemprop="title">Калькулятор стоимости шкафа-купе</strong>
</div>

<div class="calculatorW"><div class="calculatorI">
	<h1>Стоимость шкафа-купе</h1>
<div class="caption">
<?php
if(!empty($this->ci->module->d['page_res']->content)){
  print $this->ci->module->d['page_res']->content;
  ?><br /><br /><?php
}
?>
</div>
  <div class="pic"></div>

	<script type="text/javascript">
	$(document).ready(function(){
		$("select").selectbox({
			speed: 400
		});
	});
	</script>
	<form id="calcForm" action="" class="form" method="post" name="calculation_cupboard" style="position:relative;">
		<div class="formRow">
			<div class="sw"><input type="text" name="widtsh" placeholder="mm" id=""></div>
			<label for="">Высота</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="height" placeholder="mm" id=""></div>
			<label for="">Ширина</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="depth" placeholder="mm" id=""></div>
			<label for="">Глубина</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="look_cupboard">
				<option value="body_cupboard">корпусный</option>
				<option value="fitted_cupboard">встроенный</option>
			</select></div>
			<label for="">Вид шкафа</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="dsp">
				<option value="dbm">ДСП SWISSPAN</option>
				<option value="egger">ДСП EGGER</option>
			</select></div>
			<label for="">Материал корпуса</label>
		</div>
		<div class="formRow">
			<div class="sw"><select name="steel">
				<option value="usual">ARISTO</option>
				<option value="aluminium">ELITE</option>
				<option value="luxe">CIDECO</option>
			</select></div>
			<label for="">Раздвижная сиситема</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="colvo_public" placeholder="шт" id=""></div>
			<label for="">Общее количество дверей</label>
		</div>


		<div class="formRow formRowTit">
			<span>Наполнение дверей</span>
		</div>


		<div class="formRow">
			<div class="sw"><input type="text" name="filling_DSP" placeholder="шт" id=""></div>
			<label for="">ДСП</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="filling_ratang" placeholder="шт" id=""></div>
			<label for="">Ратанг</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="filling_ob_mirror_not_drawing" placeholder="шт" id=""></div>
			<label for="">Двери с обычным зеркалом без рисунка</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="filling_cv_mirror_not_drawing" placeholder="шт" id=""></div>
			<label for="">Двери с цветным зеркалом без рисунка</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="filling_ob_mirror_with_drawing" placeholder="шт" id=""></div>
			<label for="">Двери с обычным зеркалом с рисунком</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="filling_cv_mirror_with_drawing" placeholder="шт" id=""></div>
			<label for="">Двери с цветным зеркалом с рисунком</label>
		</div>


		<div class="formRow formRowTit">
			<span>Дополнительные элементы</span>
		</div>


		<div class="formRow">
			<div class="sw"><input type="text" name="extra_box_usual" placeholder="шт" id=""></div>
			<label for="">Ящики обыкновенные</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="extra_box_full_out" placeholder="шт" id=""></div>
			<label for="">Ящики на направляющих полного выдвижения</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="extra_box_blum" placeholder="шт" id=""></div>
			<label for="">Ящики на направляющих Blum с доводчиком</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="pantograph" placeholder="шт" id=""></div>
			<label for="">Пантограф</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="box_grid_chrome" placeholder="шт" id=""></div>
			<label for="">Ящик-сетка (хром)</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="box_ander_shoes" placeholder="шт" id=""></div>
			<label for="">Сетка под обувь (хром)</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="lamp_cut_in" placeholder="шт" id=""></div>
			<label for="">Светильник врезной</label>
		</div>
		<div class="formRow">
			<div class="sw"><input type="text" name="lamp_decorative" placeholder="шт" id=""></div>
			<label for="">Светильник декоративный</label>
		</div>

		<div class="clear"></div>
		<br />

		<center><button type="button" onclick="check_form(); return false;">Рассчитать стоимость</button></center>
	</form>
</div></div>

<script type="text/javascript">
var lsum=0;
function nWin(x) {
      var ln = x;
      var newWin = window.open(ln, "", "toolbar=no, scrollbars=yes, left=20, top=20, width=605, height=600, resizable=0");
      newWin.focus();
}

function initialization_array(){
   var priceCompl = new Array();
//цена транспортных расходов
   priceCompl['transport_costs'] = "300";
//цена 1-го метра корпусного шкафа
   priceCompl['price_1_corp_cupboard'] = "1450";
//цена 1-го метра встроенного шкафа
   priceCompl['price_1_vst_cupboard'] = "800";
//цена 2-го метра обоих шкафов
   priceCompl['price_2_both_cupboards'] = "800";
//--------------------------------------------------------------------------------------
//цена стальной двери
   priceCompl['price_steel_door'] = "750";
//цена двери сталь люкс
   priceCompl['price_door_steel_luxe'] = "750";
//цена алюминиевой двериы
   priceCompl['price_aluminium_door'] = "1000";
//цена 1-го м. рельсы
   priceCompl['price_1m_rails'] = "180";
//--------------------------------------------------------------------------------------
//цена 1-го кв. м. ДСП. Наполнение
   priceCompl['price_1m_kv_dsp_nap'] = "120";
//цена 1-го кв. м. Зеркала обшивочного
   priceCompl['price_1m_kv_mirror_obsh'] = "200";
//цена 1-го кв. м. Зеркала цветного
   priceCompl['price_1m_kv_mirror_color'] = "280";
//цена 1-го листа ратанга
   priceCompl['price_one_leaf_ratang'] = "1000";
//цена 1-го кв. м. пискоструйки
   priceCompl['price_1m_kv_piskostrujki'] = "400";
//--------------------------------------------------------------------------------------
//ДОПОЛНИТЕЛЬНЫЕ ЕЛЕМЕНТЫ
//Ящики обыкновенные
   priceCompl['dop_box_usual'] = "60";
//Ящики на направляющих полного выдвижения
   priceCompl['dop_box_napr_poln'] = "100";
//Ящики на направляющих Blum с доводчиком
   priceCompl['dop_box_blum'] = "240";
//Пантограф
   priceCompl['dop_pantograph'] = "450";
//Ящик - сетка (хром)
   priceCompl['dop_box_gird'] = "450";
//Сетка под обувь (хром)
   priceCompl['dop_gird_ander_shoes'] = "450";
//Светильник врезной
   priceCompl['dop_lamp_cut'] = "50";
//Светильник декоративный
   priceCompl['dop_lamp_dekor'] = "175";
//--------------------------------------------------------------------------------------
//НАЦЕНКА НА СБОРКУ
   priceCompl['markup_on_assembling'] = "30";
//--------------------------------------------------------------------------------------
   return priceCompl;
}


  var obform = document.calculation_cupboard;
   var in_necessarily = new Array();
      in_necessarily[0] = "widtsh";
      in_necessarily[1] = "height";
      in_necessarily[2] = "depth";
      in_necessarily[3] = "colvo_public";
      in_necessarily[4] = "filling_DSP";
      in_necessarily[5] = "filling_ratang";
      in_necessarily[6] = "filling_ob_mirror_not_drawing";
      in_necessarily[7] = "filling_cv_mirror_not_drawing";
      in_necessarily[8] = "filling_ob_mirror_with_drawing";
      in_necessarily[9] = "filling_cv_mirror_with_drawing";

   var in_necessarily_text = new Array();
      in_necessarily_text[0] = "Введите пожалуйста высоту шкафа";
      in_necessarily_text[1] = "Введите пожалуйста ширину шкафа";
      in_necessarily_text[2] = "Введите пожалуйста глубину шкафа";
      in_necessarily_text[3] = "Введите пожалуйста общее количество дверей";
      in_necessarily_text[4] = "Общее количество НАПОЛНЕНИЯ дверей должно быть равным общему количеству дверей";

      in_necessarily_text[5] = "( ДСП )";
      in_necessarily_text[6] = "( Ратанг )";
      in_necessarily_text[7] = "( Двери с обшитым зеркалом без рисунка )";
      in_necessarily_text[8] = "( Двери с цветным зеркалом без рисунка  )";
      in_necessarily_text[9] = "( Двери с общим зеркалом с рисунком  )";
      in_necessarily_text[10] = "( Двери с цветным зеркалом с рисунком  )";

      in_necessarily_text[11] = "высота";
      in_necessarily_text[12] = "шырина";
      in_necessarily_text[13] = "глубина";
      in_necessarily_text[14] = "количество дверей";
      in_necessarily_text[15] = "(ях) наполнения дверей";

  var not_in_necessarily = new Array();
      not_in_necessarily[0] = "extra_box_usual";
      not_in_necessarily[1] = "extra_box_full_out";
      not_in_necessarily[2] = "extra_box_blum";
      not_in_necessarily[3] = "pantograph";
      not_in_necessarily[4] = "box_grid_chrome";
      not_in_necessarily[5] = "box_ander_shoes";
      not_in_necessarily[6] = "lamp_cut_in";
      not_in_necessarily[7] = "lamp_decorative";

function check_form(){

   for( var i=0 ; i<4 ; i++ ){
     if(obform.elements[in_necessarily[i]].value == ""){
        alert(in_necessarily_text[i]);
        obform.elements[in_necessarily[i]].focus();
        return false;
     }else if(isNaN(obform.elements[in_necessarily[i]].value) == true){
        alert("В поле " + in_necessarily_text[i+11] + " должно быть числовое значение");
        obform.elements[in_necessarily[i]].focus();
        obform.elements[in_necessarily[i]].select();
        return false;
     }
    obElemPer = parseFloat(obform.elements[in_necessarily[i]].value);
    if(in_necessarily[i] == "widtsh"){
         if( (obElemPer < 1000) || (obElemPer > 2800) ){
              alert("Высота шкафа должна быть в пределах: от 1000(мм) до 2800(мм)");
              obform.elements[in_necessarily[i]].focus();
              obform.elements[in_necessarily[i]].select();
              return false;
         }
    }
    if(in_necessarily[i] == "height"){
         if( (obElemPer < 800) || (obElemPer > 10000) ){
              alert("Шырина шкафа должна быть в пределах: от 800(мм) до 10000(мм)");
              obform.elements[in_necessarily[i]].focus();
              obform.elements[in_necessarily[i]].select();
              return false;
         }
    }
    if(in_necessarily[i] == "depth"){
         if( (obElemPer < 300) || (obElemPer > 2000) ){
              alert("Глубина шкафа должна быть в пределах: от 300(мм) до 2000(мм)");
              obform.elements[in_necessarily[i]].focus();
              obform.elements[in_necessarily[i]].select();
              return false;
         }
    }
   }
   var check_nap_door_per = 0;
   var check_dop_elem_per = 0;
   check_nap_door_per = check_nap_door();
   if(check_nap_door_per == true) check_dop_elem_per = check_dop_elem();
   if(check_dop_elem_per == true) account_general();

return true;
}


function check_nap_door(){
var colvo_nap_door = 0;
obColvoPub = parseFloat(obform.colvo_public.value);
obHeight = parseFloat(obform.height.value);
   if( (obHeight/obColvoPub) < 400){
         alert("При задонной ширине шкафа и количестве дверей - получается слишком большое количество дверей");
         obform.colvo_public.focus();
         obform.colvo_public.select();
         return false;
   }
   if( (obHeight/obColvoPub) > 1800){
         alert("При задонной ширине шкафа и количестве дверей - получается слишком малое количество дверей");
         obform.colvo_public.focus();
         obform.colvo_public.select();
         return false;
   }
   if( obColvoPub < 2){
         alert("Минимальное количество дверей должно быть не менее 2-х");
         obform.colvo_public.focus();
         obform.colvo_public.select();
         return false;
   }

   for( var i=4 ; i<10 ; i++ ){
       if( obform.elements[in_necessarily[i]].value != "" && (isNaN(obform.elements[in_necessarily[i]].value) == true)){
          alert("В поле " + in_necessarily_text[i+1] + " должно быть числовое значение");
          obform.elements[in_necessarily[i]].focus();
          obform.elements[in_necessarily[i]].select();
          return false;
       }else if( obform.elements[in_necessarily[i]].value != "" && (isNaN(obform.elements[in_necessarily[i]].value) == false )){
          var nap_door = parseFloat(obform.elements[in_necessarily[i]].value);
          obformElem = parseFloat(obform.elements[in_necessarily[i]].value);
          colvo_nap_door =  colvo_nap_door + obformElem;
       }
   }
   if(colvo_nap_door != obColvoPub){
      alert(in_necessarily_text[4]);
      return false;
   }
   return true;
}


function check_dop_elem(){
  var augmented_element_text = new Array();
      augmented_element_text[0] = "ящики обыкновенные";
      augmented_element_text[1] = "ящики на направляющих полного выдвижения";
      augmented_element_text[2] = "ящики на направляющих Blum с доводчиком";
      augmented_element_text[3] = "пантограф";
      augmented_element_text[4] = "ящик - сетка (хром)";
      augmented_element_text[5] = "сетка под обувь (хром)";
      augmented_element_text[6] = "светильник врезной";
      augmented_element_text[7] = "светильник декоративный";

   var colvoJashcikov = 0;
   var colvoLamps = 0;
   var obColvoPub = parseFloat(obform.colvo_public.value);
   for( var j=0 ; j<8 ; j++ ){

         if(isNaN(obform.elements[not_in_necessarily[j]].value) == true){
                   alert("В поле - " + augmented_element_text[j] + ", должно быть числовое значение");
                   obform.elements[not_in_necessarily[j]].focus();
                   obform.elements[not_in_necessarily[j]].select();
                   return false;
         }

         if(j==3 && colvoJashcikov > 30 && (( obform.elements["extra_box_usual"].value || obform.elements["extra_box_full_out"].value || obform.elements["extra_box_blum"].value ) != "" )){
              alert("Сумма всех ящиков не должна превышать 30-ть(шт)");
              return false;
         }
         if( obform.elements[not_in_necessarily[j]].value != "" && (isNaN(obform.elements[not_in_necessarily[j]].value) == false)){
              var obColvoElem = parseFloat(obform.elements[not_in_necessarily[j]].value);
              if( (not_in_necessarily[j] == "extra_box_usual") || (not_in_necessarily[j] == "extra_box_full_out") || (not_in_necessarily[j] == "extra_box_blum") ){
                   colvoJashcikov = colvoJashcikov + obColvoElem;
              }
              if( not_in_necessarily[j] == "pantograph" &&  obColvoElem > obColvoPub){
                   alert("Количество парнографов должно быть не более количества дверей");
                   obform.elements[not_in_necessarily[j]].focus();
                   obform.elements[not_in_necessarily[j]].select();
                   return false;
              }
              if( not_in_necessarily[j] == "box_grid_chrome" &&  obColvoElem != obColvoPub){
                   alert("Количество (Ящик - сетка (хром)), должно быть равно количеству дверей");
                   obform.elements[not_in_necessarily[j]].focus();
                   obform.elements[not_in_necessarily[j]].select();
                   return false;
              }
              if( not_in_necessarily[j] == "box_ander_shoes" &&  obColvoElem > 10){
                   alert("Количество сеток под обувь (хром), должно быть не более 10(шт)");
                   obform.elements[not_in_necessarily[j]].focus();
                   obform.elements[not_in_necessarily[j]].select();
                   return false;
              }
              if( (not_in_necessarily[j] == "lamp_cut_in") || (not_in_necessarily[j] == "lamp_decorative") ){
                   colvoLamps = colvoLamps + obColvoElem;
              }
         }
         if(j==7 && colvoLamps != obColvoPub && ((obform.elements["lamp_cut_in"].value || obform.elements["lamp_decorative"].value ) != "" ) ){
              alert("Сумма всех светильников должна быть равна количеству дверей");
              return false;
         }
   }
   return true;
}

function account_general(){
  var f = 0;
  var f1 = 0;
  var f2 = 0;
  var f3 = 0;
  var f4 = 0;
  var general_sum = 0;
  var round_up_per = 0;

         priceCompl = initialization_array();
         f = account_F();
         f1 = account_F1(f);
         f2 = account_F2();
         f3 = account_F3();
         f4 = account_F4();
         general_sum = f1 + f2 + f3 + f4;
         round_up_per = round_up(general_sum, 0);
         show_msg(round_up_per);

}

function account_F(){
    var F = 0;
    var price_1_go_m = 0;

    if(obform.look_cupboard[0].checked) price_1_go_m = parseFloat(priceCompl['price_1_corp_cupboard']);
    if(obform.look_cupboard[1].checked) price_1_go_m = parseFloat(priceCompl['price_1_vst_cupboard']);

    F = (price_1_go_m + ((( obform.height.value - 1000) / 1000 ) * priceCompl['price_2_both_cupboards']));

priceCompl['price_2_both_cupboards']
    return F;
}

function account_F1(F){
    var F1 = 0;
    var value_600 = 0;
    value_600 = (obform.depth.value - 600);
    if(value_600 == 0) F1 = F;

    if(value_600 < 0) F1 = (F - ((F/100) * ((value_600/10) * (-1) )));
    if(value_600 > 0) F1 = (F + ((F/100) * (value_600/6.45)));

    if(obform.dsp[1].checked) F1 = (F1 + (F1*0.5));
   return F1;
}

function account_F2(){
    var F2 = 0;
    var price_rasdv_syst = 0;
    if(obform.steel[0].checked) price_rasdv_syst = parseFloat(priceCompl['price_steel_door']);
    if(obform.steel[1].checked) price_rasdv_syst = parseFloat(priceCompl['price_door_steel_luxe']);
    if(obform.steel[2].checked) price_rasdv_syst = parseFloat(priceCompl['price_aluminium_door']);

    F2 = ((price_rasdv_syst * obform.colvo_public.value) + (obform.height.value / 1000 * parseFloat(priceCompl['price_1m_rails'])));
   return F2;
}

function account_F3(){
    var F3 = 0;
    var square_door = 0;
    square_door = obform.height.value * obform.widtsh.value / 1000000 / obform.colvo_public.value;
    F3 = parseFloat(priceCompl['price_1m_kv_dsp_nap']) * square_door * obform.filling_DSP.value +
         parseFloat(priceCompl['price_one_leaf_ratang']) * obform.filling_ratang.value +
         parseFloat(priceCompl['price_1m_kv_mirror_obsh']) * square_door * obform.filling_ob_mirror_not_drawing.value +
         parseFloat(priceCompl['price_1m_kv_mirror_color']) * square_door * obform.filling_cv_mirror_not_drawing.value +
         ((parseFloat(priceCompl['price_1m_kv_mirror_obsh']) + parseFloat(priceCompl['price_1m_kv_piskostrujki'])) * square_door * obform.filling_ob_mirror_with_drawing.value ) +
         ((parseFloat(priceCompl['price_1m_kv_mirror_color']) + parseFloat(priceCompl['price_1m_kv_piskostrujki'])) * square_door * obform.filling_cv_mirror_with_drawing.value );
    return F3;
}

function account_F4(){
    var F4 = 0;
    F4 = (parseFloat(priceCompl['dop_box_usual']) * obform.extra_box_usual.value + parseFloat(priceCompl['dop_box_napr_poln']) * obform.extra_box_full_out.value +
         parseFloat(priceCompl['dop_box_blum']) * obform.extra_box_blum.value + parseFloat(priceCompl['dop_pantograph']) * obform.pantograph.value +
         parseFloat(priceCompl['dop_box_gird']) * obform.box_grid_chrome.value + parseFloat(priceCompl['dop_gird_ander_shoes']) * obform.box_ander_shoes.value +
         parseFloat(priceCompl['dop_lamp_cut']) * obform.lamp_cut_in.value + parseFloat(priceCompl['dop_lamp_dekor']) * obform.lamp_decorative.value);
    return F4;
}

  function show_msg(text){
    // var d = document;
    // var msg = d.getElementById('msg');
    // msg.innerHTML = '<span>Цена шкафа купе - ' + text + ' грн.</span>';
    lsum=text;

    text=(text/100)*<?php print $this->ci->config->config['calc']['cabinet']; ?>;
    
    $("#results_modal .total_amount").text(text);
    $("#results_modal").modal();
    // alert('Цена шкафа купе - ' + text + ' грн.');
  }

function round_up(price, num_okr){
         var variable_time = Math.pow(10, num_okr);
         price = Math.round( price * variable_time) / variable_time;
         return price;
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
    sum:lsum,
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