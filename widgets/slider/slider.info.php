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
                            $max_order = $this->ci->db->select('MAX(`order`) AS maxorder', FALSE)
                                    ->where('name', 'slide')
                                    ->get('uploads', 1)
                                    ->row();
				$order = (empty($max_order->maxorder)) ? 1 : ($max_order->maxorder + 1);

				$file_size=filesize("./uploads/slider/".$file_name);

				list($width,$height)=getimagesize("./uploads/slider/".$file_name);

				$options=array(
					"link"=>$_POST['slide_link'],
					"text"=>$_POST['slide_text'],
					"location"=>$_POST['slide_location'],
					"area"=>$_POST['slide_area'],
                    "header"=>$_POST['slide_header'],
                    "content"=>$_POST['slide_content'],
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
					"slide_area":"<?php print $_POST['slide_area']; ?>",
					"slide_header":"<?php print $_POST['slide_header']; ?>",
					"slide_content":"<?php print $_POST['slide_content']; ?>"

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
					"link"=>$data['link'],
					"header"=>$data['header'],
					"content"=>$data['content']
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

	html+='<tr id="files_list_'+d.id+'">';
	html+='<td class="sliderImg">';
	html+='<img alt="'+d.file_original_name+'" src="/admin/?m=admin&a=thumb&f='+d.file_path+d.file_name+'" />';
	html+='</td>';
	html+='<td class="sliderLink">';
	html+='Ссылка:<br/><input type="text" name="slide['+d.id+'][link]" value="'+d.slide_link+'" /><br/>';
    html+='Заголовок:<br/><input type="text" name="slide['+d.id+'][header]" value="'+d.slide_header+'" /><br/>';
    html+='Текст:<br/><textarea cols="10" rows="3" style="width: 95%;" name="slide['+d.id+'][content]">'+d.slide_content+'</textarea><br/>';
	html+='</td>';
//	html+='<td>';
//      html+='</td>';
	html+='<td class="sliderReorder"><a href="#" onclick="moveUpload('+d.id+',\'down\'); return false;" title="Опустить"><img src="/templates/default/admin/assets/icons/arrow_down.gif" alt="Down" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="moveUpload('+d.id+',\'up\'); return false;" title="Поднять"><img src="/templates/default/admin/assets/icons/arrow_up.gif" alt="Up" /></a></td>';
	html+='<td>';
//	html+='<a href="#" onclick="slideRemove(this,'+d.id+'); return false;"><img src="/templates/default/admin/assets/icons/cross.png" border="0" /></a>';
        html+='<a href="#" onclick="removeUpload('+d.id+'); return false;" title="Удалить элемент"><img src="/templates/default/admin/assets/icons/cross.png" alt="Remove" /></a>';
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
                        
// новый вариант удаления файлов
function removeUpload(id){
    // удаление файла из таблицы БД `uploads`
    $.post(
        '/admin/?m=admin&a=remove_upload',
        {id : id},
        function(data){
            if(data === 'ok'){
                // скрываем элемент
                $('#files_list_'+id).remove();
            }
        },
        'text'
    );
}
                       
// новый вариант перемещения файлов
function moveUpload(id, direct){
    // изменение `order` в таблице БД `uploads`
    $.post(
        '/admin/?m=admin&a=move_upload',
        {id : id, direct : direct},
        function(data){
            console.log(data);
            if(data === 'ok'){
                // перемещаем элемент
                var tr = $('#files_list_'+id);
                if(direct === 'up'){
                    tr.after(tr.prev()); // поднимаем
                }
                else{
                    tr.before(tr.next()); // опускаем
                }
            }
        },
        'text'
    );
}
</script>
<input type="hidden" name="key" value="{$key}" />
<iframe style="position:absolute; left:-50px; top:-50px;" width="1" height="1" id="sliderUploadIframe" name="sliderUploadIframe" src="about:blank"></iframe>
<table class="table" id="slideList" style="margin-top: 10px;">
<tr>
	<th class="sliderImg">Изображение</th>
	<th class="sliderLink">Ссылка, Заголовок, Текст</th>
        <th class="sliderReorder">Порядок</th>
	<th>&nbsp;</th>
</tr>
EOF;
		$res=$this->ci->db->order_by('order', 'asc')->get_where("uploads",array(
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
<tr id="files_list_{$r->id}">
	<td class="sliderImg">
		<img alt="{$r->file_original_name}" src="/admin/?m=admin&a=thumb&f={$r->file_path}{$r->file_name}" />
	</td>
	<td class="sliderLink">
	    Ссылка:<br/><input type="text" name="slide[{$r->id}][link]" value="{$r->options->link}" /><br/>
	    Заголовок:<br/><input type="text" name="slide[{$r->id}][header]" value="{$r->options->header}" /><br/>
	    Текст:<br/><textarea cols="10" rows="3" style="width: 95%;" name="slide[{$r->id}][content]">{$r->options->content}</textarea><br/>
    </td>
    <td class="sliderReorder">
        <a href="#" onclick="moveUpload('{$r->id}','down'); return false;" title="Опустить"><img src="/templates/default/admin/assets/icons/arrow_down.gif" alt="Down" /></a>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="#" onclick="moveUpload('{$r->id}','up'); return false;" title="Поднять"><img src="/templates/default/admin/assets/icons/arrow_up.gif" alt="Up" /></a>
    </td>
	<td>
            <a href="#" onclick="removeUpload('{$r->id}'); return false;" title="Удалить элемент"><img src="/templates/default/admin/assets/icons/cross.png" alt="Remove" /></a>
	</td>
</tr>
EOF;
    // старый вариант удаления слайдов
    // <a href="#" onclick="slideRemove(this,'{$r->id}'); return false;"><img src="/templates/default/admin/assets/icons/cross.png" border="0" /></a><a href="#" onclick="slideRemove(this,'{$r->id}'); return false;"><img src="/templates/default/admin/assets/icons/cross.png" border="0" /></a>
		}
		$content.=<<<EOF
</table>
<hr />
<strong>Добавить слайд:</strong><br />
<input type="file" name="slide_img" value="" />
<br /><br />
<strong>Ссылка:</strong><br />
<input type="text" style="width: 480px;" name="slide_link" value="" /><br/>
<strong>Заголовок:</strong><br />
<input type="text" style="width: 480px;" name="slide_header" value="" /><br/>
<strong>Текст:</strong><br />
<textarea cols="10" rows="3" style="width: 480px;" name="slide_content"></textarea><br/>
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