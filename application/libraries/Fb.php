<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth
*
* Author: Ben Edmunds
*		  ben.edmunds@gmail.com
*         @benedmunds
*
* Added Awesomeness: Phil Sturgeon
*
* Location: http://github.com/benedmunds/CodeIgniter-Ion-Auth
*
* Created:  10.01.2009
*
* Description:  Modified auth system based on redux_auth with extensive customization.  This is basically what Redux Auth 2 should be.
* Original Author name has been kept but that does not mean that the method has not been modified.
*
* Requirements: PHP5 or above
*
*/

class Fb
{
	/**
	 * CodeIgniter global
	 *
	 * @var string
	 **/
	protected $ci;
	protected $editor_exists=false;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	/**
	 * __call
	 *
	 * Acts as a simple way to call model methods without loads of stupid alias'
	 *
	 **/
	public function __call($method, $arguments)
	{
		if (!method_exists( $this->ci->ion_auth_model, $method) )
		{
			throw new Exception('Undefined method Fb::' . $method . '() called');
		}
	}

	private $elements=array();
	protected $errors=array();
	public $submit=false;

	private function add_element($methodName,$type,$p)
	{
		$p['method_name']=$methodName;
		if(!isset($this->elements))$this->elements=array();
		if(!isset($p['order']))$p['order']=sizeof($this->elements);
		if(!isset($p['parent']))$p['parent']="";

		$p['type']=$type;

		if(isset($p['order:before']) || isset($p['order:after'])){
			foreach($this->elements AS $i=>$r)
			{
				if($r['name']==(isset($p['order:before'])?$p['order:before']:$p['order:after'])){
					$find_index=$i;
					$find_order=$r['order'];
					break;
				}
			}

			if(isset($find_index) && $find_index>0){
				if(isset($p['order:before'])){
					$p['order']=$find_order-1;
				}elseif(isset($p['order:after'])){
					$p['order']=$find_order;
				}
			}

			$this->elements[]=$p;
		}else{
			$this->elements[]=$p;
		}
	}

	private function _elements_order($a,$b)
	{
		return $this->current_elements[$a]['order']<$this->current_elements[$b]['order']?-1:1;
	}

	private $current_elements=array();
	public function sort_elements($elements)
	{
		$this->current_elements=$elements;
		uksort($this->current_elements,array($this,"_elements_order"));

		return $this->current_elements;
	}

	var $form_have_dynamic_elements=NULL;
	var $formName="";
	public function render($parent="",$p=array(),$firstRun=true)
	{
		$outHtml="";

		$elements=array();
                               
		foreach($this->elements AS $k=>$r)
		{
			if($this->editor_exists===false && ($r['method_name']=="textarea" && $r['type']=="editor")){
				$this->editor_exists=true;
			}

			if($firstRun){
				// ищем форму, и записываем ее имя
				if($r['method_name']=="form" && empty($this->formName)){
					$this->formName=$r['name'];
				}
			}

			if($firstRun){
				// ищем в форме динамические елементы в форме, чтоб установить onsubmit в форме
				if(isset($r['dynamic']) && $r['dynamic']==true){
					if(!isset($this->form_have_dynamic_elements))$this->form_have_dynamic_elements=array();
					$this->form_have_dynamic_elements[]=$r['id'];
				}
			}
			if($r['parent']==$parent){
				$elements[$k]=$r;
                        }
		}
                
		$elements=$this->sort_elements($elements);

		if($p['method_name']=="greed" && empty($p['type'])){
			$p['type']="horizontal";
		}

		// в форме есть редактор, подключаем нужные js и css
		if($this->editor_exists===true){
			$outHtml.="<script type=\"text/javascript\" src=\"/templates/default/admin/assets/tiny_mce/jquery.tinymce.js\"></script>";
			unset($this->editor_exists);
		}
                
		foreach($elements AS $k=>$r)
		{
			unset($this->elements[$k]);

			if(!empty($p['type']) && $p['type']!="horizontal" && $p['type']!="float"){
				$outHtml.="\t<tr>\n";
			}
			if($p['method_name']=="greed" && $p['type']!="float"){
				$outHtml.="\t\t<td";
				if(isset($r['parent:valign'])){
					$outHtml.=" valign=\"".$r['parent:valign']."\"";
				}elseif(isset($p['child:valign'])){
					$outHtml.=" valign=\"".$p['child:valign']."\"";
				}
				if(isset($r['parent:align'])){
					$outHtml.=" align=\"".$r['parent:align']."\"";
				}elseif(isset($p['child:align'])){
					$outHtml.=" align=\"".$p['child:align']."\"";
				}

				if(isset($r['parent:style'])){
					$outHtml.=" style=\"".$r['parent:style']."\"";
				}
				
				$outHtml.=">\n\t\t\t";
			}
			if($p['method_name']=="greed" && $p['type']=="float"){
				$outHtml.="<div style=\"float:left;";
				if(isset($r['parent:style']))$outHtml.=" ".$r['parent:style'];
				$outHtml.="\">";
			}
			
			if($r['method_name']=="greed"){
				$greedHtml=$this->render($r['name'],$r,false);
				if(!empty($greedHtml)){
					if($r['type']!="float"){
						$outHtml.="<table";
						
						if($r['border']==1)$outHtml.=" border=\"1\"";
						
						if($r['padding']>0)$outHtml.=" cellpadding=\"".$r['padding']."\"";
						if($r['width']>0)$outHtml.=" width=\"".$r['width']."\"";

						if(!empty($r['style']))$outHtml.=" style=\"".$r['style']."\"";
						$outHtml.=">\n";
						if($r['type']=="horizontal"){
							$outHtml.="\t<tr>\n";
						}
					}
					
					$outHtml.=$greedHtml; // GREED CONTENT

					if($r['type']!="float"){
						if($r['type']=="horizontal"){
							$outHtml.="\t</tr>\n";
						}
						$outHtml.="</table>\n";
					}else{
						if($p['clear']!==false){
							$outHtml.="<div style=\"clear:both;\"><!-- --></div>";
						}
					}
				}
			}elseif($r['method_name']=="form"){

				if(!isset($r['id']))$r['id']=md5(uniqid(rand(),1));
				if(sizeof($this->form_have_dynamic_elements)>0){
					$outHtml.="<script>
var formHelper=new Object();
$(document).ready(function(){
	formHelper['{$this->formName}']=cpFormHelper({formId:'".$r['id']."',dynamicElements:['".implode("','",$this->form_have_dynamic_elements)."']});
});
</script>
";
				}
				if(!empty($r['disable_form_overflow'])){
					print $r['id'];
					$outHtml.=<<<EOF
<script>
$(document).ready(function(){
	var o=$("#disable_form_over-{$r['id']}");
	o.css({
		position:"absolute",
		"background-color":"rgba(0,0,0,0.5)",
		"margin-left":-3,
		"margin-top":-3,
		width:$("#{$r['id']}").width()+6,
		height:$("#{$r['id']}").height()+6,
		"text-align":"center"
	});

	var vhc=$(".vhcenter",o);
	vhc.css({
		"text-align":"center",
		position:"absolute",
		"color":"#FFFFFF",
		"font-size":"25px"
	});
	vhc.css({
		left:($(o).width()-vhc.width())/2,
		top:50
	});
});
</script>
<div class="disable_form_over" id="disable_form_over-{$r['id']}">
<div class="vhcenter">{$r['disable_form_overflow']}</div>
</div>
EOF;
				}
				$outHtml.="<form enctype=\"multipart/form-data\"";

				// проверяем есть ли в форме елемент с динамической загрузкой, если есть, то нам надо перехватить событие onsubmit...
				if(sizeof($this->form_have_dynamic_elements)){
					$outHtml.=" onsubmit=\"return formHelper['{$this->formName}'].submitForm();\"";
				}

				$outHtml.=" method=\"".($r['method']=="get"?"get":"post")."\"";
				if(isset($r['name']))$outHtml.=" name=\"".$r['name']."\"";
				if(isset($r['id']))$outHtml.=" id=\"".$r['id']."\"";
				if(isset($r['action']))$outHtml.=" action=\"".$r['action']."\"";
				if(isset($r['target']))$outHtml.=" target=\"".$r['target']."\"";
				$outHtml.=">";
				$outHtml.=$this->render($r['name'],$r,false);

				if(!preg_match("#[a-zA-Z0-9]{64}#is",$_POST['key']))$_POST['key']=md5(uniqid(rand(),1)).md5(uniqid(rand(),1));
				$outHtml.="<input type=\"hidden\" name=\"key\" id=\"key\" value=\"".$_POST['key']."\" />";
				
				$outHtml.="</form>";
			}elseif($r['method_name']=="block"){
				$outHtml.="<div class=\"well\">";
				if(!empty($r['title'])){
					$outHtml.="<strong>".$r['title']."</strong><br /><br />";
				}
				$outHtml.=$this->render($r['name'],$r,false);
				$outHtml.="</div>";
			}elseif($r['method_name']=="tabs"){
				$outHtml.="<ul class=\"nav nav-tabs\">";
				$i=0;
				foreach($r['tabs'] AS $tab_name=>$tab_title)
				{
					$outHtml.="<li".($i==0?" class=\"active\"":"")."><a href=\"#".$tab_name."\" data-toggle=\"tab\">".$tab_title."</a></li>";
					$i++;
				}
				$outHtml.="</ul>";
				$i=0;
				$outHtml.="<div class=\"tab-content\">";
				foreach($r['tabs'] AS $tab_name=>$tab_title)
				{
					$outHtml.="<div class=\"tab-pane".($i==0?" active":"")."\" id=\"".$tab_name."\">";
					$outHtml.=$this->render($tab_name,$r,false);
					$outHtml.="</div>";
					$i++;
				}
				$outHtml.="</div>";
			}else{
				$outHtml.=$this->$r['method_name']($r['type'],$r);
			}

			if($p['method_name']=="greed" && $p['type']!="float"){
				$outHtml.="\n\t\t</td>\n";
			}

			if(!empty($p['type']) && $p['type']!="horizontal" && $p['type']!="float"){
				$outHtml.="\t</tr>\n";
			}

			if($p['method_name']=="greed" && $p['type']=="float"){
				$outHtml.="</div>";
			}
		}

		//$elements=$this->sort_elements($elements);
                
		return $outHtml;
	}

