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
    .stats-row{
        width: 700px;
        height: auto;
        float: left;
        margin-bottom: 30px;
        margin-left: 50px;
    }
</style>
<div class="stats-menu">
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=products_all'); ?>">За всё время</a></span>
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=products_by&by=today'); ?>">За сегодня</a></span>
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=products_by&by=week'); ?>">За последние 7 дней</a></span>
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=products_by&by=month'); ?>">За последние 30 дней</a></span>
    <span class="stats-menu-item"><a href="<?=base_url('admin/?m=stats&a=summary'); ?>">Сводная информация</a></span>
    <span class="stats-menu-item">Всего просмотров товаров: <?=$summary['num_res']; ?></span>
</div>
<div class="stats-row">
<?php if(!empty($summary['browsers'])): ?>
<table data-rows-num="" class="table table-bordered table-striped" id="table-browsers" align="left" style="width:300px; margin-right: 50px;">
    <tbody>
        <tr>
            <th>Браузеры	</th>
            <th>Просмотры	</th>
        </tr>
        <?php foreach ($summary['browsers'] as $item): ?>
        <tr>
            <td><?=$item['user_browser']; ?></td>
            <td><?=$item['cnt']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php if(!empty($summary['platforms'])): ?>
<?php
$platforms = array('pc' => 'Десктопы', 'mobile' => 'Мобильные устройства');
?>
<table data-rows-num="" class="table table-bordered table-striped" id="table-devices" align="left" style="width:300px;">
    <tbody>
        <tr>
            <th>Платформы	</th>
            <th>Просмотры	</th>
        </tr>
        <?php foreach ($summary['platforms'] as $key => $item): ?>
        <tr>
            <td><?=$platforms[$key]; ?></td>
            <td><?=$item; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
</div>
<div class="stats-row">
<?php if(!empty($summary['devices'])): ?>
<table data-rows-num="" class="table table-bordered table-striped" id="table-devices" align="left" style="width:300px; margin-right: 50px;">
    <tbody>
        <tr>
            <th>Устройства	</th>
            <th>Просмотры	</th>
        </tr>
        <?php foreach ($summary['devices'] as $item): ?>
        <tr>
            <td><?=$item['user_device']; ?></td>
            <td><?=$item['cnt']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php if(!empty($summary['os'])): ?>
<table data-rows-num="" class="table table-bordered table-striped" id="table-os" align="left" style="width:300px;">
    <tbody>
        <tr>
            <th>Операционки	</th>
            <th>Просмотры	</th>
        </tr>
        <?php foreach ($summary['os'] as $item): ?>
        <tr>
            <td><?=$item['user_platform']; ?></td>
            <td><?=$item['cnt']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
</div>
