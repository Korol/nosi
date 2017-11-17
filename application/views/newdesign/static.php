<?php
/**
 * @var $page_content
 */
$breadcrumbs = array(
    0 => array(
        'title' => 'Главная',
        'url' => base_url(),
    ),
    1 => array(
        'title' => $page_content['title'],
        'url' => base_url($page_content['name'] . '.html'),
    ),
);
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
        <h2 class="text-center order-header"><?= $page_content['title']; ?></h2>
        <br/>
        <div class="row">
            <div class="col-lg-12"><?= $page_content['content']; ?></div>
        </div>
    </div>
</div>
<div class="row category-bottom-slider">
    <div class="col-lg-12">
        <?php echo $this->widgets('content_bottom'); ?>
    </div>
</div>