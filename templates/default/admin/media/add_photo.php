<script>
function mediaMakeAlbumCover(o,photo_id,album_id)
{
	$(o).attr("disabled",true);
	$.post(document.location.href,{
		"make_album_cover_sm":1,
		"photo_id":photo_id,
		"album_id":album_id
	},function(d){
		$(o).removeAttr("disabled");
		if(parentInt(d)!=1){
			alert(d);
		}
	});
}
</script>
<?php print $render; ?>