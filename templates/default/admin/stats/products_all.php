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
    #chart_div{
        float: left;
    }
</style>
<div class="stats-menu">
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=products_all'); ?>">За всё время</a></span>
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=products_by&by=today'); ?>">За сегодня</a></span>
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=products_by&by=week'); ?>">За последние 7 дней</a></span>
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=products_by&by=month'); ?>">За последние 30 дней</a></span>
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=summary'); ?>">Сводная информация</a></span>
    <span class="stats-menu-item">Всего результатов: <?=$num_res; ?></span>
</div>
<?php
if(!empty($display_charts))
{
    if(!$this->gcharts->hasErrors())
    {
        echo $this->gcharts->$chart_type($chart_name)->outputInto('chart_div');
        echo $this->gcharts->div(800, 300);
    }
    else
    {
        echo $this->gcharts->getErrors();
    }
}
?>
<?=(!empty($render)) ? $render : ''; ?>