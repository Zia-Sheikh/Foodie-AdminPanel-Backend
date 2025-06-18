<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// App Setting
Route::get('app_setting', 'App\Http\Controllers\AppSettingController@index');
Route::get('restaurant/app_setting', 'App\Http\Controllers\AppSettingController@restaurant_app_setting');
Route::get('deliveryboy/app_setting', 'App\Http\Controllers\AppSettingController@deliveryboy_app_setting');
Route::post('customer/get_referral_message', 'App\Http\Controllers\ReferralController@get_referral_message');

//faq
Route::post('customer/faq', 'App\Http\Controllers\FaqController@customer_faq');
Route::post('restaurant/faq', 'App\Http\Controllers\FaqController@restaurant_faq');
Route::post('delivery_boy/faq', 'App\Http\Controllers\FaqController@delivery_boy_faq');
Route::post('get_faq_category', 'App\Http\Controllers\FaqController@get_faq_category');
// Privacy Policy
Route::post('customer/privacy_policy', 'App\Http\Controllers\PrivacyPolicyController@customer_privacy_policy');
Route::post('restaurant/privacy_policy', 'App\Http\Controllers\PrivacyPolicyController@restaurant_privacy_policy');
Route::post('delivery_boy/privacy_policy', 'App\Http\Controllers\PrivacyPolicyController@delivery_boy_privacy_policy');
// Payment Mode
Route::post('customer/get_payment_mode', 'App\Http\Controllers\PaymentModeController@get_payment_mode');
// Notification
Route::post('customer/notification', 'App\Http\Controllers\NotificationController@customer_notification');
Route::post('restaurant/notification', 'App\Http\Controllers\NotificationController@restaurant_notification');
Route::post('delivery_partner/notification', 'App\Http\Controllers\NotificationController@delivery_partner_notification');
//Customer
Route::post('customer/register', 'App\Http\Controllers\CustomerController@register');
Route::post('customer/profile_update', 'App\Http\Controllers\CustomerController@profile_update');
Route::post('customer/check_phone', 'App\Http\Controllers\CustomerController@check_phone');
Route::post('customer/login', 'App\Http\Controllers\CustomerController@login');
Route::post('customer/get_profile', 'App\Http\Controllers\CustomerController@get_profile');
Route::post('customer/forget_password', 'App\Http\Controllers\CustomerController@forget_password');
Route::post('customer/reset_password', 'App\Http\Controllers\CustomerController@reset_password');
Route::post('customer/profile_picture', 'App\Http\Controllers\CustomerController@profile_picture');
Route::post('customer/profile_picture_update', 'App\Http\Controllers\CustomerController@profile_picture_update');
Route::post('customer/add_address', 'App\Http\Controllers\AddressController@add_address');
Route::post('customer/update_address', 'App\Http\Controllers\AddressController@update');
Route::post('customer/all_addresses', 'App\Http\Controllers\AddressController@all_addresses');
Route::post('customer/edit_address', 'App\Http\Controllers\AddressController@edit');
Route::post('customer/delete_address', 'App\Http\Controllers\AddressController@delete');
Route::post('customer/stripe_payment', 'App\Http\Controllers\CustomerController@stripe_payment');
Route::post('customer/get_address_type', 'App\Http\Controllers\CustomerController@get_address_type');
Route::get('customer/get_banners', 'App\Http\Controllers\CustomerController@get_banners');
Route::post('customer/update_favourite_restaurant', 'App\Http\Controllers\CustomerController@update_favourite_restaurant');
Route::post('customer/get_favourite_restaurant', 'App\Http\Controllers\CustomerController@get_favourite_restaurant');
Route::post('customer/get_categories', 'App\Http\Controllers\CustomerController@get_categories');
Route::post('customer/restaurant_list', 'App\Http\Controllers\CustomerController@restaurant_list');
Route::post('customer/delete_favourite_restaurant', 'App\Http\Controllers\CustomerController@delete_favourite_restaurant');
Route::post('customer/add_favourite_restaurant', 'App\Http\Controllers\CustomerController@add_favourite_restaurant');
Route::post('customer/get_promo', 'App\Http\Controllers\CustomerController@get_promo');
Route::post('customer/check_promo', 'App\Http\Controllers\CustomerController@check_promo');
Route::post('customer/get_restaurant_menu', 'App\Http\Controllers\CustomerController@get_restaurant_menu');
Route::post('customer/get_orders', 'App\Http\Controllers\CustomerController@get_orders');
Route::post('customer/get_order_detail', 'App\Http\Controllers\CustomerController@get_order_detail');
Route::get('customer/get_taxes', 'App\Http\Controllers\CustomerController@get_taxes');
Route::post('customer/get_ongoing_orders', 'App\Http\Controllers\CustomerController@get_ongoing_orders');
Route::post('customer/home_search', 'App\Http\Controllers\CustomerController@home_search');
Route::post('customer/product_search', 'App\Http\Controllers\CustomerController@product_search');
Route::post('customer/get_last_active_address', 'App\Http\Controllers\CustomerController@get_last_active_address');
Route::post('customer/restaurant_list_by_category', 'App\Http\Controllers\CustomerController@restaurant_list_by_category');
Route::post('customer/get_latest_order', 'App\Http\Controllers\CustomerController@get_latest_order');
Route::post('customer/get_wallet', 'App\Http\Controllers\CustomerController@get_wallet');
Route::post('customer/add_wallet', 'App\Http\Controllers\CustomerController@add_wallet');
Route::post('customer/get_disclaimer_messages', 'App\Http\Controllers\CustomerController@get_disclaimer_messages');

