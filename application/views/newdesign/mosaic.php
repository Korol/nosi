<?php
$mosaic_path = 'uploads/shop/products/mosaic/';
$images_path = 'uploads/shop/products/thumbs/';
?>
<?php if(!empty($this->is_mobile)): ?>
<?php
    // мобильный вид
$this->load->view($this->view_path . 'mosaic_mobile', array(
        'products' => $products,
        'mosaic_path' => $mosaic_path,
        'images_path' => $images_path,
    )
);
?>
<?php else: ?>
<div class="row">
    <div class="col-lg-12">
        <!-- Mosaic -->
        <table class="mosaic">
            <tr>
                <td rowspan="2">
                    <div class="mosaic-colonne">
                        <?php
                        if(!empty($products[1])):
                        ?>
                            <a href="<?=base_url($products[1]['url']); ?>" title="<?=htmlspecialchars($products[1]['title']); ?>">
                                <img src="<?= HTTP_HOST . $mosaic_path . $products[1]['image']; ?>" alt="<?=htmlspecialchars($products[1]['title']); ?>">
                                <div class="mosaic-info-vert miv-middle">
                                    <div class="miv-title"><?=$products[1]['title']; ?></div>
                                    <div class="miv-price">
                                        <div class="mihv-current"><?=$products[1]['price']; ?><span class="mihp-currency">грн</span></div>
                                        <?php if(!empty($products[1]['price_old'])): ?>
                                        <span class="mihv-old">
                                            <span class="mihpo-strikethrough"><?= $products[1]['price_old']; ?></span><span class="mihp-currency">грн</span>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </a>
                        <?php
                        endif;
                        ?>
                    </div>
                </td>
                <td>
                    <div class="mosaic-square">
                    <?php if(!empty($products[2])): ?>
                        <a href="<?=base_url($products[2]['url']); ?>">
                            <img src="<?=HTTP_HOST . $images_path . $products[2]['file_name']; ?>" alt="<?=htmlspecialchars($products[2]['title']); ?>">
                            <div class="mosaic-info-hor">
                                <div class="mih-title"><?=$products[2]['title']; ?></div>
                                <div class="mih-price">
                                    <span class="mihp-current"><?=$products[2]['price']; ?><span class="mihp-currency">грн</span></span>
                                <?php if(!empty($products[2]['price_old'])): ?>
                                    <span class="mihp-old">
                                      <span class="mihpo-strikethrough"><?=$products[2]['price_old']; ?></span><span class="mihp-currency">грн</span>
                                    </span>
                                <?php endif; ?>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </a>
                    <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="mosaic-square">
                        <?php if(!empty($products[3])): ?>
                            <a href="<?=base_url($products[3]['url']); ?>">
                                <img src="<?=HTTP_HOST . $images_path . $products[3]['file_name']; ?>" alt="<?=htmlspecialchars($products[3]['title']); ?>">
                                <div class="mosaic-info-hor">
                                    <div class="mih-title"><?=$products[3]['title']; ?></div>
                                    <div class="mih-price">
                                        <span class="mihp-current"><?=$products[3]['price']; ?><span class="mihp-currency">грн</span></span>
                                        <?php if(!empty($products[3]['price_old'])): ?>
                                            <span class="mihp-old">
                                      <span class="mihpo-strikethrough"><?=$products[3]['price_old']; ?></span><span class="mihp-currency">грн</span>
                                    </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="mosaic-square">
                    <?php if(!empty($categories[4])): ?>
                        <a href="<?=base_url($categories[4]['url']); ?>" title="<?=htmlspecialchars($categories[4]['title']); ?>">
                            <img src="<?=HTTP_HOST; ?>assets/newdesign/images/m4.jpg" alt="Mosaic 4">
                            <div class="mosaic-category"><?=$categories[4]['title']; ?></div>
                        </a>
                    <?php endif; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="mosaic-square">
                        <?php if(!empty($products[5])): ?>
                            <a href="<?=base_url($products[5]['url']); ?>">
                                <img src="<?=HTTP_HOST . $images_path . $products[5]['file_name']; ?>" alt="<?=htmlspecialchars($products[5]['title']); ?>">
                                <div class="mosaic-info-hor">
                                    <div class="mih-title"><?=$products[5]['title']; ?></div>
                                    <div class="mih-price">
                                        <span class="mihp-current"><?=$products[5]['price']; ?><span class="mihp-currency">грн</span></span>
                                        <?php if(!empty($products[5]['price_old'])): ?>
                                            <span class="mihp-old">
                                      <span class="mihpo-strikethrough"><?=$products[5]['price_old']; ?></span><span class="mihp-currency">грн</span>
                                    </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="mosaic-square">
                        <?php if(!empty($products[6])): ?>
                            <a href="<?=base_url($products[6]['url']); ?>">
                                <img src="<?=HTTP_HOST . $images_path . $products[6]['file_name']; ?>" alt="<?=htmlspecialchars($products[6]['title']); ?>">
                                <div class="mosaic-info-hor">
                                    <div class="mih-title"><?=$products[6]['title']; ?></div>
                                    <div class="mih-price">
                                        <span class="mihp-current"><?=$products[6]['price']; ?><span class="mihp-currency">грн</span></span>
                                        <?php if(!empty($products[6]['price_old'])): ?>
                                            <span class="mihp-old">
                                      <span class="mihpo-strikethrough"><?=$products[6]['price_old']; ?></span><span class="mihp-currency">грн</span>
                                    </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
                <td rowspan="2">
                    <div class="mosaic-colonne">
                        <?php
                        if(!empty($products[7])):
                        ?>
                            <a href="<?=base_url($products[7]['url']); ?>" title="<?=htmlspecialchars($products[7]['title']); ?>">
                                <img src="<?= HTTP_HOST . $mosaic_path . $products[7]['image']; ?>" alt="<?=htmlspecialchars($products[7]['title']); ?>">
                                <div class="mosaic-info-vert">
                                    <div class="miv-title"><?=$products[7]['title']; ?></div>
                                    <div class="miv-price">
                                        <div class="mihv-current"><?=$products[7]['price']; ?><span class="mihp-currency">грн</span></div>
                                        <?php if(!empty($products[7]['price_old'])): ?>
                                        <span class="mihv-old">
                                          <span class="mihpo-strikethrough"><?= $products[7]['price_old']; ?></span><span class="mihp-currency">грн</span>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </a>
                        <?php
                        endif;
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="mosaic-square">
                        <?php if(!empty($products[8])): ?>
                            <a href="<?=base_url($products[8]['url']); ?>">
                                <img src="<?=HTTP_HOST . $images_path . $products[8]['file_name']; ?>" alt="<?=htmlspecialchars($products[8]['title']); ?>">
                                <div class="mosaic-info-hor">
                                    <div class="mih-title"><?=$products[8]['title']; ?></div>
                                    <div class="mih-price">
                                        <span class="mihp-current"><?=$products[8]['price']; ?><span class="mihp-currency">грн</span></span>
                                        <?php if(!empty($products[8]['price_old'])): ?>
                                            <span class="mihp-old">
                                      <span class="mihpo-strikethrough"><?=$products[8]['price_old']; ?></span><span class="mihp-currency">грн</span>
                                    </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="mosaic-square">
                    <?php if(!empty($categories[9])): ?>
                        <a href="<?=base_url($categories[9]['url']); ?>" title="<?=htmlspecialchars($categories[9]['title']); ?>">
                            <img src="<?=HTTP_HOST; ?>assets/newdesign/images/m10.jpg" alt="Mosaic 10">
                            <div class="mosaic-category"><?=$categories[9]['title']; ?></div>
                        </a>
                    <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="mosaic-square">
                        <?php if(!empty($products[10])): ?>
                            <a href="<?=base_url($products[10]['url']); ?>">
                                <img src="<?=HTTP_HOST . $images_path . $products[10]['file_name']; ?>" alt="<?=htmlspecialchars($products[10]['title']); ?>">
                                <div class="mosaic-info-hor">
                                    <div class="mih-title"><?=$products[10]['title']; ?></div>
                                    <div class="mih-price">
                                        <span class="mihp-current"><?=$products[10]['price']; ?><span class="mihp-currency">грн</span></span>
                                        <?php if(!empty($products[10]['price_old'])): ?>
                                            <span class="mihp-old">
                                      <span class="mihpo-strikethrough"><?=$products[10]['price_old']; ?></span><span class="mihp-currency">грн</span>
                                    </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
<?php endif; ?>