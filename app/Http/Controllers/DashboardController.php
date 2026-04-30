<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Company;
use App\Models\CompanyEmployee;
use App\Models\Order;
use Storage;
use App\Models\ProductItem;
use App\Models\OrderItem;
use App\Models\Backload;
use App\Models\BackloadItem;
use App\Models\ProductItemCertificate;
use App\Models\CompanyPriceList;
use App\Services\ProductItemStatusService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\DatabaseNotification;


class DashboardController extends Controller
{
    private function buildTimeSheetRowsForOrder(Order $Order): array
    {
        // Calculate pricing for each order item (same logic as Order page)
        $Order->load(['OrderItems.productItem.product', 'Company']);

        foreach ($Order->OrderItems as $orderItem) {
            $pricingInfo = $this->calculateOrderItemPricing($orderItem, $Order->Company);
            $orderItem->unit_price = $pricingInfo['unit_price'];
            $orderItem->duration_days = $pricingInfo['duration_days'];
            $orderItem->total_price = $pricingInfo['total_price'];
        }

        $backloadItems = BackloadItem::query()
            ->whereIn('order_item_id', $Order->OrderItems->pluck('id')->toArray())
            ->with('Backload')
            ->get();

        $backloadDateByOrderItemId = [];
        $backloadIdByOrderItemId = [];
        foreach ($backloadItems as $bi) {
            if ($bi->order_item_id) {
                $oid = (int) $bi->order_item_id;
                $backloadDateByOrderItemId[$oid] = $bi->Backload?->date;
                $backloadIdByOrderItemId[$oid] = $bi->backload_id ?? $bi->Backload?->id;
            }
        }

        $timesheetRows = [];
        $i = 1;
        foreach ($Order->OrderItems as $orderItem) {
            $orderItemId = (int) $orderItem->id;
            $productName = $orderItem->productItem?->product?->name ?? '';
            $series = $orderItem->productItem?->series_number ?? '';
            $backloadDate = $backloadDateByOrderItemId[$orderItemId] ?? null;
            $backloadId = $backloadIdByOrderItemId[$orderItemId] ?? null;

            $timesheetRows[] = [
                'file_no' => $Order->order_number ?? '',
                'site' => $Order->site_code ?? '',
                'sno' => $i++,
                'description' => $productName,
                'tracking_number' => $series,
                'invoice_number' => '',
                'po_reference' => $Order->po_reference ? basename($Order->po_reference) : '',
                'delivery_note' => 'Order: ' . $Order->id,
                'delivery_date' => $Order->delivery_date ?: ($Order->created_at?->format('Y-m-d') ?? ''),
                'delivery_time' => '',
                'backload_note' => $backloadId !== null ? 'Backload: ' . $backloadId : '',
                'backload_date' => $backloadDate ?? '',
                'time_blkd' => '',
                'rental_status' => $backloadDate ? 'RETURNED' : 'UNDER RENTAL',
                'rental_period' => ($orderItem->duration_days ?? ''),
                'unit_rental_cost' => ($orderItem->unit_price ?? ''),
                'total_rental_cost' => ($orderItem->total_price ?? ''),
            ];
        }

        return $timesheetRows;
    }

    private function buildTimeSheetRowsForOrderItems($orderItems): array
    {
        $orderItemIds = $orderItems->pluck('id')->map(fn ($v) => (int) $v)->all();

        $backloadItems = BackloadItem::query()
            ->whereIn('order_item_id', $orderItemIds)
            ->with('Backload')
            ->get();

        $backloadDateByOrderItemId = [];
        $backloadIdByOrderItemId = [];
        foreach ($backloadItems as $bi) {
            if ($bi->order_item_id) {
                $oid = (int) $bi->order_item_id;
                $backloadDateByOrderItemId[$oid] = $bi->Backload?->date;
                $backloadIdByOrderItemId[$oid] = $bi->backload_id ?? $bi->Backload?->id;
            }
        }

        $rows = [];
        $i = 1;
        foreach ($orderItems as $orderItem) {
            $orderItemId = (int) $orderItem->id;
            $order = $orderItem->Order;
            $companyName = $order?->Company?->name ?? '';

            $productName = $orderItem->productItem?->product?->name ?? '';
            $series = $orderItem->productItem?->series_number ?? '';

            $backloadDate = $backloadDateByOrderItemId[$orderItemId] ?? null;
            $backloadId = $backloadIdByOrderItemId[$orderItemId] ?? null;

            $rows[] = [
                'file_no' => $order?->order_number ?? '',
                'order_number' => $order?->order_number ?? '',
                'company_name' => $companyName,
                'site' => $order?->site_code ?? '',
                'sno' => $i++,
                'description' => $productName,
                'remarks' => $orderItem->remarks ?? '',
                'tracking_number' => $series,
                'invoice_number' => '',
                'po_reference' => $order?->po_reference ? basename($order->po_reference) : '',
                'delivery_note' => $order ? ('Order: ' . $order->id) : '',
                'delivery_date' => $order?->delivery_date ?: ($order?->created_at?->format('Y-m-d') ?? ''),
                'delivery_time' => '',
                'backload_note' => $backloadId !== null ? 'Backload: ' . $backloadId : '',
                'backload_date' => $backloadDate ?? '',
                'time_blkd' => '',
                'rental_status' => $backloadDate ? 'RETURNED' : 'UNDER RENTAL',
                'rental_period' => ($orderItem->duration_days ?? ''),
                'unit_rental_cost' => ($orderItem->unit_price ?? ''),
                'total_rental_cost' => ($orderItem->total_price ?? ''),
            ];
        }

        return $rows;
    }

