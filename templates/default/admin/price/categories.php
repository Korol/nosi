<?php
$hotline_categories = array();
$file = '';
if(file_exists(FCPATH . 'uploads/hl_tree')){
    $file = file_get_contents(FCPATH . 'uploads/hl_tree');
    $hl_tree = file(FCPATH . 'uploads/hl_tree');
    $one_key = '';
    $two_key = '';
    $tree_key = '';
    foreach ($hl_tree as $key => $value) {
        $value = rtrim($value);
        $tabs = strspn($value, "\t");
        $value = str_replace("\t", '', $value);
        if($tabs === 1){
            $hotline_categories[$value] = array();
            $one_key = $value;
        }
        elseif($tabs === 2){
            $hotline_categories[$one_key][$value] = array();
            $two_key = $value;
        }
        elseif($tabs === 3){
            $hotline_categories[$one_key][$two_key][] = $value;
        }
        elseif($tabs === 4){
            $hotline_categories[$one_key][$two_key][$tree_key][] = $value;
        }
    }
}
//var_dump($hotline_categories);
function make_cats_list($data){
    $return = array('');
    foreach ($data as $key => $row){
        if(is_array($row)){
            $return[] = $key;
            foreach ($row as $key1 => $row1){
                if(is_array($row1)){
                    $return[] = '– ' . $key1;
                    foreach ($row1 as $key2 => $row2){
                        if(is_array($row2)){
                            $return[] = '–– ' . $key2;
                            foreach ($row2 as $key3 => $row3){
                                if(is_array($row3)){
                                    $return[] = '––– ' . $key3;
                                    foreach ($row3 as $key4 => $row4){
                                        $return[] = '–––– ' . $row4;
                                    }
                                }
                                else
                                    $return[] = '––– ' . $row3;
                            }
                        }
                        else
                            $return[] = '–– ' . $row2;
                    }
                }
                else
                    $return[] = '– ' . $row1;
            }
        }
        else
            $return[] = $row;
    }
    return $return;
}
$hl_categories_list = make_cats_list($hotline_categories);
?>
<style type="text/css">
    #table_categories td {
        font-size: 14px;
    }
    #table_categories input[type=text], #table_categories select {
        width: 50%;
        float: right
    }
    .tc-wrp{
        width: 100%;
        height: auto;
        float: left;
    }
    .tc-wrp label {
        text-align: right;
        max-width: 50%;
        display: inline;
    }
    .accordion-inner p{
        font-size: 14px !important;
    }
</style>

<style type="text/css">
    .stats-menu{
        width: 100%;
        height: auto;
        float: left;
        margin: 10px 0;
    }
    .stats-menu-item{
        display: inline-block;
        margin-right: 30px;
        margin: 20px;
    }
</style>
<div class="stats-menu">
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=price&a=categories'); ?>">Категории Прайса</a></span>
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=price&a=build_price'); ?>">Создать прайс</a></span>
</div>
<div style="clear: both;"></div>
<?php if(!empty($price_success)): ?>
<div class="alert alert-success" style="width: 400px; margin-left: auto; margin-right: auto;">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <h4>Отлично!</h4><br/>
  <p><?=$price_success; ?></p>
</div>
<?php elseif(!empty($price_error)): ?>
<div class="alert alert-error" style="width: 400px; margin-left: auto; margin-right: auto;">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <h4>Ошибка!</h4><br/>
  <p><?=$price_error; ?></p>
  <p><?=($this->ci->session->userdata('sql_error')) ? $this->ci->session->userdata('sql_error') : ''; ?></p>
</div>
<?php endif; ?>

<div class="accordion" id="accordion2">
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
        Рекомендации по заполнению параметров Категорий
      </a>
    </div>
    <div id="collapseOne" class="accordion-body collapse">
      <div class="accordion-inner">