// Delivery Boy
Route::post('delivery_boy/login', 'App\Http\Controllers\DeliveryBoyController@login');
Route::post('delivery_boy/profile_update', 'App\Http\Controllers\DeliveryBoyController@profile_update');
Route::post('delivery_boy/get_profile', 'App\Http\Controllers\DeliveryBoyController@get_profile');
Route::post('delivery_boy/profile_picture', 'App\Http\Controllers\DeliveryBoyController@profile_picture');
Route::post('delivery_boy/profile_picture_update', 'App\Http\Controllers\DeliveryBoyController@profile_picture_update');
Route::post('delivery_boy/forget_password', 'App\Http\Controllers\DeliveryBoyController@forget_password');
Route::post('delivery_boy/reset_password', 'App\Http\Controllers\DeliveryBoyController@reset_password');
Route::post('delivery_boy/check_phone', 'App\Http\Controllers\DeliveryBoyController@check_phone');
Route::post('delivery_boy/change_online_status', 'App\Http\Controllers\DeliveryBoyController@change_online_status');
Route::post('delivery_boy/wallet_histories', 'App\Http\Controllers\DeliveryBoyController@delivery_boy_wallet_histories');
Route::post('delivery_boy/earning', 'App\Http\Controllers\DeliveryBoyController@delivery_boy_earning');
Route::post('delivery_boy/dashboard', 'App\Http\Controllers\DeliveryBoyController@dashborad');
Route::post('delivery_boy/get_pending_orders', 'App\Http\Controllers\DeliveryBoyController@get_pending_orders');
Route::post('delivery_boy/get_orders', 'App\Http\Controllers\DeliveryBoyController@get_orders');
Route::post('delivery_boy/get_deliveryboy_order_detail', 'App\Http\Controllers\DeliveryBoyController@get_deliveryboy_order_detail');
Route::post('delivery_boy/withdrawal_request', 'App\Http\Controllers\DeliveryBoyController@withdrawal_request');
Route::post('deliveryboy/withdrawal_history', 'App\Http\Controllers\DeliveryBoyController@withdrawal_history');
Route::post('deliveryboy/month_wise_earning', 'App\Http\Controllers\DeliveryBoyController@delivery_boy_earning_month_wise');


