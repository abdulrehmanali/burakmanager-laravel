<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Authenticaiton;

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
Route::get('/shops/{shop_id}/ledger/{ledger_id}/recipt', 'LedgerController@viewPdf');

// Route::group([Authenticaiton::class], function()
// {
  Route::get('/home', 'HomeController@index');
  Route::get('/home/{shop_id}', 'HomeController@index');

  Route::get('/invitaions/accept/{id}', 'InvitationsController@accept');

  Route::get('/shops', 'ShopsController@index');
  Route::post('/shops', 'ShopsController@create');
  Route::get('/shops/{id}', 'ShopsController@view');
  Route::delete('/shops/{id}/users/{user_id}', 'ShopsController@delete_user');

  Route::get('/shops/{shop_id}/products', 'ProductsController@index');
  Route::post('/shops/{shop_id}/products', 'ProductsController@create');
  Route::get('/shops/{shop_id}/products/{id}', 'ProductsController@view');
  Route::post('/shops/{shop_id}/products/{id}', 'ProductsController@update');

  Route::get('/shops/{shop_id}/produciton-products', 'ProductionProductsController@index');
  Route::post('/shops/{shop_id}/produciton-products', 'ProductionProductsController@create');
  Route::get('/shops/{shop_id}/produciton-products/{id}', 'ProductionProductsController@view');
  Route::post('/shops/{shop_id}/produciton-products/{id}', 'ProductionProductsController@update');

  Route::get('/shops/{shop_id}/customers', 'CustomersController@index');
  Route::post('/shops/{shop_id}/customers', 'CustomersController@create');
  Route::get('/shops/{shop_id}/customers/{customer_id}', 'CustomersController@view');
  Route::post('/shops/{shop_id}/customers/{customer_id}', 'CustomersController@update');

  Route::get('/shops/{shop_id}/ledger', 'LedgerController@index');
  Route::post('/shops/{shop_id}/ledger', 'LedgerController@create');
  Route::get('/shops/{shop_id}/ledger/{ledger_id}', 'LedgerController@view');
  
  Route::post('/shops/{shop_id}/ledger/{ledger_id}', 'LedgerController@update');

  Route::post('/shops/{shop_id}/invitations/', 'InvitationsController@create');
  Route::delete('/shops/{shop_id}/invitaions/{invitation_id}', 'InvitationsController@delete');
// });