	public function get($name,$p_name)
	{
		foreach($this->elements AS $k=>$r)
		{
			if($r['name']==$name){
				return $r[$p_name];
			}
		}
	}

	public function change($name,$new_p,$htmlspecialchars=true)
	{
		foreach($this->elements AS $k=>$r)
		{
			if($r['name']==$name){
				foreach($new_p AS $k1=>$v1)
				{
					if(is_string($v1) && $htmlspecialchars){
						$v1=htmlspecialchars($v1);
					}

					$this->elements[$k][$k1]=$v1;
				}
			}
		}
	}

	public function add($type,$p)
	{
		if($p['translate']===true){
			foreach($this->ci->languages_res AS $language)
			{
				if($language->enabled!=1)continue;
				if($language->default==1){
					$p['attr:data-translate-language']=$language->code;
					break;
				}
			}
		}

		$methodName=$type;
		$param="";
		if(preg_match("#:#is",$type)){
			list($methodName,$param)=explode(":",$type);
		}

		if($methodName=="list")$methodName="_list";
		if($methodName=="switch")$methodName="_switch";

		if(!isset($p['form']))$p['form']="default";
		if(!isset($p['id']) && isset($p['name']))$p['id']=$p['name'];

		$this->add_element($methodName,$param,$p);

		if($_POST['sm']){
			if(method_exists($this,$methodName."_check")){
				$checkMethod=$methodName."_check";
				$this->$checkMethod($param,$p);
			}else{
				$this->check_field($methodName,$param,$p);
			}
		}

		if($p['translate']===true){
			foreach($this->ci->languages_res AS $language)
			{
				if($language->enabled!=1 || $language->default==1)continue;

				$p2=$p;
				unset($p2['translate']);
				unset($p2['check']);
				$p2['label']=$p2['label']." (".$language->title.")";
				$p2['name']=$p2['name']."_".$language->code;
				$p2['id']=$p2['id']."_".$language->code;
				$p2['attr:data-translate-language']=$language->code;
				// $p2['hidden']=true;
				$this->add($type,$p2);
			}
		}
	}

	public function attr(&$p)
	{
		$p['attr_name']=$p['name'];
		$p['attr_id']=$p['id'];

		$attrs=array();
		foreach($p AS $k=>$v)
		{
			if(preg_match("#^attr:(.*)#is",$k,$matches)){
				$attrs[$k].=$matches[1]."=\"".$v."\"";
			}
		}

		return " ".implode(" ",$attrs);
	}

	public function editor_attr(&$p)
	{
		$p['attr_name']=$p['name'];
		$p['attr_id']=$p['id'];

		$attrs=array();
		foreach($p AS $k=>$v)
		{
			if(preg_match("#^editor:(.*)#is",$k,$matches)){
				$attrs[$matches[1]]=$v;
			}
		}

		return $attrs;
	}

	public function _list($type,$p)
	{
		$html="";

		//if(sizeof($p['options'])==0)return "";

		$attr=$this->attr($p);

		$hidden="";
		if($p['hidden']){
			$hidden=' style="display:none;"';
		}

		$html.=<<<EOF
<div id="form_field_{$p['id']}"{$hidden} class="{$p['class']}">
EOF;

		if($this->submit){
			$p['value']=$this->field_post_val($type,$p);
		}

		$html.=<<<EOF
<strong>{$p['label']}:</strong><br />
EOF;

		if($type=="select"){
			$html.=<<<EOF
<select name="{$p['name']}" id="{$p['name']}" autocomplete="off"{$attr}">
EOF;
		}

		foreach($p['options'] AS $k=>$v)
		{
			switch($type)
			{
				case'checkbox':
					if(is_array($p['value'])){
						$s=in_array($k,$p['value'])?' checked="checked"':'';
					}else{
						$s=$k==$p['value']?' checked="checked"':'';
					}
					$html.=<<<EOF
<div><label><input{$s} type="checkbox" id="{$p['id']}" name="{$p['name']}" value="{$k}"{$attr} /> {$v}</label></div>
EOF;
				break;
				case'radio':
					$s=$k==$p['value']?' checked="checked"':'';
					$html.=<<<EOF
<div><label><input{$s} type="radio" id="{$p['id']}" name="{$p['name']}" value="{$k}"{$attr} /> {$v}</label></div>
EOF;
				break;
				case'select':
					if(is_array($p['value'])){
						$s=in_array($k,$p['value'])?' selected="selected"':'';
					}else{
						$s=$k==$p['value']?' selected="selected"':'';
					}
					$html.=<<<EOF
<option{$s} value="{$k}">{$v}</option>
EOF;
				break;
			}
		}

		if($type=="select"){
			$html.=<<<EOF
</select>
{$p['append']}
EOF;
		}

		$html.=<<<EOF
EOF;

		if(!empty($p['help'])){
			$html.=<<<EOF
<div class="field_help"><small>{$p['help']}</small></div>
EOF;
		}

$html.=<<<EOF
<br />
</div>
EOF;

		return $html;
	}

	var $post=array();

