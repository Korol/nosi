<?php
class standard_xml_importerPlugin extends Cms_plugins {
	function import_onMethodBeforeRender()
	{
		$ci=&get_instance();

		$options=$ci->fb->get("importer","options");

		$options['standard_xml_importer']="Стандартный XML импорт";

		$ci->fb->change("importer",array("options"=>$options));
	}
}
?>