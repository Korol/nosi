function print_r(arr, level) {
    var print_red_text = "";
    if(!level) level = 0;
    var level_padding = "";
    for(var j=0; j<level+1; j++) level_padding += "    ";
    if(typeof(arr) == 'object') {
        for(var item in arr) {
            var value = arr[item];
            if(typeof(value) == 'object') {
                print_red_text += level_padding + "'" + item + "' :\n";
                print_red_text += print_r(value,level+1);
		} 
            else 
                print_red_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
        }
    } 

    else  print_red_text = "===>"+arr+"<===("+typeof(arr)+")";
    return print_red_text;
}

$(document).ready(function(){
	$(".fixed-nav .nav-row a:not(.notr)").click(function(){
		acp_nav_buttons_click_callback(this);
	});

	tabs_click();
});

function acp_nav_buttons_click_callback(that)
{
	var form=$("form#"+$(that).data("form-name"));

	if($(that).data("url").trim()!=""){
		document.location.href=$(that).data("url").trim();
		return;
	}
	
	if(form.length==0){
		alert('Форма #'+$(that).data("form-name")+' не найден!');
		return false;
	}

	switch($(that).data("name"))
	{
		case'save':
			form.append('<input type="hidden" name="sm" value="1" />');
			form.append('<input type="hidden" name="form" value="'+$(that).data("form-name")+'" />');
			
			form.submit();
		break;
		case'apply':
			form.append('<input type="hidden" name="sm" value="1" />');
			form.append('<input type="hidden" name="apply_sm" value="1" />');
			form.append('<input type="hidden" name="form" value="'+$(that).data("form-name")+'" />');
			
			$(that).addClass("loading");
			form.ajaxSubmit({
				success:function(d){
					if(typeof d.errors=="object" && d.errors.length>0){
						var err='';
						$.each(d.errors,function(i,v){
							err+=v+'\n';
						});
						alert(err);
					}
					$(that).removeClass("loading");
				}
			});
		break;
		default:
		case'edit':
		case'add':
		case'back':
		case'delete':
		case'refresh':
			document.location.href=$(that).data("url");
		break;
	}
}