	public function field_post_val(&$type,&$p)
	{
		if(preg_match_all("#\[([^\]]+)\]#is",$p['name'],$pregs)){
			list($post_var)=explode("[",$p['name']);
			$post_var="['".$post_var."']";
			foreach($pregs[1] AS $r)
			{
				$post_var.="['".$r."']";
			}

			$val=eval("return \$_POST".$post_var.";");
		}elseif(preg_match("#\[\]#is",$p['name']) && $p['method_name']=="input"){
			if(!isset($this->post[$p['name']]))$this->post[$p['name']]=$_POST[str_replace("[]","",$p['name'])];

			foreach($this->post[$p['name']] AS $k=>$v)
			{
				if(is_numeric($k)){
					$val=$this->post[$p['name']][$k];
					unset($this->post[$p['name']][$k]);
					break;
				}
			}
		}elseif(preg_match("#\[\]#is",$p['name']) && $p['method_name']=="_list"){
			$val=$_POST[str_replace("[]","",$p['name'])];
		}else{
			$val=$_POST[$p['name']];
		}

		if(is_string($val)){
			$val=htmlspecialchars($val);
		}
		return $val;
	}

	public function input($type,$p)
	{
		$html="";

		if($this->submit){
			$p['value']=$this->field_post_val($type,$p);
		}

		$attr=$this->attr($p);

		$hidden="";
		if($p['hidden']){
			$hidden=' style="display:none;"';
		}

$html.=<<<EOF
<div id="form_field_{$p['id']}"{$hidden} class="{$p['class']}">
{$p['prepend']}
EOF;
		switch($type)
		{
			case'hidden':
				$html.=<<<EOF
<input type="hidden" name="{$p['name']}" id="{$p['id']}" value="{$p['value']}"{$attr} />
EOF;
			break;
			case'checkbox':
				if(is_null($p['value']))$p['value']=1;
				$s=$p['value']==$_POST[$p['name']]?' checked="checked"':'';
				
				$html.=<<<EOF
<label><input{$s} type="checkbox" name="{$p['name']}" id="{$p['id']}" value="{$p['value']}"{$attr} /> {$p['label']}</label>
EOF;

		if(!empty($p['help'])){
			$html.=<<<EOF
<div class="field_help"><small>{$p['help']}</small></div>
<br />
EOF;
		}else{
$html.=<<<EOF
<br />
EOF;
		}

$html.=<<<EOF
EOF;
			break;
			case'text':
				$html.=<<<EOF
<strong>{$p['label']}:</strong><br />
<input type="text" name="{$p['name']}" id="{$p['id']}" value="{$p['value']}"{$attr} />
EOF;
			break;
			case'date':
				$html.=<<<EOF
<script>
$(document).ready(function(){
	$("#{$p['id']}").datepicker({
		format:"dd.mm.yyyy"
	}).on("changeDate",function(){
		$(this).datepicker("hide");
	});

	$("#{$p['id']}").unbind("focus");
});
</script>
<strong>{$p['label']}:</strong><br />

<div class="input-append date">
	<input style="width:150px;" type="text" name="{$p['name']}" id="{$p['id']}" value="{$p['value']}" class="span2"{$attr} />
	<span class="add-on" onclick="$('#{$p['id']}').datepicker('show');"><i class="icon-calendar"></i></span>
</div>
EOF;
			break;
			case'radio':
				$html.=<<<EOF
<label><input type="radio" value="{$p['value']}"{$attr} /> {$p['label']}</label>
EOF;
			break;
			case'submit':
				$primary=$p['primary']?' btn-primary':'';
				$html.=<<<EOF
<button type="submit" class="btn {$primary}"{$attr}>{$p['label']}</button>
EOF;
			break;
			case'button':
				$html.=<<<EOF
<input type="submit" class="btn {$primary}" value="{$p['label']}"{$attr} />
EOF;
			break;
			case'file':
				$html.=<<<EOF
<strong>{$p['label']}:</strong><br />
<input type="file" name="{$p['name']}" id="{$p['id']}" value="{$p['label']}"{$attr} />
EOF;
			break;
		}

		if(!empty($p['help'])){
			$html.=<<<EOF
<div class="field_help" style="position:relative; top:-6px;"><small>{$p['help']}</small></div>
EOF;
		}
		
		$html.=<<<EOF
{$p['append']}
</div>
EOF;

		return $html;
	}

	public function textarea($type,$p)
	{
		$html="";

		$attr=$this->attr($p);

		if($this->submit){
			$p['value']=$this->field_post_val($type,$p);
		}

		$hidden="";
		if($p['hidden']){
			$hidden=' style="display:none;"';
		}

		$html.=<<<EOF
<div id="form_field_{$p['id']}"{$hidden} class="{$p['class']}">
EOF;

		if(!empty($p['label'])){
			$html.=<<<EOF
<strong>{$p['label']}:</strong><br />
EOF;
		}

		if($type=="editor"){
			$editor_attr=$this->editor_attr($p);

			$editor_plugins=array("autolink","lists","table","advhr","advimage","advlink","iespell","inlinepopups","insertdatetime","preview","media","searchreplace","contextmenu","paste","directionality","fullscreen","noneditable","visualchars","nonbreaking","xhtmlxtras","template","advlist","pagebreak");
			foreach($editor_plugins AS $k=>$plugin_name)
			{
				if($editor_attr[$plugin_name]===false){
					unset($editor_plugins[$k]);
				}
			}
			$editor_plugins=implode(",",$editor_plugins);

			$editor_options="";
			if($editor_attr['disabled_p']===true){
				$editor_options.="forced_root_block : false,
			force_br_newlines : true,
			force_p_newlines : false,";
			}

			$html.=<<<EOF
<script type="text/javascript">
	$().ready(function() {
		$("textarea#{$p['name']}").tinymce({
			{$editor_options}
			language : "ru",
			script_url : '/templates/default/admin/assets/tiny_mce/tiny_mce.js',
			theme : "advanced",
			plugins : "{$editor_plugins}",
                        relative_urls: false,
			theme_advanced_buttons1 : "pastetext,pasteword,bold,italic,underline,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect,search,replace,bullist,numlist,outdent,indent,blockquote,link,unlink,anchor,image,forecolor,backcolor",
			theme_advanced_buttons2 : "tablecontrols,hr,removeformat,sub,sup,charmap,media,ltr,rtl,fullscreen,insertlayer,moveforward,movebackward,absolute,cite,abbr,acronym,del,ins,attribs,pagebreak,undo,redo,code",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resize_horizontal:0,
			theme_advanced_path : false,
			disk_cache:false,
			//theme_advanced_statusbar_location:false,
			theme_advanced_resizing : true,

//			content_css : "css/content.css",

			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js",
			extended_valid_elements: "object[classid|codebase|width|height|align|type|data],param[id|name|type|value|valuetype<DATA?OBJECT?REF]"

			
		});
	});
</script>
EOF;
		}

		$html.=<<<EOF
<textarea name="{$p['name']}" id="{$p['id']}" {$attr}>{$p['value']}</textarea>
EOF;

		if(!empty($p['help'])){
			$html.=<<<EOF
<div class="field_help"><small>{$p['help']}</small></div>
EOF;
		}

$html.=<<<EOF
<br />
</div>
EOF;

		return $html;
	}

	public function _switch($type,$p)
	{
		$html="";

		$p['attr:href']=isset($p['link'])?$p['link']:$p['href'];

		$attr=$this->attr($p);

		if($p['enabled']){
			$html.=<<<EOF
<a{$attr} title="сейчас включено">Выключить</a>
EOF;
		}else{
			$html.=<<<EOF
<a{$attr} title="сейчас выключено">Включить</a>
EOF;
		}

		return $html;
	}

