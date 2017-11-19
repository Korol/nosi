<?php
/**
 * @var $action
 */
if(empty($action)){
    $action = array(
        'title' => '',
        'start' => '',
        'end' => '',
        'percent' => '',
        'active' => 1,
    );
}
else{
    $action['start'] = (!empty($action['start'])) ? substr($action['start'], 0, strrpos($action['start'], ':')) : '';
    $action['end'] = (!empty($action['end'])) ? substr($action['end'], 0, strrpos($action['end'], ':')) : '';
}
?>
<form action="">
    <div class="row no-ml">
        <div class="col-md-12">
            <h4>Акция:</h4>
        </div>
    </div>
    <div class="row no-ml">
        <div class="col-md-3">
            <div class="form-group">
                <label for="action_title"><span class="f-req">*</span>Название акции:</label>
                <input type="text" name="action_title" id="action_title" class="form-control" value="<?= $action['title']; ?>">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="action_start"><span class="f-req">*</span>Начало:</label>
                <div class="input-group" id="datetimepicker1">
                    <input type="text" name="action_start" id="action_start"
                           class="form-control" aria-describedby="action_from" value="<?= $action['start']; ?>">
                    <span class="input-group-addon" id="action_from">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="action_end"><span class="f-req">*</span>Окончание:</label>
                <div class="input-group" id="datetimepicker2">
                    <input type="text" name="action_end" id="action_end"
                           class="form-control" aria-describedby="action_to" value="<?= $action['end']; ?>">
                    <span class="input-group-addon" id="action_to">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="action_percent"><span class="f-req">*</span>Общая скидка, %:</label>
                <div class="input-group">
                    <input type="text" name="action_percent" id="action_percent"
                           class="form-control" aria-describedby="action_perc" value="<?= $action['percent']; ?>">
                    <span class="input-group-addon" id="action_perc">%</span>
                </div>
            </div>
        </div>
        <div class="col-md-1">
            <div class="form-group">
                <label for="action_end">Активна:</label>
                <div class="form-group">
                    <select class="form-control" name="action_active" id="action_active">
                        <option value="1" <?= ($action['active'] == 1) ? 'selected="selected"' : ''; ?>>Да</option>
                        <option value="0" <?= ($action['active'] == 0) ? 'selected="selected"' : ''; ?>>Нет</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="">&nbsp;</label>
                <div class="form-group">
                    <a class="btn btn-default btn-sm" id="save_action">Сохранить</a>
                    <span class="glyphicon glyphicon-ok ok-indicator"></span>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(function () {
        var dt_settings = {
            locale: 'ru',
            format: 'YYYY-MM-DD HH:mm'
        };
        $('#datetimepicker1').datetimepicker(dt_settings);
        $('#datetimepicker2').datetimepicker(dt_settings);

        $('#save_action').click(function () {
            var title = $('#action_title').val();
            var from = $('#action_start').val();
            var to = $('#action_end').val();
            var percent = $('#action_percent').val();
            var active = $('#action_active').val();
            console.log(title, from, to, percent, active);
            $.post(
                '/admin/?m=action&a=save_action',
                {
                    title: title,
                    from: from,
                    to: to,
                    percent: percent,
                    active: active
                },
                function (data) {
                    if(data*1 > 0){
                        $('.ok-indicator').show().delay(4000).fadeOut();
                    }
                },
                'text'
            );
        });
    });
</script>