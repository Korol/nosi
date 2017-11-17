<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title><?=$page['title']; ?></title>
    <meta name="title" content="<?=$page['meta_title']; ?>">
    <meta name="description" content="<?=$page['meta_description']; ?>">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!--    favicons-->
    <link rel="apple-touch-icon" sizes="57x57" href="<?=HTTP_HOST; ?>assets/newdesign/ico/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?=HTTP_HOST; ?>assets/newdesign/ico/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?=HTTP_HOST; ?>assets/newdesign/ico/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?=HTTP_HOST; ?>assets/newdesign/ico/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?=HTTP_HOST; ?>assets/newdesign/ico/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?=HTTP_HOST; ?>assets/newdesign/ico/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?=HTTP_HOST; ?>assets/newdesign/ico/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?=HTTP_HOST; ?>assets/newdesign/ico/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?=HTTP_HOST; ?>assets/newdesign/ico/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="<?=HTTP_HOST; ?>assets/newdesign/ico/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?=HTTP_HOST; ?>assets/newdesign/ico/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?=HTTP_HOST; ?>assets/newdesign/ico/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?=HTTP_HOST; ?>assets/newdesign/ico/favicon-16x16.png">
    <link rel="manifest" href="<?=HTTP_HOST; ?>assets/newdesign/ico/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?=HTTP_HOST; ?>assets/newdesign/ico/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <?php if(!empty($page['vk_api'])): ?>
        <!-- VK.com -->
        <script type="text/javascript" src="//vk.com/js/api/openapi.js?130"></script>
        <script type="text/javascript">
            VK.init({apiId: 5652742, onlyWidgets: true});
        </script>
    <?php endif; ?>
    <!--    /favicons-->
</head>
<body>
<!-- MODAL AREA -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center" id="loginModalLabel">Авторизация</h4>
            </div>
            <div class="modal-body text-center">
                <form method="post" method="/login.html" class="form-inline">
                    <div class="form-group">
                        <label class="sr-only" for="login_name">Email address</label>
                        <input type="text" class="form-control" id="login_name" name="login_name" placeholder="Логин">
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="login_password">Email address</label>
                        <input type="password" class="form-control" id="login_password" name="login_password" placeholder="Пароль">
                    </div>
                    <br /><br />
                    <a href="#" data-dismiss="modal" data-toggle="modal" data-target="#myModal1">Вспомнить пароль</a>
                </form>
            </div>
            <div class="modal-footer text-center">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="send_loginModal();">Войти</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModal1Label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center" id="myModal1Label">Восстановление пароля</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="remail"><span class="necessary">*</span> Логин:</label>
                    <input type="text" class="form-control" id="remail" name="email" placeholder="mail@example.com">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary"  onclick="do_remind(); return false;">Отправить</button>
            </div>
        </div>
    </div>
