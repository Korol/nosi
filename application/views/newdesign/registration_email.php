<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>A Simple Responsive HTML Email</title>
    <style type="text/css">
        body {margin: 0; padding: 0; min-width: 100%!important; font-family: sans-serif; color: #444;}
        .content {width: 100%; max-width: 600px;}
        .header {padding: 30px;}
        .header a {color: #f6f8f1; font-size: 20px; text-transform: uppercase; text-decoration: none;}
        .header a:hover {text-decoration: underline;}
        .header, .footer, .info {border-left: 1px solid #93026f; border-right: 1px solid #93026f;}
        .info {padding: 20px 30px;}
        .info-row {width: 100%; height: auto; margin: 5px 0; float: left;}
        .info-header {width: 20%; text-align: right; padding-right: 2%; float: left; font-weight: 600;}
        .reginfo {width: 76%; text-align: left; padding-left: 2%; float: left;}
        .footer {padding: 15px 30px; color: #f6f8f1;}
        .footer a {color: #f6f8f1; text-decoration: underline;}
        .footer a:hover {text-decoration: none;}
        .centered {text-align: center;}
        .clear {clear: both;}
        @media only screen and (max-width: 480px) {
            .header a{font-size: 18px;}
            .info-row {font-size: 13px;}
            .info {padding: 20px 15px;}
            .info-header {width: 30%;}
            .reginfo {width: 66%;}
        }
    </style>
</head>
<body yahoo bgcolor="#f6f8f1">
<table width="100%" bgcolor="#f6f8f1" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <table class="content" align="center" cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td class="header centered" bgcolor="#93026f">
                        <a href="<?= base_url(); ?>">NosiEto.com.ua</a>
                    </td>
                </tr>
                <tr>
                    <td class="info">
                        <h2 class="centered">Поздравляем!</h2>
                        <p class="centered">Вы успешно зарегистрированы на сайте <a href="<?= base_url(); ?>">nosieto.com.ua</a></p>
                        <h3>Ваша регистрационная информация:</h3>
                        <div class="info-row"><div class="info-header">Ф.И.О:</div><div class="reginfo"><?= element('new_fio', $post); ?></div></div>
                        <div class="info-row"><div class="info-header">Email:</div><div class="reginfo"><?= element('new_email', $post); ?></div></div>
                        <div class="info-row"><div class="info-header">Пароль:</div><div class="reginfo"><?= element('new_password', $post); ?></div></div>
                        <div class="info-row"><div class="info-header">Телефон:</div><div class="reginfo"><?= element('new_phone', $post); ?></div></div>
                        <div class="info-row"><div class="info-header">Город:</div><div class="reginfo"><?= element('new_city', $post); ?></div></div>
                        <div class="info-row"><div class="info-header">Адрес:</div><div class="reginfo"><?= element('new_address', $post); ?></div></div>
                        <div class="info-row"><div class="info-header">Facebook:</div><div class="reginfo"><?= element('new_fb', $post); ?></div></div>
                        <div class="info-row"><div class="info-header">Vkontakte:</div><div class="reginfo"><?= element('new_vk', $post); ?></div></div>
                        <div class="clear"></div>
                    </td>
                </tr>
                <tr>
                    <td class="footer centered" bgcolor="#93026f">
                        <a href="<?=base_url(); ?>">nosieto.com.ua</a> &copy; <?= date('Y'); ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>