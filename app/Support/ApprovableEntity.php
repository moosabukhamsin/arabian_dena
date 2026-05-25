<?php

namespace App\Support;

use App\Models\Backload;
use App\Models\Category;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class ApprovableEntity
{
    public const TYPE_CATEGORY = 'category';

    public const TYPE_PRODUCT = 'product';

    public const TYPE_PRODUCT_ITEM = 'product_item';

    public const TYPE_ORDER = 'order';

    public const TYPE_BACKLOAD = 'backload';

    public const TYPE_COMPANY = 'company';

    /** @var array<string, class-string<Model>> */
    private const MODELS = [
        self::TYPE_CATEGORY => Category::class,
        self::TYPE_PRODUCT => Product::class,
        self::TYPE_PRODUCT_ITEM => ProductItem::class,
        self::TYPE_ORDER => Order::class,
        self::TYPE_BACKLOAD => Backload::class,
        self::TYPE_COMPANY => Company::class,
    ];

    public static function typeFromModel(Model $model): string
    {
        foreach (self::MODELS as $type => $class) {
            if ($model instanceof $class) {
                return $type;
            }
        }

        throw new InvalidArgumentException('Model is not an approvable entity.');
    }

    public static function modelClass(string $type): string
    {
        if (! isset(self::MODELS[$type])) {
            throw new InvalidArgumentException("Unknown approvable entity type: {$type}");
        }

        return self::MODELS[$type];
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_CATEGORY => 'Category',
            self::TYPE_PRODUCT => 'Product',
            self::TYPE_PRODUCT_ITEM => 'Product Item',
            self::TYPE_ORDER => 'Order',
            self::TYPE_BACKLOAD => 'Backload',
            self::TYPE_COMPANY => 'Company',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    public static function displayName(Model $model): string
    {
        return match (true) {
            $model instanceof Category => (string) ($model->name ?: "Category #{$model->id}"),
            $model instanceof Product => (string) ($model->name ?: "Product #{$model->id}"),
            $model instanceof ProductItem => (string) ($model->series_number ?: $model->product_item_code ?: "Product Item #{$model->id}"),
            $model instanceof Order => (string) ($model->order_number ?: "Order #{$model->id}"),
            $model instanceof Backload => (string) ($model->backload_number ?: "Backload #{$model->id}"),
            $model instanceof Company => (string) ($model->name ?: "Company #{$model->id}"),
            default => class_basename($model) . " #{$model->id}",
        };
    }

    public static function viewUrl(Model $model, ?string $entityType = null): ?string
    {
        $type = $entityType ?? self::typeFromModel($model);

        return match ($type) {
            self::TYPE_CATEGORY => route('dashboard.categories'),
            self::TYPE_PRODUCT => route('dashboard.product', $model),
            self::TYPE_PRODUCT_ITEM => route('dashboard.product_item', $model),
            self::TYPE_ORDER => route('dashboard.order', $model),
            self::TYPE_BACKLOAD => route('dashboard.backload', $model),
            self::TYPE_COMPANY => route('dashboard.company', $model),
            default => null,
        };
    }
}
