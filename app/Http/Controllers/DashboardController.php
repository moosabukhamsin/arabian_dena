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
use App\Models\User;
use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Services\ApprovalNotificationService;
use App\Services\ProductItemStatusService;
use App\Support\ApprovableEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\DatabaseNotification;
use ZipArchive;


class DashboardController extends Controller
{
    private const TIME_SLOTS = ['Morning', 'Afternoon', 'Evening'];

    private const SUBMITTED_FOR_REVIEW_MESSAGE = 'Your request has been submitted for review by the responsible admin.';

    private function stripApprovalFieldsFromData(array $data): array
    {
        unset($data['approval_status'], $data['authorized_by_id'], $data['modified_by_id']);

        return $data;
    }

    private function markDataAsPendingApproval(array $data): array
    {
        $data = $this->stripApprovalFieldsFromData($data);

        return array_merge($data, [
            'approval_status' => ApprovalStatus::Pending->value,
            'authorized_by_id' => null,
            'modified_by_id' => Auth::id(),
        ]);
    }

    private function notifyApprovalReviewers(Model $entity, string $action): void
    {
        app(ApprovalNotificationService::class)->notifyReviewers($entity, $action);
    }

    private function redirectWithSubmittedForReviewMessage()
    {
        return redirect()->back()->with('success', self::SUBMITTED_FOR_REVIEW_MESSAGE);
    }

    private function redirectToOrdersWithSubmittedForReviewMessage()
    {
        return redirect()->route('dashboard.orders')->with('success', self::SUBMITTED_FOR_REVIEW_MESSAGE);
    }