	public function upload($type,$p)
	{
		$html="";

		$hidden="";
		if($p['hidden']){
			$hidden=' style="display:none;"';
		}
		$html.=<<<EOF
<div id="form_field_{$p['id']}"{$hidden} class="{$p['class']}">
<strong>{$p['label']}:</strong><br />
EOF;
		if($type=="swf"){
			//$session_id=$this->ci->session->userdata('session_id');
			$CI_COOKIE=$this->ci->input->cookie($this->ci->session->sess_cookie_name);
			
			$html.=<<<EOF

<script type="text/javascript" src="/templates/default/admin/assets/swfupload-2.2.0.1/swfupload.js"></script>

<script type="text/javascript" src="/templates/default/admin/assets/swfupload-2.2.0.1/js/swfupload.queue.js"></script>
<script type="text/javascript" src="/templates/default/admin/assets/swfupload-2.2.0.1/js/fileprogress.js"></script>
<script type="text/javascript" src="/templates/default/admin/assets/swfupload-2.2.0.1/js/handlers.js"></script>

<script type="text/javascript">
var swfu;

window.onload = function() {
	var settings = {
		flash_url : "/templates/default/admin/assets/swfupload-2.2.0.1/Flash/swfupload.swf",
		upload_url: "{$this->ci->admin_url}?m=media&a=swf_upload_photo&id={$_GET['id']}&parent_id={$_GET['parent_id']}",
		file_post_name:"{$p['name']}",
		post_params: {"CI_COOKIE" : '$CI_COOKIE'},
		file_size_limit : "100 MB",
		file_types : "*.png;*.jpeg;*.jpg;*.gif",
		file_types_description : "Изображения",
		file_upload_limit : 100,
		file_queue_limit : 0,
		custom_settings : {
			progressTarget : "fsUploadProgress",
			cancelButtonId : "btnCancel"
		},
		debug: false,

		// Button settings
		button_image_url: "/templates/default/admin/assets/swfupload-2.2.0.1/media/swfupload_placeholder.png",
		button_width: "85",
		button_height: "19",
		button_placeholder_id: "spanButtonPlaceHolder",
		button_text: '',
		button_text_style: ".theFont { font-size: 16; }",
		button_text_left_padding: 12,
		button_text_top_padding: 3,
		
		// The event handler functions are defined in handlers.js
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete	// Queue plugin event
	};

	swfu = new SWFUpload(settings);
};
</script>

<form id="form1" action="index.php" method="post" enctype="multipart/form-data">
		<div class="fieldset flash" id="fsUploadProgress">
		</div>
	<div id="divStatus">0 файлов загружено</div>
		<div>
			<span id="spanButtonPlaceHolder"></span>
			<input class="btn" id="btnCancel" type="button" value="Отменить" onclick="swfu.cancelQueue();" disabled="disabled" />
		</div>

</form>
EOF;
		}else{
			$html.=<<<EOF
<input type="file" name="{$p['name']}" value="" multiple/>
EOF;

//			if($p['dynamic']){
//				$html.=<<<EOF
//<input type="button" class="btn btn-mini" onclick="formHelper['{$this->formName}'].startUpload(this,'{$p['id']}');" value="Загрузить" />
//EOF;
//			}
				$html.=<<<EOF
<div id="{$p['name']}_thumb">
EOF;
		
			if(isset($p['sql'])){

			}else{
				$where=array();
				if(isset($p['component_type']))$where['component_type']=$p['component_type'];
				if(isset($p['component_name']))$where['component_name']=$p['component_name'];
				if(isset($p['extra_type']))$where['extra_type']=$p['extra_type'];
				if(isset($p['extra_id']))$where['extra_id']=$p['extra_id'];
				if(isset($p['name']))$where['name']=str_replace('[]','',$p['name']);
				if(isset($p['insert_id']))$where['id']=$p['insert_id'];
				if(isset($p['key']))$where['key']=$p['key'];

				if($type=="editor"){
					$res=$this->ci->db
					->select("*")
					->order_by("order")
					->get_where("uploads",$where)
					->result();

					$html.=<<<EOF
<table class="table table-striped" id="{$p['name']}_files_list" style="margin-top:30px; 
EOF;
					if(sizeof($res)==0){
						$html.=<<<EOF
 display:none;
EOF;
					}
					$html.=<<<EOF
">
<tr>
	<th>Файл</th>
EOF;
                                    if($p['extra_color']){
                                        $html.=<<<EOF
	<th>Цвет товара</th>
EOF;
                                    }
                                        
					if($p['ordering']){
						$html.=<<<EOF
	<th>Порядок</th>
EOF;
					}
					$html.=<<<EOF
	<th>&nbsp;</th>
</tr>
EOF;

					if(sizeof($res)>0){
						foreach($res AS $r)
						{
							$r->hmn_file_size=humn_file_size($r->file_size);

							$ext=strtolower(end(explode(".",$r->file_name)));

							if (empty($p['new_remove'])) {
                                                            $html.=<<<EOF
<tr id="{$p['name']}_files_list_{$r->id}">
	<td>
EOF;
                                                        }
                                                        else{
                                                            $html.=<<<EOF
<tr id="files_list_{$r->id}">
	<td>
EOF;
                                                        }
							if($p['thumbs'] && in_array($ext,array("jpg","jpeg","png","gif"))){
//								$html.=<<<EOF
//<img src="/admin/?m=admin&a=thumb&f={$r->file_path}{$r->file_name}" />
//EOF;
                                                                $html.=<<<EOF
<img src="/uploads/shop/products/thumbs3/{$r->file_name}" />
EOF;
                                                                
							}
							$html.=<<<EOF
	<a href="/{$r->file_path}{$r->file_name}" onclick="attachInsertAttachLink(this); return false;">{$r->file_original_name}</a><br /><small>размер: {$r->hmn_file_size}, <a href="/{$r->file_path}{$r->file_name}" target="_blank">скачать</a></small></td>
EOF;
                                                    if($p['extra_color']){
                                                        $checked = ($r->extra_color > 0) ? ' checked="checked" ' : '';
                                                        $html.=<<<EOF
        <td>
            <input type="checkbox" id="ec_{$r->id}" name="extra_color[{$r->id}]" value="1" {$checked} onclick="photoColor(this.id);" />                                                
        </td>
EOF;
                                                    }
                                                    
							if($p['ordering']){
                                                            if (empty($p['new_move'])) {
                                                                $html.=<<<EOF
	<td class="reorderArrows">
<a
EOF;
                                                                if (sizeof($res) == $r->order) {
                                                                    $html.=<<<EOF
 style="display:none;"
EOF;
                                                                }
                                                                $html.=<<<EOF
 href="#" onclick="formHelper['form'].reorderUpload(this,'{$p['id']}','{$r->id}','down'); return false;"><img src="/templates/default/admin/assets/icons/arrow_down.gif" /></a>
EOF;
                                                                $html.=<<<EOF
&nbsp;
EOF;
                                                                $html.=<<<EOF
<a
EOF;
                                                                if ($r->order <= 1) {
                                                                    $html.=<<<EOF
 style="display:none;"
EOF;
                                                                }
                                                                $html.=<<<EOF
 href="#" onclick="formHelper['form'].reorderUpload(this,'{$p['id']}','{$r->id}','up'); return false;"><img src="/templates/default/admin/assets/icons/arrow_up.gif" /></a>
EOF;
                                                                $html.=<<<EOF
	</td>
EOF;
                                                            }
                                                            else{
                                                                // новый вариант сортировки элементов
                                                                $html.=<<<EOF
	<td class="reorderArrows">
<a
EOF;
                                                                $html.=<<<EOF
 href="#" onclick="moveUpload('{$r->id}','down'); return false;" title="Опустить"><img src="/templates/default/admin/assets/icons/arrow_down.gif" alt="Down" /></a>
EOF;
                                                                $html.=<<<EOF
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
EOF;
                                                                $html.=<<<EOF
<a
EOF;
                                                                $html.=<<<EOF
 href="#" onclick="moveUpload('{$r->id}','up'); return false;" title="Поднять"><img src="/templates/default/admin/assets/icons/arrow_up.gif" alt="Up" /></a>
EOF;
                                                                $html.=<<<EOF
	</td>
EOF;
                                                            }
							}
                                                    
							if (empty($p['new_remove'])) {
                                                            $html.=<<<EOF
	<td width="16"><a href="#" onclick="formHelper['form'].removeUpload(this,'{$p['id']}','{$r->id}'); return false;"><img src="/templates/default/admin/assets/icons/cross.png" /></a></td>
</tr>
EOF;
                                                        }
                                                        else{
                                                            // новый вариант удаления файлов
                                                            $html.=<<<EOF
	<td width="16"><a href="#" onclick="removeUpload('{$r->id}'); return false;" title="Удалить элемент"><img src="/templates/default/admin/assets/icons/cross.png" alt="Remove" /></a></td>
</tr>
EOF;
                                                        }
						}
					}				
					$html.=<<<EOF
</table>
EOF;
				}else{
					$res=$this->ci->db
					->select("id, file_path, file_name")
					->get_where("uploads",$where)
					->row();

					if($res->id>0){
						$html.=<<<EOF
		<img src="/{$res->file_path}{$res->file_name}" />
		<div style="padding-top:4px;"><button onclick="formHelper['form'].removeUpload(this,'{$p['id']}','{$res->id}'); return false;" class="btn btn-danger btn-mini">удалить</button></div>
EOF;
					}
				}
			}

			$html.=<<<EOF
</div>
<br />
</div>
EOF;
		}

		return $html;
	}

