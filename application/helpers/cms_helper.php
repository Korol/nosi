<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('hmn_duration'))
{
	function hmn_duration($total_time,$view_null=true)
	{
		$seconds=$total_time%60;
		$minutes=(floor($total_time/60))%60;
		$hours=floor($total_time/3600);

		if(strlen($hours)==1)$hours="0".$hours;
		if(strlen($minutes)==1)$minutes="0".$minutes;
		if(strlen($seconds)==1)$seconds="0".$seconds;

		$n=array($hours,$minutes,$seconds);

		if($view_null!=true){
			$n=array();
			if($hours>0)$n[]=$hours;
			if($minutes>0)$n[]=$minutes;
			if($seconds>0)$n[]=$seconds;
		}

		return implode(":",$n);
	}
}

if ( ! function_exists('ru_strtolower'))
{
	function ru_strtolower($text)
	{
		$alfavitlover=array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
		$alfavitupper=array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');
		
		return str_replace($alfavitupper,$alfavitlover,strtolower($text));
	}
}

if ( ! function_exists('rewrite_alias'))
{
	function rewrite_alias($var)
	{
		$letters_from=array("а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","з"=>"z","и"=>"i","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","ц"=>"c","ы"=>"y","і"=>"i");
		
		$bi_letters=array("й"=>"jj","ё"=>"jo","ж"=>"zh","х"=>"kh","ч"=>"ch","ш"=>"sh","щ"=>"shh","э"=>"je","ю"=>"ju","я"=>"ja","ъ"=>"","ь"=>"","ї"=>"yi","є"=>"ye");
	    
		$var=str_replace(array("&#092;","&quot;","&#039;"),array("\\","\"","'"),$var);
		
		$var=str_replace(".php","",$var);
		$var=trim(strip_tags($var));
		$var=preg_replace("/\s+/ms","-",$var);
		
		$var=ru_strtolower($var);
		
		$var=strtr($var,$letters_from);
		
		$var=strtr($var,$bi_letters);
		$var=preg_replace("/[^a-z0-9\_\-]+/mi","",$var);
		$var=preg_replace('#[\-]+#i','-',$var);
		$var=strtolower($var);
		if(mb_strlen($var,"UTF-8")>50){
			$var=substr($var,0,50);
			if(($temp_max=strrpos($var,'-')))$var=substr($var,0,$temp_max);
		}
		return $var;
	}
}

if ( ! function_exists('current_url_query'))
{
	function current_url_query($p)
	{
		$CI =& get_instance();

		$query=$_GET;

		if(isset($p)){
			foreach($p AS $k=>$v)
			{
				if(is_null($v)){
					unset($query[$k]);
				}else{
					$query[$k]=$v;
				}
			}
		}

		return $CI->admin_url."?".urldecode(http_build_query($query));
	}
}


