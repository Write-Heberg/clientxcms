<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DarkModeController;
use App\Http\Controllers\Front\Core\ClientController;
use App\Http\Controllers\Front\Core\EmailController;
use App\Http\Controllers\Front\Core\Helpdesk\SupportController;
use App\Http\Controllers\Front\Core\InvoiceController;
use App\Http\Controllers\Front\Core\ServiceController;
use App\Http\Controllers\Front\Core\PaymentGatewayController;
use App\Http\Controllers\Front\Store\Basket\BasketController;
use App\Http\Controllers\Front\Store\StoreController;
use App\Models\Store\Group;
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

Route::get('/', function () {
    if (!setting('theme_home_enabled')) {
        return redirect()->route('front.store.index');
    }
    return view('home');
})->name('home');
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
Route::get('/licensing/return', [LicenseController::class, 'return'])->name('licensing.return');
Route::get('/darkmode', [DarkModeController::class, 'darkmode'])->name('client.darkmode');

Route::prefix('/store')->name('front.')->group(function () {
    Route::get('/', [StoreController::class, 'index'])->name('store.index');
    Route::prefix('/basket')->name('store.basket')->group(function (){
        Route::get('/', [BasketController::class, 'show'])->name('.show');
        Route::get('/checkout', [BasketController::class, 'showCheckout'])->name('.checkout');
        Route::post('/checkout', [BasketController::class, 'processCheckout']);
        Route::post('/coupon', [BasketController::class, 'coupon'])->name('.coupon');
        Route::delete('/coupon', [BasketController::class, 'removeCoupon'])->name('.coupon.remove');
        Route::any('/add/{product}', [BasketController::class, 'addProduct'])->name('.add');
        Route::get('/config/{product}', [BasketController::class, 'showConfigProduct'])->name('.config');
        Route::post('/config/{product}', [BasketController::class, 'configProduct']);
        Route::delete('/remove/{product}', [BasketController::class, 'removeRow'])->name('.remove');
        Route::post('/quantity/{product}', [BasketController::class, 'changeQuantity'])->name('.quantity');
    });
    Route::get('/{group:slug}', [StoreController::class, 'group'])->name('store.group');
    Route::get('/{group:slug}/{subgroup:slug}', [StoreController::class, 'subgroup'])->name('store.subgroup');
});
Route::prefix('/client')->name('front.')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->middleware(['auth'])->name('client.index');
    Route::prefix('/services')->name('services')->middleware(['auth', 'verified'])->group(function(){
        Route::get('/', [ServiceController::class, 'index'])->name('.index');
        Route::get('/{service}', [ServiceController::class, 'show'])->name('.show');
        Route::get('/billing/{service}', [ServiceController::class, 'renewal'])->name('.renewal');
        Route::post('/billing/{service}', [ServiceController::class, 'billing'])->name('.billing');
        Route::post('/name/{service}', [ServiceController::class, 'name'])->name('.name');
        Route::post('/cancel/{service}', [ServiceController::class, 'cancel'])->name('.cancel');
        Route::get('/tab/{service}/{tab}', [ServiceController::class, 'tab'])->name('.tab');
        Route::get('/{service}/renew/{gateway}', [ServiceController::class, 'renew'])->name('.renew');
    });
    Route::prefix('/invoices')->name('invoices')->middleware(['auth', 'verified'])->group(function(){
        Route::get('/', [InvoiceController::class, 'index'])->name('.index');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('.show');
        Route::get('/{invoice}/download', [InvoiceController::class, 'download'])->name('.download');
        Route::get('/{invoice}/pay/{gateway}', [InvoiceController::class, 'pay'])->name('.pay');
    });
    Route::prefix('/support')->name('support')->middleware('auth')->group(function(){
        Route::get('/', [SupportController::class, 'index'])->name('.index');
        Route::get('/create', [SupportController::class, 'create'])->name('.create');
        Route::post('/create', [SupportController::class, 'store']);
        Route::delete('/{ticket}/close', [SupportController::class, 'close'])->name('.close');
        Route::post('/{ticket}/reopen', [SupportController::class, 'reopen'])->name('.reopen');
        Route::post('/{ticket}/reply', [SupportController::class, 'reply'])->name('.reply');
        Route::get('/{ticket}', [SupportController::class, 'show'])->name('.show');
        Route::get('/{ticket}/download/{attachment}', [SupportController::class, 'download'])->name('.download');
    });
    Route::get('/quotes', [ClientController::class, 'quotes'])->middleware(['auth'])->name('quotes.index');
    Route::get('/tickets', [ClientController::class, 'tickets'])->middleware(['auth'])->name('tickets.index');
    Route::get('/orders', [ClientController::class, 'orders'])->middleware(['auth'])->name('orders.index');
    Route::prefix('/profile')->name('profile')->middleware(['auth'])->group(function(){
        Route::get('/', [\App\Http\Controllers\Front\Core\ProfileController::class, 'show'])->name('.index');
        Route::post('/', [\App\Http\Controllers\Front\Core\ProfileController::class, 'update'])->name('.update');
        Route::post('/password', [\App\Http\Controllers\Front\Core\ProfileController::class, 'password'])->name('.password');
        Route::post('/export', [\App\Http\Controllers\Front\Core\ProfileController::class, 'export'])->name('.export');
        Route::post('/2fa', [\App\Http\Controllers\Front\Core\ProfileController::class, 'save2fa'])->name('.2fa');
        Route::get('/download_codes', [\App\Http\Controllers\Front\Core\ProfileController::class, 'downloadCodes'])->name('.2fa_codes');
    });

    Route::prefix('/emails')->name('emails.')->group(function () {
        Route::get('/', [EmailController::class, 'index'])->middleware(['auth', 'verified'])->name('index');
        Route::get('/resend', [AuthenticatedSessionController::class, 'resend'])->middleware(['auth'])->name('resend');
        Route::get('/{email}', [EmailController::class, 'show'])->middleware(['auth', 'verified'])->name('show');
    });
});
Route::get('/gateways/{invoice:id}/{gateway}/return', [PaymentGatewayController::class, 'return'])->middleware(['auth'])->name('gateways.return');
Route::get('/gateways/{invoice:id}/{gateway}/cancel', [PaymentGatewayController::class, 'cancel'])->middleware(['auth'])->name('gateways.cancel');
Route::any('/gateways/{gateway}/notification', [PaymentGatewayController::class, 'notification'])->withoutMiddleware('csrf')->name('gateways.notification');
Route::get('/docs/api-docs.json', [\App\Http\Controllers\ApiController::class, 'apiDocs'])->name('l5-swagger.application.docs');
Route::get('/docs/asset/{asset}', [\App\Http\Controllers\ApiController::class, 'apiAsset'])->name('l5-swagger.application.asset');

require __DIR__.'/auth.php';