function cpFormHelper(p)
{
	p.originalForm=$("form#"+p.formId);
	return {
		p:p,
		submitForm:function(){
			//if(p.dynamicElements!=undefined){
				// есть динамические елементы!
			//}
			//return false;
		},
		orderedSuccess:function(d){
			var form=$("form#"+p.formId);

			var row=$("#"+d.field_name+"_files_list_"+d.insert_id);
			if(d.order=="down"){
				row.insertAfter(row.next());
			}else{
				row.insertBefore(row.prev());
			}

			if($(".reorderArrows").length==0){
				$(".reorderArrows a").hide();
			}else{
				$(".reorderArrows a").show();
				$(".reorderArrows:first a:last").hide();
				$(".reorderArrows:last a:first").hide();
			}

			$(".uploaderOrderHidden_id").remove();
		},
		reorderUpload:function(button,field_name,insert_id,order){
			var form=$("form#"+p.formId);
			if($('#uploaderIframe_'+field_name,form).length==0){
				form.append('<input type="hidden" name="sm" value="1" />'+
					'<iframe style="top:0; left:0;" width="1" height="1" frameborder="0" src="about:blank" name="uploaderIframe_'+field_name+'" id="uploaderIframe_'+field_name+'"></iframe>');
			}
			if($('#uploaderHidden_'+field_name,form).length==0){
				form.append('<input type="hidden" name="iframeUploaderHidden" value="'+p.formId+'" />');
			}
			$('#uploaderIframe_'+field_name,form).load(function(){
				$("input, textarea, select",form).removeAttr("disabled");
				$('#uploaderOrderHidden_id_'+field_name,form).remove();
				$(form).attr("target","");
			});

			if($('#uploaderOrderHidden_id_'+field_name,form).length==0){
				form.append('<input type="hidden" id="uploaderOrderHidden_id_'+field_name+'" class="uploaderOrderHidden_id" name="uploaderOrderHidden_id['+field_name+']" value="'+insert_id+':'+order+'" />');
			}

			$(form).attr("target",'uploaderIframe_'+field_name).submit();
			$("input, textarea, select",form).attr("disabled",true);
		},
		removeUpload:function(button,field_name,insert_id){
			var form=$("form#"+p.formId);
			if($('#uploaderIframe_'+field_name,form).length==0){
				form.append('<input type="hidden" name="sm" value="1" />'+
					'<iframe style="top:0; left:0;" width="1" height="1" frameborder="0" src="about:blank" name="uploaderIframe_'+field_name+'" id="uploaderIframe_'+field_name+'"></iframe>');
			}
			if($('#uploaderHidden_'+field_name,form).length==0){
				form.append('<input type="hidden" name="iframeUploaderHidden" value="'+p.formId+'" />');
			}
			$('#uploaderIframe_'+field_name,form).load(function(){
				$("input, textarea, select",form).removeAttr("disabled");
				$(form).attr("target","");
			});


			if($('#uploaderRemoveHidden_id_'+field_name,form).length==0){
				form.append('<input type="hidden" id="uploaderRemoveHidden_id_'+field_name+'" class="uploaderRemoveHidden_id" name="uploaderRemoveHidden_id['+field_name+']" value="'+insert_id+'" />');
			}

			$(form).attr("target",'uploaderIframe_'+field_name).submit();
			$("input, textarea, select",form).attr("disabled",true);
		},
		removeSuccess:function(d){
			var form=$("form#"+p.formId);

			if(d.type=="editor"){
				$("#"+d.field_name+"_files_list_"+d.insert_id).remove();

				if($('#'+d.field_name+'_files_list tr').length<=1){
					$('#'+d.field_name+'_files_list tr').hide();
				}
			}else{
				form.find("#"+d.field_name+"_thumb").html("");
			}
			$(".uploaderRemoveHidden_id").remove();

			if($(".reorderArrows").length==0){
				$(".reorderArrows a").hide();
			}else{
				$(".reorderArrows a").show();
				$(".reorderArrows:first a:last").hide();
				$(".reorderArrows:last a:first").hide();
			}
		},
		startUpload:function(button,field_name){
			var form=$("form#"+p.formId);

			if(form.find("input[name='"+field_name+"']").val()=="")return false;

			if($('#uploaderIframe_'+field_name,form).length==0){
				form.append('<input type="hidden" name="sm" value="1" />'+
					'<iframe width="1" height="1" frameborder="0" src="about:blank" name="uploaderIframe_'+field_name+'" id="uploaderIframe_'+field_name+'"></iframe>');
			}
			if($('#uploaderHidden_'+field_name,form).length==0){
				form.append('<input type="hidden" name="iframeUploaderHidden" value="'+p.formId+'" />');
			}
			$('#uploaderIframe_'+field_name,form).load(function(){
				$("input, textarea, select",form).removeAttr("disabled");
				$(form).attr("target","");
			});
			$(form).attr("target",'uploaderIframe_'+field_name).submit();
			$("input, textarea, select",form).attr("disabled",true);

			$("input[name="+field_name+"]").after('<input type="file" name="'+field_name+'" value="" />')
			$("input[name="+field_name+"]:first").remove();
		},
		uploadSuccess:function(d){
			var form=$("form#"+p.formId);
			
			var html='';

			if(d.type=="editor"){
				html+='<tr id="'+d.field_name+'_files_list_'+d.insert_id+'">';

				html+='<td>';
				if(d.ordering && /jpg|jpeg|png|gif$/.test(d.file_name)){
					html+='<img src="/admin/?m=admin&a=thumb&f='+d.file_path+d.file_name+'" />';
				}
				html+='<a href="/'+d.file_path+''+d.file_name+'" onclick="attachInsertAttachLink(this); return false;">'+d.file_original_name+'</a><br /><small>размер: '+d.hmn_file_size+', <a href="/'+d.file_path+''+d.file_name+'" target="_blank">скачать</a></small></td>';

				if(d.ordering){
					html+='<td class="reorderArrows">';
					html+='<a href="#" onclick="formHelper[\'form\'].reorderUpload(this,\''+d.field_name+'\',\''+d.insert_id+'\',\'down\'); return false;"><img src="/templates/default/admin/assets/icons/arrow_down.gif" /></a>';
					html+='&nbsp;';
					html+='<a href="#" onclick="formHelper[\'form\'].reorderUpload(this,\''+d.field_name+'\',\''+d.insert_id+'\',\'up\'); return false;"><img src="/templates/default/admin/assets/icons/arrow_up.gif" /></a>';
					html+='</td>';
				}

				html+='<td width="16"><a href="#" onclick="formHelper[\'form\'].removeUpload(this,\''+d.field_name+'\',\''+d.insert_id+'\'); return false;"><img src="/templates/default/admin/assets/icons/cross.png" /></a></td>';
				html+='</tr>';

				form.find("#"+d.field_name+"_files_list").append(html);
				$('#'+d.field_name+'_files_list').show();

				if($(".reorderArrows").length==0){
					$(".reorderArrows a").hide();
				}else{
					$(".reorderArrows a").show();
					$(".reorderArrows:first a:last").hide();
					$(".reorderArrows:last a:first").hide();
				}
			}else{
				html+='<img src="/admin/?m=admin&a=thumb&f='+d.file_path+''+d.file_name+'" />';
				html+='<div style="padding-top:4px;"><button onclick="formHelper[\'form\'].removeUpload(this,\''+d.field_name+'\',\''+d.insert_id+'\'); return false;" class="btn btn-danger btn-mini">удалить</button></div>';

				form.find("#"+d.field_name+"_thumb").html(html);
			}
		}
	};
}