if ( ! function_exists('directory_map'))
{
	function directory_map($source_dir, $directory_depth = 0, $hidden = FALSE)
	{
		if ($fp = @opendir($source_dir))
		{
			$filedata	= array();
			$new_depth	= $directory_depth - 1;
			$source_dir	= rtrim($source_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

			while (FALSE !== ($file = readdir($fp)))
			{
				if ( ! trim($file, '.') OR ($hidden == FALSE && $file[0] == '.'))
				{
					continue;
				}

				if (($directory_depth < 1 OR $new_depth > 0) && @is_dir($source_dir.$file))
				{
					$filedata[$file] = directory_map($source_dir.$file.DIRECTORY_SEPARATOR, $new_depth, $hidden);
				}
				else
				{
					$filedata[] = $file;
				}
			}

			closedir($fp);
			return $filedata;
		}

		return FALSE;
	}
}
   
if(!function_exists('directory_copy'))
{
    function directory_copy($srcdir, $dstdir)
    {
        //preparing the paths
        $srcdir=rtrim($srcdir,'/');
        $dstdir=rtrim($dstdir,'/');

        //creating the destenation directory
        if(!is_dir($dstdir))mkdir($dstdir);
        
        //Mapping the directory
        $dir_map=directory_map($srcdir);

        foreach($dir_map as $object_key=>$object_value)
        {
            if(is_numeric($object_key))
                copy($srcdir.'/'.$object_value,$dstdir.'/'.$object_value);//This is a File not a directory
            else
                directory_copy($srcdir.'/'.$object_key,$dstdir.'/'.$object_key);//this is a dirctory
        }
    }
}
   
if(!function_exists('directory_remove'))
{
    function directory_remove($srcdir,$data=array())
    {
        $srcdir=rtrim($srcdir,'/');

        if(!is_dir($srcdir))return false;

        $dh=opendir($srcdir);

		while(($file=readdir($dh))!==false)
		{
			if($file=="." || $file=="..")continue;

			if(is_dir($srcdir."/".$file)){
				directory_remove($srcdir."/".$file);
			}else{
				$data[]=$srcdir."/".$file;
			}
		}
		$data[]=$srcdir."/";

		foreach($data AS $file_path)
		{
			if(is_dir($file_path)){
				rmdir($file_path);
			}else{
				unlink($file_path);
			}
		}

		return true;
    }
}

if(!function_exists('humn_file_size'))
{
	function humn_file_size($size,$rounder="",$min="",$space="&nbsp;",$sizes=array("B","KB","MB","GB","TB","PB","EB","ZB","YB"))
	{
		$rounders=array(0,0,0,2,2,3,3,3,3);
		$ext=$sizes[0];
		$rnd=$rounders[0];
		
		if($min=="KB" && $size<1024){
			$size=$size/1024;
			$ext="KB";
			$rounder=1;
		}else{
			for($i=1,$cnt=count($sizes);($i<$cnt && $size>=1024);$i++)
			{
				$size=$size/1024;
				$ext=$sizes[$i];
				$rnd=$rounders[$i];
			}
		}
		
		if(!$rounder){
			$rounder=$rnd;
		}
		
		return round($size,$rounder).$space.$ext;
	}
}

if(!function_exists('search_clear_text'))
{
	function search_clear_text($text,$cut=true)
	{
		if($cut)$text=substr($text,0,64);
		$text=preg_replace("/[^\w\x7F-\xFF\s]/"," ",$text);
		$text=trim(preg_replace("/\s(\S{1,2})\s/"," ",ereg_replace(" +","  ",$text)));
		$text=ereg_replace(" +"," ",$text);
		return $text;
	}
}

if(!function_exists('directory_files_num'))
{
	function directory_files_num($source_dir,&$num=0)
	{
		$source_dir=rtrim($source_dir,"/")."/";

		if(!is_dir($source_dir))return false;

		if($fp=opendir($source_dir)){
			while(($file=readdir($fp))!==false)
			{
				if($file=="." || $file==".." || $file==".DS_Store" || $file=="thumbs.db")continue;

				if(is_dir($source_dir.$file)){
					directory_files_num($source_dir.$file,$num);
				}else{
					$num++;
				}
			}
		}

		return $num;
	}
}

if(!function_exists('price_double'))
{
	function price_double($price)
	{
		$price=str_replace(",",".",$price);
		$price=preg_replace("#[^0-9.]#is","",$price);
		$price=preg_replace("#\.+#",".",$price);

		if(empty($price))$price=0;

		if(preg_match("#([0-9]+)\.([0-9]{2})#is",$price,$pregs)){
			$price=floatval($pregs[1].".".$pregs[2]);
		}

		return $price;
	}
}









if(!function_exists("array_push_before"))
{
	function array_push_before($src,$in,$pos){
	    if(is_int($pos)){
	    	    	$R=array_merge(array_slice($src,0,$pos), $in, array_slice($src,$pos));
		}else{
	        foreach($src as $k=>$v){
	            if($k==$pos)$R=array_merge($R,$in);
	            $R[$k]=$v;
	        }
	    }
	    return $R;
	}
}

if(!function_exists("array_push_after"))
{
	function array_push_after($src,$in,$pos){
	    if(is_int($pos)) $R=array_merge(array_slice($src,0,$pos+1), $in, array_slice($src,$pos+1));
	    else{
	        foreach($src as $k=>$v){
	            $R[$k]=$v;
	            if($k==$pos)$R=array_merge($R,$in);
	        }
	    }return $R;
	}
}

if(!function_exists("r_mkdir"))
{
	function r_mkdir($src)
	{
		$src=rtrim($src,"/");
	    
		$dirs=explode("/",$src);
		$dirs_path=array();
		foreach($dirs AS $dir)
		{
			if(empty($dir) || $dir==".")continue;

			$dirs_path[]=$dir;

			if(!is_dir("./".implode("/",$dirs_path)."/")){
				mkdir("./".implode("/",$dirs_path)."/",0777);
			}
		}

		return true;
	}
}

if(!function_exists("n_file_name"))
{
	function n_file_name($name)
	{
		$name=strtolower($name);
		
		$tr=array("Ґ"=>"G","Ё"=>"YO","Є"=>"E","Ї"=>"YI","І"=>"I","і"=>"i","ґ"=>"g","ё"=>"yo","№"=>"N","є"=>"e","ї"=>"yi","А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D","Е"=>"E","Ж"=>"ZH","З"=>"Z","И"=>"I","Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH","Ш"=>"SH","Щ"=>"SCH","Ъ"=>"'","Ы"=>"YI","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"zh","з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"'","ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya");
		$name=str_replace(" ","_",strtr($name,$tr));
		
		$name=preg_replace("#[^a-zA-Z0-9-_.]#i","",$name);
		
		$name=preg_replace("#\.+#",".",$name);
		$name=preg_replace("#_+#","_",$name);
		
		return $name;
	}
}

if(!function_exists("file_name"))
{
	function file_name($path,$file_name,$nfilename=true)
	{
		if($nfilename)$file_name=n_file_name($file_name);
		
		for($q=1;;$q++)
		{
			if(!file_exists($path.$file_name))break;

			if($q>1){
				$file_name=preg_replace("#^[0-9]+_#is","",$file_name);
				$file_name=$q."_".$file_name;
			}else{
				$file_name=$q."_".$file_name;
			}
		}
		
		return $file_name;
	}
}







if(!function_exists("close_dangling_tags"))
{
	function close_dangling_tags($html)
	{
		preg_match_all("#<([a-z]+)( .*)?(?!/)>#iU",$html,$result);
		$openedtags=$result[1];
		
		preg_match_all("#</([a-z]+)>#iU",$html,$result);
		$closedtags=$result[1];
		$len_opened=count($openedtags);
		
		if(count($closedtags)==$len_opened){
			return $html;
		}

		$openedtags=array_reverse($openedtags);
		for($i=0;$i<$len_opened;$i++)
		{
			if(!in_array($openedtags[$i],$closedtags)){
				$html.="</".$openedtags[$i].">";
			}else{
				unset($closedtags[array_search($openedtags[$i],$closedtags)]);
			}
		}
		return $html;
	}
}

if(!function_exists("str_word"))
{
	function str_word($text,$maxlen=30,$after="...",$closeDanglingTags=true)
	{
		preg_match_all("#<[^>]*>#",$text,$tags);
		array_unique($tags);
		$tagList=array();
		$k=0;
		foreach($tags[0] AS $i)
		{
			$k++;
			$tagList[$k]=$i;
			$text=str_replace($i,"<".$k.">",$text);
		}
		
		$text_len=strlen($text);
		
		$words=explode(" ",$text);
		$text="";
		$len=0;
		foreach($words AS $i=>$word)
		{
			if($len>=$maxlen)break;
			$text.=$word." ";
			$len+=strlen($word);
		}
		
		$text=trim($text).(strlen($text)<$text_len?$after:"");
		
		foreach($tagList AS $k=>$i)
		{
			$text=str_replace("<".$k.">",$i,$text);
		}
		
		if(!$closeDanglingTags){
			return $text;
		}
		return close_dangling_tags($text);
	}
}

if(!function_exists("og_tag_content"))
{
	function og_tag_content($content)
	{
		$content=str_replace("<br />","\n",$content);
		$content=preg_replace("#<[^>]+>#is","",$content);
		$content=str_replace("\t","",$content);
		$content=str_replace("\"","&quot;",$content);

		return $content;
	}
}




if(!function_exists("num2str"))
{
function num2str($num) {
    $nul='ноль';
    $ten=array(
        array('','один','два','три','чотири','п\'ять','шість','сім', 'вісім','дев\'ять'),
        array('','одна','дві','три','чотири','п\'ять','шість','сім', 'вісім','дев\'ять'),
    );
    $a20=array('десять','одинадцять','дванадцять','тринадцять','чотырнадцять' ,'пятнадцять','шістнадцять','сімнадцять','вісімнадцять','девятнадцять');
    $tens=array(2=>'двадцять','тридцять','сорок','пятдесят','шістьдесят','сімдесят' ,'вісімдесят','дев\'яносто');
    $hundred=array('','сто','двісті','триста','чотириста','п\'ятсот','шістсот', 'сімсот','вісімсот','дев\'ятсот');
    $unit=array( // Units
        array('копійка' ,'копійки' ,'копійок',	 1),
        array('гривня'   ,'гривні'   ,'гривень'    ,1),
        array('тисяча'  ,'тисячі'  ,'тисяч'     ,1),
        array('мільйон' ,'мільйона','мільйонів' ,0),
        array('мільйард','мільйарда','мільйардів',0),
    );
    //
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub)>0) {
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
        } //foreach
    }
    else $out[] = $nul;
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}
}

