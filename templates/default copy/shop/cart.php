<?php
if(!isset($cart->products) || sizeof((array)$cart->products)<1){
	?><div class="content1Struct1">
			<div class="cartW">
				<div class="cartI">
					<div class="cart">
						<h1>Ваша корзина</h1>
						<center><strong>Ваша корзина пуста.</strong></center>
					</div>
				</div>
			</div>
		</div>
	</div><?php
}else{
	if($this->input->post("order_step2")===false || sizeof($order_errors)>0){
	?>
	<script src="/modules/shop/media/js/shop.main.js"></script>
		<div class="content1Struct1">
			<div class="cartW">
				<div class="cartI">
					<div class="cart">
						<h1>Ваша корзина</h1>

						<form method="post" id="products_form">
						<table class="products" cellspacing="1" cellpadding="0" border="0" align="center">
						<tr>
							<th>Товар</th>
							<th>Количество</th>
							<th>Цена</th>
							<th>&nbsp;</th>
						</tr>
						<?php
						foreach($cart->products AS $r)
						{
							if($r->show!=1){
								$r->quantity=0;
							}
							?><tr<?php if($r->show!=1){ ?> style="background-color:#ffd9d9;"<?php } ?>>
							<td>
								<a href="<?php print $r->link; ?>">
								<?php
								if(empty($r->main_picture_file_name)){
									?><img src="/assets/media/nophoto.png" /><?php
								}else{
									?><img src="/uploads/shop/products/thumbs/<?php print $r->main_picture_file_name; ?>" /><?php
								}
								?>
								</a>
								<a href="<?php print $r->link; ?>"><?php print $r->title; ?></a>
							</td>
							<td align="center">
								<input type="text" name="quantity[<?php print $r->id; ?>]" value="<?php print $r->quantity; ?>" class="quantity"<?php if($r->show!=1){ ?> disabled="disabled"<?php } ?> />
								<?php if($r->show!=1){ ?>
								<input type="hidden" name="quantity[<?php print $r->id; ?>]" value="<?php print $r->quantity; ?>" />
								<?php } ?>
								<?php if($r->show!=1){ ?>
								<center><small style="color:red;">этого товара нет в наличии!</small></center>
								<?php } ?>
							</td>
							<td><?php print $r->price_hmn; ?></td>
							<td><a href="#" onclick="$(this).parents('tr:eq(0)').remove(); $('#products_form').append('<input type=\'hidden\' name=\'recalc_sm\' value=\'1\'>').submit(); return false;" /><img src="/assets/media/cross.png" alt="Удалить" border="0" /></a></td>
						</tr><?php
						}
						?>
						<tr>
							<td colspan="4">
								<div class="total_amout" data-products-total="<?php print $discount['price']; ?>" data-delivery="<?php print $discount['delivery']; ?>" data-discount="<?php print $discount['difference']; ?>">
									<button name="recalc_sm" value="1" class="recalc">Пересчитать</button>
									<?php
									if($discount['difference']>0){
									?>
									<strong>Общая стоимость со скидкой: </strong> <span class="products-total"><?php print $discount['price_hmn']; ?></span>
									<br /><br />
									<?php
									if($delivery_method!==0){
									?>
									<div class="deliveryTotalW">
										<strong>Доставка: </strong> <span class="delivery"><?php print $discount['delivery']==0?'бесплатно (по киеву)':$discount['delivery_hmn']; ?></span>
										<br /><br />
									</div>
									<?php
									}
									?>
									<strong>Итого: </strong> <span class="total"><?php print $discount['price_total_hmn']; ?></span>
									<?php
									}else{
										if($delivery_method!==0){
										?>
										<strong>Общая стоимость: </strong> <span class="products-total"><?php print $discount['price_hmn']; ?></span>
										<br />
										<div class="deliveryTotalW">
											<strong>Доставка: </strong> <span class="delivery"><?php print $discount['delivery']==0?'бесплатно (по киеву)':$discount['delivery_hmn']; ?></span>
											<br /><br />
										</div>
										<?php
										}
										?>
										<strong>Итого: </strong> <span class="total"><?php print $discount['price_total_hmn']; ?></span>
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
								if(sizeof($order_errors)>0){
									?><div align="center" style="color:red; text-align:center; font-weight:bolder;"><?php print implode("<br />",$order_errors)?></div>
									<br /><?php
								}
								?>
								<form method="post">
								<?php
								if(intval($this->session->userdata("user_id"))<1){
								?>
								<script>
								$(document).ready(function(){
									$("input[name='login_type']").change(function(){
										loginTypeChange();
									});
									loginTypeChange();
								});

								function loginTypeChange()
								{
									if($("input[name='login_type']:checked").val()=="register"){
										$(".centerFormRowRegister").show();
										$(".centerFormRowLogin").hide();
									}else{
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
									<label><span class="necessarily">*</span>Доставка:</label>
									<script>
									function delivery_method_change(value)
									{
										var products_total=$(".products span.products-total");
										var discount=$(".products span.discount");
										var delivery=$(".products span.delivery");
										var total=$(".products span.total");

										var total_amout=$(".total_amout");

										$(".centerFormRowDeliveryAddress").hide();
										$(".centerFormRowDeliveryCity").hide();
										$(".centerFormRowDeliveryStorage").hide();
										$(".centerFormRowDeliveryName").hide();
										if($(".ourDeliveryAddressW").is(":visible")){
											$(".ourDeliveryAddressW").slideUp("fast");
										}
										$(".deliveryTotalW").hide();

										var final_total=total_amout.data("products-total");
										// final_total-=total_amout.data("discount");

										total.text(numberformat(final_total)+" грн.");
										if(value==1){
											// киев
											$(".centerFormRowDeliveryAddress").show();
											$(".deliveryTotalW").show();

											if(final_total<=500){
												total.text(numberformat(final_total+total_amout.data("delivery"))+" грн.");
											}
										}else if(value==2){
											// украина
											$(".centerFormRowDeliveryCity").show();
											$(".centerFormRowDeliveryStorage").show();
											$(".centerFormRowDeliveryName").show();
											// $(".deliveryTotalW").show();

											// if(final_total<=500){
											// 	total.text(numberformat(final_total+total_amout.data("delivery"))+" грн.");
											// }
										}else{
											// самовывоз
											$(".ourDeliveryAddressW").slideDown("fast");
										}
									}

									function numberformat(n)
									{
										nms=n.toString().split(".");
										if(nms.length==2){
											var nls="00";
											return nms[0]+"."+nms[1].substr(0,2)+nls.toString().substr(0,2-nms[1].length);
										}else{
											return n+".00";
										}
									}

									$(document).ready(function(){
										delivery_method_change($("#delivery_method").val());
									})
									</script>
									<select name="delivery_method" id="delivery_method" onchange="delivery_method_change(this.value);">
										<?php
										foreach($delivery_methos AS $value=>$name)
										{
											$s=$delivery_method==$value?' selected="selected"':'';
											?><option<?php print $s; ?> value="<?php print $value; ?>"><?php print $name; ?></option><?php
										}
										?>
									</select>
									<div class="ourDeliveryAddressW"><strong>Адрес для самовывоза:  </strong>Адрес: город Киев, бульв. Ромена Роллана 7 оф.232</div>
								</div>
								<div class="centerFormRow">
									<label><span class="necessarily">*</span>E-mail:</label>
									<input type="text" name="email" value="<?php print $email; ?>" class="text" />
								</div>
								
								<div class="centerFormRow centerFormRowDeliveryAddress">
									<label><span class="necessarily">*</span>Адрес доставки:</label>
									<input type="text" name="delivery_address" value="<?php print $delivery_address; ?>" class="text" />
								</div>

								<div class="centerFormRow centerFormRowDeliveryCity">
									<label><span class="necessarily">*</span>Город доставки:</label>
									<input type="text" name="delivery_city" value="<?php print $delivery_city; ?>" class="text" />
								</div>
								<div class="centerFormRow centerFormRowDeliveryStorage">
									<label><span class="necessarily">*</span>Склад доставки:</label>
									<input type="text" name="delivery_storage" value="<?php print $delivery_storage; ?>" class="text" />
								</div>
								<div class="centerFormRow centerFormRowDeliveryName">
									<label><span class="necessarily">*</span>Фамилия получателя посылки:</label>
									<input type="text" name="delivery_name" value="<?php print $delivery_name; ?>" class="text" />
								</div>

								<div class="centerFormRow">
									<label><span class="necessarily">*</span>ФИО:</label>
									<input type="text" name="name" value="<?php print $name; ?>" class="text" />
								</div>

								<div class="centerFormRow">
									<label><span class="necessarily">*</span>Телефон:</label>
									<input type="text" name="phone" value="<?php print $phone; ?>" class="text" />
								</div>
								<div class="centerFormRow">
									<label>Комментарии к заказу:</label>
									<textarea name="notes"><?php print $notes; ?></textarea>
								</div>
								<div class="centerFormRow centerFormRowButtons">
									<button name="order_step2" value="1" type="submit">Далее</button>
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
	}else{
	?>
	<script src="/modules/shop/media/js/shop.main.js"></script>
		<div class="content1Struct1">
			<div class="cartW">
				<div class="cartI">
					<div class="cart">
						<h1>Ваша корзина</h1>

						<form method="post">
						<table class="products" cellspacing="1" cellpadding="0" border="0" align="center">
						<tr>
							<th>Товар</th>
							<th>Количество</th>
							<th>Цена</th>
						</tr>
						<?php
						foreach($cart->products AS $r)
						{
							if($r->show!=1){
								$r->quantity=0;
							}

							?><tr<?php if($r->show!=1){ ?> style="background-color:#ffd9d9;"<?php } ?>>
							<td>
								<a href="<?php print $r->link; ?>">
								<?php
								if(empty($r->main_picture_file_name)){
									?><img src="/assets/media/nophoto.png" /><?php
								}else{
									?><img src="/uploads/shop/products/thumbs/<?php print $r->main_picture_file_name; ?>" /><?php
								}
								?></a>
								<a href="<?php print $r->link; ?>"><?php print $r->title; ?></a>
							</td>
							<td align="center">
								<?php print $r->quantity; ?>
								<?php if($r->show!=1){ ?>
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
									if($discount['difference']>0){
									?>
									<strong>Общая стоимость со скидкой: </strong> <?php print $discount['price_hmn']; ?>
									<br /><br />
									<?php
									if($delivery_method!=0){
									?>
									<div class="deliveryTotalW">
										<strong>Доставка: </strong> <?php print $discount['delivery']==0?'бесплатно (по киеву)':$discount['delivery_hmn']; ?>
										<br /><br />
									</div>
									<?php
									}
									?>
									<strong>Итого: </strong> <?php print $discount['price_total_hmn']; ?>
									<?php
									}else{
										?>
										<?php
										if($delivery_method!=0){
										?>
										<strong>Общая стоимость: </strong> <?php print $discount['price_hmn']; ?>
										<br />
										<div class="deliveryTotalW">
											<strong>Доставка: </strong> <?php print $discount['delivery']==0?'бесплатно (по киеву)':$discount['delivery_hmn']; ?>
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
								if(sizeof($order_errors)>0){
									?><div align="center" style="color:red; text-align:center; font-weight:bolder;"><?php print implode("<br />",$order_errors)?></div>
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