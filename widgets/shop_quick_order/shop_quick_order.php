<?php
class shop_quick_orderWidget extends Cms_modules {
	function __construct()
	{
		parent::__construct();
	}

	function view_widget(&$r)
	{
		$html;

		if($this->input->post("shop_quick_sm")!==false){
			$phone=$this->input->post("phone");
			$product_id=intval($this->input->post("product_id"));

			$product_res=$this->ci->module
			->products_query()
			->where("shop_products.id",$product_id)
			->get()
			->row();

			$product_res->quantity=1;
			$basket->products=array(
				$product_res->id=>$product_res
			);
			$basket->total_amount=$product_res->price;
			$basket->total_amount_hmn=$this->ci->module->price($product_res->price);

			$this->db->insert("shop_orders",array(
				"user_id"=>$this->ci->session->userdata("id"),
				"type"=>"quick",
				"phone"=>$phone,
				"basket"=>json_encode($basket),
				"total_amount"=>$product_res->price,
				"total_amount_with_discount"=>$this->ci->module->price($product_res->price),
				"date_add"=>mktime()
			));

			$order_id=$this->db->insert_id();

			$this->ci->module->change_order_status($order_id,"submited");

			// получаем список всех администраторов
			$users_res=$this->db
			->select("users.id, users.username, users.email, users.first_name, users.last_name")
			->join("users_groups","users_groups.user_id = users.id && users_groups.group_id = 1")
			->group_by("users.email")
			->get_where("users",array(
				"active"=>1
			))
			->result();

			$email_html="";

			$product_res->price_hmn=$this->ci->module->price($product_res->price);

			$date_add=date("d.m.Y H:i:s");

			$email_html.=<<<EOF
<p><strong>Заказ №:</strong> {$order_id}</p>
<p><strong>Телефон:</strong> {$phone}</p>
<p>&nbsp;</p>
<p><strong>ID:</strong> {$product_res->id}</p>
<p><strong>Артикул:</strong> {$product_res->code}</p>
<p><strong>Наименование товара:</strong> {$product_res->title}</p>
<p><strong>Цена:</strong> {$product_res->price_hmn}</p>
<p>&nbsp;</p>
<p><strong>Дата:</strong> {$date_add}</p>
<p><strong>IP:</strong> {$_SERVER['REMOTE_ADDR']}</p>
EOF;

			foreach($users_res AS $r)
			{
				$this->ci->email->from($this->ci->config->config['email_from'],$this->ci->config->config['email_from_name']);
				$this->ci->email->to($r->email,trim($r->first_name." ".$r->last_name));
				$this->ci->email->subject("Быстрый заказ");
				$this->ci->email->message($email_html);	
				$this->ci->email->send();
			}
			exit;
		}

		include_once("./modules/shop/shop.helper.php");
		$shopModuleHelper=new shopModuleHelper;

		$product_id=intval($this->input->get("product_id"));

		$html.=<<<EOF
<br /><br />
<button class="current quantity" onclick="openShopQuickOrder(); return false;">Купить в один клик</button>
<script>
$(document).ready(function(){
	var sqo_html='';

	sqo_html+='<div class="modal hide fade" id="modalShopQuickOrder">';
		sqo_html+='<div class="modal-header">';
			sqo_html+='<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
			sqo_html+='<h3>Купить в один клик</h3>';
		sqo_html+='</div>';
		sqo_html+='<div class="modal-body">';
			sqo_html+='<p>';
				sqo_html+='<strong>Ваш номер телефона:</strong>';
				sqo_html+='<br />';
				sqo_html+='<input type="text" name="phone" id="phone" value="" />';
			sqo_html+='</p>';
		sqo_html+='</div>';
		sqo_html+='<div class="modal-footer">';
			sqo_html+='<a href="#" class="btn" onclick="closeShopQuickOrder(); return false;">Отмена</a>';
			sqo_html+='<a href="#" class="btn btn-primary" onclick="sendShopQuickOrder(); return false;">Отправить</a>';
		sqo_html+='</div>';
	sqo_html+='</div>';
	
	$("body").append(sqo_html);
});

var product_id='{$product_id}';
function closeShopQuickOrder()
{
	$("#modalShopQuickOrder button.close").click();
}

function openShopQuickOrder()
{
	$("#modalShopQuickOrder").modal();
}

function sendShopQuickOrder()
{
	var phone=$("#modalShopQuickOrder #phone").val();

	$.post(document.location.href,{
		product_id:product_id,
		phone:phone,
		shop_quick_sm:1
	},function(d){
		closeShopQuickOrder();
		alert('Ваш запрос успешно отправлен!');
	});
}
</script>
EOF;

		return $html;
	}
}
?>