<script>
var rebuilded_ids=[];
$(document).ready(function(){
	$("#rebuild_thumbs_start").click(function(){
		do_rebuild();
	});
});

function do_rebuild()
{
	$.post(document.location.href,{
		do_rebuild:1,
		rebuilded_ids:rebuilded_ids
	},function(d){
		if(typeof d!="object"){
			alert(d);
			return false;
		}
		$(".numStatus2").html(d.uploads_to_rebuild_num);

		if(d.uploads_to_rebuild_res.length>0){
			$(".progressW:hidden").slideDown();
			$(".startProgressW:visible").slideUp();

			$.each(d.uploads_to_rebuild_res,function(i,v){
				rebuilded_ids[rebuilded_ids.length]=v.id;
			});

			do_rebuild();
		}else{
			alert('nothing to rebuild!');
		}

		$(".numStatus1").html(rebuilded_ids.length);

		var proc=rebuilded_ids.length/d.uploads_to_rebuild_num*100;
		$(".progressW .progress .bar").width(proc+"%");
	});
}
</script>
<div class="well">
	<center class="startProgressW">
		<button type="button" id="rebuild_thumbs_start" class="btn btn-primary btn-large">Начать перестройку изображений</button>
		<br /><br />
		<small style="display:block; width:400px; margin:0 auto;">
			Перестройка изображений удаляет все созданные thumbs'ы изображений, и создает их заново исходя из текущих параметров обработки изображений. Это распространяется абсолютно на все изображения, со всех компонентов.
			<br /><br />
			Пока перестройка изображений не будет выполнена на 100%, не закрывайте браузер!
		</small>
	</center>

	<div class="progressW hide">
		<center><strong style="color:red;">Идет перестройка изображений! Не закрывайте это окно!</strong></center>
		прогресс (<span class="numStatus1">100</span> из <span class="numStatus2">232</span>):
		<div class="progress progress-striped active">
			<div class="bar" style="width: 0%;"></div>
		</div>

		<button class="btn btn-mini">Отмена</button>
	</div>
</div>

<?php
print $render;
?>