<p>Чтобы программа обработки правильно установила соответствие вашего товара с разделом нашего каталога, обязательно укажите категорию товара в соответствии с рубрикатором Hotline, включая гендерное деление (например, “Женские брюки”, “Мужские рубашки” и т.д.), а также укажите следующие данные:</p>
<pre>
<?php
echo htmlentities('Возраст в теге <param name="Возраст">Взрослый</param>
Пол в теге <param name="Пол">Женский</param>
Оригинальность (Оригинал/Реплика) в теге <param name="Оригинальность">Реплика</param>
Сезон в теге <param name="Сезон">Зима</param>
Тип в теге <param name="Тип">Платье</param>', ENT_COMPAT | ENT_HTML401, 'UTF-8');
?>
</pre>
<p><b>Пол</b> - рекомендуем указывать, для какого пола предназначено товарное предложение (“женский”, “мужской”). Желательно использовать данные значения, чтобы система могла более точно разместить ваши предложения внутри категорий.<br/>
<b>Возраст</b> - для корректного размещения в соответствующем разделе каталога рекомендуем указать информацию о том, для какого возраста предназначена единица товара. Допустимые значения элемента: "Взрослый", "Детский" и "Для малышей". К одежде со значением "Взрослый" относятся те предложения, размерная линейка которых содержит размерную сетку для взрослых людей. К "Детской одежде" относятся те предложения, размерная линейка которых рассчитана для детей от 2 до 14 лет. К "Одежде для малышей" относятся предложения для детей возрастом до 2 лет.<br/>
<b>Оригинальность</b> – данный параметр является обязательным для магазинов, товарные предложения которых не являются оригинальными товарами (к примеру, репликитоваров популярних брендов Armani, Gucci, проч.). Варианты указания данного параметра – Оригинал либо Реплика.<br/>
<b>Сезон</b> - рекомендуем в вашем прайс-листе для товаров, к которым применимо понятие сезонность (пальто, куртки, ботинки, сапоги) внести в тегах param информацию о том, к какому конкретно сезону относится то или иное товарное предложение.<br/>
<b>Тип</b> – рекомендуем указывать тип товара, чтобы система могла более точно разнести ваши товарные предложения, что позволит пользователям при фильтрации найти нужный товар.<br/>
Указание вышеприведенных параметров даст возможность качественно разместить ваши товарные позиции.</p>
      </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
        Дерево каталога Hotline в категории «Одежда, обувь, аксессуары»
      </a>
    </div>
    <div id="collapseTwo" class="accordion-body collapse">
      <div class="accordion-inner">
          <pre>
Одежда, обувь, аксессуары	
<?=$file; ?>
          </pre>
      </div>
    </div>
  </div>
</div>

<?php if(!empty($categories)): ?>
<form action="<?=  base_url('admin?m=price&a=update_categories'); ?>" method="post">
<table data-rows-num="" class="table table-bordered table-striped" id="table_categories" align="left" style="">
    <thead>
        <tr>
            <th>Категория NosiEto</th>
            <th>В прайсе</th>
            <th>Параметры</th>
            <th>Параметры</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($categories as $category): ?>
        <tr>
            <td>
                <?=$category['title']; ?>
                <input type="hidden" name="ids[<?=$category['id']; ?>]" value="<?=$category['id']; ?>" />
            </td>
            <td>
                <input type="checkbox" name="in_price[<?=$category['id']; ?>]" value="1" <?=($category['price_properties']['in_price'] > 0) ? 'checked="checked"' : ''; ?> />
            </td>
            <td>
        <?php 
        unset($price_fields['in_price']);
        $chunk_parts = ceil(count($price_fields)/2);
        $ik = 0;
        foreach ($price_fields as $p_key => $property): 
            if($ik == $chunk_parts) echo '</td><td>';
        ?>
                <div class="tc-wrp">
                    
                    <?php if($p_key === 'price_title' && !empty($hl_categories_list)): ?>
                    <label class="tc-label"><?=$property; ?>:</label>
                    <select name="<?=$p_key; ?>[<?=$category['id']; ?>]" id="sel_<?=$category['id']; ?>">
                    <?php foreach ($hl_categories_list as $hlc_option): ?>
                        <option value="<?=ltrim($hlc_option, "– "); ?>" <?=(mb_strpos($hlc_option, $category['price_properties'][$p_key]) !== FALSE) ? 'selected="selected"' : ''; ?>>
                            <?=$hlc_option; ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                    <?php elseif(in_array($p_key, array_keys($price_options))): ?>
                    <label class="tc-label"><?=$property; ?>:</label>
                    <select name="<?=$p_key; ?>[<?=$category['id']; ?>]" id="sel_<?=$category['id']; ?>">
                    <?php foreach ($price_options[$p_key] as $po_option): ?>
                        <option value="<?=$po_option; ?>" <?=($po_option === $category['price_properties'][$p_key]) ? 'selected="selected"' : ''; ?>>
                            <?=$po_option; ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                    <label class="tc-label"><?=$property; ?>:</label><input type="text" name="<?=$p_key; ?>[<?=$category['id']; ?>]" value="<?=$category['price_properties'][$p_key]; ?>" />
                    <?php endif; ?>
                </div>
            <?php $ik++; ?>
        <?php endforeach; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
    <input type="submit" name="upd_cats" class="btn btn-success" value="Сохранить изменения" />
</form>
<?php endif; ?>
