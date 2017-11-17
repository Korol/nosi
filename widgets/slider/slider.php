<?php
class sliderWidget extends Cms_modules {
	function __construct()
	{
		parent::__construct();
	}

	function view_widget(&$r)
	{
		$html="";
		$html.=<<<EOF
<a class="sliderLogo" href=""><!-- --></a> <div class="littleSliderW"><div class="littleSlider">
	<a href="#" onclick="littleSliderPause(); littleSliderPrev(); return false;" class="left"></a>
	<a href="#" onclick="littleSliderPause(); littleSliderNext(); return false;" class="right"></a>
	<a href="#" onclick="littleSliderPause(); return false;" class="pause"></a>
	<div class="cont">
		<div class="contI">
EOF;
		$res=$this->ci->db->order_by('order', 'asc')->get_where("uploads",array(
			"component_type"=>"widget",
			"component_name"=>"slider",
			"extra_id"=>intval($this->widget_id)
		))
		->result();
		foreach($res AS $i=>$r2)
		{
			if(is_string($r2->options)){
				$r2->options=json_decode($r2->options);
			}
			$html.=<<<EOF
			<div class="contRow">
				<a href="{$r2->options->link}"><img src="/{$r2->file_path}{$r2->file_name}" /></a>
			</div>
EOF;
		}
		$html.=<<<EOF
		</div>
	</div>
</div></div>
<script type="text/javascript">
var littleSliderData=[];
var littleSliderCurrentPos=0;
var littleSliderWorks=false;
var littleSliderInterval;
function littleSliderInit()
{
	$(".littleSliderW .contRow").each(function(){

		littleSliderData[littleSliderData.length]={
			html:$(this).html()
		};
	});

	$(".littleSliderW .contRow:gt(0)").remove();

	littleSliderInterval=setInterval(function(){
		littleSliderNext();
	},3000);

	var hr = $(".littleSliderW .contRow a").attr("href");
	$("a.sliderLogo").attr("href",hr);
}

function littleSliderPrev()
{
	littleSliderCurrentPos--;
	if(littleSliderCurrentPos==-1)littleSliderCurrentPos=littleSliderData.length-1;
	littleSliderSwitch(littleSliderCurrentPos);
}

function littleSliderNext()
{
	littleSliderCurrentPos++;
	if(littleSliderData.length==littleSliderCurrentPos)littleSliderCurrentPos=0;
	littleSliderSwitch(littleSliderCurrentPos);
}

function littleSliderPause()
{
	$(".littleSliderW .pause").fadeOut(50);
	clearInterval(littleSliderInterval);
}

function littleSliderSwitch(i)
{
	if(littleSliderWorks)return;
	littleSliderWorks=true;
	$(".littleSliderW .contRow:first").after('<div class="contRow">'+littleSliderData[i].html+'</div>');
	$(".littleSliderW .contRow:last").css({
		opacity:0
	}).show().animate({opacity:1},800,function(){
		$(".littleSliderW .contRow:first").remove();
		var hr = $(".littleSliderW .contRow a").attr("href");
		$("a.sliderLogo").attr("href",hr);
		littleSliderWorks=false;
	});
}

$(document).ready(function(){
	littleSliderInit();
});
</script>
EOF;
		return $html;
	}