</div>
<!-- /MODAL AREA -->
<div class="container-fluid">
    <!-- HEADER -->
    <div id="top-wrapper">
        <div class="row">
            <div class="col-lg-3 col-lg-offset-9">
                <div class="tw-phone"></div>
                <div class="tw-phones">(068)962-44-36</div>
                <div class="tw-viber"></div>
                <div class="tw-phones">(066)124-51-47</div>
                <div class="clear"></div>
            </div>
        </div>
    </div>

    <div id="page-wrapper">
        <?php if(empty($this->is_mobile)): ?>
            <div class="row">
                <!-- Top -->
                <div class="col-lg-1 col-md-1">
                    <!-- Logo -->
                    <a href="<?=base_url(); ?>" title="Главная страница">
                        <!--                    <img src="--><?//=HTTP_HOST; ?><!--assets/newdesign/images/top_logo.png" alt="NosiEto.com.ua">-->
                        <img class="top-logo-img" src="<?=HTTP_HOST; ?>assets/newdesign/images/logo_big2.png" alt="NosiEto.com.ua">
                    </a>
                </div>
                <div class="col-lg-4 col-md-4">
                    <!-- Top Menu -->
                    <div id="cssmenu" class="csmdropdown">
                        <ul>
                            <?php
                            $itm1 = 0;
                            $tm_active_item = 0;
                            if(!empty($this->top_menu[1])):
                                $this->top_menu[1] = array_order_by($this->top_menu[1], 'order', SORT_ASC);
                                foreach($this->top_menu[1] as $tm_level1):
                                    $tm_class = '';
                                    if(!empty($this->tm_active_id) && ($this->tm_active_id == $tm_level1['id'])){
                                        $tm_class = ' class="active active-bold"';
                                        $tm_active_item = $tm_level1['id'];
                                    }
                                    elseif(empty($this->tm_active_id) && ($itm1 == 0)){
                                        $tm_class = ' class="active active-bold"';
                                        $tm_active_item = $tm_level1['id'];
                                    }
                                    ?>
                                    <li id="menu_<?=$tm_level1['id']; ?>"<?= $tm_class; ?>><a href="<?= base_url($tm_level1['url']); ?>"><?= $tm_level1['title']; ?></a></li>
                                    <?php
                                    $itm1++;
                                endforeach;
                            endif;
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 top-search2">
                    <!-- Search form -->
                    <form method="get" action="<?= base_url('search'); ?>">
                        <div class="input-group">
                            <input type="text" name="s" class="form-control" placeholder="Поиск..">
                          <span class="input-group-btn">
                            <button class="btn btn-default" type="submit"></button>
                          </span>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-4">
                    <!-- Auth & Basket -->
                    <div class="top-user">
                        <img src="<?=HTTP_HOST; ?>assets/newdesign/images/top_user.png" alt="User icon">
                        <?php if($this->ion_auth->logged_in() === false): ?>
                            <a href="#" data-toggle="modal" data-target="#loginModal">Войти</a> <a href="<?=base_url('registration'); ?>">(Регистрация)</a>
                        <?php else: ?>
                            <!--                    <a href="#">--><?//= mb_substr(trim($this->session->userdata('username')), 0, 12, 'UTF-8'); ?><!--</a> <a href="--><?//= base_url('logout'); ?><!--">(Выход)</a>-->
                            <a href="#">Личный кабинет</a> <a href="<?= base_url('logout'); ?>">(Выход)</a>
                        <?php endif; ?>
                    </div>
                    <div class="top-basket">
                        <img src="<?=HTTP_HOST; ?>assets/newdesign/images/top_basket.png" alt="User icon">
                        <a class="dropdown-toggle" id="top_cart_link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Корзина <span id="top_cart_span"><?= (!empty($this->items_in_cart)) ? '(' . $this->items_in_cart . ')' : ''; ?></span></a>
                        <?php //if(!empty($this->items_in_cart)): ?>
                        <ul id="top_cart_ul" class="dropdown-menu dropdown-menu-right" aria-labelledby="top_cart_link">

                        </ul>
                        <?php //endif; ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-11 col-lg-offset-1">
                    <!-- Sub Menu -->
                    <div id="submenu">
                        <?php
                        /* <li><a href="<?=base_url('designers'); ?>">ДИЗАЙНЕРЫ</a></li> */
                        if(!empty($this->top_menu[2])):
                            foreach($this->top_menu[2] as $tm2k => $tm_level2):
                                $tm2_class = '';
                                if($tm2k == $tm_active_item){
                                    $tm2_class = ' class="active-submenu"';
                                }
                                ?>
                                <ul id="sub_menu_<?= $tm2k; ?>"<?=$tm2_class; ?>>
                                    <?php
                                    if(!empty($tm_level2)):
                                        $tm_level2 = array_order_by($tm_level2, 'order', SORT_ASC);
                                        foreach($tm_level2 as $tml2):
                                            ?>
                                            <li id="sub_menu_<?= $tml2['id']; ?>"><a href="<?= base_url($tml2['url']); ?>"><?= $tml2['title']; ?></a></li>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </ul>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                    <!-- Sub Sub Menu -->
                    <div id="subsubmenu">
                        <?php
                        if(!empty($this->top_menu[3])):
                            foreach($this->top_menu[3] as $tm3k => $tm_level3):
                                ?>
                                <div class="subsubwrapper" id="sub_sub_menu_<?= $tm3k; ?>">
                                    <ul>
                                        <?php
                                        if(!empty($tm_level3)):
                                            $tm_level3 = array_order_by($tm_level3, 'order', SORT_ASC);
                                            foreach($tm_level3 as $tml3):
                                                ?>
                                                <li><a href="<?= base_url($tml3['url']); ?>"><?= $tml3['title']; ?></a></li>
                                                <?php
                                            endforeach;
                                        endif;
                                        ?>
                                    </ul>
                                </div>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php
            $this->load->view($this->view_path . 'mobile_top_menu');
            ?>
        <?php endif; ?>
        <hr class="top-hr">
        <!-- /HEADER -->