if(!function_exists("morph"))
{
function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20) return $f5;
    $n = $n % 10;
    if ($n>1 && $n<5) return $f2;
    if ($n==1) return $f1;
    return $f5;
}
}

if ( ! function_exists('plural_form'))
	{
		function plural_form($n,$forms)
	{
		return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
	}
}

if(!function_exists('array_by_index'))
{
    /**
     * Индексирует исходный массив по указанному полю дочерних элементов массива
     * @param array $data - многомерный массив вида: array(0 => array('id' => 23, 'title' => 'Item 23'), ..., N => array('id' => 42, 'title' => 'Item 42'))
     * @param string $index_field - поле исходного массива, по которому будут созданы индексы нового массива: например, 'id'
     * @return array - новый многомерный массив вида: array(23 => array('id' => 23, 'title' => 'Item 23'), ..., 42 => array('id' => 42, 'title' => 'Item 42'))
     */
    function array_by_index($data, $index_field = 'id')
    {
        $return = array();
        if(!empty($data)){
            foreach ($data as $item){
                if(isset($item[$index_field])){
                    $return[$item[$index_field]] = $item;
                }
            }
        }
        return $return;
    }
}

if(!function_exists('array_to_simple'))
{
    /**
     * Упрощает массив - из многомерного массива возвращает одномерный массив со значениями указанного поля
     * Хорошо подходит для использования в SQL-условии IN:
     * AR: $this->db->where_in('id', array_to_simple($data));
     * Plain SQL: "... WHERE `id` IN ('" . implode("', '", array_to_simple($data)) . "') ..." --> "... WHERE `id` IN ('23', '42') ..."
     * @param array $data – многомерный массив вида: array(0 => array('id' => 23, 'title' => 'Item 23'), ..., N => array('id' => 42, 'title' => 'Item 42'))
     * @param string $field - поле, значения которого пойдут в новый массив
     * @return array - новый одномерный массив вида: array(23, 42)
     */
    function array_to_simple($data, $field = 'id')
    {
        $return = array();
        if(!empty($data)){
            foreach ($data as $item) {
                if(isset($item[$field])){
                    $return[] = $item[$field];
                }
            }
        }
        return $return;
    }
}