//Restaurant
Route::post('restaurant/login', 'App\Http\Controllers\RestaurantController@login');
Route::post('restaurant/profile_update', 'App\Http\Controllers\RestaurantController@profile_update');
Route::post('restaurant/get_profile', 'App\Http\Controllers\RestaurantController@get_profile');
Route::post('restaurant/restaurant_image', 'App\Http\Controllers\RestaurantController@restaurant_image');
Route::post('restaurant/restaurant_image_update', 'App\Http\Controllers\RestaurantController@restaurant_image_update');
Route::post('restaurant/forget_password', 'App\Http\Controllers\RestaurantController@forget_password');
Route::post('restaurant/reset_password', 'App\Http\Controllers\RestaurantController@reset_password');
Route::post('restaurant/check_phone', 'App\Http\Controllers\RestaurantController@check_phone');
Route::post('restaurant/register', 'App\Http\Controllers\RestaurantController@register');
Route::post('restaurant/certificate_upload', 'App\Http\Controllers\RestaurantController@certificate_upload');
Route::post('restaurant/earning', 'App\Http\Controllers\RestaurantController@restaurant_earning');
Route::post('restaurant/wallet_histories', 'App\Http\Controllers\RestaurantController@restaurant_wallet_histories');
Route::post('restaurant/change_online_status', 'App\Http\Controllers\RestaurantController@change_online_status');
Route::post('restaurant/withdrawal_request', 'App\Http\Controllers\RestaurantController@restaurant_withdrawal_request');
Route::post('restaurant/withdrawal_history', 'App\Http\Controllers\RestaurantController@restaurant_withdrawal_history');
Route::post('restaurant/get_orders', 'App\Http\Controllers\RestaurantController@get_orders');
Route::post('restaurant/get_pending_orders', 'App\Http\Controllers\RestaurantController@get_pending_orders');
Route::post('restaurant/dashboard', 'App\Http\Controllers\RestaurantController@dashborad');
Route::post('restaurant/stock_update', 'App\Http\Controllers\RestaurantController@stock_update');
Route::post('restaurant/delivery_order_status_change', 'App\Http\Controllers\RestaurantController@delivery_order_status_change');
Route::post('restaurant/get_order_request', 'App\Http\Controllers\RestaurantController@get_order_request');
Route::post('restaurant/get_restaurant_order_detail', 'App\Http\Controllers\RestaurantController@get_restaurant_order_detail');
Route::post('restaurant/get_menu', 'App\Http\Controllers\RestaurantController@get_menu');
Route::post('restaurant/get_complaints', 'App\Http\Controllers\RestaurantController@get_complaints');

//Order
Route::post('customer/place_order', 'App\Http\Controllers\OrderController@store');
Route::post('restaurant/order_accept', 'App\Http\Controllers\OrderController@restaurant_order_accept');
Route::post('restaurant/delivery_order_status_change', 'App\Http\Controllers\OrderController@delivery_order_status_change');
Route::post('customer/rating_upload', 'App\Http\Controllers\OrderController@rating_upload');
Route::post('customer/cancel_order', 'App\Http\Controllers\OrderController@customer_cancel_order');
Route::post('order_status_change', 'App\Http\Controllers\OrderController@order_status_change');
Route::post('deliveryboy/accept', 'App\Http\Controllers\OrderController@partner_accept');
Route::post('deliveryboy/reject', 'App\Http\Controllers\OrderController@partner_reject');
Route::get('find_partner/{id}', 'App\Http\Controllers\OrderController@find_partner');
Route::get('partner_cron', 'App\Http\Controllers\OrderController@partner_cron');
Route::post('calculate_tax', 'App\Http\Controllers\OrderController@calculate_tax');

Route::get('download_qr/{id}','App\Http\Controllers\RestaurantController@download_qr');

























