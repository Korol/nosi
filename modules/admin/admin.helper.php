<?php
class adminModuleHelper extends Cms_modules {
	function __construct()
	{
		parent::__construct();

		// подключаем библиотеку для отрисовки интерфейса (формы, таблицы и тп.)
		$this->ci->load->library("fb");
		$this->load->helper('url');
		$this->load->helper('cms');
	}

	protected function rebuild_url_structure_orders($parent_id=0)
	{
		$url_structure_res=$this->db
		->order_by("order")
		->get_where("url_structure",array(
			"parent_id"=>$parent_id,
			"in_basket"=>"0"
		))
		->result();

		$i=1;
		foreach($url_structure_res AS $r)
		{
			$this->db
			->where("id",$r->id)
			->update("url_structure",array(
				"order"=>$i
			));
			$this->rebuild_url_structure_orders($r->id);

			$i++;
		}
	}

	protected function get_config_from_components($type,&$config=array(),&$config_ids=array())
	{
		foreach($this->ci->modules AS $module)
		{
			if(!is_object($module->info))continue;
			if(!method_exists($module->info,"admin_config"))continue;

			if(!isset($this->current_config_group_res) && $this->input->get("id")!==false){
				$this->current_config_group_res=$this->db
				->get_where("config",array(
					"id"=>intval($this->input->get("id"))
				))
				->row();
			}

			foreach($module->info->admin_config() AS $item)
			{
				if(!is_null($type) && $item['type']!=$type)continue;
				if(is_null($type) && $item['type']=="group")continue;

				$config_item_res=$this->ci->db->get_where("config",array(
					"component_type"=>"module",
					"component_name"=>$module->name,
					"type"=>$item['type'],
					"name"=>$item['name']
				))
				->row();

				$current_hash=
				$config_item_res->parent_id.
				$config_item_res->config_file_name.
				$config_item_res->name.
				$config_item_res->var_name.
				// $config_item_res->value.
				$config_item_res->default_value.
				$config_item_res->description.
				$config_item_res->type.
				$config_item_res->upload_path.
				$config_item_res->write_to_file.
				$config_item_res->hidden.
				$config_item_res->php_after_save;
				$current_hash=md5($current_hash);

				$file_hash=
				((int)$item['parent_id']).
				$item['config_file_name'].
				$item['name'].
				$item['var_name'].
				// $item['value'].
				$item['default_value'].
				$item['description'].
				$item['type'].
				$item['upload_path'].
				((int)$item['write_to_file']==1?1:0).
				((int)$item['hidden']==1?1:0).
				$item['php_after_save'];
				$file_hash=md5($file_hash);

				$item['id']=$config_item_res->id;

				if(isset($config_item_res->id) && $current_hash!=$file_hash){
					$item2=$item;
					unset($item2['value']);
					$this->ci->db
					->where("id",$config_item_res->id)
					->update("config",$item2);
				}elseif(!isset($config_item_res->id)){
					$this->ci->db->insert("config",array_merge($item,array(
						"component_type"=>"module",
						"component_name"=>$module->name
					)));
					$item['id']=$this->ci->db->insert_id();
				}

				$item['value']=$config_item_res->value;

				if($type!="group" && $module->name!=$this->current_config_group_res->component_name)continue;
				
				$config_ids[]=$item['id'];
				$config[]=(object)$item;
			}
		}

		return array($config,$config_ids);
	}

	protected function get_config($type=NULL,$id=NULL)
	{
		$where=array();

		if(is_numeric($id) || is_null($id)){
			if(!is_null($type)){
				$where['type']=$type;
			}
			$where['parent_id']=(int)$id;

			$where['component_type']="";

			$this->config_res=$this->db
			->select("config.*")
			->where($where)
			->get("config")
			->result();
		}else{
			$this->config_res=array();
		}

		list($config,$config_ids)=$this->get_config_from_components($type);

		return array_merge($this->config_res,$config);
	}

	protected function array_to_php($a,&$o="",$level=0)
	{
		$o.="array(\n";

		$level++;

		$i=1;
		foreach($a AS $k=>$v)
		{
			$o.=str_repeat("\t",$level);
			if(is_array($v)){
				$o.=$k."=>";
				$this->array_to_php($v,$o,$level);
			}elseif((is_numeric($v) && !preg_match("#\.#",$v)) || is_bool($v)){
				$o.=$k."=>".$v;
			}else{
				$o.=$k."=>\"".str_replace("\"","\\\"",$v)."\"";
			}

			if($i<sizeof($a)){
				$o.=",\n";
			}
			$i++;
		}

		$o.="\n";
		$o.=str_repeat("\t",$level-1);
		$o.=")";

		if($level==1)$o.=";";

		return $o;
	}

