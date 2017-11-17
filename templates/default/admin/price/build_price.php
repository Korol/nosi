<style type="text/css">
    .stats-menu{
        width: 100%;
        height: auto;
        float: left;
        margin: 10px 0 20px;
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
<?php
var_dump($price_categories);
?>