<script>
var isDraged=false;

var cpsSimpleDropDownStatus="close";
var cpsSimpleDropDownCloseEvent=false;
var cpsSimpleDropDownLastTarget;
function cpsSimpleDropDownShow(d)
{
	if(isDraged)return false;
	
	var pos=$(d.obj).position();
	
	var left=pos.left+$(d.obj).width()+parseInt($(d.obj).css("padding-left"))+parseInt($(d.obj).css("padding-right"))-$("#simpleDropDownW").width();
	if(left<0)left=pos.left+parseInt($(d.obj).css("padding-left"))+parseInt($(d.obj).css("padding-right"));
	
	var links='';
	$.each(d.links,function(i,v){
		if(v.length==0){
			links+='<li class="p"><!-- --></li>';
		}else{
			var confirm='';
			if(v[3])confirm=' onclick="return confirm(\'Вы уверены?\');"';
			if(v[0]!=""){
				links+='<li><a href="'+v[2]+'" style="background-image:url('+v[0]+');" class="icon"'+confirm+'>'+v[1]+'</a></li>';
			}else{
				links+='<li><a href="'+v[2]+'"'+confirm+'>'+v[1]+'</a></li>';
			}
		}
	});
	
	$("#simpleDropDownW ul").html(links);
	
	$("#simpleDropDownW").css({
		top:pos.top+$(d.obj).height()+parseInt($(d.obj).css("padding-top"))+parseInt($(d.obj).css("padding-bottom")),
		left:left
	}).show();
	cpsSimpleDropDownStatus="open";
	
	if(!cpsSimpleDropDownCloseEvent){
		$(document).click(function(e){
			var evt=e||window.event;
			var target=evt.target||evt.srcElement;
			if(target!=cpsSimpleDropDownLastTarget)$("#simpleDropDownW").hide();
		});
		cpsSimpleDropDownCloseEvent=true;
	}
	cpsSimpleDropDownLastTarget=d.obj;
}

function chStructureView()
{
	if($("div.siteStructureTree").hasClass("siteStructureTreeLinks")){
		// дерево уже в виде ссылок, нужно показать нормальное
		$("div.siteStructureTree input:text").stop().animate({width:1},300,function(){
			$(this).hide();
			$("div.siteStructureTree").removeClass("siteStructureTreeLinks");
			$("input.chStructureViewButton").val("Показать ссылки");
		});
	}else{
		$("div.siteStructureTree input:text").stop().width(1).show().animate({width:200},300,function(){
			$("div.siteStructureTree").addClass("siteStructureTreeLinks");
			$("input.chStructureViewButton").val("Спрятать ссылки");
		});
	}
}

$(function(){
	$(".tree").sortable({
		start:function(){
			isDraged=true;
			$("#simpleDropDownW").hide();
		},
		stop:function(event,ui){
			isDraged=false;

			var order={};
			$("div.siteStructureTree ul.tree li").each(function(){
				if(typeof $(this).data("id")!="undefined"){
					if(typeof order[$(this).data("parent-id")]=="undefined")order[$(this).data("parent-id")]=new Array();
					order[$(this).data("parent-id")][order[$(this).data("parent-id")].length]=$(this).data("id");
				}
			});

			$.post(document.location.href,{
				save_structure_order:1,
				order:order
			},function(d){
				if(parseInt(d)!=1){
					alert(d);
				}
			});
		}
	});
	$(".tree").disableSelection();
});
</script>

<div class="well">
	<div id="simpleDropDownW">
		<ul>
			<li><a href="#" style="background-image:url(/media/acp/topmenu/page.png);" class="icon">Добавить страницу</a></li>
			<li><a href="#" style="background-image:url(/media/acp/topmenu/page.png);" class="icon">Добавить раздел</a></li>
			<li class="p"><!-- --></li>
			<li><a href="#" style="background-image:url(/media/acp/topmenu/page.png);" class="icon">Свойства раздела</a></li>
			<li><a href="#" style="background-image:url(/media/acp/topmenu/page.png);" class="icon">Доступ к разделу</a></li>
			<li class="p"><!-- --></li>
			<li><a href="#" style="background-image:url(/media/acp/topmenu/page.png);" class="icon">Удалить раздел</a></li>
		</ul>
	</div>
	<input type="button" value="Показать ссылки" class="chStructureViewButton btn btn-info" style="float:right;" onclick="chStructureView();" />
	<?php print $structure; ?>
</div>