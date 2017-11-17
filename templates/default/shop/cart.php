<?php
$cart_currency = 'usd';
$e_rates_res = $this->ci->db->select('var_name, value')->get('e_rates')->result_array();
$e_rates = array();
if(!empty($e_rates_res)){
    foreach ($e_rates_res as $e_rate){
        $e_rates[$e_rate['var_name']] = $e_rate['value'];
    }
}

if (!isset($cart->products) || sizeof((array) $cart->products) < 1) {
    ?><div class="content1Struct1">
        <div class="cartW">
            <div class="cartI">
                <div class="bascet">
                    <h1>Ваша корзина</h1>
                    <center><strong>Ваша корзина пуста.</strong></center>
                </div>
            </div>
        </div>
   
    </div><?php
} else {
    if ($this->input->post("order_step2") === false || sizeof($order_errors) > 0) {
        ?>
        <script src="/modules/shop/media/js/shop.main.js"></script>
        <div class="content1Struct1">
            <div class="cartW">
                <div class="cartI">
                    <div class="bascet">
                        <h1>Корзина</h1>

                        <form method="post" id="products_form">
                            <table class="products" cellspacing="1" cellpadding="0" border="0" align="center" width="100%">
                                <tr><td colspan="4" class="line"><br /></td></tr>
                                <tr><td colspan="4"><br /></td></tr>
                                <?php
                                $pric = 0;
                                foreach ($cart->products AS $idr => $r) {
                                    $r->price_total = ($r->currency == $cart_currency) ? $r->price_total : ceil($r->price_total * $e_rates[$r->currency . '_usd']);
                                    $pric = $pric + $r->price_total;

                                    if ($r->show != 1) {
                                        $r->quantity = 0;
                                    }
                                    ?><tr class="productRowP">
                                        <td class="imgP">
                                            <a href="<?php print $r->link; ?>">
                                                <?php
                                                if (empty($r->color)) {
                                                    if (empty($r->main_picture_file_name)) {
                                                        ?><img src="/assets/media/nophoto.png" /><?php
                                                    } else {
                                                        ?><img src="/uploads/shop/products/thumbs3/<?php print $r->main_picture_file_name; ?>" /><?php
                                                    }
                                                } else {
                                                    $img = explode(':', $idr);
                                                    $img = '/uploads/shop/products/thumbs3/' . $img[1];
                                                    ?><img src="<?php print $img; ?>" /><?
                                                    }
                                                    ?>
                                                </a>
                                            </td>
                                            <td class="titleP">
                                                <a href="<?php print $r->link; ?>"><?php print $r->title; ?></a>
                                                <?php
                                                if ($r->category_ids != "") {
                                                    $cats = explode(",", $r->category_ids);
                                                    foreach ($cats as $c) {
                                                        $res = $this->db->query("SELECT `parent_id`,`title` FROM `categoryes` WHERE `id`=" . $c . " ")->result();
                                                        if ($res[0]->parent_id > 0) {
                                                            print '<br /><span class="catP">' . $res[0]->title . '</span>';
                                                        }
                                                    }
                                                }
                                                if (!empty($r->size)) {
                                                    $size = explode(':', $idr);
                                                    $size = $size[2];
                                                    print "<br />Размер: " . $size;
                                                }

                                                $idr_quanity = str_replace('.', '-', $idr);
                                                ?>
                                            </td>
                                            <td align="center">
                                                <input type="text" name="quantity[<?php print $idr_quanity; ?>]" value="<?php print $r->quantity; ?>" class="quantity"<?php if ($r->show != 1) { ?> disabled="disabled"<?php } ?> />
                                                <?php if ($r->show != 1) { ?>
                                                    <input type="hidden" name="quantity[<?php print $r->id; ?>]" value="<?php print $r->quantity; ?>" />
                                                <?php } ?>
                                                <?php if ($r->show != 1) { ?>
                                            <center><small style="color:red;">этого товара нет в наличии!</small></center>
                                        <?php } ?>
                                        <a href="#" class="remove" onclick="$(this).parents('tr:eq(0)').remove(); $('#products_form').append('<input type=\'hidden\' name=\'recalc_sm\' value=\'1\'>').submit(); return false;" /><!-- --></a>
                                        <button name="recalc_sm" value="1" class="recalc"></button>
                                        <div class="clear"><!-- --></div>
                                        </td>
                                        <td class="priceP"><?php print $r->price_total; ?> $</td>
                                        </tr>
                                        <tr><td colspan="4" class="line"><br /></td></tr>
                                        <tr><td colspan="4"><br /></td></tr>
                                        <?php
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="total_amout" data-products-total="<?php print $discount['price']; ?>" data-delivery="<?php print $discount['delivery']; ?>" data-discount="<?php print $discount['difference']; ?>">

                                                <?php
                                                if ($discount['difference'] > 0) {
                                                    ?>
                                                    <strong>Общая стоимость со скидкой: </strong> <span class="products-total"><?php print $discount['price_hmn']; ?></span>
                                                    <br /><br />
                                                    <?php
                                                    if ($delivery_method !== 0) {
                                                        ?>
                                                        <div class="deliveryTotalW">
                                                            <strong>Доставка: </strong> <span class="delivery"><?php print $discount['delivery'] == 0 ? 'бесплатно (по киеву)' : $discount['delivery_hmn']; ?></span>
                                                            <br /><br />
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                    <strong>Итого: </strong> <span class="total"><?php print $discount['price_total_hmn']; ?></span>
                                                    <?php
                                                } else {
                                                    if ($delivery_method !== 0) {
                                                        ?>
                                                                    <!-- <strong>Общая стоимость: </strong> <span class="products-total"><?php print $discount['price_hmn']; ?></span> -->
                                                        <br />
                                                        <!-- <div class="deliveryTotalW">
                                                                <strong>Доставка: </strong> <span class="delivery"><?php print $discount['delivery'] == 0 ? 'бесплатно (по киеву)' : $discount['delivery_hmn']; ?></span>
                                                                <br /><br />
                                                        </div> -->
                                                        <?php
                                                    }
                                                    $discount['price_total_hmn'] = $pric . ' $.';
                                                    ?>
                                                    <p class="totalP">ОБЩАЯ СУММА <span class="total"><?php print $discount['price_total_hmn']; ?></span></p>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </form>

                            <div class="centerFormW">
                                <div class="centerForm">
                                    <?php
                                    if (sizeof($order_errors) > 0) {
                                        ?><div align="center" style="color:red; text-align:center; font-weight:bolder;"><?php print implode("<br />", $order_errors) ?></div>
                                        <br /><?php
                                    }
                                    ?>
									<form id="live_recalc" method="POST" action="cart.html">
									
									</form> 
									
                                    <form method="post" id="formSendOrder" action="order_process.html">
                                        <?php
                                        if (intval($this->session->userdata("user_id")) < 1) {
                                            ?>
                                            <script>
                                                $(document).ready(function () {
												$('.quantity').change(function () {
											var prod_name = $(this).attr("name");
												console.log(prod_name);
												var prod_quantity = $(this).val();
												$('#live_recalc').html('');
												$('#live_recalc').append('<input type=\'hidden\' name=\'recalc_sm\' value=\'1\'>');
												$('#live_recalc').append('<input type=\'hidden\' name=\''+prod_name+'\' value=\''+prod_quantity +'\'>');
											//	$('#live_recalc').submit();
												//return false;
												
												var msg   = $('#live_recalc').serialize();
        $.ajax({
          type: 'POST',
          url: 'cart.html',
          data: msg,
          success: function(data) {
         //  alert(data);
          }
        });
 
												
												
												
												
												
											//	$.post( "cart1.html", { eval("prod_name"): prod_name, quantity:prod_quantity,  recalc_sm: "1" })
  //.done(function( data ) {
 //   alert( "Data Loaded: " + data );
//  });
												
										//$('#products_form').append('<input type=\'hidden\' name=\'recalc_sm\' value=\'1\'>').submit();
                                      
                                                 });
                                                    $("input[name='login_type']").change(function () {
                                                        loginTypeChange();
                                                    });
                                                    loginTypeChange();
                                                });

                                                function loginTypeChange()
                                                {
                                                    if ($("input[name='login_type']:checked").val() == "register") {
                                                        $(".centerFormRowRegister").show();
                                                        $(".centerFormRowLogin").hide();
                                                    } else {
                                                        $(".centerFormRowRegister").hide();
                                                        $(".centerFormRowLogin").show();
                                                    }
                                                }
                                            </script>

                                            <?php
                                            /*
                                              <div class="centerFormRow centerFormRowInfo">
                                              Для оформления заказа вам необходимо войти или пройти быструю регистрацию на сайте.
                                              </div>
                                              <div class="centerFormRow centerFormRowCheckbox">
                                              <label><input type="radio" name="login_type" value="register"<?php print $login_type_checked['register']; ?> /> регистрация</label>
                                              <label><input type="radio" name="login_type" value="login"<?php print $login_type_checked['login']; ?> /> вход</label>
                                              <div class="clear"></div>
                                              </div>

                                              <div class="centerFormRow centerFormRowRegister">
                                              <label><span class="necessarily">*</span>ФИО:</label>
                                              <input type="text" name="register_name" value="" class="text" />
                                              </div>
                                              <div class="centerFormRow centerFormRowRegister">
                                              <label><span class="necessarily">*</span>Пароль:</label>
                                              <input type="password" name="register_password" value="" class="text" />
                                              </div>

                                              <div class="centerFormRow centerFormRowLogin">
                                              <label><span class="necessarily">*</span>E-mail:</label>
                                              <input type="text" name="login_name" value="" class="text" />
                                              </div>
                                              <div class="centerFormRow centerFormRowLogin">
                                              <label><span class="necessarily">*</span>Пароль:</label>
                                              <input type="password" name="login_password" value="" class="text" />
                                              </div>
                                              <div class="centerFormRow">
                                              <br />
                                              </div>
                                             */
                                        }
                                        ?>

                                        <div class="centerFormRow">
                                        </div>
                                        <div class="centerFormRow centerFormRowButtons">
                                            <a class="backP" href="<?php
                                            if (isset($_SERVER['HTTP_REFERER'])) {
                                                echo $_SERVER['HTTP_REFERER'];
                                            } else {
                                                echo"/";
                                            }
                                            ?>">Продолжить покупки</a>

                                       <!--   <button name="order_step2" value="1" type="submit" class="sendP">Оформить заказ</button> -->
                                       <button name="order_process" value="1" type="submit" class="sendP">Оформить заказ</button> 
                                        </div>
                                        <div class="clear"><!-- --></div>
                                        <div class="centerFormRow commP">
                                            <label>Комментарий к заказу</label>
                                            <textarea name="notes"><?php print $notes; ?></textarea>
                                        </div>
                                    </form>
                                   <!--     <form action="order_process.html" ><button name="order_process" value="1" type="submit" >Оформить заказ2</button>  </form>-->
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <script src="/modules/shop/media/js/shop.main.js"></script>
            <div class="content1Struct1">
                <div class="cartW">
                    <div class="cartI">
                        <div class="bascet">
                            <h1>Ваша корзина</h1>

                            <form method="post">
                                <table class="products" cellspacing="1" cellpadding="0" border="0" align="center">
                                    <tr>
                                        <th>Товар</th>
                                        <th>Количество</th>
                                        <th>Цена</th>
                                    </tr>
                                    <?php
                                    foreach ($cart->products AS $r) {
                                        if ($r->show != 1) {
                                            $r->quantity = 0;
                                        }
                                        ?><tr<?php if ($r->show != 1) { ?> style="background-color:#ffd9d9;"<?php } ?>>
                                            <td>
                                                <a href="<?php print $r->link; ?>">
                                                    <?php
                                                    if (empty($r->main_picture_file_name)) {
                                                        ?><img src="/assets/media/nophoto.png" /><?php
                                                    } else {
                                                        ?><img src="/uploads/shop/products/thumbs/<?php print $r->main_picture_file_name; ?>" /><?php
                                                    }
                                                    ?></a>
                                                <a href="<?php print $r->link; ?>"><?php print $r->title; ?></a>
                                            </td>
                                            <td align="center">
                                                <?php print $r->quantity; ?>
                                                <?php if ($r->show != 1) { ?>
                                            <center><small style="color:red;">этого товара нет в наличии!</small></center>
                                        <?php } ?>
                                        </td>
                                        <td><?php print $r->price_hmn; ?></td>
                                        </tr><?php
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="total_amout">
                                                <?php
                                                if ($discount['difference'] > 0) {
                                                    ?>
                                                    <strong>Общая стоимость со скидкой: </strong> <?php print $discount['price_hmn']; ?>
                                                    <br /><br />
                                                    <?php
                                                    if ($delivery_method != 0) {
                                                        ?>
                                                        <div class="deliveryTotalW">
                                                            <strong>Доставка: </strong> <?php print $discount['delivery'] == 0 ? 'бесплатно (по киеву)' : $discount['delivery_hmn']; ?>
                                                            <br /><br />
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                    <strong>Итого: </strong> <?php print $discount['price_total_hmn']; ?>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <?php
                                                    if ($delivery_method != 0) {
                                                        ?>
                                                        <strong>Общая стоимость: </strong> <?php print $discount['price_hmn']; ?>
                                                        <br />
                                                        <div class="deliveryTotalW">
                                                            <strong>Доставка: </strong> <?php print $discount['delivery'] == 0 ? 'бесплатно (по киеву)' : $discount['delivery_hmn']; ?>
                                                            <br /><br />
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                    <strong>Итого: </strong> <?php print $discount['price_total_hmn']; ?>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </form>

                            <div class="centerFormW">
                                <div class="centerForm">
                                    <?php
                                    if (sizeof($order_errors) > 0) {
                                        ?><div align="center" style="color:red; text-align:center; font-weight:bolder;"><?php print implode("<br />", $order_errors) ?></div>
                                        <br /><?php
                                    }
                                    ?>
                                    <form method="post">
                                        <div class="centerFormRow centerFormRowButtons">
                                            <button name="order_step3" value="1" type="submit">Все верно, оформить заказ</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            </div>
            <?php
        }
    }
    ?>