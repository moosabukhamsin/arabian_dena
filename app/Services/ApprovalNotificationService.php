<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\UserRole;
use App\Models\Backload;
use App\Models\Category;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductItem;
use App\Models\User;
use App\Notifications\ApprovalDecisionNotification;
use App\Notifications\ApprovalPendingNotification;
use App\Support\ApprovableEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ApprovalNotificationService
{
    /** @var list<UserRole> */
    private const REVIEWER_ROLES = [
        UserRole::Admin,
        UserRole::OperationHead,
        UserRole::OperationCoordinator,
    ];

    public function notifyReviewers(Model $entity, string $action): void
    {
        $actor = Auth::user();

        if (! $actor instanceof User) {
            return;
        }

        $reviewers = User::query()
            ->whereIn('role', self::REVIEWER_ROLES)
            ->where('id', '!=', $actor->id)
            ->get();

        $notification = new ApprovalPendingNotification($entity, $action, $actor);

        foreach ($reviewers as $reviewer) {
            $reviewer->notify($notification);
        }
    }

    public function notifyModifierOfDecision(Model $entity, string $decision): void
    {
        $reviewer = Auth::user();

        if (! $reviewer instanceof User) {
            return;
        }

        if (! in_array($decision, ['approved', 'rejected'], true)) {
            return;
        }

        $modifierId = $entity->modified_by_id;

        if (! $modifierId || (int) $modifierId === (int) $reviewer->id) {
            return;
        }

        $modifier = User::query()->find($modifierId);

        if (! $modifier) {
            return;
        }

        $modifier->notify(new ApprovalDecisionNotification($entity, $decision, $reviewer));
    }

    public function resolveModel(string $entityType, int $id): Model
    {
        $class = ApprovableEntity::modelClass($entityType);

        return $class::query()->findOrFail($id);
    }

    /**
     * Pending items for the approvals page (excludes requests modified by the current user).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function pendingApprovalRows(): Collection
    {
        $rows = collect();
        $currentUserId = Auth::id();

        $append = function (string $type, $query) use ($rows, $currentUserId) {
            $query->where('approval_status', ApprovalStatus::Pending->value);

            if ($currentUserId) {
                $query->where(function ($q) use ($currentUserId) {
                    $q->whereNull('modified_by_id')
                        ->orWhere('modified_by_id', '!=', $currentUserId);
                });
            }

            foreach ($query->with('modifiedBy')->orderByDesc('updated_at')->get() as $model) {
                $rows->push([
                    'entity_type' => $type,
                    'entity_id' => $model->id,
                    'type_label' => ApprovableEntity::typeLabel($type),
                    'label' => ApprovableEntity::displayName($model),
                    'modified_by' => $model->modifiedBy?->name ?? '—',
                    'updated_at' => $model->updated_at,
                    'model' => $model,
                ]);
            }
        };

        $append(ApprovableEntity::TYPE_CATEGORY, Category::query()->where('is_active', true));
        $append(ApprovableEntity::TYPE_PRODUCT, Product::query()->where('is_active', true));
        $append(ApprovableEntity::TYPE_PRODUCT_ITEM, ProductItem::query()->where('is_active', true));
        $append(ApprovableEntity::TYPE_ORDER, Order::query()->where('is_active', 1));
        $append(ApprovableEntity::TYPE_BACKLOAD, Backload::query()->where('is_active', 1));
        $append(ApprovableEntity::TYPE_COMPANY, Company::query()->where('is_active', 1));

        return $rows->sortByDesc(fn (array $row) => $row['updated_at']?->timestamp ?? 0)->values();
    }

    /**
     * Pending items submitted by the current user (profile "Pending Requests").
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function currentUserPendingRequestRows(): Collection
    {
        $rows = collect();
        $currentUserId = Auth::id();

        if (! $currentUserId) {
            return $rows;
        }

        $append = function (string $type, $query) use ($rows, $currentUserId) {
            $query
                ->where('approval_status', ApprovalStatus::Pending->value)
                ->where('modified_by_id', $currentUserId);

            foreach ($query->orderByDesc('updated_at')->get() as $model) {
                $rows->push([
                    'entity_type' => $type,
                    'entity_id' => $model->id,
                    'type_label' => ApprovableEntity::typeLabel($type),
                    'label' => ApprovableEntity::displayName($model),
                    'updated_at' => $model->updated_at,
                ]);
            }
        };

        $append(ApprovableEntity::TYPE_CATEGORY, Category::query()->where('is_active', true));
        $append(ApprovableEntity::TYPE_PRODUCT, Product::query()->where('is_active', true));
        $append(ApprovableEntity::TYPE_PRODUCT_ITEM, ProductItem::query()->where('is_active', true));
        $append(ApprovableEntity::TYPE_ORDER, Order::query()->where('is_active', 1));
        $append(ApprovableEntity::TYPE_BACKLOAD, Backload::query()->where('is_active', 1));
        $append(ApprovableEntity::TYPE_COMPANY, Company::query()->where('is_active', 1));

        return $rows->sortByDesc(fn (array $row) => $row['updated_at']?->timestamp ?? 0)->values();
    }

    public function canCurrentUserReview(Model $model): bool
    {
        if (! $model->isPendingApproval()) {
            return false;
        }

        $currentUserId = Auth::id();

        if ($currentUserId && (int) $model->modified_by_id === (int) $currentUserId) {
            return false;
        }

        return true;
    }

    public function resolveModelForReview(string $entityType, int $id): Model
    {
        $class = ApprovableEntity::modelClass($entityType);

        $query = $class::query()->where('approval_status', ApprovalStatus::Pending->value);

        return match ($entityType) {
            ApprovableEntity::TYPE_CATEGORY => $query->with('modifiedBy')->findOrFail($id),
            ApprovableEntity::TYPE_PRODUCT => $query->with(['modifiedBy', 'Category'])->findOrFail($id),
            ApprovableEntity::TYPE_PRODUCT_ITEM => $query->with(['modifiedBy', 'product'])->findOrFail($id),
            ApprovableEntity::TYPE_ORDER => $query->with(['modifiedBy', 'Company'])->findOrFail($id),
            ApprovableEntity::TYPE_BACKLOAD => $query->with(['modifiedBy', 'Company'])->findOrFail($id),
            ApprovableEntity::TYPE_COMPANY => $query->with('modifiedBy')->findOrFail($id),
            default => $query->with('modifiedBy')->findOrFail($id),
        };
    }

    /**
     * @return list<array{label: string, value: string, type?: string}>
     */
    public function buildReviewFields(Model $model, string $entityType): array
    {
        return match ($entityType) {
            ApprovableEntity::TYPE_CATEGORY => $this->categoryReviewFields($model),
            ApprovableEntity::TYPE_PRODUCT => $this->productReviewFields($model),
            ApprovableEntity::TYPE_PRODUCT_ITEM => $this->productItemReviewFields($model),
            ApprovableEntity::TYPE_ORDER => $this->orderReviewFields($model),
            ApprovableEntity::TYPE_BACKLOAD => $this->backloadReviewFields($model),
            ApprovableEntity::TYPE_COMPANY => $this->companyReviewFields($model),
            default => [],
        };
    }

    /**
     * @return list<array{label: string, value: string, type?: string}>
     */
    private function categoryReviewFields(Category $category): array
    {
        return [
            $this->field('ID', $category->id),
            $this->field('Category Code', $category->category_code),
            $this->field('Name', $category->name),
            $this->imageField('Image', $category->image_url ?? null),
            $this->field('Modified By', $category->modifiedBy?->name),
            $this->field('Last Updated', $category->updated_at?->format('d M Y H:i')),
        ];
    }

    /**
     * @return list<array{label: string, value: string, type?: string}>
     */
    private function productReviewFields(Product $product): array
    {
        return [
            $this->field('ID', $product->id),
            $this->field('Product Code', $product->product_code),
            $this->field('Name', $product->name),
            $this->field('Category', $product->Category?->name),
            $this->field('Description', $product->description),
            $this->field('Daily Price', $product->daily_price),
            $this->field('Weekly Price', $product->weekly_price),
            $this->field('Monthly Price', $product->monthly_price),
            $this->imageField('Image', $product->image_url ?? null),
            $this->field('Modified By', $product->modifiedBy?->name),
            $this->field('Last Updated', $product->updated_at?->format('d M Y H:i')),
        ];
    }

    /**
     * @return list<array{label: string, value: string, type?: string}>
     */
    private function productItemReviewFields(ProductItem $productItem): array
    {
        $certificateUrl = $productItem->certificate
            ? asset('storage/' . $productItem->certificate)
            : null;

        return [
            $this->field('ID', $productItem->id),
            $this->field('Item Code', $productItem->product_item_code),
            $this->field('Series Number', $productItem->series_number),
            $this->field('Product', $productItem->product?->name),
            $this->field('Inventory Status', $productItem->status),
            $this->field('Inspection Date', $productItem->inspection_date),
            $this->linkField('Certificate', $certificateUrl),
            $this->field('Modified By', $productItem->modifiedBy?->name),
            $this->field('Last Updated', $productItem->updated_at?->format('d M Y H:i')),
        ];
    }

    /**
     * @return list<array{label: string, value: string, type?: string}>
     */
    private function orderReviewFields(Order $order): array
    {
        $productLines = $this->formatOrderProducts($order);

        return [
            $this->field('ID', $order->id),
            $this->field('Order Number', $order->order_number),
            $this->field('Company', $order->Company?->name),
            $this->field('Site Code', $order->site_code),
            $this->field('Delivery Date', $order->delivery_date),
            $this->field('Time Slot', $order->time),
            $this->field('Address', $order->address),
            $this->field('PO Number', $order->po_number),
            $this->fileField('PO Reference', $order->po_reference),
            $this->fileField('Attachment', $order->attachment),
            $this->field('Driver Name', $order->driver_name),
            $this->field('Driver Mobile', $order->driver_mobile),
            $this->field('Driver ID Number', $order->driver_id_number),
            $this->field('Truck Number', $order->truck_number),
            $this->field('Order Status', $order->status),
            $this->field('Products Requested', $productLines),
            $this->field('Modified By', $order->modifiedBy?->name),
            $this->field('Last Updated', $order->updated_at?->format('d M Y H:i')),
        ];
    }

    /**
     * @return list<array{label: string, value: string, type?: string}>
     */
    private function backloadReviewFields(Backload $backload): array
    {
        return [
            $this->field('ID', $backload->id),
            $this->field('Backload Number', $backload->backload_number),
            $this->field('Company', $backload->Company?->name),
            $this->field('Date', $backload->date),
            $this->field('Time Slot', $backload->time),
            $this->field('Address', $backload->address),
            $this->field('Remarks', $backload->remarks),
            $this->field('Driver Name', $backload->driver_name),
            $this->field('Driver Mobile', $backload->driver_mobile),
            $this->field('Driver ID Number', $backload->driver_id_number),
            $this->field('Truck Number', $backload->truck_number),
            $this->fileField('Attachment', $backload->attachment),
            $this->field('Modified By', $backload->modifiedBy?->name),
            $this->field('Last Updated', $backload->updated_at?->format('d M Y H:i')),
        ];
    }

    /**
     * @return list<array{label: string, value: string, type?: string}>
     */
    private function companyReviewFields(Company $company): array
    {
        $pricingLabel = $company->pricing_type === 'daily_monthly'
            ? 'Daily & Monthly'
            : 'Daily, Weekly & Monthly';

        return [
            $this->field('ID', $company->id),
            $this->field('Name', $company->name),
            $this->field('Email', $company->email),
            $this->field('Mobile Number', $company->mobile_number),
            $this->field('Address', $company->address),
            $this->field('Pricing Type', $pricingLabel),
            $this->imageField('Logo', $company->image_url ?? null),
            $this->field('Modified By', $company->modifiedBy?->name),
            $this->field('Last Updated', $company->updated_at?->format('d M Y H:i')),
        ];
    }

    private function formatOrderProducts(Order $order): string
    {
        $productIds = $order->product_ids ?? [];
        $quantities = $order->product_quantities ?? [];

        if (! is_array($productIds) || $productIds === []) {
            return '—';
        }

        $products = Product::query()->whereIn('id', $productIds)->get()->keyBy('id');
        $lines = [];

        foreach ($productIds as $index => $productId) {
            $name = $products->get($productId)?->name ?? "Product #{$productId}";
            $qty = is_array($quantities) ? ($quantities[$index] ?? $quantities[$productId] ?? null) : null;
            $lines[] = $qty !== null && $qty !== '' ? "{$name} (qty: {$qty})" : $name;
        }

        return implode("\n", $lines);
    }

    /**
     * @return array{label: string, value: string, type?: string}
     */
    private function field(string $label, mixed $value): array
    {
        if ($value === null || $value === '') {
            return ['label' => $label, 'value' => '—'];
        }

        if ($value instanceof \DateTimeInterface) {
            return ['label' => $label, 'value' => $value->format('d M Y H:i')];
        }

        return ['label' => $label, 'value' => (string) $value, 'type' => str_contains((string) $value, "\n") ? 'multiline' : 'text'];
    }

    /**
     * @return array{label: string, value: string, type: string}
     */
    private function imageField(string $label, ?string $url): array
    {
        return [
            'label' => $label,
            'value' => $url ?? '—',
            'type' => $url ? 'image' : 'text',
        ];
    }

    /**
     * @return array{label: string, value: string, type: string}
     */
    private function linkField(string $label, ?string $url): array
    {
        return [
            'label' => $label,
            'value' => $url ?? '—',
            'type' => $url ? 'link' : 'text',
        ];
    }

    /**
     * @return array{label: string, value: string, type: string}
     */
    private function fileField(string $label, ?string $path): array
    {
        if (! $path) {
            return ['label' => $label, 'value' => '—', 'type' => 'text'];
        }

        $url = str_starts_with($path, 'assets/') ? asset($path) : asset('storage/' . $path);

        return [
            'label' => $label,
            'value' => $url,
            'type' => 'link',
        ];
    }
}