    private function buildTimeSheetRowsForOrderItems($orderItems): array
    {
        $orderItemIds = $orderItems->pluck('id')->map(fn ($v) => (int) $v)->all();

        $backloadItems = BackloadItem::query()
            ->whereIn('order_item_id', $orderItemIds)
            ->with('Backload')
            ->get();

        $backloadDateByOrderItemId = [];
        $backloadTimeByOrderItemId = [];
        $backloadIdByOrderItemId = [];
        foreach ($backloadItems as $bi) {
            if ($bi->order_item_id) {
                $oid = (int) $bi->order_item_id;
                $backloadDateByOrderItemId[$oid] = $bi->Backload?->date;
                $backloadTimeByOrderItemId[$oid] = $bi->Backload?->time;
                $backloadIdByOrderItemId[$oid] = $bi->backload_id ?? $bi->Backload?->id;
            }
        }

        $rows = [];
        $i = 1;
        foreach ($orderItems as $orderItem) {
            $orderItemId = (int) $orderItem->id;
            $order = $orderItem->order;
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
                'po_number' => $order?->po_number ?? '',
                'delivery_note' => $order ? ('Order: ' . $order->id) : '',
                'delivery_date' => $order?->delivery_date ?: ($order?->created_at?->format('Y-m-d') ?? ''),
                'delivery_time' => $order?->time ?? '',
                'backload_note' => $backloadId !== null ? 'Backload: ' . $backloadId : '',
                'backload_date' => $backloadDate ?? '',
                'time_blkd' => $backloadTimeByOrderItemId[$orderItemId] ?? '',
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

    /**
     * @return array<int, list<string>>
     */
    private function remarksByProductIdFromOrder(Order $order): array
    {
        $remarksByProductId = [];

        foreach ($order->OrderItems as $orderItem) {
            $productId = (int) ($orderItem->productItem?->product_id ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $remark = trim((string) ($orderItem->remarks ?? ''));
            if ($remark === '') {
                continue;
            }

            if (!isset($remarksByProductId[$productId])) {
                $remarksByProductId[$productId] = [];
            }

            if (!in_array($remark, $remarksByProductId[$productId], true)) {
                $remarksByProductId[$productId][] = $remark;
            }
        }

        return $remarksByProductId;
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
        $activeRentals = \App\Models\OrderItem::whereHas('order', function($query) {
            $query->where('is_active', true);
        })->whereNotIn('id', function($query) {
            $query->select('order_item_id')
                  ->from('backload_items')
                  ->whereNotNull('order_item_id');
        })->with(['order.Company', 'ProductItem.product'])
        ->limit(10)
        ->get();

        // Calculate pricing for active rentals
        foreach ($activeRentals as $rental) {
            $pricingInfo = $this->calculateOrderItemPricing($rental, $rental->order->Company);
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
        $categories = Category::where('is_active', true)->visible()->get();
        return view('dashboard.categories', ['categories' => $categories]);
    }
    public function StoreCategory(Request $request)
    {
        $data = $this->markDataAsPendingApproval($request->except(['image']));
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
        $this->notifyApprovalReviewers($category, 'created');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    public function UpdateCategory(Request $request, Category $Category)
    {
        $data = $this->markDataAsPendingApproval($request->except(['image']));
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        $Category->update($data);
        $this->notifyApprovalReviewers($Category, 'updated');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    public function DeleteCategory(Category $Category)
    {
        $Category->update(['is_active' => false]);
        return redirect()->back();
    }


    public function Users()
    {
        $users = User::orderBy('name')->orderBy('id')->get();

        return view('dashboard.users', [
            'users' => $users,
            'roles' => UserRole::options(),
        ]);
    }

    public function UpdateUserRole(Request $request, User $User)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::enum(UserRole::class)],
        ]);

        $newRole = $validated['role'] instanceof UserRole
            ? $validated['role']
            : UserRole::from($validated['role']);

        if ($User->role === $newRole) {
            return redirect()->route('dashboard.users');
        }

        $User->update(['role' => $newRole]);

        return redirect()->route('dashboard.users')->with('success', 'User role updated successfully.');
    }

    public function DeleteUser(Request $request, User $User)
    {
        $deletingSelf = $User->id === Auth::id();

        $User->delete();

        if ($deletingSelf) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return redirect()->route('dashboard.users')->with('success', 'User deleted successfully.');
    }

    public function Companies()
    {
        $companies = Company::where('is_active', 1)->visible()->get();
        return view('dashboard.companies', ['companies' => $companies]);
    }
    public function StoreCompany(Request $request)
    {
        $data = $this->markDataAsPendingApproval($request->except(['image']));
        $data['is_active'] = true;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        $company = Company::create($data);
        $this->notifyApprovalReviewers($company, 'created');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    public function UpdateCompany(Request $request, Company $Company)
    {
        $data = $this->markDataAsPendingApproval($request->except(['image']));
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        $Company->update($data);
        $this->notifyApprovalReviewers($Company, 'updated');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    public function DeleteCompany(Company $Company)
    {
        $Company->update(['is_active' => 0]);
        return redirect()->back();
    }
    public function Company(Company $Company)
    {
        // Load orders with their order items and related data (hide pending approval)
        $Company->load([
            'Orders' => fn ($query) => $query->where('is_active', 1)->visible()->with('OrderItems.productItem.product'),
            'Backloads' => fn ($query) => $query->where('is_active', 1)->visible()->with('BackloadItems'),
        ]);

        // Calculate total amount for each order
        foreach ($Company->Orders as $order) {
            $totalAmount = 0;

            foreach ($order->OrderItems as $orderItem) {
                $pricingInfo = $this->calculateOrderItemPricing($orderItem, $Company);
                $totalAmount += $pricingInfo['total_price'];
            }

            $order->total_amount = $totalAmount;
        }

        $products = Product::where('is_active', 1)->visible()->get();
        $suggestedBackloadNumber = $this->generateBackloadNumberForCompany($Company);

        return view('dashboard.company', [
            'Company' => $Company,
            'products' => $products,
            'suggestedBackloadNumber' => $suggestedBackloadNumber,
        ]);
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
        $categories = Category::where('is_active', true)->visible()->get();
        $products = Product::where('is_active', true)->visible()->get();
        return view('dashboard.products', compact('categories', 'products'));
    }

    public function ProductItems()
    {
        $productItems = ProductItem::with(['product', 'orderItems.order', 'backloadItems'])
            ->where('is_active', true)
            ->visible()
            ->get();
        $products = Product::where('is_active', true)->visible()->get();

        return view('dashboard.product_items', compact('productItems', 'products'));
    }

    public function StoreProduct(Request $request)
    {
        $data = $this->markDataAsPendingApproval($request->except(['image']));
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
        $this->notifyApprovalReviewers($product, 'created');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    public function Product(Product $Product)
    {
        // Load product items with their relationships (hide pending approval)
        $Product->load([
            'ProductItems' => fn ($query) => $query->where('is_active', true)->visible()->with('product'),
        ]);

        return view('dashboard.product', ['Product' => $Product]);
    }
    public function UpdateProduct(Request $request, Product $Product)
    {
        $data = $this->markDataAsPendingApproval($request->except(['image']));
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Storage::disk('public')->put('/', $file);
            $data['image'] = $filename;
        }
        $Product->update($data);
        $this->notifyApprovalReviewers($Product, 'updated');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    public function DeleteProduct(Product $Product)
    {
        $Product->update(['is_active' => false]);
        return redirect()->back();
    }

    public function ProductItem(ProductItem $ProductItem)
    {
        $ProductItem->load(['product', 'Certificates' => function ($q) {
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

    private function latestCertificatePathForProductItem(ProductItem $productItem): ?string
    {
        if (!$productItem->relationLoaded('Certificates')) {
            $productItem->load(['Certificates' => function ($q) {
                $q->orderByDesc('created_at');
            }]);
        }

        $current = $productItem->Certificates->first();
        $path = $current?->certificate ?: $productItem->certificate;

        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        return $path;
    }

    public function DownloadOrderCertificatesZip(Order $Order)
    {
        $Order->load([
            'OrderItems.ProductItem.product',
            'OrderItems.ProductItem.Certificates' => function ($q) {
                $q->orderByDesc('created_at');
            },
        ]);

        $seenProductItemIds = [];
        $filesToAdd = [];

        foreach ($Order->OrderItems as $orderItem) {
            $productItem = $orderItem->ProductItem;
            if (!$productItem || isset($seenProductItemIds[$productItem->id])) {
                continue;
            }
            $seenProductItemIds[$productItem->id] = true;

            $path = $this->latestCertificatePathForProductItem($productItem);
            if ($path) {
                $filesToAdd[] = [
                    'path' => $path,
                    'productItem' => $productItem,
                ];
            }
        }

        if ($filesToAdd === []) {
            return redirect()->back()->withErrors([
                'certificate' => 'No certificates found for the product items in this order.',
            ]);
        }

        if (!class_exists(ZipArchive::class)) {
            return redirect()->back()->withErrors([
                'certificate' => 'ZIP support is not available on this server.',
            ]);
        }

        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir . '/order_' . $Order->id . '_certificates_' . uniqid('', true) . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->withErrors(['certificate' => 'Could not create ZIP file.']);
        }

        $usedNames = [];
        foreach ($filesToAdd as $entry) {
            $fullPath = Storage::disk('public')->path($entry['path']);
            $series = (string) ($entry['productItem']->series_number ?? ('item_' . $entry['productItem']->id));
            $safeBase = preg_replace('/[^A-Za-z0-9._-]+/', '_', trim($series)) ?: ('item_' . $entry['productItem']->id);
            $ext = strtolower(pathinfo($entry['path'], PATHINFO_EXTENSION)) ?: 'pdf';
            $zipName = $safeBase . '.' . $ext;
            $suffix = 1;
            while (isset($usedNames[$zipName])) {
                $zipName = $safeBase . '_' . $suffix . '.' . $ext;
                $suffix++;
            }
            $usedNames[$zipName] = true;
            $zip->addFile($fullPath, $zipName);
        }

        $zip->close();

        $safeOrderNumber = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($Order->order_number ?: ('order_' . $Order->id)));
        $downloadName = $safeOrderNumber . '_certificates.zip';

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
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

        $data = $this->markDataAsPendingApproval($request->except(['certificate']));
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
        $this->notifyApprovalReviewers($productItem, 'created');

        return $this->redirectWithSubmittedForReviewMessage();
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
            'status' => ['required', 'string', Rule::in(['In Stock', 'Under Rental', 'Backloaded', 'Lost', 'Scrap'])],
        ])->validateWithBag('updateProductItem');

        $data = $this->markDataAsPendingApproval($request->except(['editing_product_item_id', 'certificate']));
        $ProductItem->update($data);

        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $filename = Storage::disk('public')->put('/', $file);
            ProductItemCertificate::create([
                'product_item_id' => $ProductItem->id,
                'certificate' => $filename,
            ]);
            // keep legacy column in sync with "current" certificate
            $ProductItem->update($this->markDataAsPendingApproval(['certificate' => $filename]));
        }
        $ProductItem->refresh();
        $this->notifyApprovalReviewers($ProductItem, 'updated');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    // orders
    public function StoreOrderDirect(Request $request)
    {
        $data = $this->markDataAsPendingApproval($request->except(['delivery_note']));
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
                    $order = Order::create($data);
                    $this->notifyApprovalReviewers($order, 'created');

                    return $this->redirectToOrdersWithSubmittedForReviewMessage();
                } catch (\Illuminate\Database\QueryException $e) {
                    // retry on unique collision
                }
            }
        }

        $order = Order::create($data);
        $this->notifyApprovalReviewers($order, 'created');

        return $this->redirectToOrdersWithSubmittedForReviewMessage();
    }
    public function StoreOrder(Request $request,Company $Company)
    {
        $data = $this->markDataAsPendingApproval($request->except(['delivery_note']));
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
                $order = Order::create($data);
                $this->notifyApprovalReviewers($order, 'created');

                return $this->redirectWithSubmittedForReviewMessage();
            } catch (\Illuminate\Database\QueryException $e) {
                // retry on unique collision
            }
        }

        $order = Order::create($data);
        $this->notifyApprovalReviewers($order, 'created');

        return $this->redirectWithSubmittedForReviewMessage();
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
        $request->validate([
            'order_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('orders', 'order_number')->ignore($Order->id),
            ],
            'po_number' => ['nullable', 'string', 'max:255'],
            'time' => ['nullable', 'string', Rule::in(self::TIME_SLOTS)],
        ]);

