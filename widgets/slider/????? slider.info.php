<?php
class sliderWidgetInfo {
	var $title="Слайдер";

	public function __construct()
	{
		$this->ci=&get_instance();

		if($_POST['remove_slide']){
			$res=$this->ci->db->get_where("uploads",array(
				"id"=>intval($_POST['remove_slide'])
			))
			->row();

			if(file_exists("./".$res->file_path.$res->file_name))unlink("./".$res->file_path.$res->file_name);

			$this->ci->db->where(array(
				"id"=>intval($_POST['remove_slide'])
			))->delete("uploads");

			print 1;
			exit;
		}
	}

	public function admin_before_save($d)
	{
		if(!empty($_FILES['slide_img']['tmp_name']) && $_POST['slider_upload_sm']){
			$ext=strtolower(end(explode(".",$_FILES['slide_img']['name'])));

			if(!is_dir("./uploads/slider/"))mkdir("./uploads/slider/",0777);

			$file_name=md5(uniqid(rand(),1)).md5(uniqid(rand(),1)).".".$ext;

			move_uploaded_file($_FILES['slide_img']['tmp_name'],"./uploads/slider/".$file_name);
			if(file_exists("./uploads/slider/".$file_name)){
				$order=0;

				$file_size=filesize("./uploads/slider/".$file_name);

				list($width,$height)=getimagesize("./uploads/slider/".$file_name);

				$options=array(
					"link"=>$_POST['slide_link'],
					"text"=>$_POST['slide_text'],
					"location"=>$_POST['slide_location'],
					"area"=>$_POST['slide_area']
				);

				$this->ci->db
				->insert("uploads",array(
					"key"=>intval($_GET['id'])>0?"":$_POST['key'],
					"user_id"=>0,
					"title"=>"",
					"name"=>"slide",
					"file_size"=>$file_size,
					"file_name"=>$file_name,
					"file_path"=>"uploads/slider/",
					"file_original_name"=>$_FILES['slide_img']['name'],
					"image_size"=>$width."x".$height,
					"component_type"=>"widget",
					"component_name"=>"slider",
					"extra_type"=>"widget_id",
					"extra_id"=>intval($_GET['id']),
					"date_add"=>mktime(),
					"order"=>$order,
					"options"=>json_encode($options)
				));

				$id=$this->ci->db->insert_id();

				?><html><head><script>top.slideAdd({
					"id":"<?php print $id; ?>",
					"file_name":"<?php print $file_name; ?>",
					"file_path":"<?php print "uploads/slider/"; ?>",
					"file_original_name":"<?php print $_FILES['slide_img']['name']; ?>",
					"slide_link":"<?php print $_POST['slide_link']; ?>",
					"slide_text":"<?php print $_POST['slide_text']; ?>",
					"slide_location":"<?php print $_POST['slide_location']; ?>",
					"slide_area":"<?php print $_POST['slide_area']; ?>"

				});</script></head></html><?php
				exit;
			}
		}
	}

	public function admin_after_save(&$widget_id,&$d)
	{
		$this->ci->db
		->where(array(
			"key"=>$_POST['key'],
			"component_type"=>"widget",
			"component_name"=>"slider"
		))
		->update("uploads",array(
			"key"=>"",
			"extra_id"=>$widget_id
		));

		foreach($_POST['slide'] AS $id=>$data)
		{
			$this->ci->db
			->where(array(
				"id"=>$id
			))
			->update("uploads",array(
				"options"=>json_encode(array(
					"link"=>$data['link']
				))
			));
		}
	}

