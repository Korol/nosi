<!-- CONTENT -->
<div class="row">
    <div class="col-lg-12">
        <!-- Slider 1200x485 -->

        <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
            <!-- Wrapper for slides -->
            <div class="carousel-inner">
                <?php 
                $firstOptions = (!empty($main_slider[0]['options'])) ? json_decode($main_slider[0]['options'], true) : array();
                $firstSliderLink = (!empty($firstOptions['link'])) ? $firstOptions['link'] : ''; 
                ?>
<!--                <a class="sliderLogo" id="sliderLogo" href="--><?//=$firstSliderLink;?><!--"> </a>-->
                <?php
                foreach($main_slider as $ms_key => $ms_slide){
                    $ms_slide['options'] = json_decode($ms_slide['options'], true);
                ?>
                <div class="item <?= ($ms_key == 0) ? 'active' : ''; ?>">
                    <?php if(empty($this->is_mobile)): ?>
                        <img src="<?=HTTP_HOST . $ms_slide['file_path'] . $ms_slide['file_name']; ?>" alt="Slide <?=$ms_key; ?>" class="center-block">
                        <div class="carousel-caption">
    <!--                    description here-->
                            <h1 class="carousel-caption-header"><?= (!empty($ms_slide['options']['header'])) ? $ms_slide['options']['header'] : ''; ?></h1>
                            <p class="carousel-caption-text">
                                <?= (!empty($ms_slide['options']['content'])) ? $ms_slide['options']['content'] : ''; ?>
                            </p>
                            <a href="<?=$ms_slide['options']['link']; ?>">
                                <div class="cc-btn-link"></div>
                            </a>
                        </div>
                    <?php else: ?>
                    <a href="<?=$ms_slide['options']['link']; ?>">
                        <img src="<?=HTTP_HOST . $ms_slide['file_path'] . $ms_slide['file_name']; ?>" alt="Slide <?=$ms_key; ?>" class="center-block">
                    </a>
                    <?php endif; ?>
                </div>
                <?php } ?>
            </div>

            <!-- Indicators -->
            <ol class="carousel-indicators">
                <?php foreach($main_slider as $ms_key => $ms_slide){ ?>
                <li data-target="#carousel-example-generic" data-slide-to="<?=$ms_key; ?>" class="<?= ($ms_key == 0) ? 'active' : ''; ?>"></li>
                <?php } ?>
            </ol>

            <!-- Controls -->
            <?php if(empty($this->is_mobile)): ?>
                <a class="left carousel-control" href="#carousel-example-generic" data-slide="prev">
                    <div class="big-arrow-left"></div>
                </a>
                <a class="right carousel-control" href="#carousel-example-generic" data-slide="next">
                    <div class="big-arrow-right"></div>
                </a>
            <?php else: ?>
                <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php echo $this->widgets('main-page-middle'); ?>
<?php if(!empty($main_seo_text)): ?>
<div class="row">
    <div class="col-lg-1"></div>
    <div class="col-lg-10">
        <div class="cutted-text">
            <?= $main_seo_text; ?>
        </div>
    </div>
    <div class="col-lg-1"></div>
</div>
<?php endif; ?>
<!-- /CONTENT -->
<?php echo $this->widgets('content_bottom'); ?>