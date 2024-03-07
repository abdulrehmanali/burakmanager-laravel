<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Authentication;

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


Route::post('/signup', 'AuthenticationController@signup');
Route::post('/login', 'AuthenticationController@login');

Route::post('/webhooks/shop/{shop_id}/wordpress/ledger/new', 'LedgerController@wordpress_webhook_new');
Route::any('/webhooks/shop/{shop_id}/wordpress/ledger/update', 'LedgerController@wordpress_webhook_update');

// Route::get('/admin_script/ledger/move_existing_receiving_in_new', 'LedgerController@move_existing_receiving_in_new');

// Route::group([Authentication::class], function()
// {
  Route::get('/home', 'HomeController@index');
  Route::get('/home/{shop_id}', 'HomeController@index');

  Route::get('/invitations/accept/{id}', 'InvitationsController@accept');

  Route::get('/shops', 'ShopsController@index');
  Route::post('/shops', 'ShopsController@create');
  Route::get('/shops/{shop_id}', 'ShopsController@view');
  Route::delete('/shops/{shop_id}/users/{user_id}', 'ShopsController@delete_user');

  Route::get('/shops/{shop_id}/products', 'ProductsController@index');
  Route::post('/shops/{shop_id}/products', 'ProductsController@create');
  Route::get('/shops/{shop_id}/products/{id}', 'ProductsController@view');
  Route::post('/shops/{shop_id}/products/{id}', 'ProductsController@update');

  Route::get('/shops/{shop_id}/production-products', 'ProductionProductsController@index');
  Route::post('/shops/{shop_id}/production-products', 'ProductionProductsController@create');
  Route::post('/shops/{shop_id}/production-products/{id}/create-product', 'ProductionProductsController@createProduct');
  Route::get('/shops/{shop_id}/production-products/{id}.pdf', 'ProductionProductsController@viewPdf');
  Route::get('/shops/{shop_id}/production-products/{id}', 'ProductionProductsController@view');
  Route::post('/shops/{shop_id}/production-products/{id}', 'ProductionProductsController@update');


  Route::get('/shops/{shop_id}/customers', 'CustomersController@index');
  Route::post('/shops/{shop_id}/customers', 'CustomersController@create');
  Route::get('/shops/{shop_id}/customers/{customer_id}', 'CustomersController@view');
  Route::post('/shops/{shop_id}/customers/{customer_id}', 'CustomersController@update');

  Route::get('/shops/{shop_id}/ledger', 'LedgerController@index');
  Route::post('/shops/{shop_id}/ledger', 'LedgerController@create');
  Route::get('/shops/{shop_id}/ledger/{ledger_id}/invoice', 'LedgerController@viewInvoice');
  Route::get('/shops/{shop_id}/ledger/{ledger_id}/receipt', 'LedgerController@viewPdf');
  Route::post('/shops/{shop_id}/ledger/{ledger_id}/delete-receiving/{receiving_id}', 'LedgerController@delete_receiving');
  Route::get('/shops/{shop_id}/ledger/{ledger_id}', 'LedgerController@view');
  Route::delete('/shops/{shop_id}/ledger/{ledger_id}', 'LedgerController@delete');
  Route::post('/shops/{shop_id}/ledger/{ledger_id}', 'LedgerController@update');

  Route::post('/shops/{shop_id}/invitations/', 'InvitationsController@create');
  Route::delete('/shops/{shop_id}/invitations/{invitation_id}', 'InvitationsController@delete');

  Route::get('/shops/{shop_id}/receiving', 'ReceivingsController@index');
  Route::post('/shops/{shop_id}/receiving', 'ReceivingsController@create');
  Route::post('/shops/{shop_id}/receiving/{receiving_id}/deduct', 'ReceivingsController@batchDeduct');
  Route::get('/shops/{shop_id}/receiving/{receiving_id}', 'ReceivingsController@view');
  Route::post('/shops/{shop_id}/receiving/{receiving_id}', 'ReceivingsController@update');
  Route::delete('/shops/{shop_id}/receiving/{receiving_id}', 'ReceivingsController@delete');

  Route::get('/shops/{shop_id}/webhooks', 'ShopWebhooksController@index');
  Route::post('/shops/{shop_id}/webhooks', 'ShopWebhooksController@create');
  Route::delete('/shops/{shop_id}/webhooks/{webhook_id}', 'ShopWebhooksController@delete');

// });
