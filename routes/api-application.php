<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
use App\Http\Controllers\Api\Customers\CustomerController;
use App\Http\Controllers\Api\Provisioning\ServiceController;
use App\Http\Controllers\Api\Store\Groups\GroupController;
use App\Http\Controllers\Api\Store\Pricings\PricingController;
use App\Http\Controllers\Api\Store\Products\ProductController;
use App\Http\Controllers\ApiController;
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
Route::middleware(['ability:health,*'])->get('/health', [ApiController::class, 'health'])->name('health');
Route::middleware(['ability:statistics,*'])->get('/statistics', [ApiController::class, 'statistics'])->name('statistics');
Route::middleware(['ability:license,*'])->get('/license', [ApiController::class, 'license'])->name('license');

Route::middleware(['ability:customers:index,*'])->get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::middleware(['ability:customers:store,*'])->post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::middleware(['ability:customers:show,*'])->get('/customers/{id}', [CustomerController::class, 'show'])->name('customers.show');
Route::middleware(['ability:customers:update,*'])->post('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::middleware(['ability:customers:delete,*'])->delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.delete');

Route::middleware(['ability:products:index,*'])->get('/products', [ProductController::class, 'index'])->name('products.index');
Route::middleware(['ability:products:store,*'])->post('/products', [ProductController::class, 'store'])->name('products.store');
Route::middleware(['ability:products:show,*'])->get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::middleware(['ability:products:update,*'])->post('/products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::middleware(['ability:products:delete,*'])->delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.delete');

Route::middleware(['ability:groups:index,*'])->get('/groups', [GroupController::class, 'index'])->name('groups.index');
Route::middleware(['ability:groups:store,*'])->post('/groups', [GroupController::class, 'store'])->name('groups.store');
Route::middleware(['ability:groups:show,*'])->get('/groups/{id}', [GroupController::class, 'show'])->name('groups.show');
Route::middleware(['ability:groups:update,*'])->post('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
Route::middleware(['ability:groups:delete,*'])->delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.delete');

Route::middleware(['ability:pricings:index,*'])->get('/pricings', [PricingController::class, 'index'])->name('pricings.index');
Route::middleware(['ability:pricings:store,*'])->post('/pricings', [PricingController::class, 'store'])->name('pricings.store');
Route::middleware(['ability:pricings:show,*'])->get('/pricings/{id}', [PricingController::class, 'show'])->name('pricings.show');
Route::middleware(['ability:pricings:update,*'])->post('/pricings/{product}', [PricingController::class, 'update'])->name('pricings.update');
Route::middleware(['ability:pricings:delete,*'])->delete('/pricings/{product}', [PricingController::class, 'destroy'])->name('pricings.delete');

Route::middleware(['ability:services:index,*'])->get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::middleware(['ability:services:show,*'])->get('/services/{id}', [ServiceController::class, 'show'])->name('services.show');
Route::middleware(['ability:services:delete,*'])->delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.delete');
Route::middleware(['ability:services:expire,*'])->post('/services/{id}/expire', [ServiceController::class, 'expire'])->name('services.expire');
Route::middleware(['ability:services:unsuspend,*'])->post('/services/{id}/unsuspend', [ServiceController::class, 'unsuspend'])->name('services.unsuspend');
Route::middleware(['ability:services:suspend,*'])->post('/services/{id}/suspend', [ServiceController::class, 'suspend'])->name('services.suspend');