        $data = $this->markDataAsPendingApproval($request->except(['po_reference', 'attachment']));

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
        $data['time'] = $request->input('time') ?: null;
        $Order->update($data);
        $this->notifyApprovalReviewers($Order, 'updated');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    public function Order(Order $Order)
    {
        $ProductItems = $this->availableProductItemsForOrder($Order);

        $Order->load([
            'Company',
            'OrderItems.ProductItem.product',
            'OrderItems.ProductItem.Certificates' => function ($q) {
                $q->orderByDesc('created_at');
            },
        ]);

        foreach ($Order->OrderItems as $orderItem) {
            $pricingInfo = $this->calculateOrderItemPricing($orderItem, $Order->Company);
            $orderItem->unit_price = $pricingInfo['unit_price'];
            $orderItem->duration_days = $pricingInfo['duration_days'];
            $orderItem->total_price = $pricingInfo['total_price'];
        }

        return view('dashboard.order', [
            'Order' => $Order,
            'ProductItems' => $ProductItems,
        ]);
    }

    public function DeliveryNote(Order $Order)
    {
        $Order->load(['Company', 'OrderItems.productItem.product', 'authorizedBy']);

        $companyName = $Order->Company->name ?? '';
        $clientCode = $this->companyNameInitials($companyName);

        $productIds = is_array($Order->product_ids) ? $Order->product_ids : [];
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $requestedMap = is_array($Order->product_quantities) ? $Order->product_quantities : [];

        $rows = [];
        $i = 1;

        if ($Order->OrderItems->isNotEmpty()) {
            foreach ($Order->OrderItems as $orderItem) {
                $productItem = $orderItem->productItem;
                $product = $productItem?->product;
                $series = $productItem?->series_number;

                $rows[] = [
                    'no' => $i++,
                    'product_name' => $product?->name ?? '',
                    'requested_qty' => 1,
                    'series' => $series ? [$series] : [],
                    'remarks' => trim((string) ($orderItem->remarks ?? '')),
                ];
            }
        } else {
            foreach ($productIds as $productId) {
                $productId = (int) $productId;
                $product = $products->get($productId);
                if (!$product) {
                    continue;
                }

                $rows[] = [
                    'no' => $i++,
                    'product_name' => $product->name,
                    'requested_qty' => (int) ($requestedMap[$productId] ?? 0),
                    'series' => [],
                    'remarks' => '',
                ];
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.delivery_note', [
            'Order' => $Order,
            'clientCode' => $clientCode,
            'rows' => $rows,
            'currentUserName' => auth()->user()?->name ?? '',
        ]);
        return $pdf->download('delivery_note_order_' . $Order->id . '.pdf');
    }

