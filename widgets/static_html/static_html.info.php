<?php
class static_htmlWidgetInfo {
	public function admin_options(&$f)
	{
		$f->add("textarea:editor",array(
			"label"=>"HTML код",
			"name"=>"content",
			"id"=>"content",
			"parent"=>"greed",
			"attr:style"=>"height:400px; width:700px;",
			"editor:pagebreak"=>false,
			"editor:disabled_p"=>true,
			"translate"=>true
		));

		
	}
}
?>