	function view_widget3(&$r)
	{
		$html="";
		$html.=<<<EOF
<script>
$(document).ready(function(){
	initSlider();
});

var sliderData=[];
var currentSliderData=0;
function initSlider()
{
	$(".sliderW .sliderI").hover(function(){
		if($(".sliderW .dotsW a").length>1){
			$(".sliderW .nav").show();
		}
	},function(){
		$(".sliderW .nav").hide();
	});

	$(".sliderW .nav").mouseover(function(){
		$(".sliderW .nav").show();
	});

	$(".sliderI .slider .sliderRow").each(function(){
		sliderData[sliderData.length]={
			content:$(this).html()
		};
	});

	$(".sliderI .slider .sliderRowHide").remove();

	var html='';
	for(var i=0;i<sliderData.length;i++)
	{
		html+='<a onclick="sliderMove('+i+'); return false;" href="#"'+(i==0?' class="active"':'')+'></a>';
	}
	$(".sliderW .dotsW").html(html);

	var oneDotWidth=$(".sliderW .dotsW a:eq(0)").width()+parseInt($(".sliderW .dotsW a:eq(0)").css("margin-right"));
	var navWidth=oneDotWidth*$(".sliderW .dotsW a").length-parseInt($(".sliderW .dotsW a:eq(0)").css("margin-right"));

	if($(".sliderW .dotsW a").length>1){
		$(".dotsW").show().css("margin-left",($(".sliderW").width()-7)-navWidth);
	}else{
		$(".sliderW .dotsW").hide();
	}

	$(".sliderW .nav a").click(function(){
		if($(this).is(".left")){
			if(!sliderMove_works){
				currentSliderData--;
				sliderMove(currentSliderData);
			}
		}else{
			if(!sliderMove_works){
				currentSliderData++;
				sliderMove(currentSliderData);
			}
		}
	});
}

var sliderMove_works=false;
function sliderMove(n)
{
	if(sliderMove_works)return false;
	sliderMove_works=true;

	currentSliderData=n;
	if(n==-1){
		currentSliderData=sliderData.length-1;
	}

	if(n==sliderData.length){
		currentSliderData=0;
	}
	
	$(".sliderW .dotsW a.active").removeClass("active");
	$(".sliderW .dotsW a:eq("+currentSliderData+")").addClass("active");

	var html='';

	html+='<div class="sliderRow" style="position:absolute; display:none;">';
	html+=sliderData[currentSliderData].content;
	html+='</div>';

	$(".sliderI .slider .sliderRow:last").before(html);

	$(".sliderI .slider .sliderRow:last .dline").animate({
		opacity:0
	},100);

	$(".sliderI .slider .sliderRow:first").css({
		opacity:0,
		display:"block"
	})
	.animate({opacity:1},400,function(){
		$(this).css("position","static");
		$(".sliderI .slider .sliderRow:last").remove();
		sliderMove_works=false;
	});
}
</script>
<div class="sliderW">
	<div class="nav">
		<a href="#" onclick="return false;" class="left"></a>
		<a href="#" onclick="return false;" class="right"></a>
	</div>
	<div class="dotsW"></div>
	<div class="angls">
		<div class="angls1"></div>
		<div class="angls2"></div>
		<div class="angls3"></div>
		<div class="angls4"></div>
	</div>
	<div class="sliderI">
		<div class="slider">
EOF;
		$res=$this->ci->db->get_where("uploads",array(
			"component_type"=>"widget",
			"component_name"=>"slider",
			"extra_id"=>intval($this->widget_id)
		))
		->result();
		foreach($res AS $i=>$r2)
		{
			if(is_string($r2->options)){
				$r2->options=json_decode($r2->options);
			}

			$h="";
			if($i>0){
				$h=" sliderRowHide";
			}

			if(isset($r2->options->link) && !empty($r2->options->link)){
				$html.=<<<EOF
			<div class="sliderRow{$h}">
				<div class="dline">
					<span class="darea">{$r2->options->area}</span>
					<span class="dlocation">{$r2->options->location}</span>
					<span class="dtext">{$r2->options->text}</span>
				</div>
				<a href="{$r2->options->link}">
					<img src="/{$r2->file_path}{$r2->file_name}" border="0" />
				</a>
			</div>
EOF;
			}else{
				$html.=<<<EOF
			<div class="sliderRow{$h}">
				<div class="dline">
					<span class="darea">{$r2->options->area}</span>
					<span class="dlocation">{$r2->options->location}</span>
					<span class="dtext">{$r2->options->text}</span>
				</div>
				<img src="/{$r2->file_path}{$r2->file_name}" border="0" />
			</div>
EOF;
			}
		}
		$html.=<<<EOF
		</div>
	</div>
</div>
EOF;
		return $html;
	}

	function view_widget2(&$r)
	{
		$html="";

		$html.=<<<EOF
<style>
.sliderW .thumbs .thumb {
	display:block;
	height:253px;
	background-repeat:no-repeat;
	border:none;
}

.sliderW .thumbs .thumbHide {
	display:none;
}
</style>
<script>
$(document).ready(function(){
	initSlider();
});

$(window).resize(function(){
	$(".sliderW .thumbs .thumb").css({
		width:$(window).width()
	});
});

var sliderData=[];
var sliderDataPos=0;
function initSlider()
{
	$(".sliderW .thumbs .thumb").each(function(){
		sliderData[sliderData.length]={
			src:$(this).data("src"),
			link:$(this).attr("href")
		}
	});

	$(".sliderW .thumbs .thumb:gt(0)").remove();

	switchSliderTimer();
}

function switchSliderTimer()
{
	if(sliderDataPos==sliderData.length-1)sliderDataPos=-1;

	setTimeout(function(){
		sliderDataPos++;
		switchSlider(sliderDataPos);

		switchSliderTimer();
	},8000);
}

function switchSlider(n)
{
	var html='';
	if(typeof sliderData[n].link=="undefined"){
		html+='<span class="thumb thumbH2ide" style="background-image:url('+sliderData[n].src+');"></span>';
	}else{
		html+='<a class="thumb thumbH2ide" href="'+sliderData[n].link+'" style="background-image:url('+sliderData[n].src+');"></a>';
	}

	$(".sliderW .thumbs").prepend(html);

	$(".sliderW .thumbs .thumb:first").css({
		position:'absolute',
		opacity:0,
		width:$(".sliderW .thumbs").width()
	}).removeClass("thumbHide").animate({opacity:1},500,function(){
		$(".sliderW .thumbs .thumb:last").remove();
		$(".sliderW .thumbs .thumb:first").css({
			position:'static'
		});
	});
}
</script>
<div class="sliderW">
	<div class="slider">
		<div class="thumbsW">
			<div class="thumbsI">
				<div class="thumbs">
EOF;
		$res=$this->ci->db->get_where("uploads",array(
			"component_type"=>"widget",
			"component_name"=>"slider",
			"extra_id"=>intval($this->widget_id)
		))
		->result();
		foreach($res AS $r)
		{
			if(is_string($r->options)){
				$r->options=json_decode($r->options);
			}

			if(isset($r->options->link) && !empty($r->options->link)){
				$html.=<<<EOF
<a class="thumb" href="{$r->options->link}" data-src="/{$r->file_path}{$r->file_name}" style="background-image:url(/{$r->file_path}{$r->file_name});"></a>
EOF;
			}else{
				$html.=<<<EOF
<span class="thumb" data-src="/{$r->file_path}{$r->file_name}" style="background-image:url(/{$r->file_path}{$r->file_name});"></span>
EOF;
			}
		}
		$html.=<<<EOF
				</div>
			</div>
		</div>
	</div>
</div>
EOF;
		return $html;
	}
}
?>