	public function admin_options(&$f)
	{
		$content="";

		if(!preg_match("#^[a-zA-Z0-9]{64}$#is",$key))$key=md5(uniqid(rand(),1)).md5(uniqid(rand(),1));

		$content.=<<<EOF
<script>
function slideAdd(d)
{
	var html='';

	html+='<tr>';
	html+='<td>';
	html+='<img alt="'+d.file_original_name+'" src="/admin/?m=admin&a=thumb&f='+d.file_path+d.file_name+'" />';
	html+='</td>';
	html+='<td><input type="text" name="slide['+d.id+'][link]" value="'+d.slide_link+'" /></td>';
	html+='<td><input type="text" name="slide['+d.id+'][location]" value="'+d.slide_location+'" /></td>';
	html+='<td><input type="text" name="slide['+d.id+'][text]" value="'+d.slide_text+'" /></td>';
	html+='<td><input type="text" name="slide['+d.id+'][area]" value="'+d.slide_area+'" /></td>';
	html+='<td>';
	html+='<a href="#" onclick="slideRemove(this,'+d.id+'); return false;"><img src="/templates/default/admin/assets/icons/cross.png" border="0" /></a>';
	html+='</td>';
	html+='</tr>';

	$("#slideList tr:last").after(html);
}

function sliderUpload(o)
{
	var form=$(o).parents("form:eq(0)");
	form.attr("target","sliderUploadIframe");
	form.append('<input type="hidden" name="slider_upload_sm" value="1" /><input type="hidden" name="sm" value="1" />');
	form.submit();

	form.find("iframe").load(function(){
		var file=form.find("input:file");
		file.after('<input type="file" name="slide_img" value="" />');
		file.remove();
		form.removeAttr("target");
	});
}

function slideRemove(o,id)
{
	$.post(document.location.href,{
		remove_slide:id
	},function(d){
		$(o).parents("tr:eq(0)").remove();
	});
}
</script>
<input type="hidden" name="key" value="{$key}" />
<iframe style="position:absolute; left:-50px; top:-50px;" width="1" height="1" id="sliderUploadIframe" name="sliderUploadIframe" src="about:blank"></iframe>
<table class="table" id="slideList">
<tr>
	<th>Изображение</th>
	<th>Ссылка</th>
	<th>Расположение</th>
	<th>Текст</th>
	<th>Площадь и цена</th>
	<th>&nbsp;</th>
</tr>
EOF;
		$res=$this->ci->db->get_where("uploads",array(
			"component_type"=>"widget",
			"component_name"=>"slider",
			"extra_id"=>intval($_GET['id'])
		))
		->result();
		foreach($res AS $r)
		{
			if(is_string($r->options)){
				$r->options=json_decode($r->options);
			}
			$content.=<<<EOF
<tr>
	<td>
		<img alt="{$r->file_original_name}" src="/admin/?m=admin&a=thumb&f={$r->file_path}{$r->file_name}" />
	</td>
	<td><input style="width:150px;" type="text" name="slide[{$r->id}][link]" value="{$r->options->link}" /></td>
	<td><input style="width:150px;" type="text" name="slide[{$r->id}][location]" value="{$r->options->location}" /></td>
	<td><input style="width:150px;" type="text" name="slide[{$r->id}][text]" value="{$r->options->text}" /></td>
	<td><input style="width:150px;" type="text" name="slide[{$r->id}][area]" value="{$r->options->area}" /></td>
	<td>
		<a href="#" onclick="slideRemove(this,'{$r->id}'); return false;"><img src="/templates/default/admin/assets/icons/cross.png" border="0" /></a>
	</td>
</tr>
EOF;
		}
		$content.=<<<EOF
</table>
<hr />
<strong>Добавить слайд:</strong><br />
<input type="file" name="slide_img" value="" />
<br /><br />
<strong>Ссылка:</strong><br />
<input type="text" name="slide_link" value="" />
<br /><br />
<strong>Расположение:</strong><br />
<input type="text" name="slide_location" value="" />
<br /><br />
<strong>Текст:</strong><br />
<input type="text" name="slide_text" value="" />
<br /><br />
<strong>Площадь и цена:</strong><br />
<input type="text" name="slide_area" value="" />
<br /><br />
<button onclick="sliderUpload(this); return false;" class="btn btn-mini">Добавить слайд</button>
EOF;

		$f->add("html",array(
			"label"=>"Слайды",
			"content"=>$content,
			"parent"=>"greed"
		));
	}
}
?>