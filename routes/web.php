<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\VNPayConTroller;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Auth::routes();
Route::get('active/{token}', 'Auth\RegisterController@activation')->name('active_account');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
 */
Route::namespace('Admin')->prefix('admin')->name('admin.')->middleware('admin')
    ->group(function () {
        Route::get('dashboard', 'DashboardController@index')->name('dashboard');

        Route::get('users', 'UserController@index')->name('users');
        Route::post('user/new', 'UserController@new')->name('user_new');
        Route::post('user/delete', 'UserController@delete')->name('user_delete');
        Route::get('user/{id}/show', 'UserController@show')->name('user_show');
        Route::get('user/{id}/send', 'UserController@send')->name('user_send');

        Route::get('posts', 'PostController@index')->name('post.index');
        Route::get('post/new', 'PostController@new')->name('post.new');
        Route::post('post/save', 'PostController@save')->name('post.save');
        Route::post('post/delete', 'PostController@delete')->name('post.delete');
        Route::get('post/{id}/edit', 'PostController@edit')->name('post.edit');
        Route::post('post/{id}/update', 'PostController@update')->name('post.update');

        Route::get('advertises', 'AdvertiseController@index')->name('advertise.index');
        Route::get('advertise/new', 'AdvertiseController@new')->name('advertise.new');
        Route::post('advertise/save', 'AdvertiseController@save')->name('advertise.save');
        Route::post('advertise/delete', 'AdvertiseController@delete')->name('advertise.delete');
        Route::get('advertise/{id}/edit', 'AdvertiseController@edit')->name('advertise.edit');
        Route::post('advertise/{id}/update', 'AdvertiseController@update')->name('advertise.update');

        Route::get('producers', 'ProducerController@index')->name('producer.index');
        Route::get('producer/new', 'ProducerController@new')->name('producer.new');
        Route::post('producer/save', 'ProducerController@save')->name('producer.save');
        Route::post('producer/delete', 'ProducerController@delete')->name('producer.delete');
        Route::get('producer/{id}/edit', 'ProducerController@edit')->name('producer.edit');
        Route::post('producer/{id}/update', 'ProducerController@update')->name('producer.update');

        Route::get('products', 'ProductController@index')->name('product.index');
        Route::get('product/new', 'ProductController@new')->name('product.new');
        Route::post('product/save', 'ProductController@save')->name('product.save');
        Route::post('product/delete', 'ProductController@delete')->name('product.delete');
        Route::get('product/{id}/edit', 'ProductController@edit')->name('product.edit');
        Route::post('product/{id}/update', 'ProductController@update')->name('product.update');
        Route::post('promotion/delete', 'ProductController@delete_promotion')->name('product.delete_promotion');
        Route::post('product_detail/delete', 'ProductController@delete_product_detail')->name('product.delete_product_detail');
        Route::post('product/image/delete', 'ProductController@delete_image')->name('product.delete_image');

        Route::get('orders', 'OrderController@index')->name('order.index');
        Route::get('order/{id}/show', 'OrderController@show')->name('order.show');
        Route::get('active/{action}/{id}', 'OrderController@actionTransaction')->name('orderTransaction');
        


        Route::get('statistic', 'StatisticController@index')->name('statistic');
        Route::post('statistic/change', 'StatisticController@edit')->name('statistic.edit');

        route::get('warehouse', 'WarehouseController@index')->name('warehouse');
        route::get('orderDetails', 'WarehouseController@orderDetails')->name('orderDetails');
    });

Route::namespace('Pages')->group(function () {
    Route::get('/', 'HomePage')->name('home_page');
    Route::get('about', 'AboutPage')->name('about_page');
    Route::get('contact', 'ContactPage')->name('contact_page');
    Route::get('search', 'SearchController')->name('search');
    Route::get('posts', 'PostController@index')->name('posts_page');
    Route::get('post/{id}', 'PostController@show')->name('post_page');
    Route::get('orders', 'OrderController@index')->name('orders_page');
    Route::get('order/{id}', 'OrderController@show')->name('order_page');
    Route::get('orders/cancel/{id}', 'OrderController@cancel')->name('order.cancel');

    Route::get('user/profile', 'UserController@show')->name('show_user');
    Route::get('user/edit', 'UserController@edit')->name('edit_user');
    Route::post('user/save', 'UserController@save')->name('save_user');
    //page products
    Route::get('products', 'ProductsController@index')->name('products_page');
    Route::get('producer/{id}', 'ProductsController@getProducer')->name('producer_page');
    Route::get('product/{id}', 'ProductsController@getProduct')->name('product_page');
    Route::post('vote', 'ProductsController@addVote')->name('add_vote');
    Route::post('cart/add', 'CartController@addCart')->name('add_cart');
    Route::post('cart/remove', 'CartController@removeCart')->name('remove_cart');
    Route::post('minicart/update', 'CartController@updateMiniCart')->name('update_minicart');
    Route::post('cart/update', 'CartController@updateCart')->name('update_cart');
    Route::get('cart', 'CartController@showCart')->name('show_cart');
    Route::post('checkout', 'CartController@showCheckout')->name('show_checkout');
    Route::get('checkout', 'CartController@showCheckout')->name('show_checkout');

    Route::post('payment', 'CartController@payment')->name('payment');
    Route::get('vnpay_return', 'CartController@vnpay_return')->name('vnpay_return');
    Route::post('vnpay_payment', 'CartController@vnpay_payment')->name('vnpay_payment');
});


Route::controller(LoginController::class)->group(function () {
    Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('auth/google/callback', 'handleGoogleCallback');
});
Route::controller(LoginController::class)->group(function () {
  Route::get('auth/facebook', 'redirectToFacebook')->name('auth.facebook');
  Route::get('auth/facebook/callback', 'handleFacebookCallback');
});