	protected function rebuild_uploads_order($where)
	{
		$res=$this->ci->db
		->select("id")
		->order_by("order")
		->get_where("uploads",$where)
		->result();

		$i=1;
		foreach($res AS $r)
		{
			$this->ci->db
			->where("id",$r->id)
			->update("uploads",array(
				"order"=>$i
			));
			$i++;
		}
	}

	public function upload_check($type,$p)
	{
		$uploaded=false;
		if(!empty($_FILES[$p['id']]['tmp_name'][0])){
			///$userId=$this->ci->ion_auth->user()->row()->id;
			$userId=0;

			r_mkdir($p['upload_path']);

			if(isset($p['upload_path'])){
				$config['upload_path']=$p['upload_path'];
			}else{
				$config['upload_path']="./uploads/";
			}
			if(isset($p['allowed_types'])){
				$config['allowed_types']=$p['allowed_types'];
			}else{
				$config['allowed_types']="*";
			}
			if(isset($p['max_size'])){
				$config['max_size']=$p['max_size'];
			}
			if(isset($p['max_width'])){
				$config['max_width']=$p['max_width'];
			}
			if(isset($p['max_height'])){
				$config['max_height']=$p['max_height'];
			}
			if(isset($p['file_name'])){
				$config['file_name']=$p['file_name'];
			}else{
				$config['file_name']=$userId."_".md5(uniqid(rand(),true));
			}

			$this->ci->load->library('upload',$config);
                        
                        // set correct filenames
                        foreach ($_FILES[$p['id']]['name'] as $fn_key => $fn_val){
                            $ex_fname = explode('.', $fn_val);
                            $_FILES[$p['id']]['name'][$fn_key] = $userId . '_' . md5(uniqid(rand(),true)) . '.' . end($ex_fname);
                        }

			if(!$this->ci->upload->do_multi_upload($p['id'])){
//				print $this->ci->upload->display_errors();
                $this->errors[$p['name']]['upload_errors'] = $this->ci->upload->display_errors();
			}else{
				$data = $this->ci->upload->get_multi_upload_data();

				// NOT MULTI UPLOAD
				if(empty($data))
				{
					$data[] = $this->ci->upload->get_upload_data();
				}

                foreach($data AS $d)
                {
                    $d['file_path']=str_replace(FCPATH,"",$d['file_path']);

                    // образатываем изображение если требуется
                    $out_files="";
                    if($d['is_image']==1 && !empty($p['image_proc'])){
                        $this->ci->load->library("img");
                        $proc=$this->ci->img->proc($d['file_path'].$d['file_name'],$p['image_proc']);
                        $out_files=implode("\n",$proc['out_files']);
                    }

                    if(!empty($p['proc_config_var_name'])){
                        $images_options_res=$this->ci->db->get_where("config",array(
                            "var_name"=>$p['proc_config_var_name']
                        ))->row();

                        if(isset($images_options_res->id)){
                            $this->ci->load->library("img");
                            if(!empty($images_options_res->value)){
                                $images_options_data=$this->ci->img->proc(FCPATH.$d['file_path'].$d['file_name'],$images_options_res->value);
                            }
                        }

                        $thumb_files=implode("\n",$images_options_data['out_files']);
                    }

                    // проверяем загружен ли уже файл в это поле, для того чтоб при добавлении нового удалить старый
                    $where=array();
                    if(isset($p['component_type']))$where['component_type']=$p['component_type'];
                    if(isset($p['component_name']))$where['component_name']=$p['component_name'];
                    if(isset($p['extra_type']))$where['extra_type']=$p['extra_type'];
                    if(isset($p['extra_id']))$where['extra_id']=$p['extra_id'];
                    if(isset($p['name']))$where['name']=$p['name'];
                    if(isset($p['insert_id']))$where['id']=$p['insert_id'];
                    if(isset($p['key']))$where['key']=$p['key'];

                    if($type=="editor"){

                    }else{
                        $current_image_res=$this->ci->db
                            ->select("id, file_path, file_name")
                            ->get_where("uploads",$where)
                            ->row();

                        if($current_image_res->id>0){
                            $_POST['uploaderRemoveHidden_id'][$p['name']]=$current_image_res->id;
                        }
                    }

                    $file_size=filesize($_FILES[$p['name']]['tmp_name']);

                    if(isset($p['sql'])){
                        // добавляем данные в базу
                        $identifier_field="";
                        foreach($p['sql']['fields'] AS $name=>$value)
                        {
                            $p['sql']['fields'][$name]=str_replace("%FILE_NAME%",$d['file_name'],$p['sql']['fields'][$name]);
                            $p['sql']['fields'][$name]=str_replace("%FILE_PATH%",$d['file_path'],$p['sql']['fields'][$name]);
                            $p['sql']['fields'][$name]=str_replace("%FILE_SIZE%",$d['file_size'],$p['sql']['fields'][$name]);
                            $p['sql']['fields'][$name]=str_replace("%OUT_FILES%",$out_files,$p['sql']['fields'][$name]);
                            if($d['is_image']==1){
                                $p['sql']['fields'][$name]=str_replace("%IMAGE_SIZE%",$d['image_width']."x".$d['image_height'],$p['sql']['fields'][$name]);
                            }
                        }

                        if(isset($p['sql']['where']) && sizeof($p['sql']['where'])>0){
                            $this->ci->db->where($p['sql']['where']);
                            $this->ci->db->update($p['sql']['table'],$p['sql']['fields']);

                            $insert_id=0;
                        }else{
                            $this->ci->db->insert($p['sql']['table'],$p['sql']['fields']);

                            $insert_id=$this->ci->db->insert_id();
                        }
                    }else{
                        $where=array();
                        if(isset($p['component_type']))$where['component_type']=$p['component_type'];
                        if(isset($p['component_name']))$where['component_name']=$p['component_name'];
                        if(isset($p['extra_type']))$where['extra_type']=$p['extra_type'];
                        if(isset($p['extra_id']))$where['extra_id']=$p['extra_id'];
                        if(isset($p['name']))$where['name']=str_replace(array('[',']'),'',$p['name']);
                        if(isset($p['insert_id']))$where['id']=$p['insert_id'];

                        $fields['user_id']=$userId;
                        // пользователь не указал в какую таблицу добавлять данные, так что добавляем в стандартную
                        if(!isset($p['component_type']))$p['component_type']="";
                        if(!isset($p['component_name']))$p['component_name']="";
                        if(!isset($p['extra_type']))$p['extra_type']="";
                        if(!isset($p['extra_id']))$p['extra_id']="";
                        if(isset($p['key']))$where['key']=$p['key'];

                        $order=$this->ci->db
                            ->where($where)
                            ->count_all_results("uploads");

                        $order++;

                        $fields=array(
                            "key"=>$p['key'],
                            "user_id"=>$userId,
                            "name"=>str_replace('[]','',$p['name']),
                            "file_size"=>$file_size,
                            "file_name"=>$d['file_name'],
                            "file_path"=>$d['file_path'],
                            "file_original_name"=>$d['orig_name'],
                            "image_size"=>$d['is_image']==1?$d['image_width']."x".$d['image_height']:"",
                            "component_type"=>$p['component_type'],
                            "component_name"=>$p['component_name'],
                            "extra_type"=>$p['extra_type'],
                            "extra_id"=>$p['extra_id'],
                            "order"=>$order,
                            "proc_config_var_name"=>(string)$p['proc_config_var_name']
                        );

                        if(isset($thumb_files)){
                            $fields['thumb_files']=$thumb_files;
                        }

                        $this->ci->db->insert("uploads",$fields);
                        $insert_id=$this->ci->db->insert_id();
                    }
                    $uploaded=true;
                }
			}

			
		}

		if(isset($_POST['uploaderOrderHidden_id']) && sizeof($_POST['uploaderOrderHidden_id'])>0
			&& in_array($p['id'],array_keys($_POST['uploaderOrderHidden_id']))
			){
			$where=array();
			if(isset($p['component_type']))$where['component_type']=$p['component_type'];
			if(isset($p['component_name']))$where['component_name']=$p['component_name'];
			if(isset($p['extra_type']))$where['extra_type']=$p['extra_type'];
			if(isset($p['extra_id']))$where['extra_id']=$p['extra_id'];
			if(isset($p['name']))$where['name']=str_replace(array('[',']'),'',$p['name']);
            list($id,$order)=explode(":",$_POST['uploaderOrderHidden_id'][$p['id']]);



			$res=$this->ci->db
			->get_where("uploads",array(
				"id"=>$id
			))
			->row();

			$this->ci->db
			->where($where)
			->where(array(
				"order"=>$order=="up"?$res->order-1:$res->order+1
			))
			->update("uploads",array(
				"order"=>$res->order
			));
			
			$this->ci->db
			->where($where)
			->where(array(
				"id"=>$id
			))
			->update("uploads",array(
				"order"=>$order=="up"?$res->order-1:$res->order+1
			));

			$this->rebuild_uploads_order($where);

			?><html><head><script>
				parent.formHelper['<?php print $_POST['iframeUploaderHidden']; ?>'].orderedSuccess({
"field_name":"<?php print $p['id']; ?>",
"insert_id":"<?php print $id; ?>",
"type":"<?php print $type; ?>",
"order":"<?php print $order; ?>"
});</script></head><body>ordered</body></html><?
			exit;
		}


		if(isset($_POST['uploaderRemoveHidden_id']) && sizeof($_POST['uploaderRemoveHidden_id'])>0
			&& in_array($p['id'],array_keys($_POST['uploaderRemoveHidden_id']))
			){
			$this->ci->load->library("uploads");
			foreach($_POST['uploaderRemoveHidden_id'] AS $field_name=>$id)
			{
				if($p['id']!=$field_name)continue;

				if(isset($p['sql'])){

				}else{
					// удаляем прикрепление
					$this->ci->uploads->remove(array(
						"id"=>$id
					));
				}
			}

			// перестраиваем порядок прикреплений после удаления
			$where=array();
			if(isset($p['component_type']))$where['component_type']=$p['component_type'];
			if(isset($p['component_name']))$where['component_name']=$p['component_name'];
			if(isset($p['extra_type']))$where['extra_type']=$p['extra_type'];
			if(isset($p['extra_id']))$where['extra_id']=$p['extra_id'];
			if(isset($p['name']))$where['name']=$p['name'];
			if(isset($p['key']))$where['key']=$p['key'];
			$this->rebuild_uploads_order($where);

			if(!$uploaded){
				?><html><head><script>
				parent.formHelper['<?php print $_POST['iframeUploaderHidden']; ?>'].removeSuccess({
"field_name":"<?php print $p['id']; ?>",
"insert_id":"<?php print $id; ?>",
"type":"<?php print $type; ?>"
});</script></head><body>removed</body></html><?
				exit;
			}
		}

		if(!empty($_FILES[$p['id']]['tmp_name'])){
			$d['hmn_file_size']=humn_file_size($file_size);
			if($_POST['iframeUploaderHidden']){
				?><html><head><script>parent.formHelper['<?php print $_POST['iframeUploaderHidden']; ?>'].uploadSuccess({
"file_size":"<?php print $file_size; ?>",
"hmn_file_size":"<?php print $d['hmn_file_size']; ?>",
"file_name":"<?php print $d['file_name']; ?>",
"file_original_name":"<?php print $_FILES[$p['name']]['name']; ?>",
"file_path":"<?php print $d['file_path']; ?>",
"image_size":"<?php print $d['is_image']==1?$d['image_width']."x".$d['image_height']:""; ?>",
"field_name":"<?php print $p['id']; ?>",
"type":"<?php print $type; ?>",
"insert_id":"<?php print $insert_id; ?>",
"ordering":<?php print $p['ordering']?"true":"false"; ?>,
"thumbs":<?php print $p['thumbs']?"true":"false"; ?>
});</script></head><body>uploaded</body></html><?
				exit;
			}
		}
	}