    private function companyNameInitials(string $companyName): string
    {
        $words = preg_split('/[^A-Za-z0-9]+/', trim($companyName), -1, PREG_SPLIT_NO_EMPTY);
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        return $initials !== '' ? $initials : 'COMP';
    }

    private function generateOrderNumberForCompany(Company $company): string
    {
        $prefix = $this->companyNameInitials($company->name ?? '');
        $year = now()->format('Y');
        $month = now()->format('m');

        $rmrl = 'ORD';
        $base = "{$prefix}-{$year}-{$rmrl}-{$month}-";

        $lastForScope = Order::query()
            ->where('order_number', 'like', $base . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $lastSeq = 0;
        if (is_string($lastForScope) && str_starts_with($lastForScope, $base)) {
            $suffix = substr($lastForScope, strlen($base));
            if (ctype_digit($suffix)) {
                $lastSeq = (int) $suffix;
            }
        }

        $nextSeq = $lastSeq + 1;
        $seq3 = str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);

        return $base . $seq3;
    }

    private function generateBackloadNumberForCompany(Company $company): string
    {
        $prefix = $this->companyNameInitials($company->name ?? '');
        $year = now()->format('Y');
        $month = now()->format('m');

        $rmrl = 'BLKD';
        $base = "{$rmrl}-{$year}-{$prefix}-{$month}-";

        $lastForScope = Backload::query()
            ->where('backload_number', 'like', $base . '%')
            ->orderByDesc('backload_number')
            ->value('backload_number');

        $lastSeq = 0;
        if (is_string($lastForScope) && str_starts_with($lastForScope, $base)) {
            $suffix = substr($lastForScope, strlen($base));
            if (ctype_digit($suffix)) {
                $lastSeq = (int) $suffix;
            }
        }

        $nextSeq = $lastSeq + 1;
        $seq3 = str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);

