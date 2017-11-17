<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/
$route['admin'] = 'core/admin';
$route['newdesign/(:any)'] = 'core';
$route['newdesign/(:any)/(:any)'] = 'core';
$route['newdesign/(:any)/(:any)/(:any)'] = 'core';
$route['newdesign/(:any)/(:any)/(:any)/(:any)'] = 'core';
//$route['user_register'] = 'core/user_register';
//$nd_route_prefix = 'newdesign/'; // newdesign/ - это префикс нового дизайна для правил, сделать пустым при полном переходе на новый дизайн
$nd_route_prefix = ''; // newdesign/ - это префикс нового дизайна для правил, сделать пустым при полном переходе на новый дизайн
//$route['newdesign'] = "newdesign/router/index"; // установить это правило как default_controller при полном переходе на новый дизайн
$route['default_controller'] = "newdesign/router/index"; // установить это правило как default_controller при полном переходе на новый дизайн
$route[$nd_route_prefix . 'logout'] = "newdesign/pages/logout"; // pages controller
$route[$nd_route_prefix . 'login.html'] = "newdesign/pages/login"; // pages controller
$route[$nd_route_prefix . 'registration'] = "newdesign/pages/registration"; // pages controller
$route[$nd_route_prefix . 'contacts'] = "newdesign/pages/contacts"; // pages controller
$route[$nd_route_prefix . 'search'] = "newdesign/pages/search"; // pages controller
$route[$nd_route_prefix . 'pages/contact_form'] = "newdesign/pages/contact_form"; // pages controller
$route[$nd_route_prefix . 'brands-womens'] = "newdesign/designers/by_category";
$route[$nd_route_prefix . 'brands-womens/(:num)/(:any)'] = "newdesign/designers/by_category_brand/$1/$2";
$route[$nd_route_prefix . 'brands-womens/(:num)/(:any)/(:num)'] = "newdesign/designers/by_category_brand/$1/$2/$3";
$route[$nd_route_prefix . 'brands-mens'] = "newdesign/designers/by_category";
$route[$nd_route_prefix . 'brands-mens/(:num)/(:any)'] = "newdesign/designers/by_category_brand/$1/$2";
$route[$nd_route_prefix . 'brands-mens/(:num)/(:any)/(:num)'] = "newdesign/designers/by_category_brand/$1/$2/$3";
$route[$nd_route_prefix . 'brands-childrens'] = "newdesign/designers/by_category";
$route[$nd_route_prefix . 'brands-childrens/(:num)/(:any)'] = "newdesign/designers/by_category_brand/$1/$2";
$route[$nd_route_prefix . 'brands-childrens/(:num)/(:any)/(:num)'] = "newdesign/designers/by_category_brand/$1/$2/$3";
$route[$nd_route_prefix . 'brands'] = "newdesign/designers/index";
$route[$nd_route_prefix . 'brands/(:num)'] = "newdesign/designers/index";
$route[$nd_route_prefix . 'brand/(:num)/(:any)'] = "newdesign/designers/view/$1/$2";
$route[$nd_route_prefix . 'brand/(:num)/(:any)/(:num)'] = "newdesign/designers/view/$1/$2/$3";
$route[$nd_route_prefix . 'womens-sale'] = "newdesign/shop/by_category";
$route[$nd_route_prefix . 'womens-new'] = "newdesign/shop/by_new";
$route[$nd_route_prefix . 'mens-sale'] = "newdesign/shop/by_category";
$route[$nd_route_prefix . 'mens-new'] = "newdesign/shop/by_new";
$route[$nd_route_prefix . 'childrens-sale'] = "newdesign/shop/by_category";
$route[$nd_route_prefix . 'childrens-new'] = "newdesign/shop/by_new";
$route[$nd_route_prefix . 'order'] = "newdesign/order/index"; // order controller
$route[$nd_route_prefix . 'order/add_to_cart'] = "newdesign/order/add_to_cart"; // order controller
$route[$nd_route_prefix . 'order/cart'] = "newdesign/order/cart"; // order controller
$route[$nd_route_prefix . 'order/thanks/(:num)'] = "newdesign/order/thanks/$1"; // order controller
$route[$nd_route_prefix . 'order/checkout'] = "newdesign/order/checkout"; // order controller
$route[$nd_route_prefix . 'order/checkout_action'] = "newdesign/order/checkout_action"; // order controller
$route[$nd_route_prefix . 'order/get_cart'] = "newdesign/order/get_cart"; // order controller
$route[$nd_route_prefix . 'order/del_from_cart'] = "newdesign/order/del_from_cart"; // order controller
$route[$nd_route_prefix . 'order/cart_qty'] = "newdesign/order/cart_qty"; // order controller
$route[$nd_route_prefix . 'ajax/(:any)'] = "ajax/$1"; // ajax controller
$route[$nd_route_prefix . 'ajax/(:any)/(:any)'] = "ajax/$1/$2"; // ajax controller
$route[$nd_route_prefix . 'ajax/(:any)/(:any)/(:any)'] = "ajax/$1/$2/$3"; // ajax controller

// cropper
$route['cropper/(:any)'] = 'cropper/$1';
$route['cropper/(:any)/(:any)'] = 'cropper/$1/$2';
$route['cropper/(:any)/(:any)/(:any)'] = 'cropper/$1/$2/$3';
$route['cropper/(:any)/(:any)/(:any)/(:any)'] = 'cropper/$1/$2/$3/$4';
$route['cropper/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'cropper/$1/$2/$3/$4/$5';
$route['cropper/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'cropper/$1/$2/$3/$4/$5/$6';
$route['cropper/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'cropper/$1/$2/$3/$4/$5/$6/$7';

// в наличии
$route[$nd_route_prefix . 'in-stock'] = "newdesign/shop/by_one_category";
$route[$nd_route_prefix . 'in-stock-womens'] = "newdesign/shop/by_category";
$route[$nd_route_prefix . 'in-stock-mens'] = "newdesign/shop/by_category";
$route[$nd_route_prefix . 'in-stock-childrens'] = "newdesign/shop/by_category";

// default routing
$route[$nd_route_prefix . '(:any)'] = "newdesign/router/index"; // router controller
$route[$nd_route_prefix . '(:any)/(:any)'] = "newdesign/router/index"; // router controller
$route[$nd_route_prefix . '(:any)/(:any)/(:any)'] = "newdesign/router/index"; // router controller

//$route['default_controller'] = "core";
$route['404_override'] = 'core';

/* End of file routes.php */
/* Location: ./application/config/routes.php */

include("./config.php");