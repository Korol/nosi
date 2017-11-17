<?php
/**
 * Created by PhpStorm.
 * User: korol
 * Date: 07.06.16
 * Time: 19:28
 */
$types = array(
    'app' => 'Application folder',
    'code' => 'Code folders',
);
$results = array(
    0 => 'Save',
    1 => 'Download',
);
?>
    <style>
        input, select {
            margin-bottom: 10px;
        }
    </style>
<?= form_open(base_url('test/backup')); ?>
Password:<br/>
<?= form_password('passw') . '<br/>'; ?>
Type:<br/>
<?= form_dropdown('type', $types) . '<br/>'; ?>
Result:<br/>
<?= form_dropdown('result', $results) . '<br/>'; ?>
<?= form_submit('subm', 'Send'); ?>
<?= form_close(); ?>