    public function OrderRequest(Order $Order)
    {
        $Order->load(['Company', 'OrderItems.productItem']);

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

        $remarksByProductId = $this->remarksByProductIdFromOrder($Order);

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
            $remarks = implode('; ', $remarksByProductId[$productId] ?? []);

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
        $Backload->load(['Company', 'BackloadItems.OrderItem.ProductItem.product', 'BackloadItems.OrderItem.Order', 'authorizedBy']);

        $companyName = $Backload->Company->name ?? '';
        $clientCode = $this->companyNameInitials($companyName);

        $order = null;
        $firstBackloadItem = $Backload->BackloadItems->first();
        if ($firstBackloadItem?->OrderItem?->order) {
            $order = $firstBackloadItem->OrderItem->order;
        }

        $byProduct = [];
        foreach ($Backload->BackloadItems as $backloadItem) {
            $product = $backloadItem->OrderItem?->ProductItem?->product;
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

        $siteCodes = [];
        foreach ($Backload->BackloadItems as $backloadItem) {
            $site = trim((string) ($backloadItem->OrderItem?->order?->site_code ?? ''));
            if ($site !== '') {
                $siteCodes[$site] = true;
            }
        }
        $siteText = implode(', ', array_keys($siteCodes));

        $poNumbers = [];
        foreach ($Backload->BackloadItems as $backloadItem) {
            $poNumber = trim((string) ($backloadItem->OrderItem?->order?->po_number ?? ''));
            if ($poNumber !== '') {
                $poNumbers[$poNumber] = true;
            }
        }
        $poNumbersText = implode(', ', array_keys($poNumbers));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.backload_note', [
            'Backload' => $Backload,
            'Order' => $order,
            'clientCode' => $clientCode,
            'rows' => $rows,
            'siteText' => $siteText,
            'poNumbersText' => $poNumbersText,
            'currentUserName' => auth()->user()?->name ?? '',
        ]);