	public function table($type,$p)
	{
		$html="";

		if($this->ci->input->post("select_all_from_table_sm")!==false){
			// это ajax запрос для получения всех ID таблицы! просто выводим их в json'e
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');

			print json_encode(array(
				"ids"=>$p['rows']
			));
			exit;
		}

		if(!isset($p['id']))$p['id']="default";

		$rows="";
		$buttons=false;
		$first_checkbox=false;
		$first_checkbox_select_all_from_table=false;
		$first_checkbox_name="";
		$cols_width=array();
		if(isset($p['rows']) && sizeof($p['rows'])>0){
			foreach($p['rows'] AS $i=>$row)
			{
				$rows.=<<<EOF
<tr>
EOF;
				$col_i=0;
				foreach($row AS $k=>$r)
				{
					if(preg_match("#^order#is",$k) && preg_match("#:num#is",$k)){
						$k=str_replace(":num","",$k);
						$order_num_show=$col_i;
						$order_num_url=$r[0];
					}
					if($k==="checkbox"){
						if(empty($first_checkbox_name) && !empty($r['name'])){
							$first_checkbox_name=$r['name'];
						}
						$checked=$r['checked']?' checked="checked"':'';
						$rows.=<<<EOF
<td style="text-align:center;"><input type="checkbox" name="{$r['name']}[]" value="{$r['value']}"{$checked} /></td>
EOF;
						$first_checkbox=true;

						if($r['select_all_from_table']){
							$first_checkbox_select_all_from_table=true;
						}
					}elseif($k==="order" || $k==="order:desc"){
						$p1="arrow_down.gif";
						$p2="arrow_up.gif";
						$p1d="down";
						$p2d="up";
						if($k==="order:desc"){
							$p1d="up";
							$p2d="down";
						}
						$rows.=<<<EOF
<td style="text-align:center;">
EOF;
						$r[1]=(int)$r[1];
						if($r[2]>0){
							if(isset($order_num_show)){
								$rows.=<<<EOF
<input data-id="{$r[3]}" name="table_order[{$p['id']}][{$r[3]}]" type="text" style="width:30px; font-size:11px; height:20px; text-align:center;" value="{$r[1]}" />
&nbsp;&nbsp;
EOF;
							}

							if($r[1]<$r[2]){
								$rows.=<<<EOF
<a href="{$r[0]}{$p1d}"><img src="/templates/default/admin/assets/icons/{$p1}" /></a>
EOF;
							}
							if($r[1]>1){
							$rows.=<<<EOF
<a href="{$r[0]}{$p2d}"><img src="/templates/default/admin/assets/icons/{$p2}" /></a>
EOF;
							}
						}
						$rows.=<<<EOF
</td>
EOF;
					}elseif($k==="enabled"){
						//$cols_width[$col_i]=20;
						$rows.=<<<EOF
<td style="text-align:center;">
EOF;
						if(is_null($r)){
								$rows.=<<<EOF
---
EOF;
						}else{
							$n=$r[1]==1?"0":"1";
							if($r[1]!=1){
								$rows.=<<<EOF
	<a href="{$r[0]}{$n}"><img src="/templates/default/admin/assets/icons/lightbulb_off.png"></a>
EOF;
							}else{
								$rows.=<<<EOF
	<a href="{$r[0]}{$n}"><img src="/templates/default/admin/assets/icons/lightbulb.png"></a>
EOF;
							}
						}
						if(!empty($r['warning'])){
							$rows.=<<<EOF
<div class="clear"></div>
<br />
<small style="width:180px; display:block; margin:0 auto;" class="alert"><img src="/templates/default/admin/assets/icons/bullet_error.png" /> {$r['warning']}</small>
EOF;
						}
						$rows.=<<<EOF
</td>
EOF;
					}elseif($k==="default"){
						$cols_width[$col_i]=20;
						$rows.=<<<EOF
<td style="text-align:center;">
EOF;
						if(is_null($r)){
								$rows.=<<<EOF
---
EOF;
						}else{
							$n=$r[1]==1?"0":"1";
							if($r[1]!=1){
								$rows.=<<<EOF
	<a href="{$r[0]}{$n}"><img src="/templates/default/admin/assets/icons/star_off.png"></a></td>
EOF;
							}else{
								$rows.=<<<EOF
	<a href="{$r[0]}{$n}"><img src="/templates/default/admin/assets/icons/star.png"></a></td>
EOF;
							}
						}
					}elseif($k==="buttons"){
						$width=sizeof($r)*20;
						$cols_width[$col_i]=$width;
						$rows.=<<<EOF
<td align="center" style="text-align:center; width:{$width}px;">
EOF;
						foreach($r AS $button)
						{
							$buttons=true;
							
							if(is_null($button))continue;

							$onclick="document.location.href='{$button[1]}'; return false;";
							if($button[0]=="delete" || $button[0]=="remove" || $button[0]=="cross"){
								$onclick="if(confirm('Вы уверены?')){ document.location.href='{$button[1]}'; } return false;";
							}

							$rows.=<<<EOF
<a href="#" onclick="{$onclick}"><img src="/templates/default/admin/assets/icons/{$button[0]}.png" /></a> 
EOF;
						}
						$rows.=<<<EOF
</td>
EOF;
					}else{
						$rows.=<<<EOF
<td>{$r}</td>
EOF;
					}
					$col_i++;
				}
				$rows.=<<<EOF
</tr>
EOF;
			}
		}

		$head="";
		if(isset($p['head']) && sizeof($p['head'])>0){
			$head.=<<<EOF
<tr>
EOF;
			$col_i=0;
			if($first_checkbox){
				$first_checkbox_select_all_from_table=$first_checkbox_select_all_from_table?1:0;
				$head.=<<<EOF
<th style="text-align:center;"{$attr}><input data-checkbox-name="{$first_checkbox_name}" data-select-all-from-table="{$first_checkbox_select_all_from_table}" type="checkbox" onchange="acp_table_first_checkedbox(this);" /></th>
EOF;
				$col_i++;
			}
			foreach($p['head'] AS $r)
			{
				$attr="";

				if(isset($cols_width[$col_i])){
					$attr.=' width="'.$cols_width[$col_i].'"';
				}

				$name=$r;
				if(is_array($r)){
					$name=current($r);
				}
				$name=str_replace(" ","&nbsp;",$name);
				$head.=<<<EOF
<th{$attr}>
EOF;
				if($order_num_show===$col_i){
					$head.=<<<EOF
<a href="#" onclick="cpFormTableOrderSave('{$p['id']}','{$order_num_url}'); return false;"><img src="/templates/default/admin/assets/icons/disk2.png" /></a>&nbsp;
EOF;
				}

				if(is_array($r) && !empty($r['order_by'])){
					$direction=current(array_keys($_GET['order_by'][$p['id']]))=="asc"?"asc":"desc";
					$new_direction=current(array_keys($_GET['order_by'][$p['id']]))=="asc"?"desc":"asc";
					$url=array();
					foreach($_GET AS $k=>$v)
					{
						if($k=="m" || $k=="a" || $k=="order_by")continue;
						$url[]=$k."=".urlencode($v);

					}
					$url[]="order_by[".$p['id']."][".$new_direction."]=".$r['order_by'];
					$url=implode("&",$url);

					$head.=<<<EOF
<a href="{$this->ci->admin_url}?m={$_GET['m']}&a={$_GET['a']}&{$url}" style="white-space:nowrap;">
EOF;
				}
				$head.=<<<EOF
{$name}
EOF;
				if(!empty($r['order_by'])){
					$head.=<<<EOF
</a>
EOF;
					if($_GET['order_by'][$p['id']][$direction]==$r['order_by']){
						if(current(array_keys($_GET['order_by'][$p['id']]))=="asc"){
							$head.=<<<EOF
&nbsp;<img src="/templates/default/admin/assets/icons/arrow_up.gif" />
EOF;
						}else{
							$head.=<<<EOF
&nbsp;<img src="/templates/default/admin/assets/icons/arrow_down.gif" />
EOF;
						}
					}
				}
				$head.=<<<EOF
	</th>
EOF;
				$col_i++;
			}

			if($buttons){
				$col_i++;
				if(isset($cols_width[$col_i])){
					$attr.=' width="'.$cols_width[$col_i].'"';
				}

				$head.=<<<EOF
<th{$attr}>&nbsp;</th>
EOF;
			}
			$head.=<<<EOF
</tr>
EOF;
		}

		$style="";
		if($p['width']){
			$style.=" width:{$p['width']}px; ";
		}

		if(!empty($p['title'])){
			$html.=<<<EOF
<h5>{$p['title']}</h5>
EOF;
		}

		$html.=<<<EOF
{$p['prepend']}
<table data-rows-num="{$p['rows_num']}" class="table table-bordered table-striped" id="table-{$p['id']}" align="center" style="{$style}">
<tbody>
{$head}
{$rows}
EOF;
		$html.=<<<EOF
</tbody>
</table>
<center>{$p['pagination']}</center>
{$p['append']}
EOF;
		
		return $html;
	}