if (!function_exists('array_order_by')) 
{
    /**
     * Сортирует массив по указанным полям в указанных направлениях
     * 
     * $data[] = array('id' => 1,  'name' => 'Ezequiel',   'time' => 14234, 'points' => 8,  'friends' => 23);
     * $data[] = array('id' => 2,  'name' => 'Dumme',      'time' => 14234, 'points' => 8,  'friends' => 0);
     * $data[] = array('id' => 3,  'name' => 'Carla',      'time' => 14234, 'points' => 8,  'friends' => 17);
     * $data[] = array('id' => 4,  'name' => 'Yegor',      'time' => 11342, 'points' => 7,  'friends' => 12);
     * 
     * Pass the array, followed by the column names and sort flags 
     * $sorted = array_order_by($data, 'points', SORT_DESC, 'time', SORT_ASC, 'friends', SORT_DESC);
     * 
     * @return array
     */
    function array_order_by()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp[$field] = array();
                foreach ($data as $key => $row)
                    $tmp[$field][$key] = $row[$field];
                $args[$n] = &$tmp[$field];
            } else {
                $args[$n] = &$args[$n];
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
}

if(!function_exists('array_group_by_index'))
{
    /**
     * Группирует исходный массив по указанному полю дочерних элементов массива
     * @param array $data - многомерный массив вида: array(0 => array('id' => 23, 'group_id' => 152, 'title' => 'Item 23'), ..., N => array('id' => 42, 'group_id' => 153, 'title' => 'Item 42'))
     * @param string $index_field - поле исходного массива, по которому будут созданы индексы нового массива: например, 'id'
     * @return array - новый многомерный массив вида: array(152 => array(array('id' => 23, 'group_id' => 152, 'title' => 'Item 23'), ..., array('id' => 42, 'group_id' => 152, 'title' => 'Item 42')), 153 => array(array('id' => 23, 'group_id' => 153, 'title' => 'Item 23'), ..., array('id' => 42, 'group_id' => 153, 'title' => 'Item 42')),)
     */
    function array_group_by_index($data, $index_field = 'id')
    {
        $return = array();
        if(!empty($data)){
            foreach ($data as $item){
                if(isset($item[$index_field])){
                    $return[$item[$index_field]][] = $item;
                }
            }
        }
        return $return;
    }
}