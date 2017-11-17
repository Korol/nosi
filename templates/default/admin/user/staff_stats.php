<?php
//$dp_start_date = (!empty($dp_start_date)) ? $dp_start_date : date('d.m.Y', mktime(0, 0, 0, date('n'), 1, date('Y')));
//$dp_end_date = (!empty($dp_end_date)) ? $dp_end_date : date('d.m.Y', mktime(23, 59, 59, date('n'), date('t'), date('Y')));
?>
<script src="/templates/default/admin/assets/bootstrap/js/bootstrap-datepicker.ru.js" charset="UTF-8"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#dateStart').datepicker({
            language: "ru",
            autoclose: true
        });
        $('#dateEnd').datepicker({
            language: "ru",
            autoclose: true
        });
    });
</script>
<form action="<?=  base_url('admin'); ?>" method="get" class="form-inline">
    <input type="hidden" name="m" value="user" />
    <input type="hidden" name="a" value="staff_stats" />
    <label for="dateStart">Начало:</label>
    <div class="input-append" style="margin-right: 20px;">
        <input name="date_start" type="text" class="span2" id="dateStart" value="<?=$dp_start_date; ?>" />
        <span class="add-on"><i class="icon-calendar"></i></span>
    </div>
    <label for="dateEnd">Конец:</label>
    <div class="input-append" style="margin-right: 20px;">
        <input name="date_end" type="text" class="span2" id="dateEnd" value="<?=$dp_end_date; ?>" />
        <span class="add-on"><i class="icon-calendar"></i></span>
    </div>
    <button type="submit" class="btn">Показать</button>
</form>
<?php if(!empty($staff) && !empty($products_cnt)): ?>
<div style="float: left; margin: 10px auto; width: 100%;">
    Добавлено товаров за выбранный период: <b><?=$products_cnt; ?></b>
</div>
<div style="float: left; margin: 10px auto 30px;">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Пользователь</th>
                <th>Количество товаров</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach($staff as $person): ?>
            <tr>
                <td>
                    <a href="<?=  base_url('admin/?m=user&a=staff_user_stats&user_id=' . $person['user_id'] . '&date_start=' . $dp_start_date . '&date_end=' . $dp_end_date); ?>">
                        <?=(!empty($person['first_name'])) ? $person['first_name'] . ' ' . $person['last_name'] : $person['username']; ?>
                    </a>
                </td>
                <td><?=$person['cnt']; ?></td>
            </tr>
                <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="alert alert-error" style="width: 495px;">
    <p>За выбранный вами период не добавлено ни одного товара((</p>
</div>
<?php endif; ?>