function tabs_click()
{
	$(".nav li").click(function(){
		$(".datepicker").hide();
	});
}

function cpFormTableOrderSave(table_id,save_url)
{
	var d={order:{}};
	var html='';
	html+='<input type="hidden" name="table_order_sm" value="1" />';
	$("#table-"+table_id).find("input[name^=table_order]").each(function(){
		d['order'][$(this).data("id")]=$(this).val();
		html+='<input type="hidden" name="order['+$(this).data("id")+']" value="'+$(this).val()+'" />';
	});

	$("#table-"+table_id).wrap('<form method="post" id="table-form-'+table_id+'"></form>');
	$('#table-form-'+table_id).append(html).submit();
}


function attachInsertAttachLink(o)
{
	var link=$(o).attr("href");

	var html='';

	try {
		if(tinyMCE){
			if(/\.jpeg|jpg|gif|png|bmp$/i.test(link)){
				html='<img src="'+link+'" border="0" />';
			}else if(/\.mp3$/i.test(link)){
				html='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="" width="100%" height="17">';
				html+='<param name="movie" value="/templates/default/assets/media/video_player.swf" />';
				html+='<param name="allowFullScreen" value="true" />';
				html+='<param name="wmode" value="opaque">';
				html+='<param name="flashvars" value="file='+link+'&image=&usecaptions=true&usefullscreen=true&allowfullscreen=true" />';
				html+='<param name="bgcolor" value="#762e06">';
				html+='<embed wmode="opaque" pluginspage="http://www.macromedia.com/go/getflashplayer" allowScriptAccess="always" swliveconnect="true" name="cpmp3player_swf" src="/templates/default/assets/media/video_player.swf" quality="high" bgcolor="#762e06" width="100%" height="17" type="application/x-shockwave-flash" flashvars="mediaURL='+link+'&allowSmoothing=true&autoPlay=false&buffer=6&showTimecode=true&loop=false&controlColor=0xf0ce5b&controlBackColor=0x762e06&scaleIfFullScreen=true&defaultVolume=70&showScalingButton=true" allowfullscreen="true"></embed>';
				html+='</object>';
			}else if(/\.flv|\.mov|\.mp4|\.m4v$/i.test(link)){
				html='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="" width="400" height="310">';
				html+='<param name="movie" value="/templates/default/assets/media/video_player.swf" />';
				html+='<param name="allowFullScreen" value="true" />';
				html+='<param name="wmode" value="opaque">';
				html+='<param name="flashvars" value="file='+link+'&image=&usecaptions=true&usefullscreen=true&allowfullscreen=true" />';
				html+='<param name="bgcolor" value="#762e06">';
				html+='<embed wmode="opaque" pluginspage="http://www.macromedia.com/go/getflashplayer" allowScriptAccess="always" swliveconnect="true" name="cpmp3player_swf" src="/templates/default/assets/media/video_player.swf" quality="high" bgcolor="#762e06" width="400" height="310" type="application/x-shockwave-flash" flashvars="mediaURL='+link+'&allowSmoothing=true&autoPlay=false&buffer=6&showTimecode=true&loop=false&controlColor=0xf0ce5b&controlBackColor=0x762e06&scaleIfFullScreen=true&defaultVolume=70&showScalingButton=true" allowfullscreen="true"></embed>';
				html+='</object>';
			}else{
				html=' <a href="'+link+'" target="_blank">'+$(o).text()+'</a> ';
			}

			if(!tinyMCE.execCommand('mceInsertContent', false, html)){
                // обычная textarea
                if($("textarea:eq(0)").length>0){
                	$("textarea:eq(0)").val($("textarea:eq(0)").val()+html);
                }
            }
		}
	}catch(e){
	}
}
//$('#content').tinymce().execCommand('mceInsertContent',false,'<b>Hello world!!</b>');



