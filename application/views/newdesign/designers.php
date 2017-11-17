<?php
/**
 * @see Designers::index
 * @var Designers $designers
 * @var Designers $designers_logos
 */

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
    <div class="col-lg-12">
        <p class="designers-title">Бренды</p>
    </div>
</div>
<div class="row">
    <div class="col-lg-9 col-lg-push-3">
        <div class="row">
            <?php
            if(!empty($designers)){
                foreach($designers as $d_key => $d_row){
                    if(($d_key > 0) && (($d_key % 3) == 0))
                        echo '</div><div class="row">';

                    $src = HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                    if(!empty($designers_logos[$d_row['id']]['file_name'])) {
                        $src = (file_exists(FCPATH . $designers_logos[$d_row['id']]['file_path'] . '/' . $designers_logos[$d_row['id']]['file_name']))
                            ? HTTP_HOST . $designers_logos[$d_row['id']]['file_path'] . '/' . $designers_logos[$d_row['id']]['file_name']
                            : HTTP_HOST . 'assets/newdesign/images/m4.jpg';
                    }
                    $link = base_url('brand/' . $d_row['id'] . '/' . $d_row['name']);
            ?>
            <div class="col-lg-4">
                <div class="thumbnail designers-thumb">
                    <a href="<?= $link; ?>">
                        <div class="designers-thumb-img">
                        <img src="<?= $src; ?>" alt="<?= $d_row['title']; ?>">
                        </div>
                        <div class="caption">
                            <h4><?= $d_row['title']; ?></h4>
                        </div>
                    </a>
                </div>
            </div>
            <?php
                }
            }
            ?>
        </div>
        <div class="row">
            <div class="top-pg">
                <?=$pagination; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-lg-pull-9">
        <?php if(!empty($designers_all)): ?>
        <ul class="nav nav-pills nav-stacked cat-childs-list">
            <?php foreach($designers_all as $designer_row): ?>
                <li role="presentation">
                    <a href="<?=base_url('brand/' . $designer_row['id'] . '/' . $designer_row['name']); ?>">
                        <?= $designer_row['title']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>