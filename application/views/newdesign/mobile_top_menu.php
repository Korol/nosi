<?php

?>
<nav class="navbar navbar-default" id="mob_top_nav">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?= base_url(); ?>">
                <img alt="Brand" src="<?= HTTP_HOST . 'assets/newdesign/images/top_logo.png'; ?>" class="mh-top-logo">
            </a>
            <a href="<?=base_url('order/cart'); ?>">
                <span class="glyphicon glyphicon-shopping-cart" id="tm_cart_icon" aria-hidden="true">
                    <span id="tm_cart_icon_num"><?= (!empty($this->items_in_cart)) ? $this->items_in_cart : ''; ?></span>
                </span>
            </a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <form class="navbar-form navbar-right" method="get" action="<?=base_url('search'); ?>">
                <div class="input-group">
                    <input type="text" name="s" class="form-control" placeholder="Поиск">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                    </span>
                </div>
            </form>
            <ul class="nav navbar-nav">
                <?php
                if(!empty($this->orig_top_menu)):
                    $this->orig_top_menu = array_order_by($this->orig_top_menu, 'order', SORT_ASC);
                    foreach($this->orig_top_menu as $mtm_level1):
                        $mtm_li_class = (!empty($mtm_level1['children'])) ? 'class="dropdown"' : '';
                        $mtm_l1_url = (!empty($mtm_level1['url'])) ? $mtm_level1['url'] : '#';
                ?>
                <li <?= $mtm_li_class; ?>>
                    <?php if(!empty($mtm_level1['children'])): ?>
                    <a href="#" class="dropdown-toggle mtm-l1-header" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= $mtm_level1['title']; ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url($mtm_l1_url); ?>"><?= $mtm_level1['title']; ?></a></li>
                        <?php $mtm_level1['children'] = array_order_by($mtm_level1['children'], 'order', SORT_ASC); ?>
                        <?php foreach($mtm_level1['children'] as $l1_children): ?>
                            <?php
                            $l1_options = (!empty($l1_children['options']))
                                ? json_decode($l1_children['options'], true)
                                : array();
                            $l1_url = (!empty($l1_children['url']))
                                ? base_url($l1_children['url'])
                                : ((!empty($l1_options['url'])) ? base_url(end(explode('/', $l1_options['url']))) : '#');
                            ?>
                        <li>
                            <a href="<?= $l1_url; ?>"><?= $l1_children['title']; ?></a>
                            <?php
                            if(!empty($l1_children['children'])):
                            ?>
                            <ul class="mtm-ul">
                                <?php
                                $l1_children['children'] = array_order_by($l1_children['children'], 'order', SORT_ASC);
                                foreach($l1_children['children'] as $l2_children):
                                    $l2_options = (!empty($l2_children['options']))
                                        ? json_decode($l2_children['options'], true)
                                        : array();
                                    $l2_url = (!empty($l2_children['url']))
                                        ? base_url($l2_children['url'])
                                        : ((!empty($l2_options['url'])) ? base_url(end(explode('/', $l2_options['url']))) : '#');
                                ?>
                                <li><a href="<?= $l2_url; ?>"><?= $l2_children['title']; ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <a href="<?= base_url($mtm_l1_url); ?>" class="mtm-l1-header"><?= $mtm_level1['title']; ?></a>
                    <?php endif; ?>
                </li>
                <?php
                    endforeach;
                endif;
                ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li>
                <?php if($this->ion_auth->logged_in() === false): ?>
                    <a href="#" data-toggle="modal" data-target="#loginModal" onclick="collapse_menu();">Войти</a> <a href="<?=base_url('registration'); ?>">Регистрация</a>
                <?php else: ?>
                    <!--                    <a href="#">--><?//= mb_substr(trim($this->session->userdata('username')), 0, 12, 'UTF-8'); ?><!--</a> <a href="--><?//= base_url('logout'); ?><!--">(Выход)</a>-->
                    <a href="#">Личный кабинет</a> <a href="<?= base_url('logout'); ?>">Выход</a>
                <?php endif; ?>
                </li>
                <?php /*li><a href="<?=base_url('order/cart'); ?>">Корзина</a></li*/?>
            </ul>
            <?php /*form class="navbar-form navbar-right" method="get" action="<?=base_url('search'); ?>">
                <div class="form-group">
                    <input type="text" name="s" class="form-control" placeholder="Поиск">
                </div>
                <button type="submit" class="btn btn-default">Найти</button>
            </form*/?>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
<script>
    function collapse_menu(){
        $('#bs-example-navbar-collapse-1').removeClass('in');
    }
</script>