function acp_table_first_checkedbox(that)
{
	var table=$(that).parents('table:eq(0)');
	var rows_num=parseInt(table.data("rows-num"));
	
	table.prevAll(".table_select_all_table_checkboxes").remove();

	if(that.checked){
		table.find('tr > td').find('input:checkbox:first').attr('checked',that.checked);

		if(rows_num>table.find("tr").find("td:first").length && parseInt($(that).data("select-all-from-table"))==1){
			// выбрать все объекты из таблицы

			var html='';
			html+='<div class="table_select_all_table_checkboxes"><label><input type="checkbox" onchange="acp_table_first_checkedbox_check_all_from_table(this); return false;" value="1" /> выбрать все <strong>'+rows_num+'</strong> объекта таблицы</label></div>';

			table.before(html);
		}
	}else{
		table.find('tr > td').find('input:checkbox:first').attr('checked',that.checked).removeAttr('checked');
	}
}

function acp_table_first_checkedbox_check_all_from_table(that)
{
	var div=$(that).parents("div.table_select_all_table_checkboxes:eq(0)");
	var table=div.nextAll('table:eq(0)');
	var checkbox_name=table.find("input:checkbox:first").data("checkbox-name");

	div.find("input:text, input:hidden").remove();

	if(that.checked){
		$(that).attr("disabled",true);

		var exclud_ids=[];
		table.find('tr > td').find('input:checkbox:first').each(function(){
			exclud_ids[exclud_ids.length]=$(this).val();
		});

		$.post(document.location.href,{
			select_all_from_table_sm:1,
			table_id:typeof table.attr("id")=="string"?table.attr("id"):"",
			exclud_ids:exclud_ids
		},function(d){
			$(that).removeAttr("disabled",true);

			var ids='';
			$.each(d.ids,function(i,v){
				ids+=v+',';
			});
			div.append('<input type="hidden" name="'+checkbox_name+'[all_from_table]" value="'+ids+'" />');
		});
	}
}