	public function errors_list()
	{
		$d=array();
		foreach($this->errors AS $r)
		{
			$d[]=current($r);
		}

		if($this->ci->input->post("apply_sm")!==false && sizeof($d)>0){
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');

			print json_encode(array(
				"errors"=>$d
			));
			exit;
		}
		
		return $d;
	}

	public function check_field($method_name,$type,$p)
	{
		$this->submit=true;

		$label=$p['label'];
		if(mb_substr($p['label'],-1)==":"){
			$label=mb_substr($p['label'],0,-1);
		}

		if(isset($p['check']['min_length'])){
			if(mb_strlen($_POST[$p['name']])<=$p['check']['min_length']){
				if($p['check']['min_length']==0){
					$this->errors[$p['name']]['min_length']="Поле \"".$label."\" не может быть пустым!";
				}else{
					$this->errors[$p['name']]['min_length']="Поле \"".$label."\" содержит слишком мало символов! (должно быть не менее ".$p['check']['min_length']." символов)";
				}
			}
		}

		if(isset($p['check']['max_length'])){
			if(mb_strlen($_POST[$p['name']])>=$p['check']['max_length']){
				$this->errors[$p['name']]['max_length']="Поле \"".$label."\" превышает допустиму длину! (".$p['check']['max_length']." символов)";
			}
		}
	}

