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
use App\Models\ProductItemCertification;
use App\Models\OrderItem;
use App\Models\Backload;
use App\Models\BackloadItem;
use App\Models\CompanyPriceList;
use App\Services\ProductItemStatusService;


class DashboardController extends Controller
{

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
        Category::create($data);
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

        return view('dashboard.company', ['Company' => $Company]);
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
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        Product::create($data);
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
        return view('dashboard.product_item', ['ProductItem' => $ProductItem]);
    }
    public function StoreProductItem(Request $request, Product $Product)
    {
        $data = $request->all();
        $data['is_active'] = true;
        $data['product_id'] = $Product->id;
        $data['status'] = 'In Stock'; // Set default status to In Stock
        ProductItem::create($data);
        return redirect()->back();
    }
    public function DeleteProductItem(ProductItem $ProductItem)
    {
        $ProductItem->update(['is_active' => false]);
        return redirect()->back();
    }
    public function UpdateProductItem(Request $request, ProductItem $ProductItem)
    {
        $ProductItem->update($request->all());
        return redirect()->back();
    }
    public function StoreCertification(Request $request,ProductItem $ProductItem)
    {
        $data = $request->except(['file']);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Storage::disk('public')->put('/', $file);
            $data['file'] = $filename;
        }
        $data['product_item_id'] = $ProductItem->id;
        ProductItemCertification::create($data);
        return redirect()->back();
    }
    public function DeleteCertification(ProductItemCertification $Certification)
    {
        $Certification->delete();
        return redirect()->back();
    }
    // orders
    public function StoreOrderDirect(Request $request)
    {
        $data = $request->except(['delivery_note']);
        $data['is_active'] = true;
        if ($request->hasFile('delivery_note')) {
            $file = $request->file('delivery_note');
            $filename = Storage::disk('public')->put('/', $file);
            $data['delivery_note'] = $filename;
        }
        Order::create($data);
        return redirect()->route('dashboard.orders');
    }
    public function StoreOrder(Request $request,Company $Company)
    {
        $data = $request->except(['delivery_note']);
        $data['is_active'] = true;
        $data['company_id'] = $Company->id;
        if ($request->hasFile('delivery_note')) {
            $file = $request->file('delivery_note');
            $filename = Storage::disk('public')->put('/', $file);
            $data['delivery_note'] = $filename;
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

        // Get available product items that are:
        // 1. Active (is_active = 1)
        // 2. Status is 'In Stock'
        // 3. Not already in this order
        // 4. Not currently rented by any company
        $ProductItems = ProductItem::where('is_active', 1)
            ->where('status', 'In Stock') // Only show items that are in stock
            ->whereNotIn('id', $Order->OrderItems()->pluck('product_item_id')->toArray()) // Not already in this order
            ->whereNotIn('id', $rentedProductItemIds) // Not currently rented by any company
            ->with('product') // Eager load product relationship for better performance
            ->get();

        // Calculate pricing for each order item
        $Order->load(['OrderItems.productItem.product', 'Company']);

        foreach ($Order->OrderItems as $orderItem) {
            $pricingInfo = $this->calculateOrderItemPricing($orderItem, $Order->Company);
            $orderItem->unit_price = $pricingInfo['unit_price'];
            $orderItem->duration_days = $pricingInfo['duration_days'];
            $orderItem->total_price = $pricingInfo['total_price'];
        }

        return view('dashboard.order', ['Order' => $Order, 'ProductItems' => $ProductItems]);
    }

    public function DeliveryNote(Order $Order)
    {
        $Order->load(['Company', 'OrderItems.productItem.product']);

        // Calculate pricing for each order item
        foreach ($Order->OrderItems as $orderItem) {
            $pricingInfo = $this->calculateOrderItemPricing($orderItem, $Order->Company);
            $orderItem->unit_price = $pricingInfo['unit_price'];
            $orderItem->duration_days = $pricingInfo['duration_days'];
            $orderItem->total_price = $pricingInfo['total_price'];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.delivery_note', compact('Order'));
        return $pdf->download('delivery_note_order_' . $Order->id . '.pdf');
    }

    private function calculateOrderItemPricing($orderItem, $company)
    {
        // Calculate duration
        $startDate = $orderItem->Order->delivery_date ? \Carbon\Carbon::parse($orderItem->Order->delivery_date) : $orderItem->Order->created_at;

        // Check if this order item has been returned via backload
        $backloadItem = BackloadItem::where('order_item_id', $orderItem->id)->first();

        if ($backloadItem) {
            // Item has been returned, use backload date as end date
            $endDate = \Carbon\Carbon::parse($backloadItem->Backload->date);
        } else {
            // Item is still active, use current date
            $endDate = now();
        }

        $durationDays = max(1, round($startDate->diffInDays($endDate)));

        // Get company price list for this product
        $companyPriceList = CompanyPriceList::where('company_id', $company->id)
            ->where('product_id', $orderItem->productItem->product->id)
            ->first();

        if (!$companyPriceList) {
            return [
                'unit_price' => 0,
                'duration_days' => $durationDays,
                'total_price' => 0
            ];
        }

        // Determine unit price based on company pricing type
        $unitPrice = 0;

        if ($company->pricing_type === 'daily_monthly') {
            // Rule 1: daily_monthly
            if ($durationDays <= 10) {
                $unitPrice = $companyPriceList->daily_price;
            } else {
                $unitPrice = $companyPriceList->monthly_price;
            }
        } else {
            // Rule 2: daily_weekly_monthly
            if ($durationDays <= 7) {
                $unitPrice = $companyPriceList->daily_price;
            } elseif ($durationDays <= 30) {
                $unitPrice = $companyPriceList->weekly_price;
            } else {
                $unitPrice = $companyPriceList->monthly_price;
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

        // Calculate total amount for each order
        foreach ($orders as $order) {
            $totalAmount = 0;

            foreach ($order->OrderItems as $orderItem) {
                $pricingInfo = $this->calculateOrderItemPricing($orderItem, $order->Company);
                $totalAmount += $pricingInfo['total_price'];
            }

            $order->total_amount = $totalAmount;
        }

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

        return view('dashboard.order_items', compact('orderItems', 'orders', 'productItems'));
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

        // Check if this order item has been returned via backload
        $backloadItem = BackloadItem::where('order_item_id', $orderItem->id)->first();

        if ($backloadItem) {
            // Item has been returned, use backload date as end date
            $endDate = \Carbon\Carbon::parse($backloadItem->Backload->date);
            $isActive = false;
        } else {
            // Item is still on rental - calculate from start to now
            $endDate = now();
            $isActive = true;
        }

        // Ensure we always get positive days
        $days = max(1, round($startDate->diffInDays($endDate)));

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

        if (!$companyPriceList) {
            return null;
        }

        $totalPrice = 0;
        $breakdown = [];

        if ($company->pricing_type === 'daily_monthly') {
            // Rule 1: daily_monthly
            if ($days <= 10) {
                $totalPrice = max(0, $days * $companyPriceList->daily_price);
                $breakdown[] = "{$days} days × $" . number_format($companyPriceList->daily_price, 2) . " (daily)";
            } else {
                $totalPrice = max(0, $companyPriceList->monthly_price);
                $breakdown[] = "1 month × $" . number_format($companyPriceList->monthly_price, 2) . " (monthly)";
            }
        } else {
            // Rule 2: daily_weekly_monthly
            if ($days <= 7) {
                $totalPrice = max(0, $days * $companyPriceList->daily_price);
                $breakdown[] = "{$days} days × $" . number_format($companyPriceList->daily_price, 2) . " (daily)";
            } elseif ($days <= 30) {
                $weeks = ceil($days / 7);
                $totalPrice = max(0, $weeks * $companyPriceList->weekly_price);
                $breakdown[] = "{$weeks} week" . ($weeks !== 1 ? 's' : '') . " × $" . number_format($companyPriceList->weekly_price, 2) . " (weekly)";
            } else {
                $months = ceil($days / 30);
                $totalPrice = max(0, $months * $companyPriceList->monthly_price);
                $breakdown[] = "{$months} month" . ($months !== 1 ? 's' : '') . " × $" . number_format($companyPriceList->monthly_price, 2) . " (monthly)";
            }
        }

        return [
            'total' => $totalPrice,
            'breakdown' => implode(', ', $breakdown)
        ];
    }
    public function StoreOrderItem(Request $request, Order $Order)
    {
        $data = $request->all();
        $data['order_id'] = $Order->id;
        $orderItem = OrderItem::create($data);
        
        // Update ProductItem status
        $statusService = new ProductItemStatusService();
        $statusService->updateRentalStatus($orderItem->productItem);
        
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
        $data = $request->except(['back_load_note']);
        $data['is_active'] = true;
        $data['company_id'] = $Company->id;
        if ($request->hasFile('back_load_note')) {
            $file = $request->file('back_load_note');
            $filename = Storage::disk('public')->put('/', $file);
            $data['back_load_note'] = $filename;
        }
        Backload::create($data);
        return redirect()->back();
    }
    public function UpdateBackload(Request $request, Backload $Backload)
    {
        $data = $request->except(['back_load_note']);
        if ($request->hasFile('back_load_note')) {
            $file = $request->file('back_load_note');
            $filename = Storage::disk('public')->put('/', $file);
            $data['back_load_note'] = $filename;
        }
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
        $data = $request->all();
        $data['backload_id'] = $Backload->id;
        $backloadItem = BackloadItem::create($data);
        
        // Immediately set status to Backloaded when BackloadItem is created
        $backloadItem->orderItem->productItem->update(['status' => 'Backloaded']);
        
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