	protected function rebuild_config_file()
	{
		$config_res=$this->db
		->get_where("config")
		->result();

		$config="";
		foreach($config_res AS $r)
		{
			if(!empty($r->var_name)){
				$r->var_name_php=$r->var_name;
				$r->var_name_php=str_replace("[","['",$r->var_name_php);
				$r->var_name_php=str_replace("]","']",$r->var_name_php);
				$r->var_name_php="\$".$r->var_name_php;

				$config.=$r->var_name_php."=";

				if($r->value=="TRUE" || $r->value=="FALSE"){
					$r->value=strtolower($r->value);
				}

				if(($a=unserialize($r->value))!==false){
					$config.=$this->array_to_php();
				}else{
					if((is_numeric($r->value) && !preg_match("#\.#",$r->value)) || $r->value=="true" || $r->value=="false"){
						$config.=$r->value;
					}else{
						$config.="\"".str_replace("\"","\\\"",$r->value)."\"";
					}
				}
				$config.=";\n";
			}

			if(!empty($r->php_after_save)){
				$value=$r->value;
				eval($r->php_after_save);
			}
		}

		$config="<?php\n".trim($config)."\n?>";

		if(!is_writable("./config.php")){
			return false;
		}

		file_put_contents("./config.php",$config);

		return true;
	}

	// сканирует дерево категорий, возвращает путь к первому попавшемося .info.php файлу
	protected function folder_scan_for_info_file($root_folder_path="",&$find_path="")
	{
		if($root_folder_path=="")return false;

		$dh=opendir($root_folder_path);
		while(($file=readdir($dh))!==false)
		{
			if($file=="." || $file=="..")continue;

			if(preg_match("#^(.*)\.info\.php$#is",$file)){
				$find_path=$root_folder_path.$file;
				break;
			}else{
				if(is_dir($root_folder_path.$file)){
					$find_path=$this->folder_scan_for_info_file($root_folder_path.$file."/",$find_path);
				}
			}
		}

		return $find_path;
	}

	/*
	* Распаковывает модуль компонент в директорию ./install/
	*/
	protected function install_component($install_component_folder_name_path)
	{
		$info_file=$this->folder_scan_for_info_file($install_component_folder_name_path);
		if($info_file!==false && file_exists($info_file)){
			if(preg_match("#^(.*)\.info\.php$#is",basename($info_file),$pregs)){
				$component_name=$pregs[1];
			}
			
			$info_file_content=file_get_contents($info_file);
			include_once($info_file);

			if(preg_match("#class ".$component_name."ModuleInfo#is",$info_file_content)){
				$component_info_class_name=$component_name."ModuleInfo";
				$component_info=new $component_info_class_name();
				$component_type="module";
				$component_folder_path="./modules/";
			}elseif(preg_match("#class ".$component_name."WidgetInfo#is",$info_file_content)){
				$component_info_class_name=$component_name."WidgetInfo";
				$component_info=new $component_info_class_name();
				$component_type="widget";
				$component_folder_path="./widgets/";
			}elseif(preg_match("#class ".$component_name."TemplateInfo#is",$info_file_content)){
				$component_info_class_name=$component_name."TemplateInfo";
				$component_info=new $component_info_class_name();
				$component_type="template";
				$component_folder_path="./templates/";
			}else{
				return array(
					"error"=>"Расширение не опознано"
				);
			}
			
			if(is_dir($component_folder_path.$component_name)){
				$dh=opendir($component_folder_path.$component_name);
				$files_num=0;
				while(($file=readdir($dh))!==false)
				{
					if($file=="." || $file==".." || $file==".DS_Store")continue;
					$files_num++;
				}
				if($files_num==0){
					unlink($component_folder_path.$component_name);
				}else{
					return array(
						"error"=>"Это расширение уже установлено. Вам надо удалить расширение или в ручную удалить директорию ".$component_folder_path.$component_name
					);
				}
			}
			
			mkdir($component_folder_path.$component_name,0777);
			if(!is_writable($component_folder_path.$component_name)){
					return array(
						"error"=>"Недостаточно прав доступа для записи в директорию ".$component_folder_path.$component_name
					);
			}else{
				$path=pathinfo($info_file);
				$install_component_folder=$path['dirname']."/";
				
				// копируем файлы компонента в соответствующую директорию
				directory_copy($install_component_folder,$component_folder_path.$component_name);

				// копируем файлы шаблона админ. панели если они есть
				if(is_dir($component_folder_path.$component_name."/admin_tpl/")){
					mkdir("./templates/default/admin/".$component_name."/",0777);
					directory_copy($component_folder_path.$component_name."/admin_tpl/","./templates/default/admin/".$component_name."/");
					directory_remove($component_folder_path.$component_name."/admin_tpl/");
				}

				// копируем файлы шаблона фронт. части сайта если они есть
				if(is_dir($component_folder_path.$component_name."/site_tpl/")){
					mkdir("./templates/default/".$component_name."/",0777);
					directory_copy($component_folder_path.$component_name."/site_tpl/","./templates/default/".$component_name."/");
					directory_remove($component_folder_path.$component_name."/site_tpl/");
				}

				if(is_dir($component_folder_path.$component_name."/sql/")){
					foreach(glob($component_folder_path.$component_name."/sql/*.sql") AS $sql_file)
					{
						$this->sql_dump_import($sql_file);
						break;
					}
				}

				// добавляем запись в базу
				$this->db
				->insert("components",array(
					"type"=>$component_type,
					"title"=>!isset($component_info->title)?"":$component_info->title,
					"name"=>$component_name,
					"date_add"=>mktime()
				));

				$component_id=$this->db->insert_id();

				directory_remove($install_component_folder_name_path);
			}
		}

		return array(
			"component_id"=>$component_id
		);
	}

