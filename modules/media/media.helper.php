<?php
class mediaModuleHelper extends Cms_modules {
	public function items_query()
	{
		$db=clone $this->ci->db;

		return $db->get_where();
	}
}
?>