<script src="/modules/shop/media/js/shop.main.js"></script>
<?php  //error_reporting(E_ALL); //ini_set('display_errors', true);
include_once("./modules/shop/shop.helper.php");
$shopModuleHelper=new shopModuleHelper;

$products_query=$shopModuleHelper->products_query()->where("frontpage =",1);
$products_query->order_by("date_public DESC");
$prods_ch[] = $products_query->limit(3)->get()->result();//echo '<pre>';var_dump($this->db->_error_message());die();

$fp_erates = $shopModuleHelper->get_erates(); // курсы валют для конветации цен товаров

$currency = 'grn';
if ($this->ci->session->userdata('currentCurrency') != null){
    $currency = $this->ci->session->userdata('currentCurrency');    
}

switch (true){
    case $currency == 'usd':
        $currencyIcon = '$';
    break;
    case $currency == 'eur':
        $currencyIcon = '&euro;';
    break;
    case $currency == 'grn':
        $currencyIcon = 'грн.';
    break;
}

if(count($prods_ch)>0){
    // поместить ярлычки на NEW, SALE, STOCK картинки
    // приоритет ярлыков: Наличие => SALE => NEW
    $wm_categories = array(
        0 => array('id' => '1641', 'title' => 'В наличии', 'class' => 'wm-stock'),
        1 => array('id' => '1618', 'title' => 'SALE', 'class' => 'wm-sale'),
        2 => array('id' => '1617', 'title' => 'NEW', 'class' => 'wm-new'),
    );
    $disabled_cats = array(1641, 1618, 1617); // на категориях NEW, SALE, STOCK - ярлыки не показываем !!!
    $stikers = (in_array($category_res->id, $disabled_cats)) ? FALSE : TRUE;
    
	$i = 0;
	foreach($prods_ch[0] AS $p)
	{	
            // проверка категорий товара – для размещения на нём wm-стикера
            $stiker_info = array();
            if ($stikers) {
                $r_cats = (!empty($p->category_ids)) ? explode(',', $p->category_ids) : '';
                if (!empty($r_cats)) {
                    foreach ($wm_categories as $category) {
                        if (in_array($category['id'], $r_cats)) {
                            $stiker_info = $category;
                            break;
                        }
                    }
                }
            }
            // --
		$i++;
		// $p->full_description = implode(array_slice(explode('<br>',wordwrap(strip_tags($p->full_description),150,'<br>',false)),0,1));
//		$description = str_replace('&nbsp;',' ',preg_replace('/<[^>]*>/is','',$p->full_description));
//		$max = strlen($description);
//		$description=explode(" ",$description);
//		$text='';
//		foreach($description as $i=>$word){
//			if($i>=50) break;
//			$text.=$word." ";
//		}

		$p->link=$shopModuleHelper->link_product_view($p);
		$p->price_hmn=$shopModuleHelper->product_price($p);
		$p->add_to_cart_attrs=$shopModuleHelper->add_to_cart_attrs($p);
                $p->price = $shopModuleHelper->convert_price_by_currency($p->price, $p->currency, $currency, $fp_erates); // конвертим цену в текущую валюту
                $p->price_old = (!empty($p->price_old)) ? $shopModuleHelper->convert_price_by_currency($p->price_old, $p->currency, $currency, $fp_erates) : 0; // аналогично поступаем со старой ценой
		?><div class="productRow">
		<a href="<?php print $p->link; ?>">
                    <div class="productImgWrap"><!-- для размещения ярлыка на фото товара -->
			<span class="th"><img src="/uploads/shop/products/thumbs/<?php print $p->main_picture_file_name; ?>" alt="<?php print $p->title; ?>" /></span>
                        <?php 
                        // показ ярлыка поверх картинки товара
                        if((!empty($stiker_info)) && file_exists('./uploads/shop/products/thumbs/' . $p->main_picture_file_name)) {
                            echo '<div class="wm-stiker ' . $stiker_info['class'] . '"></div>'; 
                        }
                        ?>
                        <br />
                    </div><!-- /для размещения ярлыка на фото товара -->
			<div class="title" itemprop="name"><?php print $p->title; ?></div><br />
                        <div class="description"><?php print strip_tags($p->full_description); ?></div>
                        <?php if(!empty($p->code)): ?><span class="art">Арт: <?=$p->code; ?></span><?php endif; ?>
                        <span class="price"><span itemprop="price"><?php print $p->price; ?></span> <?=$currencyIcon;?></span>
                        <?php if(!empty($p->price_old)): ?><span class="price price-old"><?=$p->price_old; ?> <?=$currencyIcon;?></span><?php endif; ?>
			<!-- <a href="#"<?php print $shopModuleHelper->add_to_cart_attrs($p,"$(this).parents('.buttons').find('.quantity').val()"); ?> class="addToCart current quantity"><span>В корзину</span></a> -->
		</a>
		</div><?php
	}
	print '<div class="clear"><!-- --></div><div class="line"><!-- --></div>';
}