	protected function extract_component($zip_file_path="")
	{
		$this->load->library("unzip");

		$install_component_folder_name=substr(uniqid(md5(rand()),true).uniqid(md5(rand()),true),0,40);

		if(!is_dir("./install/"))mkdir("./install/",0777);
		if(!is_writable("./install/")){
			return array("error"=>"Недостаточно прав доступа для записи в директорию ./install/");
		}
		
		if(!is_writable("./modules/")){
			return array("error"=>"Недостаточно прав доступа для записи в директорию ./modules/");
		}
		if(!is_writable("./widgets/")){
			return array("error"=>"Недостаточно прав доступа для записи в директорию ./widgets/");
		}
		if(!is_writable("./templates/default/")){
			return array("error"=>"Недостаточно прав доступа для записи в директорию ./templates/default/");
		}
		if(!is_writable("./templates/default/admin/")){
			return array("error"=>"Недостаточно прав доступа для записи в директорию ./templates/default/admin/");
		}

		if(!is_dir("./install/".$install_component_folder_name."/"))mkdir("./install/".$install_component_folder_name."/",0777);
		if(!is_writable("./install/".$install_component_folder_name."/")){
			return array("error"=>"Недостаточно прав доступа для записи в директорию ./install/".$install_component_folder_name."/");
		}

		$this->ci->unzip->extract($zip_file_path,"./install/".$install_component_folder_name."/");

		return array(
			"path"=>"./install/".$install_component_folder_name."/"
		);
	}

