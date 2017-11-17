<?php
if(!$ajax){ // ajax
?>
<script>
function mediaGetItems(id)
{
	$.get("<?php print $this->admin_url; ?>?m=media&a=browse&ajax=1&id="+id,function(d){
		$(".browseList").html(d);
	});
}

function mediaCreateFolder()
{
	var title=$("#addFolderModal input#title").val().trim();

	if(title==""){
		alert('Введите название директории!');
		return false;
	}

	$.post("<?php print $this->admin_url; ?>?m=media&a=add_folder_ajax&id=<?php print intval($this->input->get("id")); ?>",{
		"title":title
	},function(d){
		if(typeof d.errors=="object"){
			var err="";
			$.each(d.errors,function(i,v){
				err+=v+'\n';
			});
			alert(err);
		}else{
			$("#addFolderModal input#title").val("");
			$("#addFolderModal .close").click();
		}

		mediaGetItems(<?php print intval($_GET['id']); ?>);
	});
}

function mediaSelectAll(o)
{
	if($(o).hasClass("allSelected")){
		$(o).removeClass("allSelected")
		$(o).html("выделить все");
		$(".browseList input:checkbox").removeAttr("checked");
	}else{
		$(o).addClass("allSelected")
		$(o).html("снять выделение со всех");
		$(".browseList input:checkbox").attr("checked",true);
	}
}

function mediaMassRemove()
{
	$("#browseForm").append('<input type="hidden" name="mass_remove_sm" value="1">').submit();
	/*$(".browseList input:checkbox[name^='item[']:checked").each(function(){

		alert($(thandlehis).data("type")+" : "+$(this).val());
	})*/
}

$(document).ready(function(){
	$(".browselist").sortable({
		handle:".thumb, .photoThumb",
		stop:function(event,ui){
			saveMediaOrder();
		}
	}).disableSelection();
});

function saveMediaOrder()
{
	var ids=[];
	$(".browselist .browseListRow input[name='order[]']").each(function(){
		ids[ids.length]=$(this).val();
	});

	$.post(document.location.href,{
		save_order_sm:1,
		order_ids:ids
	},function(d){
		if(parseInt(d)!=1){
			alert(d);
		}
	});
}
</script>
<div class="modal hide" id="addFolderModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Создать директорию</h3>
  </div>
  <div class="modal-body">
	<label>Название директории</label>
	<input type="text" name="title" id="title" value="" placeholder="Название директории" />
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Отмена</button>
    <button type="button" onclick="mediaCreateFolder(); return false;" class="btn btn-primary">Создать директорию</button>
  </div>
</div>

<div class="well">
	<!-- убрать выделение со всех -->
<div style="float:right;">
	<button href="#addFolderModal" role="button" data-toggle="modal" class="btn btn-primary btn-mini">создать директорию</button>
</div>
<button class="btn btn-mini" onclick="mediaSelectAll(this); return false;">выделить все</button>&nbsp;
<button class="btn btn-danger btn-mini" onclick="mediaMassRemove(this);">удалить выделенное</button>
</div>
<div>
<strong>Путь:</strong> <a href="<?php print $this->admin_url; ?>?m=media&a=browse&id=0">начало</a>
<?php
foreach($bread_crumbs_res AS $i=>$r)
{
	if($i==0)continue;
	?> / <a href="<?php print $this->admin_url; ?>?m=media&a=browse&id=<?php print $r[0]; ?>"><?php print $r[1]; ?></a><?php

	if($r[0]===$this->input->get("id")){
		?> <a title="Редактировать папку" href="<?php print $this->admin_url; ?>?m=media&a=edit_folder&id=<?php print $r[0]; ?>"><img src="/templates/default/admin/assets/icons/pencil.png"></a><?php
	}
}
?>
</div>

<script>
function openInfoModal(d)
{
	$("#infoModal").modal();

	$("#infoModal #link").val("http://"+document.domain+"/"+d.file_path+d.file_name);
}
</script>

<div class="modal hide" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Modal header</h3>
  </div>
  <div class="modal-body">
  	<strong>Ссылка:</strong><br />
    <input type="text" id="link" value="" autocomplete="off" onclick="this.select();" style="width:100%;" />
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button class="btn btn-primary">Save changes</button>
  </div>
</div>

<link href="/templates/default/admin/media/assets/css/main.css" rel="stylesheet" type="text/css">
<div class="browseListW">
<form method="post" id="browseForm">
	<input type="hidden" name="id" value="<?php print intval($_GET['id']); ?>" />
	<ul class="browseList">
		<?php
		} // ajax
		foreach($items_res AS $r)
		{
			$r->title_short=$r->title;
			if(mb_strlen($r->title_short)>17){
				$r->title_short=mb_substr($r->title_short,0,17)."...";
			}

			switch($r->type)
			{
				case'video':
					?><li class="browseListRow">
					<input type="hidden" name="order[]" value="<?php print $r->id; ?>:video" />
					<a href="<?php print $this->admin_url; ?>?m=media&a=remove_video&parent_id=<?php print $_GET['id']; ?>&id=<?php print $r->id; ?>" class="remove"></a>
					<a href="<?php print $this->admin_url; ?>?m=media&a=edit_video&parent_id=<?php print $_GET['id']; ?>&id=<?php print $r->id; ?>" class="edit"></a>

					<!-- <input type="checkbox" data-type="video" name="item[video][]" value="<?php print $r->id; ?>" /> -->
					<a href="<?php print $this->admin_url; ?>?m=media&a=edit_video&parent_id=<?php print $_GET['id']; ?>&id=<?php print $r->id; ?>" class="photoThumb" style="background-image:url(/admin/?m=admin&a=thumb&s=128&f=<?php print $r->thumb_file_path.$r->thumb_file_name; ?>);"></a>
					<span><?php print $r->title_short; ?></span>
				</li><?php
				break;
				case'photo':
					?><li class="browseListRow">
					<input type="hidden" name="order[]" value="<?php print $r->id; ?>:photo" />
					<a href="<?php print $this->admin_url; ?>?m=media&a=remove_photo&parent_id=<?php print $_GET['id']; ?>&id=<?php print $r->id; ?>" class="remove"></a>
					<a href="<?php print $this->admin_url; ?>?m=media&a=edit_photo&parent_id=<?php print $_GET['id']; ?>&id=<?php print $r->id; ?>" class="edit"></a>
					<a href="#" onclick="openInfoModal({file_path:'<?php print $r->file_path; ?>',file_name:'<?php print $r->file_name; ?>'}); return false;" class="info"></a>

					<input type="checkbox" data-type="photo" name="item[photo][]" value="<?php print $r->id; ?>" />
					<a href="<?php print $this->admin_url; ?>?m=media&a=edit_photo&parent_id=<?php print $_GET['id']; ?>&id=<?php print $r->id; ?>" class="photoThumb" style="background-image:url(/admin/?m=admin&a=thumb&s=128&f=<?php print $r->file_path.$r->file_name; ?>);"></a>
					<span><?php print $r->title_short; ?></span>
				</li><?php
				break;
				case'folder':
					$cover="";
					if(!empty($r->cover_file_name2)){
						$cover=$r->cover_file_path.$r->cover_file_name2;
					}elseif(!empty($r->cover_file_name)){
						$cover=$r->cover_file_name;
					}
					?><li class="browseListRow">
					<input type="hidden" name="order[]" value="<?php print $r->id; ?>:folder" />
					<a href="<?php print $this->admin_url; ?>?m=media&a=remove_folder&parent_id=<?php print $_GET['id']; ?>&id=<?php print $r->id; ?>" class="remove"></a>
					<a href="<?php print $this->admin_url; ?>?m=media&a=edit_folder&parent_id=<?php print $_GET['id']; ?>&id=<?php print $r->id; ?>" class="edit"></a>
					<input type="checkbox" data-type="folder" name="item[folder][]" value="<?php print $r->id; ?>" />
					<a href="<?php print $this->admin_url; ?>?m=media&a=browse&id=<?php print $r->id; ?>" class="thumb">
						<?php
						if(empty($cover)){
							?><div class="noveCover"></div><?php
						}else{
							?><div class="cover" style="background-image:url(/admin/?m=admin&a=thumb&s=100&f=<?php print $cover; ?>);"></div><?php
						}
						?>
					</a>
					<span><?php print $r->title_short; ?></span>
				</li><?php
				break;
			}
		}
		if(!$ajax){ // ajax
		?>
	</ul>
</form>
</div>

<?php print $render; ?>
<?php
} // ajax
?>