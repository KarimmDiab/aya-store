<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\rolesController;
use App\Http\Controllers\WarehousesController;
use App\Http\Controllers\branchesController;
use App\Http\Controllers\purchasesController;
use App\Http\Controllers\SuppliersController;
use App\Http\Controllers\salesController;
use App\Http\Controllers\SalesReturnsController;
use App\Http\Controllers\categoryController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseDetailsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\ReportsController;




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
    return auth()->check() ? redirect()->route('home') : view('auth.login');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);

Auth::routes(['register' => false]);


Route::get('/main', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/{page}', 'App\Http\Controllers\AdminController@index');

// users routes
Route::middleware('auth')->group(function () {
    Route::get('users/all_users', [UsersController::class, 'index'])->middleware('permission:users.view')->name('all_users');
    Route::post('users', [UsersController::class, 'store'])->middleware('permission:users.create')->name('add_users');
    Route::put('/users/{id}', [UsersController::class, 'update'])->middleware('permission:users.update')->name('updateUser');
    Route::delete('/users/{id}', [UsersController::class, 'destroy'])->middleware('permission:users.delete')->name('deleteUser');


// roles routes
    Route::get('users/roles', [rolesController::class, 'index'])->middleware('permission:users.view')->name('all_roles');
    Route::post('users/roles', [rolesController::class, 'store'])->middleware('permission:users.create')->name('add_roles');
    Route::delete('/roles/delete/{id}', [rolesController::class, 'destroy'])->middleware('permission:users.delete')->name('deleteroles');
    Route::post('/roles/update/{id}', [rolesController::class, 'update'])->middleware('permission:users.update')->name('updateroles');
});


//wearhouses routes
Route::get('warehouses&branches/Warehouses', [WarehousesController::class, 'index'])->name('Warehouses');
Route::post('/add_warehouses', [WarehousesController::class, 'store'])->name('add_Warehouses');
Route::post('/update_warehouses/{id}', [WarehousesController::class, 'update'])->name('updateWarehouses');
Route::delete('/delete_warehouses/{id}', [WarehousesController::class, 'destroy'])->name('deleteWarehouses');

// Branches routes
Route::get('warehouses&branches/branches', [BranchesController::class, 'index'])->name('branches');
Route::post('warehouses&branches/branches', [BranchesController::class, 'store'])->name('branches_store');
Route::put('warehouses&branches/branches/{id}', [BranchesController::class, 'update'])->name('branches_update');
Route::delete('warehouses&branches/branches/{id}', [BranchesController::class, 'destroy'])->name('branches_destroy');


// supplier routes
Route::get('purchases/suppliers', [SuppliersController::class, 'index'])->name('suppliers');
Route::get('purchases/credit_suplliers', [SuppliersController::class, 'creditSuppliers'])->name('credit_suplliers');
Route::post('purchases/suppliers', [SuppliersController::class, 'store'])->name('suppliers.store');
Route::post('purchases/suppliers_profile/{supplier_id}', [SuppliersController::class, 'show'])->name('supplier_profile');
Route::get('purchases/suppliers_profile/{supplier_id}', [SuppliersController::class, 'showForAllSuppliers'])->name('showForAllSuppliers');
Route::post('purchases/suppliers_profile/{purchase_id}/{supplier_id}', [SuppliersController::class, 'supplierPaymentStore'])->name('supplierPaymentStore');
Route::put('purchases/suppliers_profile/{supplierID}/{invoice_id}/{transactionId}',
    [SuppliersController::class, 'updateSupplierPayment'])
    ->name('supplier-payment-transactions.supplierTransaction');
Route::put('purchases/suppliers/{supplier}', [SuppliersController::class, 'update'])->name('suppliers.update');
Route::delete('purchases/suppliers/{supplier}', [SuppliersController::class, 'destroy'])->name('suppliers.destroy');
Route::get('purchases/print_purchase_invoice/{invoice_no}/{supplier_id}', [SuppliersController::class, 'printInvoice'])
    ->name('suppliers.printInvoice');




// category routes
Route::get('products/category', [categoryController::class, 'index'])->name('categories');
Route::post('products/category', [categoryController::class, 'store'])->name('category.store');
Route::put('products/category/{category_id}', [categoryController::class, 'update'])->name('category.update');
Route::delete('products/category/{category_id}', [categoryController::class, 'destroy'])->name('category.destroy');


// products routes
Route::get('products/products', [ProductsController::class, 'index'])->name('products');
Route::post('products/products', [ProductsController::class, 'store'])->name('products.store');
Route::put('products/products/{id}', [ProductsController::class, 'update'])->name('products.update');
Route::delete('products/products/{id}', [ProductsController::class, 'destroy'])->name('products.destroy');


//purchases routes
Route::get('purchases/purchase_invoices', [purchasesController::class, 'index'])->name('purchase_invoices');
Route::get('purchases/editPricesInPurchaseInvoice', [purchasesController::class, 'editPricesInPurchaseInvoice'])->name('editPricesInPurchaseInvoice');
Route::post('purchases/purchase_invoices', [purchasesController::class, 'store'])->name('add_purchase_invoices');
Route::post('purchases/purchase_invoices/{supplier}', [purchasesController::class, 'newStoreInvoice'])->name('add_purchase_invoices_new');
Route::put('purchases/purchase_invoices/{invoice_id}', [purchasesController::class, 'update'])->name('purchase_invoices_update');
Route::delete('purchases/purchase_invoices/{id}', [purchasesController::class, 'destroy'])->name('purchase_invoices_destroy');

Route::get('purchases/purchase_invoices/{invoice_id}/edit', [purchasesController::class, 'edit'])->name('purchase_invoices.edit');
Route::post('purchases/update_purchase_invoices/{invoice_id}', [purchasesController::class, 'edit'])->name('editInvoice');
Route::post('purchases/update_price_purchase_invoices/{invoice_id}', [purchasesController::class, 'editPricePurchaseInvoice'])->name('editPricePurchaseInvoice');

//Route::post('purchases/update_purchase_invoices/{invoice_id}', [PurchasesController::class, 'edit'])->name('editInvoice');




//sales invoices routes
Route::get('sales/sales_invoice', [salesController::class, 'index'])->middleware('permission:sales.view')->name('sales_invoice');
Route::post('sales/sales_invoice', [salesController::class, 'store'])->middleware('permission:sales.create')->name('submit_order');
Route::get('sales/invoice/{id}', [salesController::class, 'show'])->middleware('permission:sales.view')->name('invoice.show');
Route::get('sales/all_sales_invoices', [salesController::class, 'allInvoices'])->middleware('permission:sales.view')->name('all_sales');
Route::get('sales/products/search', [salesController::class, 'searchProducts'])->middleware('permission:sales.view')->name('sales.products.search');
Route::get('sales/returns', [SalesReturnsController::class, 'index'])->middleware('permission:sales.view')->name('sales.returns.index');
Route::post('sales/returns', [SalesReturnsController::class, 'store'])->middleware('permission:sales.create')->name('sales.returns.store');
Route::get('sales/returns/invoice/{id}', [SalesReturnsController::class, 'show'])->middleware('permission:sales.view')->name('sales.returns.show');
Route::delete('sales/invoice/{id}', [salesController::class, 'destroy'])->middleware('permission:sales.delete')->name('invoice.destroy');
Route::put('sales/invoice/{id}', [salesController::class, 'update'])->middleware('permission:sales.update')->name('invoice.update');



// expenses routes
Route::get('expenses/add_expenses', [ExpenseController::class, 'index'])->name('expenses');
Route::post('expenses/add_expenses', [ExpenseController::class, 'storeExpensesItem'])->name('storeExpensesItem');
Route::put('expenses/add_expenses/{expense}', [ExpenseController::class, 'update'])->name('expensesItemUpdate');
Route::delete('expenses/add_expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expensesItemDestroy');


// expenses details routes
Route::get('expenses/expenses_details', [ExpenseDetailsController::class, 'index'])->name('expenses_details');
Route::post('expenses/add_expenses', [ExpenseDetailsController::class, 'storeExpensesItem'])->name('storeExpensesItem');
Route::post('expenses/expenses_details', [ExpenseDetailsController::class, 'store'])->name('storeExpensesDetails');
Route::get('expenses/all_expenses', [ExpenseDetailsController::class, 'showDetails'])->name('showDetails');
Route::put('expenses/all_expenses/{expense}', [ExpenseDetailsController::class, 'update'])->name('expenses.update');
Route::delete('expenses/all_expenses/{expense}', [ExpenseDetailsController::class, 'destroy'])->name('expenses.destroy');


// customers routes
Route::get('customers/all_customers', [CustomersController::class, 'index'])->name('all_customers');
Route::put('customers/all_customers/{id}', [CustomersController::class, 'update'])->name('customers.update');
Route::delete('customers/all_customers/{id}', [CustomersController::class, 'destroy'])->name('customers.destroy');
Route::get('customers/customer_profile/{id}', [CustomersController::class, 'show'])->name('customer_profile');
Route::get('/customers/invoice/{invoice_id}/{customer_id}', [CustomersController::class, 'printInvoice'])->name('invoices.print');
Route::put('/customers/customer_profile/{customerId}/{invoice_id}/{transactionId}', [CustomersController::class, 'customerTransaction'])
    ->name('customer-payment-transactions.customerTransaction');
Route::post('/customers/customer_profile/{sales_invoice_id}/{customer_id}',
    [CustomersController::class, 'store']
)->name('customer.payments.store');
Route::get('customers/credit_customers', [CustomersController::class, 'creditCustomers'])->name('credit_customers');


// reports routes
Route::get('reports/reports_main_page', [ReportsController::class, 'index'])->name('home_reports');
Route::get('reports/sub_reports/sales_per_product', [ReportsController::class, 'salesPerPeriod'])->name('sales_per_product');
Route::get('reports/sub_reports/total_sales', [ReportsController::class, 'totalSales'])->name('total_sales');
Route::get('reports/sub_reports/sales_per_customer', [ReportsController::class, 'salesPerCustomer'])->name('salesPerCustomer');
Route::get('reports/sub_reports/sales_per_user', [ReportsController::class, 'salesPerUser'])->name('salesPerUser');
Route::get('reports/inventory/stock-summary', [ReportsController::class, 'inventoryStockSummary'])->name('inventory_stock_summary');
Route::get('reports/inventory/movements', [ReportsController::class, 'inventoryMovements'])->name('inventory_movements');
Route::get('reports/inventory/low-stock', [ReportsController::class, 'inventoryLowStock'])->name('inventory_low_stock');
Route::get('reports/purchases/total', [ReportsController::class, 'purchaseTotals'])->name('purchase_report_total');
Route::get('reports/purchases/suppliers', [ReportsController::class, 'purchasesPerSupplier'])->name('purchase_report_suppliers');
Route::get('reports/purchases/products', [ReportsController::class, 'purchasesPerProduct'])->name('purchase_report_products');
Route::get('reports/purchases/payments', [ReportsController::class, 'supplierPaymentsReport'])->name('purchase_report_payments');
Route::get('reports/suppliers/paid', [ReportsController::class, 'supplierPaidReport'])->name('supplier_report_paid');
Route::get('reports/suppliers/balances', [ReportsController::class, 'supplierBalancesReport'])->name('supplier_report_balances');
Route::get('reports/profits/summary', [ReportsController::class, 'profitSummary'])->name('profit_report_summary');
Route::get('reports/profits/compare', [ReportsController::class, 'profitComparison'])->name('profit_report_comparison');
Route::get('reports/customers/sales', [ReportsController::class, 'customerSalesReport'])->name('customer_report_sales');
Route::get('reports/customers/balances', [ReportsController::class, 'customerBalancesReport'])->name('customer_report_balances');
Route::get('reports/customers/collections', [ReportsController::class, 'customerCollectionsReport'])->name('customer_report_collections');












//Route::group(['middleware' => ['auth']] , function(){
    //Route::resource('roles' , 'RoleController');
    //Route::resource('users','UserController');

//});
