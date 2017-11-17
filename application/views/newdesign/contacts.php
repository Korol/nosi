<?php
$error = $this->session->flashdata('cf_error');
$success = $this->session->flashdata('cf_success');
?>
<div class="row">
    <div class="col-lg-12">
        <?php if(!empty($breadcrumbs)): ?>
            <ol class="breadcrumb category-breadcrumbs">
                <?php for($i = 0; $i < sizeof($breadcrumbs); $i++): ?>
                    <?php $bc_li_class = ($i == (sizeof($breadcrumbs) - 1)) ? 'active' : ''; ?>
                    <li class="<?= $bc_li_class; ?>"><a href="<?= $breadcrumbs[$i]['url']; ?>"><?= $breadcrumbs[$i]['title']; ?></a></li>
                <?php endfor; ?>
            </ol>
        <?php endif; ?>
    </div>
</div>
<div class="row">
    <div class="col-lg-2"></div>
    <div class="col-lg-8 category-info">
        <h1 class="category-title"><?= $page_header; ?></h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab accusantium aspernatur at beatae commodi consequatur corporis cumque cupiditate deserunt doloremque dolores doloribus eaque eius esse est eum excepturi exercitationem explicabo fugiat impedit inventore modi molestiae molestias, nemo omnis, possimus quia quis recusandae reiciendis rerum sequi sint tenetur totam ullam vero voluptas voluptatem? Assumenda esse maxime nemo obcaecati quod sequi tempora!</p>
    </div>
    <div class="col-lg-2"></div>
</div>
<?php if(!empty($error) || !empty($success)): ?>
<div class="row">
    <div class="col-lg-2"></div>
    <div class="col-lg-8 category-info">
        <div class="alert alert-<?= (!empty($success)) ? 'success' : 'danger'; ?> text-center" role="alert">
            <?= (!empty($success)) ? $success : $error; ?>
        </div>
    </div>
    <div class="col-lg-2"></div>
</div>
<?php endif; ?>
<div class="row contact-info-block">
    <div class="col-lg-6">
        <div class="row">
            <div class="col-lg-5">
                <div class="cib-row"><img src="<?=HTTP_HOST . 'assets/newdesign/images/c_phone.png'; ?>" alt="Phone Icon"/><span class="cib-text">(068) 962-44-36</span></div>
                <div class="cib-row"><img src="<?=HTTP_HOST . 'assets/newdesign/images/c_phone.png'; ?>" class="img-hidden" alt="Phone Icon"/><span class="cib-text">(066) 124-51-47</span></div>
            </div>
            <div class="col-lg-7">
                <div class="cib-row"><img src="<?=HTTP_HOST . 'assets/newdesign/images/c_viber.png'; ?>" alt="Viber Icon"/><span class="cib-text">(068) 962-44-36</span></div>
                <div class="cib-row"><img src="<?=HTTP_HOST . 'assets/newdesign/images/c_skype.png'; ?>" alt="Skype Icon"/><span class="cib-text">loremipsum</span></div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="cib-row"><img src="<?=HTTP_HOST . 'assets/newdesign/images/c_email.png'; ?>" alt="Email Icon"/><span class="cib-text">sale@nosieto.com.ua</span></div>
            </div>
            <div class="col-lg-6"></div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <span class="cib-address">Наш адрес: Lorem ipsum dolor sit amet, consectetur adipisicing.</span>
            </div>
        </div>
        <div class="row cib-map">
            <div class="col-lg-12">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2541.8097495134352!2d30.53862900000001!3d50.42601620000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40d4cf4ee15a4505%3A0x764931d2170146fe!2z0JrQuNC10LI!5e0!3m2!1sru!2sua!4v1477231708659" width="560" height="350" frameborder="0" style="border:0" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <?= form_open(base_url('pages/contact_form')); ?>
    <?= form_hidden('from', ''); ?>
    <div class="col-lg-6">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="cib-header">Обратная связь</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group checkout-fg">
                    <label for="c_subject">Тема</label>
                    <input type="text" name="c_subject" class="form-control co-input input-lg"/>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="form-group checkout-fg">
                    <label for="c_fio">Ф.И.О</label>
                    <input type="text" name="c_fio" class="form-control co-input input-lg"/>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form-group checkout-fg">
                    <label for="c_email">Email</label>
                    <input type="text" name="c_email" class="form-control co-input input-lg"/>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group checkout-fg">
                    <label for="c_message">Сообщение</label>
                    <textarea name="c_message" class="form-control co-input input-lg c-message"></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 text-right">
                <button type="submit" id="cform_submit" class="btn btn-orange">Отправить</button>
            </div>
        </div>
    </div>
    <?= form_close(); ?>
</div>
<div class="row contact-staff-block">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-center order-header">Наши сотрудники</h2>
            </div>
        </div>
        <div class="row staff-peoples">
            <div class="col-lg-8 col-lg-offset-2">
                <div class="row">
                    <div class="col-lg-4 text-center">
                        <img class="img-circle staff-img" src="<?=HTTP_HOST . 'assets/newdesign/images/r1.jpg'; ?>" alt="Staff girl"/>
                        <h3 class="csb-sp-name">Lorem Ipsum</h3>
                        <p class="csb-sp-position">lorem ipsum</p>
                    </div>
                    <div class="col-lg-4 text-center">
                        <img class="img-circle staff-img" src="<?=HTTP_HOST . 'assets/newdesign/images/r2.jpg'; ?>" alt="Staff girl"/>
                        <h3 class="csb-sp-name">Lorem Ipsum</h3>
                        <p class="csb-sp-position">lorem ipsum</p>
                    </div>
                    <div class="col-lg-4 text-center">
                        <img class="img-circle staff-img" src="<?=HTTP_HOST . 'assets/newdesign/images/r3.jpg'; ?>" alt="Staff girl"/>
                        <h3 class="csb-sp-name">Lorem Ipsum</h3>
                        <p class="csb-sp-position">lorem ipsum</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row contact-staff-block">
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-center order-header">Наш офис</h2>
            </div>
        </div>
        <div class="row staff-peoples">
            <div class="col-lg-12">
                <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                    <!-- Wrapper for slides -->
                    <div class="carousel-inner">
                        <?php
                        for($i = 1; $i <= 4; $i++):
                        ?>
                            <div class="item <?= ($i == 1) ? 'active' : ''; ?>">
                                <a href="#">
                                    <img src="<?=HTTP_HOST . 'assets/newdesign/images/o' . $i . '.jpg'; ?>" alt="Slide <?=$i; ?>" class="center-block">
                                    <div class="carousel-caption">
                                        <!--                    description here-->
                                    </div>
                                </a>
                            </div>
                        <?php endfor; ?>
                    </div>

                    <!-- Indicators -->
                    <ol class="carousel-indicators">
                        <?php for($k = 1; $k <= 4; $k++): ?>
                            <li data-target="#carousel-example-generic" data-slide-to="<?=$k; ?>" class="<?= ($k == 1) ? 'active' : ''; ?>"></li>
                        <?php endfor; ?>
                    </ol>

                    <!-- Controls -->
                    <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
                        <div class="big-arrow-left"></div>
                    </a>
                    <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
                        <div class="big-arrow-right"></div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>