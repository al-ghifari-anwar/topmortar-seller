<?php
defined('BASEPATH') or exit('No direct script access allowed');

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
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Auth
$route['api/auth/register'] = "Auth/register";
$route['api/request-otp'] = "Auth/send_otp";
$route['api/verify-otp'] = "Auth/verify_otp";
$route['api/auth/reset'] = "Auth/reset_password";
$route['api/auth/login'] = "Auth/login";
//Bank
$route['api/bank'] = 'Bank/get';
// Rekening Toko
$route['api/rekeningtoko'] = 'RekeningToko/create';
$route['api/rekeningtoko/contact/(:num)'] = 'RekeningToko/getByIdContact/$1';
$route['api/rekeningtoko/(:num)'] = 'RekeningToko/update/$1';
// Claim
$route['api/voucher/claim'] = 'VoucherTukang/claim';
// Get Claimed
$route['api/voucher/claimed/(:num)'] = 'VoucherTukang/getByIdContact/$1';
// Konten
$route['api/konten'] = 'Konten/get';
// Contact
$route['api/contact/delete'] = 'Contact/delete';
// Produk
$route['api/produk'] = 'Produk/get';
// Cart
$route['api/cart'] = 'Cart/get';
// Cart Detail
$route['api/cart/insert'] = 'CartDetail/create';
$route['api/cart/delete'] = 'CartDetail/delete';
$route['api/cart/checkout'] = 'Cart/checkout';
// Apporder (Pesanan)
$route['api/apporder'] = 'Apporder/index';
// Invoice
$route['api/invoice'] = 'Invoice/index';
