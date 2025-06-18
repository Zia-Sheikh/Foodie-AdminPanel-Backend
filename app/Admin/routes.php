<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('view_orders/{id}', 'ViewOrderController@index');
    $router->get('get_category', 'GeneralController@GetCategory');
    $router->resource('categories', CategoryController::class);
    $router->resource('sub-categories', SubCategoryController::class);
    $router->resource('items', ItemController::class);
    $router->resource('taxes', TaxController::class);
    $router->resource('restaurants', RestaurantController::class);
    $router->resource('restaurant-timings', RestaurantTimingController::class);
    $router->resource('days', DayController::class);
    $router->resource('tickets', TicketController::class);
    $router->resource('ticket-histories', TicketHistoryController::class);
    $router->resource('customer-complaints', CustomerComplaintController::class);
    $router->resource('faqs', FaqController::class);
    $router->resource('statuses', StatusController::class);
    $router->resource('banners', BannerController::class);
    $router->resource('food-cuisines', FoodCuisineController::class);
    $router->resource('customers', CustomerController::class);
    $router->resource('favourite-restaurants', FavouriteRestaurantController::class);
    $router->resource('favourite-items', FavouriteItemController::class);
    $router->resource('customer-addresses', CustomerAddressController::class);
    $router->resource('orders', OrderController::class);
    $router->resource('order-items', OrderItemController::class);
    $router->resource('notifications', NotificationController::class);
    $router->resource('customer-notifications', CustomerNotificationController::class);
    $router->resource('delivery-boy-notifications', DeliveryBoyNotificationController::class);
    $router->resource('restaurant-notifications', RestaurantNotificationController::class);
    $router->resource('payment-modes', PaymentModeController::class);
    $router->resource('payment-types', PaymentTypeController::class);
    $router->resource('order-statuses', OrderStatusController::class);
    $router->resource('app-settings', AppSettingController::class);
    $router->resource('customer-app-settings', CustomerAppSettingController::class);
    $router->resource('restaurant-settings', RestaurantSettingController::class);
    $router->resource('privacy-policies', PrivacyPolicyController::class);
    $router->resource('food-types', FoodTypeController::class);
    $router->resource('user-types', UserTypeController::class);
    $router->resource('promo-codes', PromoCodeController::class);
    $router->resource('promo-types', PromoTypeController::class);
    $router->resource('complaint-types', ComplaintTypeController::class);
    $router->resource('faq-categories', FaqCategoryController::class);
    $router->resource('delivery-boys', DeliveryBoyController::class);
    $router->resource('tags', TagController::class);
    $router->resource('address-types', AddressTypeController::class);
    $router->resource('restaurant-cuisines', RestaurantCuisineController::class);
    $router->resource('restaurant-earnings', RestaurantEarningController::class);
    $router->resource('restaurant-wallet-histories', RestaurantWalletHistoryController::class);
    $router->resource('restaurant-withdrawals', RestaurantWithdrawalController::class);
    $router->resource('delivery-boy-app-settings', DeliveryBoyAppSettingController::class);
    $router->resource('restaurant-app-settings', RestaurantAppSettingController::class);
    $router->resource('customer-promo-histories', CustomerPromoHistoryController::class);
    $router->resource('customer-wallet-histories', CustomerWalletHistoryController::class);
    $router->resource('order-ratings', OrderRatingController::class);
    $router->resource('cancellation-reasons', CancellationReasonController::class);
    $router->resource('restaurant-categories', RestaurantCategoryController::class);
    $router->resource('customer-complaints', CustomerComplaintController::class);
    $router->resource('delivery-boy-earnings', DeliveryBoyEarningController::class);
    $router->resource('delivery-boy-wallet-histories', DeliveryBoyWalletHistoryController::class);
    $router->resource('delivery-boy-withdrawals', DeliveryBoyWithdrawalController::class);
    $router->resource('option-groups', OptionGroupController::class);


});