        return $base . $seq3;
    }

    public function Index()
    {
        // Get dashboard statistics
        $totalCompanies = \App\Models\Company::where('is_active', true)->count();
        $totalOrders = \App\Models\Order::where('is_active', true)->count();
        $totalProducts = \App\Models\Product::where('is_active', true)->count();
        $totalProductItems = \App\Models\ProductItem::where('is_active', true)->count();

        // Get recent orders
        $recentOrders = \App\Models\Order::where('is_active', true)
            ->with(['Company', 'OrderItems'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate pricing for recent orders
        foreach ($recentOrders as $order) {
            $order->load(['OrderItems.productItem.product', 'Company']);
            $totalAmount = 0;
            foreach ($order->OrderItems as $orderItem) {
                $pricingInfo = $this->calculateOrderItemPricing($orderItem, $order->Company);
                $totalAmount += $pricingInfo['total_price'];
            }
            $order->total_amount = $totalAmount;
        }

        // Get active rentals (orders with items not returned)
        $activeRentals = \App\Models\OrderItem::whereHas('Order', function($query) {
            $query->where('is_active', true);
        })->whereNotIn('id', function($query) {
            $query->select('order_item_id')
                  ->from('backload_items')
                  ->whereNotNull('order_item_id');
        })->with(['Order.Company', 'ProductItem.product'])
        ->limit(10)
        ->get();

        // Calculate pricing for active rentals
        foreach ($activeRentals as $rental) {
            $pricingInfo = $this->calculateOrderItemPricing($rental, $rental->Order->Company);
            $rental->unit_price = $pricingInfo['unit_price'];
            $rental->duration_days = $pricingInfo['duration_days'];
            $rental->total_price = $pricingInfo['total_price'];
        }

        return view('dashboard.index', compact(
            'totalCompanies',
            'totalOrders',
            'totalProducts',
            'totalProductItems',
            'recentOrders',
            'activeRentals'
        ));
    }
    public function Categories()
    {
        $categories = Category::where('is_active', true)->get();
        return view('dashboard.categories', ['categories' => $categories]);
    }
    public function StoreCategory(Request $request)
    {
        $data = $request->except(['image']);
        $data['is_active'] = true;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        $category = Category::create($data);
        if (!$category->category_code) {
            $category->category_code = (string) $category->id;
            $category->save();
        }
        return redirect()->back();
    }
    public function UpdateCategory(Request $request, Category $Category)
    {
        $data = $request->except(['image']);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        $Category->update($data);
        return redirect()->back();
    }
    public function DeleteCategory(Category $Category)
    {
        $Category->update(['is_active' => false]);
        return redirect()->back();
    }


    public function Companies()
    {
        $companies = Company::where('is_active', 1)->get();
        return view('dashboard.companies', ['companies' => $companies]);
    }
    public function StoreCompany(Request $request)
    {
        $data = $request->except(['image']);
        $data['is_active'] = true;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        Company::create($data);
        return redirect()->back();
    }
    public function UpdateCompany(Request $request, Company $Company)
    {
        $data = $request->except(['image']);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        $Company->update($data);
        return redirect()->back();
    }
    public function DeleteCompany(Company $Company)
    {
        $Company->update(['is_active' => 0]);
        return redirect()->back();
    }
    public function Company(Company $Company)
    {
        // Load orders with their order items and related data
        $Company->load(['Orders.OrderItems.productItem.product']);

        // Calculate total amount for each order
        foreach ($Company->Orders as $order) {
            $totalAmount = 0;

            foreach ($order->OrderItems as $orderItem) {
                $pricingInfo = $this->calculateOrderItemPricing($orderItem, $Company);
                $totalAmount += $pricingInfo['total_price'];
            }

            $order->total_amount = $totalAmount;
        }

        $products = Product::where('is_active', 1)->get();

        return view('dashboard.company', ['Company' => $Company, 'products' => $products]);
    }
    public function StoreEmployee(Request $request,Company $Company)
    {
        $data = $request->except(['company_id']);
        $data['is_active'] = true;
        $data['company_id'] = $Company->id;
        CompanyEmployee::create($data);
        return redirect()->back();
    }
    public function DeleteEmployee(CompanyEmployee $Employee)
    {
        $Employee->update(['is_active' => 0]);
        return redirect()->back();
    }
    public function Products()
    {
        $categories = Category::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        return view('dashboard.products', compact('categories', 'products'));
    }

    public function ProductItems()
    {
        $productItems = ProductItem::with(['product', 'orderItems.order', 'backloadItems'])->where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('dashboard.product_items', compact('productItems', 'products'));
    }

    public function StoreProduct(Request $request)
    {
        $data = $request->except(['image']);
        $data['is_active'] = true;
        $data['daily_price'] = $request->filled('daily_price') ? $request->input('daily_price') : 20;
        $data['weekly_price'] = $request->filled('weekly_price') ? $request->input('weekly_price') : 15;
        $data['monthly_price'] = $request->filled('monthly_price') ? $request->input('monthly_price') : 10;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        $product = Product::create($data);
        if (!$product->product_code) {
            $product->product_code = (string) $product->id;
            $product->save();
        }
        return redirect()->back();
    }
    public function Product(Product $Product)
    {
        // Load product items with their relationships
        $Product->load(['ProductItems.product']);

        return view('dashboard.product', ['Product' => $Product]);
    }
    public function UpdateProduct(Request $request, Product $Product)
    {
        $data = $request->except(['image']);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        $Product->update($data);
        return redirect()->back();
    }
    public function DeleteProduct(Product $Product)
    {
        $Product->update(['is_active' => false]);
        return redirect()->back();
    }

    public function ProductItem(ProductItem $ProductItem)
    {
        $ProductItem->load(['Product', 'Certificates' => function ($q) {
            $q->orderByDesc('created_at');
        }]);
        return view('dashboard.product_item', ['ProductItem' => $ProductItem]);
    }
    public function DownloadProductItemCertificate(ProductItem $ProductItem)
    {
        // Backwards-compatible: download "current" certificate (latest version if available)
        $current = $ProductItem->Certificates()->orderByDesc('created_at')->first();
        $path = $current?->certificate ?: $ProductItem->certificate;

        if (!$path || !Storage::disk('public')->exists($path)) {
            return redirect()->back()->withErrors(['certificate' => 'Certificate file not found.']);
        }

        return Storage::disk('public')->download($path);
    }

    public function DownloadProductItemCertificateVersion(ProductItemCertificate $ProductItemCertificate)
    {
        $path = $ProductItemCertificate->certificate;
        if (!$path || !Storage::disk('public')->exists($path)) {
            return redirect()->back()->withErrors(['certificate' => 'Certificate file not found.']);
        }

        return Storage::disk('public')->download($path);
    }
    public function MarkNotificationAsRead(DatabaseNotification $notification)
    {
        if ($notification->notifiable_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return redirect()->back();
    }
    public function MarkAllNotificationsAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back();
    }
    public function StoreProductItem(Request $request, Product $Product)
    {
        Validator::make($request->all(), [
            'series_number' => ['required', 'string', 'max:255', Rule::unique('product_items', 'series_number')],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'inspection_date' => ['required', 'date'],
            'certificate' => ['nullable', 'file'],
            'product_item_code' => ['nullable', 'string', 'max:255'],
        ])->validateWithBag('createProductItem');

        $data = $request->except(['certificate']);
        $data['is_active'] = true;
        $data['product_id'] = $Product->id;
        $data['status'] = 'In Stock'; // Set default status to In Stock
        $productItem = ProductItem::create($data);
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $filename = Storage::disk('public')->put('/', $file);
            ProductItemCertificate::create([
                'product_item_id' => $productItem->id,
                'certificate' => $filename,
            ]);
            // keep legacy column in sync with "current" certificate
            $productItem->certificate = $filename;
            $productItem->save();
        }
        if (!$productItem->product_item_code) {
            $productItem->product_item_code = (string) $productItem->id;
            $productItem->save();
        }
        return redirect()->back();
    }
    public function DeleteProductItem(ProductItem $ProductItem)
    {
        $ProductItem->update(['is_active' => false]);
        return redirect()->back();
    }
    public function UpdateProductItem(Request $request, ProductItem $ProductItem)
    {
        Validator::make($request->all(), [
            'series_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_items', 'series_number')->ignore($ProductItem->id),
            ],
            'editing_product_item_id' => ['nullable', 'integer'],
            'inspection_date' => ['nullable', 'date'],
            'certificate' => ['nullable', 'file'],
            'product_item_code' => ['nullable', 'string', 'max:255'],
        ])->validateWithBag('updateProductItem');

        $data = $request->except(['editing_product_item_id', 'certificate']);
        $ProductItem->update($data);

        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $filename = Storage::disk('public')->put('/', $file);
            ProductItemCertificate::create([
                'product_item_id' => $ProductItem->id,
                'certificate' => $filename,
            ]);
            // keep legacy column in sync with "current" certificate
            $ProductItem->update(['certificate' => $filename]);
        }
        return redirect()->back();
    }
    // orders
    public function StoreOrderDirect(Request $request)
    {
        $data = $request->except(['delivery_note']);
        $data['is_active'] = true;
        if (isset($data['product_ids']) && is_array($data['product_ids'])) {
            $data['product_ids'] = array_values(array_filter($data['product_ids'], fn ($v) => $v !== null && $v !== ''));
        }
        if (isset($data['product_quantities']) && is_array($data['product_quantities'])) {
            $data['product_quantities'] = array_filter(
                $data['product_quantities'],
                fn ($qty) => $qty !== null && $qty !== '' && (int) $qty > 0
            );
        }

        if ($request->hasFile('delivery_note')) {
            $file = $request->file('delivery_note');
            $filename = Storage::disk('public')->put('/', $file);
            $data['delivery_note'] = $filename;
        }

        $company = null;
        if (!empty($data['company_id'])) {
            $company = Company::find($data['company_id']);
        }

        if ($company) {
            for ($i = 0; $i < 5; $i++) {
                $data['order_number'] = $this->generateOrderNumberForCompany($company);
                try {
                    Order::create($data);
                    return redirect()->route('dashboard.orders');
                } catch (\Illuminate\Database\QueryException $e) {
                    // retry on unique collision
                }
            }
        }

        Order::create($data);
        return redirect()->route('dashboard.orders');
    }
    public function StoreOrder(Request $request,Company $Company)
    {
        $data = $request->except(['delivery_note']);
        $data['is_active'] = true;
        $data['company_id'] = $Company->id;
        if (isset($data['product_ids']) && is_array($data['product_ids'])) {
            $data['product_ids'] = array_values(array_filter($data['product_ids'], fn ($v) => $v !== null && $v !== ''));
        }
        if (isset($data['product_quantities']) && is_array($data['product_quantities'])) {
            $data['product_quantities'] = array_filter(
                $data['product_quantities'],
                fn ($qty) => $qty !== null && $qty !== '' && (int) $qty > 0
            );
        }

        if ($request->hasFile('delivery_note')) {
            $file = $request->file('delivery_note');
            $filename = Storage::disk('public')->put('/', $file);
            $data['delivery_note'] = $filename;
        }

        for ($i = 0; $i < 5; $i++) {
            $data['order_number'] = $this->generateOrderNumberForCompany($Company);
            try {
                Order::create($data);
                return redirect()->back();
            } catch (\Illuminate\Database\QueryException $e) {
                // retry on unique collision
            }
        }

        Order::create($data);
        return redirect()->back();
    }
    public function DeleteOrder(Order $Order)
    {
        // Get all product items affected by this order before deactivating
        $productItems = $Order->orderItems->pluck('productItem')->unique();
        
        $Order->update(['is_active' => 0]);
        
        // Recalculate status for all affected product items
        $statusService = new ProductItemStatusService();
        foreach ($productItems as $productItem) {
            $statusService->updateRentalStatus($productItem);
        }
        
        return redirect()->back();
    }
    public function UpdateOrder(Request $request, Order $Order)
    {
        $data = $request->except(['po_reference', 'attachment']);
        unset($data['order_number']);

        if (isset($data['product_ids']) && is_array($data['product_ids'])) {
            $data['product_ids'] = array_values(array_filter($data['product_ids'], fn ($v) => $v !== null && $v !== ''));
        }
        if (isset($data['product_quantities']) && is_array($data['product_quantities'])) {
            $data['product_quantities'] = array_filter(
                $data['product_quantities'],
                fn ($qty) => $qty !== null && $qty !== '' && (int) $qty > 0
            );
        }

        if ($request->hasFile('po_reference')) {
            $file = $request->file('po_reference');
            $filename = Storage::disk('public')->put('/', $file);
            $data['po_reference'] = $filename;
        }
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = Storage::disk('public')->put('/', $file);
            $data['attachment'] = $filename;
        }
        $Order->update($data);
        return redirect()->back();
    }
    public function Order(Order $Order)
    {
        // Get all product items that are currently on rental (active order items not returned)
        // An item is considered rented if:
        // 1. It has an order item that hasn't been returned via a backload item
        // 2. (If there's no backload item, the order item is still active/rented)

        $rentedProductItemIds = OrderItem::whereNotIn('id', function($query) {
                $query->select('order_item_id')
                      ->from('backload_items')
                      ->whereNotNull('order_item_id');
            })
            ->pluck('product_item_id')
            ->toArray();

        $orderProductIds = is_array($Order->product_ids) ? $Order->product_ids : [];

        // Get available product items that are:
        // 1. Active (is_active = 1)
        // 2. Status is 'In Stock'
        // 3. Not already in this order
        // 4. Not currently rented by any company
        $ProductItems = ProductItem::where('is_active', 1)
            ->where('status', 'In Stock') // Only show items that are in stock
            ->when(!empty($orderProductIds), function ($query) use ($orderProductIds) {
                $query->whereIn('product_id', $orderProductIds);
            }, function ($query) {
                // If order has no selected products, show none to prevent mismatched order items
                $query->whereRaw('1 = 0');
            })
            ->whereNotIn('id', $Order->OrderItems()->pluck('product_item_id')->toArray()) // Not already in this order
            ->whereNotIn('id', $rentedProductItemIds) // Not currently rented by any company
            ->with('product') // Eager load product relationship for better performance
            ->get();

        $timesheetRows = $this->buildTimeSheetRowsForOrder($Order);

        return view('dashboard.order', [
            'Order' => $Order,
            'ProductItems' => $ProductItems,
            'timesheetRows' => $timesheetRows,
        ]);
    }

    public function TimeSheetPdf(Order $Order)
    {
        $Order->load(['Company']);
        $timesheetRows = $this->buildTimeSheetRowsForOrder($Order);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.time_sheet_pdf', [
            'Order' => $Order,
            'timesheetRows' => $timesheetRows,
            'invoiceNumber' => '',
            'vendor' => '',
            'todayDate' => now()->format('d F Y'),
            'clientName' => $Order->Company?->name ?? '',
        ])->setPaper('a4', 'landscape');

        $safeOrderNumber = $Order->order_number ?: ('order_' . $Order->id);
        return $pdf->download('time_sheet_' . $safeOrderNumber . '.pdf');
    }

    public function DeliveryNote(Order $Order)
    {
        $Order->load(['Company', 'OrderItems.productItem.product']);

        $companyName = $Order->Company->name ?? '';
        $clientCode = $this->companyNameInitials($companyName);

        $productIds = is_array($Order->product_ids) ? $Order->product_ids : [];
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $requestedMap = is_array($Order->product_quantities) ? $Order->product_quantities : [];

        $seriesByProductId = [];
        foreach ($Order->OrderItems as $orderItem) {
            $productId = $orderItem->productItem?->product_id;
            $series = $orderItem->productItem?->series_number;
            if ($productId && $series) {
                $seriesByProductId[(int) $productId][] = $series;
            }
        }

        $rows = [];
        $i = 1;
        foreach ($productIds as $productId) {
            $productId = (int) $productId;
            $product = $products->get($productId);
            if (!$product) {
                continue;
            }

            $requestedQty = (int) ($requestedMap[$productId] ?? 0);
            $seriesList = $seriesByProductId[$productId] ?? [];

            $rows[] = [
                'no' => $i++,
                'product_name' => $product->name,
                'requested_qty' => $requestedQty,
                'series' => $seriesList,
            ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.delivery_note', [
            'Order' => $Order,
            'clientCode' => $clientCode,
            'rows' => $rows,
        ]);
        return $pdf->download('delivery_note_order_' . $Order->id . '.pdf');
    }

    public function OrderRequest(Order $Order)
    {
        $Order->load(['Company']);

        $requester = null;
        if (!empty($Order->company_employe_id)) {
            $requester = CompanyEmployee::find($Order->company_employe_id);
        } elseif (!empty($Order->company_id)) {
            // Some older forms store employee id in company_id by mistake
            $requester = CompanyEmployee::find($Order->company_id);
        }

        $productIds = is_array($Order->product_ids) ? $Order->product_ids : [];
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $requestedMap = is_array($Order->product_quantities) ? $Order->product_quantities : [];

        $availableByProductId = ProductItem::query()
            ->where('is_active', 1)
            ->where('status', 'In Stock')
            ->whereIn('product_id', $productIds)
            ->selectRaw('product_id, COUNT(*) as cnt')
            ->groupBy('product_id')
            ->pluck('cnt', 'product_id')
            ->toArray();

        $rows = [];
        $i = 1;
        foreach ($productIds as $productId) {
            $productId = (int) $productId;
            $product = $products->get($productId);
            if (!$product) {
                continue;
            }

            $requestedQty = (int) ($requestedMap[$productId] ?? 0);
            $availableQty = (int) ($availableByProductId[$productId] ?? 0);
            $remarks = $availableQty === 0 ? 'Waiting for new shipment' : '';

            $rows[] = [
                'no' => $i++,
                'product_name' => $product->name,
                'requested_qty' => $requestedQty,
                'available_qty' => $availableQty,
                'remarks' => $remarks,
            ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.order_request', [
            'Order' => $Order,
            'requesterName' => $requester?->name,
            'rows' => $rows,
        ]);

        $safeOrderNumber = $Order->order_number ?: ('order_' . $Order->id);
        return $pdf->download('order_request_' . $safeOrderNumber . '.pdf');
    }

    public function BackloadNote(Backload $Backload)
    {
        $Backload->load(['Company', 'BackloadItems.OrderItem.ProductItem.Product', 'BackloadItems.OrderItem.Order']);

        $companyName = $Backload->Company->name ?? '';
        $clientCode = $this->companyNameInitials($companyName);

        $order = null;
        $firstBackloadItem = $Backload->BackloadItems->first();
        if ($firstBackloadItem?->OrderItem?->Order) {
            $order = $firstBackloadItem->OrderItem->Order;
        }

        $byProduct = [];
        foreach ($Backload->BackloadItems as $backloadItem) {
            $product = $backloadItem->OrderItem?->ProductItem?->Product;
            $productId = $product?->id;
            $series = $backloadItem->OrderItem?->ProductItem?->series_number;
            if (!$productId) {
                continue;
            }

            if (!isset($byProduct[$productId])) {
                $byProduct[$productId] = [
                    'product_name' => $product?->name ?? '',
                    'series' => [],
                ];
            }

            if ($series) {
                $byProduct[$productId]['series'][] = $series;
            }
        }

        $rows = [];
        $i = 1;
        foreach ($byProduct as $data) {
            $series = $data['series'] ?? [];
            $rows[] = [
                'no' => $i++,
                'product_name' => $data['product_name'] ?? '',
                'returned_qty' => count($series),
                'series' => $series,
            ];
        }

        $siteText = implode(', ', array_values(array_filter(array_unique(array_map(
            fn ($r) => (string) ($r['product_name'] ?? ''),
            $rows
        )))));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.backload_note', [
            'Backload' => $Backload,
            'Order' => $order,
            'clientCode' => $clientCode,
            'rows' => $rows,
            'siteText' => $siteText,
        ]);

        return $pdf->download('backload_note_' . $Backload->id . '.pdf');
    }

    private function calculateOrderItemPricing($orderItem, $company)
    {
        // Calculate duration
        $startDate = $orderItem->Order->delivery_date ? \Carbon\Carbon::parse($orderItem->Order->delivery_date) : $orderItem->Order->created_at;
        $startDate = $startDate->startOfDay(); // Normalize to start of day for calendar day calculation

        // Check if this order item has been returned via backload
        $backloadItem = BackloadItem::where('order_item_id', $orderItem->id)->first();

        if ($backloadItem) {
            // Item has been returned, use backload date as end date
            $endDate = \Carbon\Carbon::parse($backloadItem->Backload->date)->startOfDay();
        } else {
            // Item is still active, use current date
            $endDate = now()->startOfDay();
        }

        // Calculate duration: count each calendar day (inclusive of start and end)
        // Example: Day 17 to Day 20 = 4 days (Day 17, 18, 19, 20)
        $durationDays = round($startDate->diffInDays($endDate)) + 1;

        // Get company price list for this product
        $companyPriceList = CompanyPriceList::where('company_id', $company->id)
            ->where('product_id', $orderItem->productItem->product->id)
            ->first();

        // Get product for fallback default prices
        $product = $orderItem->productItem->product;

        // Use company price list if available, otherwise fall back to product default prices
        if ($companyPriceList) {
            $dailyPrice = $companyPriceList->daily_price ?? 0;
            $weeklyPrice = $companyPriceList->weekly_price ?? 0;
            $monthlyPrice = $companyPriceList->monthly_price ?? 0;
        } else {
            // Fall back to product default prices
            $dailyPrice = $product->daily_price ? (float) $product->daily_price : 0;
            $weeklyPrice = $product->weekly_price ? (float) $product->weekly_price : 0;
            $monthlyPrice = $product->monthly_price ? (float) $product->monthly_price : 0;
        }

        // Determine unit price based on company pricing type
        $unitPrice = 0;

        if ($company->pricing_type === 'daily_monthly') {
            // Rule 1: daily_monthly
            if ($durationDays <= 10) {
                $unitPrice = $dailyPrice;
            } else {
                $unitPrice = $monthlyPrice;
            }
        } else {
            // Rule 2: daily_weekly_monthly
            if ($durationDays <= 7) {
                $unitPrice = $dailyPrice;
            } elseif ($durationDays <= 30) {
                $unitPrice = $weeklyPrice;
            } else {
                $unitPrice = $monthlyPrice;
            }
        }

        // Calculate total price
        $totalPrice = $unitPrice * $durationDays;

        return [
            'unit_price' => $unitPrice,
            'duration_days' => $durationDays,
            'total_price' => $totalPrice
        ];
    }
    public function Orders()
    {
        $orders = Order::where('is_active', 1)->with(['Company', 'OrderItems.productItem.product'])->get();

        $companies = Company::where('is_active', 1)->get();
        $employees = CompanyEmployee::where('is_active', 1)->get();
        return view('dashboard.orders', ['orders' => $orders, 'companies' => $companies, 'employees' => $employees]);
    }

    public function OrderItems()
    {
        $orderItems = OrderItem::with(['order.company', 'productItem.product'])->get();
        $orders = Order::where('is_active', 1)->get();
        $productItems = ProductItem::where('is_active', 1)->get();

        // Calculate pricing for each order item using the same method as Order page
        foreach ($orderItems as $orderItem) {
            $pricingInfo = $this->calculateOrderItemPricing($orderItem, $orderItem->order->company);
            $orderItem->unit_price = $pricingInfo['unit_price'];
            $orderItem->duration_days = $pricingInfo['duration_days'];
            $orderItem->total_price = $pricingInfo['total_price'];
        }

        $timesheetAllRows = $this->buildTimeSheetRowsForOrderItems($orderItems);

        return view('dashboard.order_items', compact('orderItems', 'orders', 'productItems', 'timesheetAllRows'));
    }

    public function TimeSheetAllPdf()
    {
        $orderItems = OrderItem::with(['order.company', 'productItem.product'])->get();

        foreach ($orderItems as $orderItem) {
            $company = $orderItem->order?->company;
            if ($company) {
                $pricingInfo = $this->calculateOrderItemPricing($orderItem, $company);
                $orderItem->unit_price = $pricingInfo['unit_price'];
                $orderItem->duration_days = $pricingInfo['duration_days'];
                $orderItem->total_price = $pricingInfo['total_price'];
            }
        }

        $timesheetRows = $this->buildTimeSheetRowsForOrderItems($orderItems);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.time_sheet_all_pdf', [
            'timesheetRows' => $timesheetRows,
            'invoiceNumber' => '',
            'vendor' => '',
            'todayDate' => now()->format('d F Y'),
            'clientName' => '',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('time_sheet_all_order_items.pdf');
    }

    private function calculateOrderItemRentalStatus($orderItem)
    {
        // Check if this order item has been returned in backloads
        $backloadItem = BackloadItem::where('order_item_id', $orderItem->id)->first();

        if ($backloadItem) {
            return 'returned';
        } else {
            return 'active';
        }
    }

    private function calculateRentalDuration($orderItem)
    {
        $startDate = $orderItem->order->created_at ?? now();
        $startDate = $startDate->startOfDay(); // Normalize to start of day for calendar day calculation

        // Check if this order item has been returned via backload
        $backloadItem = BackloadItem::where('order_item_id', $orderItem->id)->first();

        if ($backloadItem) {
            // Item has been returned, use backload date as end date
            $endDate = \Carbon\Carbon::parse($backloadItem->Backload->date)->startOfDay();
            $isActive = false;
        } else {
            // Item is still on rental - calculate from start to now
            $endDate = now()->startOfDay();
            $isActive = true;
        }

        // Calculate duration: count each calendar day (inclusive of start and end)
        // Example: Day 17 to Day 20 = 4 days (Day 17, 18, 19, 20)
        $days = round($startDate->diffInDays($endDate)) + 1;

        return [
            'days' => $days,
            'period' => $days . ' day' . ($days !== 1 ? 's' : ''),
            'is_active' => $isActive
        ];
    }

    private function calculateOrderItemPrice($orderItem)
    {
        if (!$orderItem->calculated_duration) {
            return null;
        }

        $company = $orderItem->order->company;
        $product = $orderItem->productItem->product;
        $days = $orderItem->calculated_duration['days'];

        // Get company price list for this product
        $companyPriceList = CompanyPriceList::where('company_id', $company->id)
            ->where('product_id', $product->id)
            ->first();

        // Use company price list if available, otherwise fall back to product default prices
        if ($companyPriceList) {
            $dailyPrice = $companyPriceList->daily_price ?? 0;
            $weeklyPrice = $companyPriceList->weekly_price ?? 0;
            $monthlyPrice = $companyPriceList->monthly_price ?? 0;
        } else {
            // Fall back to product default prices
            $dailyPrice = $product->daily_price ? (float) $product->daily_price : 0;
            $weeklyPrice = $product->weekly_price ? (float) $product->weekly_price : 0;
            $monthlyPrice = $product->monthly_price ? (float) $product->monthly_price : 0;
        }

        $totalPrice = 0;
        $breakdown = [];

        if ($company->pricing_type === 'daily_monthly') {
            // Rule 1: daily_monthly
            if ($days <= 10) {
                $totalPrice = max(0, $days * $dailyPrice);
                $breakdown[] = "{$days} days × $" . number_format($dailyPrice, 2) . " (daily)";
            } else {
                $totalPrice = max(0, $monthlyPrice);
                $breakdown[] = "1 month × $" . number_format($monthlyPrice, 2) . " (monthly)";
            }
        } else {
            // Rule 2: daily_weekly_monthly
            if ($days <= 7) {
                $totalPrice = max(0, $days * $dailyPrice);
                $breakdown[] = "{$days} days × $" . number_format($dailyPrice, 2) . " (daily)";
            } elseif ($days <= 30) {
                $weeks = ceil($days / 7);
                $totalPrice = max(0, $weeks * $weeklyPrice);
                $breakdown[] = "{$weeks} week" . ($weeks !== 1 ? 's' : '') . " × $" . number_format($weeklyPrice, 2) . " (weekly)";
            } else {
                $months = ceil($days / 30);
                $totalPrice = max(0, $months * $monthlyPrice);
                $breakdown[] = "{$months} month" . ($months !== 1 ? 's' : '') . " × $" . number_format($monthlyPrice, 2) . " (monthly)";
            }
        }

        return [
            'total' => $totalPrice,
            'breakdown' => implode(', ', $breakdown)
        ];
    }
    public function StoreOrderItem(Request $request, Order $Order)
    {
        $validated = Validator::make($request->all(), [
            'product_item_id' => ['required', 'integer', Rule::exists('product_items', 'id')],
        ])->validate();

        $productItemId = (int) $validated['product_item_id'];

        try {
            DB::transaction(function () use ($Order, $productItemId) {
                $productItem = ProductItem::query()
                    ->where('id', $productItemId)
                    ->lockForUpdate()
                    ->first();

                if (!$productItem || !$productItem->is_active) {
                    throw new \RuntimeException('This product item is not available.');
                }

                if (($productItem->status ?? '') !== 'In Stock') {
                    throw new \RuntimeException('This product item is no longer in stock.');
                }

                $alreadyInThisOrder = OrderItem::query()
                    ->where('order_id', $Order->id)
                    ->where('product_item_id', $productItemId)
                    ->exists();

                if ($alreadyInThisOrder) {
                    throw new \RuntimeException('This product item was already added to this order.');
                }

                $orderItem = OrderItem::create([
                    'order_id' => $Order->id,
                    'product_item_id' => $productItemId,
                ]);

                $orderItem->productItem?->update(['inactive_90d_notified_at' => null]);

                // Update ProductItem status (will set it to Under Rental if not returned)
                $statusService = new ProductItemStatusService();
                $statusService->updateRentalStatus($productItem);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['product_item_id' => $e->getMessage()]);
        }

        return redirect()->back();
    }
    public function DeleteOrderItem(OrderItem $OrderItem)
    {
        $productItem = $OrderItem->productItem;
        $OrderItem->delete();
        
        // Recalculate ProductItem status
        $statusService = new ProductItemStatusService();
        $statusService->updateRentalStatus($productItem);
        
        return redirect()->back();
    }
    public function UpdateOrderItem(Request $request,OrderItem $OrderItem)
    {
        $OrderItem->update($request->all());
        return redirect()->back();
    }
    // backloads
    public function StoreBackload(Request $request, Company $Company)
    {
        $data = $request->all();
        $data['is_active'] = true;
        $data['company_id'] = $Company->id;

        for ($i = 0; $i < 5; $i++) {
            $data['backload_number'] = $this->generateBackloadNumberForCompany($Company);
            try {
                Backload::create($data);
                return redirect()->back();
            } catch (\Illuminate\Database\QueryException $e) {
                // retry on unique collision
            }
        }

        Backload::create($data);
        return redirect()->back();
    }
    public function UpdateBackload(Request $request, Backload $Backload)
    {
        $data = $request->all();
        unset($data['backload_number']);
        $Backload->update($data);
        return redirect()->back();
    }
    public function DeleteBackload(Backload $Backload)
    {
        $Backload->update(['is_active' => 0]);
        return redirect()->back();
    }
    public function Backload(Backload $Backload)
    {
        $OrderItems = OrderItem::whereNotIn('id', $Backload->BackloadItems->pluck('order_item_id')->toArray())
            ->whereIn('id', $Backload->Company->OrderItems->pluck('id')->toArray())
            ->get();
        return view('dashboard.backload', ['Backload' => $Backload, 'OrderItems' => $OrderItems]);

    }
    public function Backloads()
    {
        $backloads = Backload::where('is_active', 1)->get();
        return view('dashboard.backloads', ['backloads' => $backloads]);
    }
    public function StoreBackloadItem(Request $request, Backload $Backload)
    {
        $validated = Validator::make($request->all(), [
            'order_item_id' => ['required', 'integer', Rule::exists('order_items', 'id')],
        ])->validate();

        $orderItemId = (int) $validated['order_item_id'];

        try {
            DB::transaction(function () use ($Backload, $orderItemId) {
                $orderItem = OrderItem::query()
                    ->where('id', $orderItemId)
                    ->lockForUpdate()
                    ->first();

                if (!$orderItem) {
                    throw new \RuntimeException('This order item is not available.');
                }

                // Prevent duplicate backload items for the same order item (double-click protection)
                $alreadyBackloaded = BackloadItem::query()
                    ->where('order_item_id', $orderItemId)
                    ->exists();

                if ($alreadyBackloaded) {
                    throw new \RuntimeException('This product item was already backloaded.');
                }

                $productItem = ProductItem::query()
                    ->where('id', (int) $orderItem->product_item_id)
                    ->lockForUpdate()
                    ->first();

                if (!$productItem || !$productItem->is_active) {
                    throw new \RuntimeException('This product item is not available.');
                }

                if (($productItem->status ?? '') === 'Backloaded') {
                    throw new \RuntimeException('This product item is already backloaded.');
                }

                $backloadItem = BackloadItem::create([
                    'backload_id' => $Backload->id,
                    'order_item_id' => $orderItemId,
                ]);

                $backloadItem->orderItem->productItem->update(['inactive_90d_notified_at' => null]);

                // Immediately set status to Backloaded when BackloadItem is created
                $productItem->update(['status' => 'Backloaded']);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['order_item_id' => $e->getMessage()]);
        }

        return redirect()->back();
    }
    public function DeleteBackloadItem(BackloadItem $BackloadItem)
    {
        $productItem = $BackloadItem->orderItem->productItem;
        $BackloadItem->delete();
        
        // Recalculate ProductItem status (might go back to on_rental)
        $statusService = new ProductItemStatusService();
        $statusService->updateRentalStatus($productItem);
        
        return redirect()->back();
    }
    // Company Price Lists
    public function CompanyPriceLists(Company $Company)
    {
        $priceLists = $Company->priceLists()->with('product')->get();
        $products = Product::where('is_active', 1)->get();
        return view('dashboard.company_price_lists', ['Company' => $Company, 'priceLists' => $priceLists, 'products' => $products]);
    }

    public function StoreCompanyPriceList(Request $request, Company $Company)
    {
        $data = $request->all();
        $data['company_id'] = $Company->id;
        $data['is_active'] = true;
        CompanyPriceList::create($data);
        return redirect()->back();
    }

    public function BulkUpdateCompanyPriceLists(Request $request, Company $Company)
    {
        $dailyPrices = $request->input('daily_price', []);
        $weeklyPrices = $request->input('weekly_price', []);
        $monthlyPrices = $request->input('monthly_price', []);

        foreach ($dailyPrices as $productId => $dailyPrice) {
            $weeklyPrice = $weeklyPrices[$productId] ?? 0;
            $monthlyPrice = $monthlyPrices[$productId] ?? 0;

            // Skip if all prices are 0
            if ($dailyPrice == 0 && $weeklyPrice == 0 && $monthlyPrice == 0) {
                continue;
            }

            $existingPriceList = CompanyPriceList::where('company_id', $Company->id)
                ->where('product_id', $productId)
                ->first();

            if ($existingPriceList) {
                // Update existing price list
                $existingPriceList->update([
                    'daily_price' => $dailyPrice,
                    'weekly_price' => $weeklyPrice,
                    'monthly_price' => $monthlyPrice,
                    'pricing_type' => $Company->pricing_type
                ]);
            } else {
                // Create new price list
                CompanyPriceList::create([
                    'company_id' => $Company->id,
                    'product_id' => $productId,
                    'daily_price' => $dailyPrice,
                    'weekly_price' => $weeklyPrice,
                    'monthly_price' => $monthlyPrice,
                    'pricing_type' => $Company->pricing_type,
                    'is_active' => true
                ]);
            }
        }

        return redirect()->back();
    }

    public function UpdateCompanyPriceList(Request $request, CompanyPriceList $CompanyPriceList)
    {
        $CompanyPriceList->update($request->all());
        return redirect()->back();
    }

    public function DeleteCompanyPriceList(CompanyPriceList $CompanyPriceList)
    {
        $CompanyPriceList->update(['is_active' => false]);
        return redirect()->back();
    }





}