        return $pdf->download('backload_note_' . $Backload->id . '.pdf');
    }

    private function calculateOrderItemPricing($orderItem, $company)
    {
        // Calculate duration
        $startDate = $orderItem->order->delivery_date ? \Carbon\Carbon::parse($orderItem->order->delivery_date) : $orderItem->order->created_at;
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
            // Day / Monthly: 1–10 daily; 11–30 monthly (spec); >30 uses same monthly rate
            if ($durationDays <= 10) {
                $unitPrice = $dailyPrice;
            } else {
                $unitPrice = $monthlyPrice;
            }
        } else {
            // Day / Weekly / Monthly: 1–7 daily; 8–14 weekly; 15–30 monthly (spec); >30 same monthly rate
            if ($durationDays <= 7) {
                $unitPrice = $dailyPrice;
            } elseif ($durationDays <= 14) {
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
        $orders = Order::where('is_active', 1)->visible()->with(['Company', 'OrderItems.productItem.product'])->get();

        $companies = Company::where('is_active', 1)->visible()->get();
        $employees = CompanyEmployee::where('is_active', 1)->get();
        return view('dashboard.orders', ['orders' => $orders, 'companies' => $companies, 'employees' => $employees]);
    }

    public function OrderItems()
    {
        $orderItems = OrderItem::with(['order.company', 'productItem.product'])
            ->whereHas('order', fn ($query) => $query->visible())
            ->get();
        $orders = Order::where('is_active', 1)->visible()->get();
        $productItems = ProductItem::where('is_active', 1)->visible()->get();

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
        $orderItems = OrderItem::with(['order.company', 'productItem.product'])
            ->whereHas('order', fn ($query) => $query->visible())
            ->get();

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
            // Day / Monthly: 1–10 daily; 11–30 monthly (spec); >30 same monthly rate
            if ($days <= 10) {
                $totalPrice = max(0, $days * $dailyPrice);
                $breakdown[] = "{$days} days × $" . number_format($dailyPrice, 2) . " (daily)";
            } else {
                $totalPrice = max(0, $days * $monthlyPrice);
                $breakdown[] = "{$days} days × $" . number_format($monthlyPrice, 2) . " (monthly)";
            }
        } else {
            // Day / Weekly / Monthly: 1–7 daily; 8–14 weekly; 15–30 monthly (spec); >30 same monthly rate
            if ($days <= 7) {
                $totalPrice = max(0, $days * $dailyPrice);
                $breakdown[] = "{$days} days × $" . number_format($dailyPrice, 2) . " (daily)";
            } elseif ($days <= 14) {
                $totalPrice = max(0, $days * $weeklyPrice);
                $breakdown[] = "{$days} days × $" . number_format($weeklyPrice, 2) . " (weekly)";
            } else {
                $totalPrice = max(0, $days * $monthlyPrice);
                $breakdown[] = "{$days} days × $" . number_format($monthlyPrice, 2) . " (monthly)";
            }
        }

        return [
            'total' => $totalPrice,
            'breakdown' => implode(', ', $breakdown)
        ];
    }
    /**
     * Product items that can be added to an order (in stock, approved, not rented elsewhere).
     */
    private function availableProductItemsForOrder(Order $order)
    {
        $rentedProductItemIds = OrderItem::query()
            ->whereNotIn('id', function ($query) {
                $query->select('order_item_id')
                    ->from('backload_items')
                    ->whereNotNull('order_item_id');
            })
            ->pluck('product_item_id')
            ->filter()
            ->all();

        $orderProductIds = $this->normalizeOrderProductIds($order->product_ids);

        $alreadyOnOrderIds = $order->OrderItems()->pluck('product_item_id')->filter()->all();

        return ProductItem::query()
            ->where('is_active', true)
            ->visible()
            ->where('status', 'In Stock')
            ->when(
                $orderProductIds !== [],
                fn ($query) => $query->whereIn('product_id', $orderProductIds)
            )
            ->whereNotIn('id', $alreadyOnOrderIds)
            ->when(
                $rentedProductItemIds !== [],
                fn ($query) => $query->whereNotIn('id', $rentedProductItemIds)
            )
            ->with([
                'product',
                'Certificates' => fn ($q) => $q->orderByDesc('created_at'),
            ])
            ->orderBy('series_number')
            ->get();
    }

    /**
     * @return list<int>
     */
    private function normalizeOrderProductIds(mixed $productIds): array
    {
        if (! is_array($productIds)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($id) => (int) $id,
            $productIds
        ), fn ($id) => $id > 0)));
    }

    private function addProductItemToOrder(Order $order, int $productItemId): void
    {
        $productItem = ProductItem::query()
            ->where('id', $productItemId)
            ->visible()
            ->lockForUpdate()
            ->first();

        if (! $productItem || ! $productItem->is_active) {
            throw new \RuntimeException('This product item is not available.');
        }

        if (($productItem->status ?? '') !== 'In Stock') {
            throw new \RuntimeException('This product item is no longer in stock.');
        }

        $orderProductIds = $this->normalizeOrderProductIds($order->product_ids);
        if ($orderProductIds !== [] && ! in_array((int) $productItem->product_id, $orderProductIds, true)) {
            throw new \RuntimeException('This product item does not belong to a product on this order.');
        }

        $alreadyInThisOrder = OrderItem::query()
            ->where('order_id', $order->id)
            ->where('product_item_id', $productItemId)
            ->exists();

        if ($alreadyInThisOrder) {
            throw new \RuntimeException('This product item was already added to this order.');
        }

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_item_id' => $productItemId,
        ]);

        $orderItem->productItem?->update(['inactive_90d_notified_at' => null]);

        $statusService = new ProductItemStatusService();
        $statusService->updateRentalStatus($productItem);
    }

    public function StoreOrderItem(Request $request, Order $Order)
    {
        $validated = Validator::make($request->all(), [
            'product_item_id' => ['required', 'integer', Rule::exists('product_items', 'id')],
        ])->validate();

        try {
            DB::transaction(function () use ($Order, $validated) {
                $this->addProductItemToOrder($Order, (int) $validated['product_item_id']);
            });
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['product_item_id' => $e->getMessage()]);
        }

        return redirect()->back();
    }

    public function StoreOrderItems(Request $request, Order $Order)
    {
        $validated = Validator::make($request->all(), [
            'product_item_ids' => ['required', 'array', 'min:1'],
            'product_item_ids.*' => ['integer', Rule::exists('product_items', 'id')],
        ], [
            'product_item_ids.required' => 'Select at least one product item.',
            'product_item_ids.min' => 'Select at least one product item.',
        ])->validate();

        $productItemIds = array_values(array_unique(array_map('intval', $validated['product_item_ids'])));
        $added = 0;
        $errors = [];

        foreach ($productItemIds as $productItemId) {
            try {
                DB::transaction(function () use ($Order, $productItemId) {
                    $this->addProductItemToOrder($Order, $productItemId);
                });
                $added++;
            } catch (\RuntimeException $e) {
                $errors[] = "#{$productItemId}: {$e->getMessage()}";
            }
        }

        if ($added === 0) {
            return redirect()->back()->withErrors([
                'product_item_ids' => $errors ?: ['No product items could be added.'],
            ]);
        }

        if ($errors !== []) {
            return redirect()->back()->withErrors([
                'product_item_ids' => 'Some items were skipped: ' . implode(' ', $errors),
            ]);
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
        $request->validate([
            'backload_number' => ['nullable', 'string', 'max:255', Rule::unique('backloads', 'backload_number')],
            'time' => ['nullable', 'string', Rule::in(self::TIME_SLOTS)],
        ]);

        $data = $this->markDataAsPendingApproval($request->all());
        $data['is_active'] = true;
        $data['company_id'] = $Company->id;
        $data['time'] = $request->input('time') ?: null;

        $backloadNumber = trim((string) ($data['backload_number'] ?? ''));
        unset($data['backload_number']);

        if ($backloadNumber === '') {
            for ($i = 0; $i < 5; $i++) {
                $data['backload_number'] = $this->generateBackloadNumberForCompany($Company);
                try {
                    $backload = Backload::create($data);
                    $this->notifyApprovalReviewers($backload, 'created');

                    return $this->redirectWithSubmittedForReviewMessage();
                } catch (\Illuminate\Database\QueryException $e) {
                    // retry on unique collision
                }
            }

            $data['backload_number'] = $this->generateBackloadNumberForCompany($Company);
            $backload = Backload::create($data);
            $this->notifyApprovalReviewers($backload, 'created');

            return $this->redirectWithSubmittedForReviewMessage();
        }

        $data['backload_number'] = $backloadNumber;
        $backload = Backload::create($data);
        $this->notifyApprovalReviewers($backload, 'created');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    public function UpdateBackload(Request $request, Backload $Backload)
    {
        $request->validate([
            'backload_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('backloads', 'backload_number')->ignore($Backload->id),
            ],
            'time' => ['nullable', 'string', Rule::in(self::TIME_SLOTS)],
        ]);

        $data = $this->markDataAsPendingApproval($request->all());
        $data['backload_number'] = trim((string) ($data['backload_number'] ?? '')) ?: null;
        $data['time'] = $request->input('time') ?: null;

        $Backload->update($data);
        $this->notifyApprovalReviewers($Backload, 'updated');

        return $this->redirectWithSubmittedForReviewMessage();
    }
    public function DeleteBackload(Backload $Backload)
    {
        $Backload->update(['is_active' => 0]);
        return redirect()->back();
    }
    public function Backload(Backload $Backload)
    {
        $Backload->load([
            'BackloadItems.OrderItem.ProductItem.product',
            'BackloadItems.OrderItem.Order',
            'Company',
        ]);

        $backloadOrderItemIds = $Backload->BackloadItems
            ->pluck('order_item_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $OrderItems = OrderItem::query()
            ->whereNotIn('id', $backloadOrderItemIds)
            ->whereIn('id', $Backload->Company->OrderItems()->pluck('order_items.id'))
            ->with(['ProductItem.product', 'order'])
            ->get();

        return view('dashboard.backload', ['Backload' => $Backload, 'OrderItems' => $OrderItems]);
    }
    public function Backloads()
    {
        $backloads = Backload::where('is_active', 1)->visible()->get();
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

    public function PendingApprovals(ApprovalNotificationService $approvalService)
    {
        Gate::authorize('approve-pending');

        return view('dashboard.pending_approvals', [
            'pendingRows' => $approvalService->pendingApprovalRows(),
        ]);
    }

    public function ReviewPending(string $entityType, int $id, ApprovalNotificationService $approvalService)
    {
        Gate::authorize('approve-pending');

        $model = $approvalService->resolveModelForReview($entityType, $id);

        if (! $approvalService->canCurrentUserReview($model)) {
            abort(403, 'You cannot review this request.');
        }

        return view('dashboard.partials.pending_approval_review_body', [
            'entityType' => $entityType,
            'entityId' => $id,
            'typeLabel' => ApprovableEntity::typeLabel($entityType),
            'displayName' => ApprovableEntity::displayName($model),
            'fields' => $approvalService->buildReviewFields($model, $entityType),
        ]);
    }

    public function ApprovePending(string $entityType, int $id)
    {
        Gate::authorize('approve-pending');

        $model = app(ApprovalNotificationService::class)->resolveModel($entityType, $id);

        if (! $model->isPendingApproval()) {
            return redirect()->route('dashboard.pending_approvals')
                ->with('error', 'This item is no longer pending approval.');
        }

        $model->update([
            'approval_status' => ApprovalStatus::Approved->value,
            'authorized_by_id' => Auth::id(),
        ]);
        $model->refresh();
        app(ApprovalNotificationService::class)->notifyModifierOfDecision($model, 'approved');

        return redirect()->route('dashboard.pending_approvals')
            ->with('success', ApprovableEntity::typeLabel($entityType) . ' approved successfully.');
    }

    public function RejectPending(string $entityType, int $id)
    {
        Gate::authorize('approve-pending');

        $model = app(ApprovalNotificationService::class)->resolveModel($entityType, $id);

        if (! $model->isPendingApproval()) {
            return redirect()->route('dashboard.pending_approvals')
                ->with('error', 'This item is no longer pending approval.');
        }

        $model->update([
            'approval_status' => ApprovalStatus::Rejected->value,
            'authorized_by_id' => Auth::id(),
        ]);
        $model->refresh();
        app(ApprovalNotificationService::class)->notifyModifierOfDecision($model, 'rejected');

        return redirect()->route('dashboard.pending_approvals')
            ->with('success', ApprovableEntity::typeLabel($entityType) . ' rejected.');
    }
}