	protected function download_component($component_name)
	{
		$component_res=$this->db
		->get_where("components",array(
			"name"=>$component_name
		))
		->row();

		if(intval($component_res->id)<1){
			return array(
				"error"=>"Компонент не найден"
			);
		}

		$this->load->library("zip");

		switch($component_res->type)
		{
			case'module':
				$component_folder_path="./modules";
				$component_class_name=$component_res->name."ModuleInfo";

				// удаляем каталоги и SQL файлы модуля
				directory_remove($component_folder_path."/".$component_res->name."/sql/");
				directory_remove($component_folder_path."/".$component_res->name."/admin_tpl/");
				directory_remove($component_folder_path."/".$component_res->name."/site_tpl/");

				if(!is_dir($component_folder_path."/".$component_res->name."/")){
					return array(
						"error"=>"Директория не найдена ".$component_folder_path."/".$component_res->name
					);
				}

				if(!is_writable($component_folder_path."/".$component_res->name."/")){
					return array(
						"error"=>"Недостаточно прав доступа для записи в директорию ".$component_folder_path."/".$component_res->name
					);
				}

				mkdir($component_folder_path."/".$component_res->name."/admin_tpl/",0777);
				directory_copy("./templates/default/admin/".$component_res->name."/",$component_folder_path."/".$component_res->name."/admin_tpl/");

				mkdir($component_folder_path."/".$component_res->name."/site_tpl/",0777);
				directory_copy("./templates/default/".$component_res->name."/",$component_folder_path."/".$component_res->name."/site_tpl/");
			break;
			case'widget':
				$component_folder_path="./widgets";
			break;
			case'template':
				$component_folder_path="./templates";
				$component_class_name=$component_res->name."TemplateInfo";
			break;
			default:
				return array(
					"error"=>"Расширение не опознано"
				);
			break;
		}

		if(!file_exists($component_folder_path."/".$component_res->name."/".$component_res->name.".info.php")){
			return array("error"=>"Файл ".$component_folder_path."/".$component_res->name."/".$component_res->name.".info.php"." не найден!");
		}

		include_once($component_folder_path."/".$component_res->name."/".$component_res->name.".info.php");

		if(!class_exists($component_class_name)){
			return array("error"=>"Класс ".$component_class_name." не найден!");
		}
		
		$component_info=new $component_class_name();

		if(is_array($component_info->sql_tables) && sizeof($component_info->sql_tables)>0){
			$tables_res=$this->db
			->query("SHOW TABLES")
			->result_array();

			$tables=array();
			foreach($component_info->sql_tables AS $r)
			{
				if(is_string($r)){
					foreach($tables_res AS $tables_r)
					{
						if(preg_match("#^".str_replace("*",".*",$r)."$#is",current($tables_r))){
							$tables[]=current($tables_r);
						}
					}
				}
			}

			if(sizeof($tables)>0){
				$sql="";
				foreach($tables AS $table)
				{
					$moduleTables[]=$table;
					// генерируем дамп структуры таблицы
					$createTable=$this->db
					->query("SHOW CREATE TABLE `".$table."`")
					->row_array();

					$sql.="--\n";
					$sql.="-- EXPORT TABLE `".$table."`\n";
					$sql.="--\n\n";
					$sql.=$createTable['Create Table'].";";
					$sql.="\n\n--\n\n";
					
					// генерируем дамп данных таблицы
					$NumericColumn = array();
					$columns_res=$this->db
					->query("SHOW COLUMNS FROM `".$table."`")
					->result_array();
					//$result=$this->db->q("SHOW COLUMNS FROM `".$table."`;");
					$field=0;
					foreach($columns_res AS $r)
					{
						$NumericColumn[$field++]=preg_match("/^(\w*int|year)/",$r['Type'])?1:0;
					}
					$fields=$field;
					$from=0;
					$limit=10;
					$numRows=$this->db
					->count_all_results($table);
					if($numRows>0){
						$data_res=$this->db
						->query("SELECT * FROM `".$table."`")
						->result_array();

						if(sizeof($data_res)>0){
							$sql.="--\n";
							$sql.="-- EXPORT TABLE `".$table."` DATA\n";
							$sql.="-- ROWS ".$numRows."\n";
							$sql.="--\n\n";
							$insert_query="INSERT INTO `".$table."` VALUES";
							$sql.=$insert_query;

							$row_i=1;
							for(;;)
							{
								$break=false;
								$rows_limit_num=0;
								for($i=0;$i<sizeof($data_res);$i++)
								{
									$field=$data_res[$i];
									if($row_i>sizeof($data_res)){
										$break=true;
										break;
									}
									if($rows_limit_num>11){
										$sql.="\n\n".$insert_query."\n";
										break;
									}
									$field_i=0;
									$row=array();
									foreach($field AS $col)
									{
										if($NumericColumn[$field_i]){
											$row[$field_i]=isset($col)?$col:"NULL";
										}else{
											$row[$field_i]=isset($col)?"'".mysql_escape_string($col)."'":"NULL";
										}
										$field_i++;
									}

									$sql.="(".implode(",",$row).")".($row_i==sizeof($data_res) || $rows_limit_num==11?";\n":",\n");
									$row_i++;
									$rows_limit_num++;
								}
								if($break){
									break;
								}
							}
							$sql.="\n\n--\n\n";
						}
					}
				}
			}
		}

		if(!empty($sql)){
			$sql_file_name="install-backup.sql";
			mkdir($component_folder_path."/".$component_res->name."/sql/",0777);
			file_put_contents($component_folder_path."/".$component_res->name."/sql/".$sql_file_name,$sql);
		}

		// добавляем в архив файлы модуля
		$this->ci->zip->read_dir($component_folder_path."/".$component_res->name."/");
		
		$final_zip_file_name="install-".$component_res->type."-".$component_res->name."-".date("d.m.Y_H-i-s").".zip";
		$this->ci->zip->archive("./uploads/".$final_zip_file_name);

		directory_remove($component_folder_path."/".$component_res->name."/sql/");
		directory_remove($component_folder_path."/".$component_res->name."/admin_tpl/");
		directory_remove($component_folder_path."/".$component_res->name."/site_tpl/");

		return array(
			"path"=>"./uploads/".$final_zip_file_name
		);
	}

	protected function sql_dump_import($sql_dump_file)
	{
		$a_sql_dump_file=file($sql_dump_file);

		array_walk($a_sql_dump_file,create_function('&$v,$k,$arr_rtn','$v2=str_replace(array("\n","\r","\t"),"",$v); if(preg_match("#^--#is",$v) || empty($v2)){unset($arr_rtn[$k]);}'),$a_sql_dump_file);
		
		$query="";
		$i=0;
		$querys=array();
		$error="";
		foreach($a_sql_dump_file AS $line)
		{
			if(preg_match("#^ *(CREATE|INSERT)#is",$line) && !empty($query)){
				$this->db->query(trim($query));
				$query="";
			}
			$query.=$line;
			
			if($i+1==sizeof($a_sql_dump_file) && !empty($query)){
				$this->db->query(trim($query));
			}
			$i++;
		}
		
		return true;
	}
}
?>