	function html($type,$p)
	{
		$html="";

		$attr=$this->attr($p);

		$hidden="";
		if($p['hidden']){
			$hidden=' style="display:none;"';
		}

		$html.=<<<EOF
<div class="formHtmlBlock {$p['class']}"{$attr}{$hidden}>
EOF;

		if(!empty($p['label'])){
			$html.=<<<EOF
<strong>{$p['label']}:</strong><br />
EOF;
		}

		$html.=<<<EOF
{$p['content']}
</div>
<br />
EOF;

		return $html;
	}

	public function pagination_init($total_rows=0,$per_page=15,$base_url="",$query_string_segment="")
	{
		$this->ci->load->library('pagination');

		$cur_page=intval($this->ci->input->get($query_string_segment));

		$config['base_url']=$base_url;
		$config['total_rows']=$total_rows;
		$config['cur_page']=$cur_page;
		$config['per_page']=$per_page;
		$config['num_links']=3;

		$config['page_query_string']=true;
		$config['query_string_segment']=$query_string_segment;

		$config['full_tag_open'] = '<div class="pagination"><ul>';
		$config['full_tag_close'] = '</ul></div>';

		$config['first_link'] = 'В начало';
		$config['first_tag_open'] = '<li>';
		$config['first_tag_close'] = '</li>';

		$config['last_link'] = 'В конец';
		$config['last_tag_open'] = '<li>';
		$config['last_tag_close'] = '</li>';

		$config['next_link'] = 'Следующая';
		$config['next_tag_open'] = '<li>';
		$config['next_tag_close'] = '</li>';

		$config['prev_link'] = 'Предыдущая';
		$config['prev_tag_open'] = '<li>';
		$config['prev_tag_close'] = '</li>';

		$config['cur_tag_open'] = '<li class="disabled"><a href="#" onclick="return false;">';
		$config['cur_tag_close'] = '</a></li>';

		$config['num_tag_open'] = '<li>';
		$config['num_tag_close'] = '</li>';

		$this->ci->pagination->initialize($config);

		return $this->ci->pagination;
	}

	public function user($type,&$p)
	{
		$html="";

		$value="";
		if(!empty($p['value'])){
			$user_res=$this->ci->db
			->get_where("users",array(
				"id"=>intval($p['value'])
			))
			->row();
			$value="{$user_res->username} (id: {$user_res->id})";
		}

		$hidden="";
		if($p['hidden']){
			$hidden=' style="display:none;"';
		}

		$html.=<<<EOF
<div id="form_field_{$p['id']}"{$hidden} class="{$p['class']}">
EOF;

		if(!empty($p['label'])){
			$html.=<<<EOF
<strong>{$p['label']}:</strong><br />
EOF;
		}

		if(empty($p['id'])){
			$p['id']=md5(uniqid(rand(),true));
		}
		$html.=<<<EOF
<script>
function openModal{$p['id']}()
{
	$("#modal{$p['id']}").modal();
}

function closeModal{$p['id']}()
{
	$("#modal{$p['id']} button.close").click();
}

function add_user_{$p['id']}(o)
{
	var id=$(o).data("id");
	var email=$(o).data("email");
	var first_name=$(o).data("first-name");
	var last_name=$(o).data("last-name");
	var username=$(o).data("username");

	$("#user_f{$p['id']}").val(username+" (id: "+id+")");
	$("input[name='{$p['name']}']").val(id);
}

function cancel_user_{$p['id']}()
{
	$("#user_f{$p['id']}").val("");
	$("input[name='{$p['name']}']").val("");
}
</script>
<div class="modal hide fade" id="modal{$p['id']}">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>Выбрать пользователя</h3>
	</div>
	<div class="modal-body">
		<iframe src="{$this->admin_url}?m=user&a=users&iframe_display=1&f_id={$p['id']}" style="border:none; height:500px; width:530px;" frameborder="0"></iframe>
	</div>
	<div class="modal-footer">
		<a href="#" onclick="closeModal{$p['id']}(); return false;" class="btn">Закрыть</a>
	</div>
</div>
<input type="text" id="user_f{$p['id']}" name="user_f{$p['id']}" value="{$value}" disabled="disabled" />
<input type="hidden" id="{$p['id']}" name="{$p['id']}" value="{$user_res->id}" />
<br />
<button class="btn btn-mini" onclick="openModal{$p['id']}(); return false;">выбрать пользователя</button>
&nbsp;
<button class="btn btn-mini btn-danger" onclick="cancel_user_{$p['id']}(); return false;">учистить</button>
EOF;

		$html.=<<<EOF
<br />
</div>
EOF;

		return $html;
	}

	public function access($type,$p)
	{
		$html="";

		if($this->submit){
			$p['value']=$this->field_post_val($type,$p);
		}

		$attr=$this->attr($p);

		$hidden="";
		if($p['hidden']){
			$hidden=' style="display:none;"';
		}

		$html.=<<<EOF
<div id="form_field_{$p['id']}"{$hidden} class="{$p['class']}">
{$p['prepend']}
EOF;

		$html.=<<<EOF
<h4>{$p['label']}:</h4>
EOF;

		$this->components_res=$this->ci->db
		->select("*")
		->get_where("components",array(
			"type"=>"module"
		))
		->result();

		if(is_string($p['value'])){
			$p['value']=json_decode($p['value']);
		}

		$all_access=array();
		foreach($this->components_res AS $r)
		{
			if(!empty($r->name) && file_exists("modules/".$r->name."/".$r->name.".info.php")){
				include_once("modules/".$r->name."/".$r->name.".info.php");
				$name=$r->name."ModuleInfo";
				$component_info[$r->type][$r->name]=new $name;
			}else{
				continue;
			}

			if(!method_exists($component_info[$r->type][$r->name],"access_rules"))continue;

			$access_rules=$component_info[$r->type][$r->name]->access_rules();

			if(sizeof($access_rules)<1)continue;

			foreach($access_rules AS $field_name=>$field_data)
			{
				if(!isset($all_access[$r->type]))$all_access[$r->type]=array();
				if(!isset($all_access[$r->type][$r->name]))$all_access[$r->type][$r->name]=array();

				$field_data['name']="access_rules[".$r->type."][".$r->name."][".$field_name."]";
				
				$all_access[$r->type][$r->name][$field_name]=$field_data;
			}
		}

		foreach($all_access AS $component_type=>$components)
		{
			foreach($components AS $component_name=>$component)
			{
				$title=$component_info[$component_type][$component_name]->title;
				$html.=<<<EOF
<h5>{$title}</h5>
EOF;
				foreach($component AS $field_name=>$field)
				{
					if(is_object($p['value'])){
						$value=$p['value']->{$component_type}->{$component_name}->{$field_name};
					}
					switch($field['type'])
					{
						case'input:checkbox':
							$checked=$value==1?' checked="checked"':'';
							$html.=<<<EOF
<label><input type="checkbox" name="{$field['name']}" value="1"{$checked} /> {$field['label']}</label>
EOF;
						break;
					}
				}
			}
		}

		$html.=<<<EOF
</div>
{$p['append']}
EOF;



		return $html;
	}
}