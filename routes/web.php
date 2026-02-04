<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
// categories
Route::get('/categories', [DashboardController::class, 'Categories'])->name('dashboard.categories');
Route::post('/category/create', [DashboardController::class, 'StoreCategory'])->name('dashboard.store_category');
Route::get('/category/{Category}', [DashboardController::class, 'Category'])->name('dashboard.category');
Route::get('/category/{Category}/delete', [DashboardController::class, 'DeleteCategory'])->name('dashboard.delete_category');
Route::post('/category/{Category}/update', [DashboardController::class, 'UpdateCategory'])->name('dashboard.update_category');
// products
Route::get('products', [DashboardController::class, 'Products'])->name('dashboard.products');
Route::post('product/create', [DashboardController::class, 'StoreProduct'])->name('dashboard.store_product');
Route::get('product/{Product}', [DashboardController::class, 'Product'])->name('dashboard.product');
Route::get('product/{Product}/delete', [DashboardController::class, 'DeleteProduct'])->name('dashboard.delete_product');
Route::post('product/{Product}/update', [DashboardController::class, 'UpdateProduct'])->name('dashboard.update_product');
// product items
Route::get('product-items', [DashboardController::class, 'ProductItems'])->name('dashboard.product_items');
Route::post('product/{Product}/create_product_item', [DashboardController::class, 'StoreProductItem'])->name('dashboard.store_product_item');
Route::get('product_item/{ProductItem}', [DashboardController::class, 'ProductItem'])->name('dashboard.product_item');
Route::get('product_item/{ProductItem}/delete', [DashboardController::class, 'DeleteProductItem'])->name('dashboard.delete_product_item');
Route::post('product_item/{ProductItem}/update', [DashboardController::class, 'UpdateProductItem'])->name('dashboard.update_product_item');
Route::post('product_item/{ProductItem}/create_certification', [DashboardController::class, 'StoreCertification'])->name('dashboard.store_certification');
Route::get('certification/{ProductItemCertification}/delete', [DashboardController::class, 'DeleteCertification'])->name('dashboard.delete_certification');
// company
Route::get('/companies', [DashboardController::class, 'Companies'])->name('dashboard.companies');
Route::post('/company/create', [DashboardController::class, 'StoreCompany'])->name('dashboard.store_company');
Route::get('/company/{Company}', [DashboardController::class, 'Company'])->name('dashboard.company');
Route::get('/company/{Company}/delete', [DashboardController::class, 'DeleteCompany'])->name('dashboard.delete_company');
Route::post('/company/{Company}/update', [DashboardController::class, 'UpdateCompany'])->name('dashboard.update_company');
Route::post('/company/{Company}/create_employee', [DashboardController::class, 'StoreEmployee'])->name('dashboard.store_employee');
Route::get('employee/{Employee}/delete', [DashboardController::class, 'DeleteEmployee'])->name('dashboard.delete_employee');
Route::post('/company/{Company}/create_order', [DashboardController::class, 'StoreOrder'])->name('dashboard.store_order');
Route::post('/company/{Company}/create_backload', [DashboardController::class, 'StoreBackload'])->name('dashboard.store_backload');
// orders
Route::get('/orders', [DashboardController::class, 'Orders'])->name('dashboard.orders');
Route::post('/order/create', [DashboardController::class, 'StoreOrderDirect'])->name('dashboard.store_order_direct');
// order items
Route::get('/order-items', [DashboardController::class, 'OrderItems'])->name('dashboard.order_items');
Route::get('/order/{Order}', [DashboardController::class, 'Order'])->name('dashboard.order');
Route::get('/order/{Order}/delivery-note', [DashboardController::class, 'DeliveryNote'])->name('dashboard.delivery_note');
Route::get('/order/{Order}/delete', [DashboardController::class, 'DeleteOrder'])->name('dashboard.delete_order');
Route::post('/order/{Order}/update', [DashboardController::class, 'UpdateOrder'])->name('dashboard.update_order');
Route::post('/order/{Order}/create_order_item', [DashboardController::class, 'StoreOrderItem'])->name('dashboard.store_order_item');
Route::post('order_item/{OrderItem}/update', [DashboardController::class, 'UpdateOrderItem'])->name('dashboard.update_order_item');
Route::get('order_item/{OrderItem}/delete', [DashboardController::class, 'DeleteOrderItem'])->name('dashboard.delete_order_item');

// backloads
Route::get('backloads', [DashboardController::class, 'Backloads'])->name('dashboard.backloads');
Route::get('backload/{Backload}', [DashboardController::class, 'Backload'])->name('dashboard.backload');
Route::get('backload/{Backload}/delete', [DashboardController::class, 'DeleteBackload'])->name('dashboard.delete_backload');
Route::post('backload/{Backload}/update', [DashboardController::class, 'UpdateBackload'])->name('dashboard.update_backload');
Route::post('backload/{Backload}/create_backload_item', [DashboardController::class, 'StoreBackloadItem'])->name('dashboard.store_backload_item');
Route::post('backload_item/{BackloadItem}/update', [DashboardController::class, 'UpdateBackloadItem'])->name('dashboard.update_backload_item');
Route::get('backload_item/{BackloadItem}/delete', [DashboardController::class, 'DeleteBackloadItem'])->name('dashboard.delete_backload_item');

// company price lists
Route::get('/company/{Company}/price-lists', [DashboardController::class, 'CompanyPriceLists'])->name('dashboard.company_price_lists');
Route::post('/company/{Company}/price-list/create', [DashboardController::class, 'StoreCompanyPriceList'])->name('dashboard.store_company_price_list');
Route::post('/company/{Company}/bulk-update-price-lists', [DashboardController::class, 'BulkUpdateCompanyPriceLists'])->name('dashboard.bulk_update_company_price_lists');
Route::post('/company-price-list/{CompanyPriceList}/update', [DashboardController::class, 'UpdateCompanyPriceList'])->name('dashboard.update_company_price_list');
Route::get('/company-price-list/{CompanyPriceList}/delete', [DashboardController::class, 'DeleteCompanyPriceList'])->name('dashboard.delete_company_price_list');

