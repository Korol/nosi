<?php
/**
 * @var $categories
 * @var $selected_cat
 * @var $action
 * @var $pagination
 * @var $get
 * @var $total_products
 * @var $data_type
 * @var $action_products
 * @var $flash
 */

?>
<div class="container-fluid action-container">
<?php $this->ci->load->view('action/css'); ?>
<?php $this->ci->load->view('action/form',
    array(
        'action' => $action
    )
); ?>
<?php $this->ci->load->view('action/table',
    array(
        'categories' => $categories,
        'selected_cat' => $selected_cat,
        'products' => $products,
        'pagination' => $pagination,
        'get' => $get,
        'total_products' => $total_products,
        'data_type' => $data_type,
        'action_products' => $action_products,
        'flash' => $flash,
    )
); ?>
</div>
