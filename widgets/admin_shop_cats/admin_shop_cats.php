<?php
class admin_shop_catsWidget extends Cms_modules {
	function __construct()
	{
		parent::__construct();
	}

	function r_cats(&$res,$parent_id=0,&$level=0,&$html="")
	{
		$num=0;
		foreach($res AS $r)
		{
			if($parent_id!=$r->parent_id)continue;
			$num++;
		}

		if($num==0)return;

		if($level==0){
			$html.=<<<EOF
<ul class="treeview" id="tree">
EOF;
		}else{
			$html.=<<<EOF
<ul>
EOF;
		}
		$level++;
		foreach($res AS $r)
		{
			if($parent_id!=$r->parent_id)continue;

			$link=$this->admin_url."?m=shop&a=products&filter_category_id=".$r->id;

			$html.=<<<EOF
<li>
	<a href="{$link}">{$r->title}</a>
EOF;
			$this->r_cats($res,$r->id,$level,$html);
			$html.=<<<EOF
</li>
EOF;
		}
		$html.=<<<EOF
</ul>
EOF;

		return $html;
	}

	function view_widget(&$r)
	{
		$html="";

		$categories_res=$this->ci->db->get_where("categoryes",array(
			"type"=>"shop-category"
		))
		->result();

		foreach($categories_res AS $r)
		{
			// print_r($r);
		}

		$r_cats=$this->r_cats($categories_res);

		$html.=<<<EOF
<link rel="stylesheet" href="/widgets/admin_shop_cats/jquery.treeview/jquery.treeview.css" />
<script src="/widgets/admin_shop_cats/jquery.treeview/lib/jquery.cookie.js" type="text/javascript"></script>
<script src="/widgets/admin_shop_cats/jquery.treeview/jquery.treeview.js" type="text/javascript"></script>

<script type="text/javascript">
$(function(){
	$("#tree").treeview({
		collapsed: true,
		animated: "fast",
		control:"#sidetreecontrol",
		prerendered: false,
		persist: "location"
	});
});

</script>
<div class="well" style="padding:8px 0;">
	<ul class="nav nav-list">
		<li class="nav-header">
		Категории магазина
		</li>
		<li>
			<center id="sidetreecontrol"><small><a href="?#">Свернуть</a> - <a href="?#">Развернуть</a></small></center>
			{$r_cats}
		</li>
	</ul>
</div>
EOF;

		return $html;
	}
}
?>