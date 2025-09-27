<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Company;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCategory;
use App\Models\UserAddress;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\Banner;
use App\Models\Story;
use App\Models\Setting;

class RemoteController extends Controller
{
    public function checkAccess()
    {
        return response()->json(['message' => 'Access granted'], 200);
    }

    public function getSchema()
    {
        return response()->json([
            'tables' => [
                [
                    'name' => 'companies',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true],
                        ['name' => 'description', 'type' => 'text'],
                        ['name' => 'country', 'type' => 'string'],
                        ['name' => 'state', 'type' => 'string'],
                        ['name' => 'city', 'type' => 'string'],
                        ['name' => 'street', 'type' => 'string'],
                        ['name' => 'house', 'type' => 'string'],
                        ['name' => 'phone', 'type' => 'string'],
                        ['name' => 'phone_additional', 'type' => 'string'],
                        ['name' => 'email_address', 'type' => 'email'],
                        ['name' => 'logo', 'type' => 'string']
                    ]
                ],
                [
                    'name' => 'users',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true],
                        ['name' => 'email', 'type' => 'email', 'required' => true],
                        ['name' => 'tel', 'type' => 'string'],
                        ['name' => 'password', 'type' => 'password', 'required' => true],
                        ['name' => 'dob', 'type' => 'date']
                    ]
                ],
                [
                    'name' => 'products',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true],
                        ['name' => 'consist', 'type' => 'text'],
                        ['name' => 'weight', 'type' => 'decimal'],
                        ['name' => 'price', 'type' => 'decimal', 'required' => true],
                        ['name' => 'product_category_id', 'type' => 'integer'],
                        ['name' => 'visible', 'type' => 'boolean'],
                        ['name' => 'sku', 'type' => 'string']
                    ]
                ],
                [
                    'name' => 'orders',
                    'fields' => [
                        ['name' => 'discriminator', 'type' => 'string'],
                        ['name' => 'name', 'type' => 'string', 'required' => true],
                        ['name' => 'tel', 'type' => 'string', 'required' => true],
                        ['name' => 'full_address', 'type' => 'text'],
                        ['name' => 'street', 'type' => 'string'],
                        ['name' => 'house', 'type' => 'string'],
                        ['name' => 'building', 'type' => 'string'],
                        ['name' => 'staircase', 'type' => 'string'],
                        ['name' => 'floor', 'type' => 'string'],
                        ['name' => 'apartment', 'type' => 'string'],
                        ['name' => 'total', 'type' => 'decimal'],
                        ['name' => 'delivery', 'type' => 'decimal'],
                        ['name' => 'user_id', 'type' => 'integer'],
                        ['name' => 'payType', 'type' => 'string'],
                        ['name' => 'personQty', 'type' => 'integer'],
                        ['name' => 'comment', 'type' => 'text'],
                        ['name' => 'eatsId', 'type' => 'string'],
                        ['name' => 'restaurantId', 'type' => 'string'],
                        ['name' => 'latitude', 'type' => 'decimal'],
                        ['name' => 'longitude', 'type' => 'decimal'],
                        ['name' => 'deliveryDate', 'type' => 'datetime'],
                        ['name' => 'deliveryType', 'type' => 'string'],
                        ['name' => 'itemsCost', 'type' => 'decimal'],
                        ['name' => 'deliveryFee', 'type' => 'decimal'],
                        ['name' => 'change', 'type' => 'decimal'],
                        ['name' => 'promos', 'type' => 'json'],
                        ['name' => 'deliveryAddress', 'type' => 'text']
                    ]
                ],
                [
                    'name' => 'order_items',
                    'fields' => [
                        ['name' => 'order_id', 'type' => 'integer', 'required' => true],
                        ['name' => 'product_id', 'type' => 'integer', 'required' => true],
                        ['name' => 'qty', 'type' => 'integer', 'required' => true],
                        ['name' => 'sku', 'type' => 'string'],
                        ['name' => 'price', 'type' => 'decimal'],
                        ['name' => 'modifications', 'type' => 'text']
                    ]
                ],
                [
                    'name' => 'product_categories',
                    'fields' => [
                        ['name' => 'uri', 'type' => 'string'],
                        ['name' => 'name', 'type' => 'string', 'required' => true]
                    ]
                ],
                [
                    'name' => 'user_addresses',
                    'fields' => [
                        ['name' => 'user_id', 'type' => 'integer', 'required' => true],
                        ['name' => 'street', 'type' => 'string'],
                        ['name' => 'house', 'type' => 'string'],
                        ['name' => 'building', 'type' => 'string'],
                        ['name' => 'staircase', 'type' => 'string'],
                        ['name' => 'floor', 'type' => 'string'],
                        ['name' => 'apartment', 'type' => 'string']
                    ]
                ],
                [
                    'name' => 'modifiers',
                    'fields' => [
                        ['name' => 'group_id', 'type' => 'integer'],
                        ['name' => 'name', 'type' => 'string', 'required' => true],
                        ['name' => 'price', 'type' => 'decimal'],
                        ['name' => 'vat', 'type' => 'decimal'],
                        ['name' => 'min_amount', 'type' => 'integer'],
                        ['name' => 'max_amount', 'type' => 'integer'],
                        ['name' => 'quantity', 'type' => 'integer'],
                        ['name' => 'visible', 'type' => 'boolean']
                    ]
                ],
                [
                    'name' => 'modifier_groups',
                    'fields' => [
                        ['name' => 'name', 'type' => 'string', 'required' => true]
                    ]
                ],
                [
                    'name' => 'banners',
                    'fields' => [
                        ['name' => 'title', 'type' => 'string'],
                        ['name' => 'description', 'type' => 'text'],
                        ['name' => 'image', 'type' => 'string'],
                        ['name' => 'link', 'type' => 'string'],
                        ['name' => 'active', 'type' => 'boolean']
                    ]
                ],
                [
                    'name' => 'stories',
                    'fields' => [
                        ['name' => 'title', 'type' => 'string'],
                        ['name' => 'content', 'type' => 'text'],
                        ['name' => 'image', 'type' => 'string'],
                        ['name' => 'active', 'type' => 'boolean']
                    ]
                ],
                [
                    'name' => 'settings',
                    'fields' => [
                        ['name' => 'key', 'type' => 'string', 'required' => true],
                        ['name' => 'value', 'type' => 'text']
                    ]
                ]
            ]
        ], 200);
    }

    /**
     * Получить список записей из таблицы с Eloquent
     */
    public function getRecords(Request $request, $table)
    {
        $model = $this->getModel($table);

        if (!$model) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        $page = $request->get('page', 1);
        $limit = min($request->get('limit', 20), 100);
        $with = $request->get('with', []); // для загрузки связей

        try {
            $query = $model::query();
            if (!empty($with)) {
                $query->with($with);
            }

            // Добавляем фильтры если есть
            if ($request->has('filters')) {
                $filters = $request->get('filters');
                foreach ($filters as $field => $value) {
                    if (is_array($value)) {
                        $query->whereIn($field, $value);
                    } else {
                        $query->where($field, 'like', "%{$value}%");
                    }
                }
            }

            // Сортировка
            if ($request->has('sort')) {
                $sort = $request->get('sort');
                $direction = $request->get('direction', 'asc');
                $query->orderBy($sort, $direction);
            }

            $records = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'data' => $records->items(),
                'pagination' => [
                    'page' => $records->currentPage(),
                    'limit' => $records->perPage(),
                    'total' => $records->total(),
                    'pages' => $records->lastPage(),
                    'has_more' => $records->hasMorePages()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Получить одну запись по ID с Eloquent
     */
    public function getRecord($table, $id)
    {
        $model = $this->getModel($table);

        if (!$model) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        try {
            $record = $model::find($id);

            if (!$record) {
                return response()->json(['error' => 'Record not found'], 404);
            }

            return response()->json(['data' => $record], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Создать новую запись с Eloquent
     */
    public function createRecord(Request $request, $table)
    {
        $model = $this->getModel($table);

        if (!$model) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        $validationRules = $this->getValidationRules($table);
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->except(['_token']);
            $record = $model::create($data);

            return response()->json(['data' => $record], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Обновить запись с Eloquent
     */
    public function updateRecord(Request $request, $table, $id)
    {
        $model = $this->getModel($table);

        if (!$model) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        $record = $model::find($id);
        if (!$record) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $validationRules = $this->getValidationRules($table, $id);
        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->except(['_token', '_method']);
            $record->update($data);

            return response()->json(['data' => $record->fresh()], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Удалить запись с Eloquent
     */
    public function deleteRecord($table, $id)
    {
        $model = $this->getModel($table);

        if (!$model) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        try {
            $record = $model::find($id);

            if (!$record) {
                return response()->json(['error' => 'Record not found'], 404);
            }

            // Для моделей с SoftDeletes используем soft delete
            if (method_exists($record, 'delete')) {
                $record->delete();
            } else {
                $record->forceDelete();
            }

            return response()->json(['message' => 'Record deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Получить модель по названию таблицы
     */
    private function getModel($table)
    {
        $models = [
            'companies' => Company::class,
            'users' => User::class,
            'products' => Product::class,
            'orders' => Order::class,
            'order_items' => OrderItem::class,
            'product_categories' => ProductCategory::class,
            'user_addresses' => UserAddress::class,
            'modifiers' => Modifier::class,
            'modifier_groups' => ModifierGroup::class,
            'banners' => Banner::class,
            'stories' => Story::class,
            'settings' => Setting::class
        ];

        return $models[$table] ?? null;
    }

    /**
     * Получить правила валидации для таблицы
     */
    private function getValidationRules($table, $id = null)
    {
        $rules = [];

        switch ($table) {
            case 'companies':
                $rules = [
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'country' => 'nullable|string|max:100',
                    'state' => 'nullable|string|max:100',
                    'city' => 'nullable|string|max:100',
                    'street' => 'nullable|string|max:255',
                    'house' => 'nullable|string|max:50',
                    'phone' => 'nullable|string|max:50',
                    'phone_additional' => 'nullable|string|max:50',
                    'email_address' => 'nullable|email|max:255',
                    'logo' => 'nullable|string|max:255'
                ];
                break;

            case 'users':
                $rules = [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:users,email' . ($id ? ",{$id}" : ''),
                    'tel' => 'nullable|string|max:50',
                    'password' => ($id ? 'nullable' : 'required') . '|string|min:6',
                    'dob' => 'nullable|date'
                ];
                break;

            case 'products':
                $rules = [
                    'name' => 'required|string|max:255',
                    'consist' => 'nullable|string',
                    'weight' => 'nullable|numeric',
                    'price' => 'required|numeric|min:0',
                    'product_category_id' => 'nullable|integer|exists:product_categories,id',
                    'visible' => 'nullable|boolean',
                    'sku' => 'nullable|string|max:100'
                ];
                break;

            case 'orders':
                $rules = [
                    'name' => 'required|string|max:255',
                    'tel' => 'required|string|max:50',
                    'full_address' => 'nullable|string',
                    'street' => 'nullable|string|max:255',
                    'house' => 'nullable|string|max:50',
                    'building' => 'nullable|string|max:50',
                    'staircase' => 'nullable|string|max:50',
                    'floor' => 'nullable|string|max:50',
                    'apartment' => 'nullable|string|max:50',
                    'total' => 'nullable|numeric|min:0',
                    'delivery' => 'nullable|numeric|min:0',
                    'user_id' => 'nullable|integer|exists:users,id',
                    'payType' => 'nullable|string|max:50',
                    'personQty' => 'nullable|integer|min:1',
                    'comment' => 'nullable|string',
                    'eatsId' => 'nullable|string|max:100',
                    'restaurantId' => 'nullable|string|max:100',
                    'latitude' => 'nullable|numeric',
                    'longitude' => 'nullable|numeric',
                    'deliveryDate' => 'nullable|date',
                    'deliveryType' => 'nullable|string|max:50',
                    'itemsCost' => 'nullable|numeric|min:0',
                    'deliveryFee' => 'nullable|numeric|min:0',
                    'change' => 'nullable|numeric|min:0',
                    'promos' => 'nullable|array',
                    'deliveryAddress' => 'nullable|string'
                ];
                break;

            case 'order_items':
                $rules = [
                    'order_id' => 'required|integer|exists:orders,id',
                    'product_id' => 'required|integer|exists:products,id',
                    'qty' => 'required|integer|min:1',
                    'sku' => 'nullable|string|max:100',
                    'price' => 'nullable|numeric|min:0',
                    'modifications' => 'nullable|string'
                ];
                break;

            case 'product_categories':
                $rules = [
                    'uri' => 'nullable|string|max:255',
                    'name' => 'required|string|max:255'
                ];
                break;

            case 'user_addresses':
                $rules = [
                    'user_id' => 'required|integer|exists:users,id',
                    'street' => 'nullable|string|max:255',
                    'house' => 'nullable|string|max:50',
                    'building' => 'nullable|string|max:50',
                    'staircase' => 'nullable|string|max:50',
                    'floor' => 'nullable|string|max:50',
                    'apartment' => 'nullable|string|max:50'
                ];
                break;

            case 'modifiers':
                $rules = [
                    'group_id' => 'nullable|integer|exists:modifier_groups,id',
                    'name' => 'required|string|max:255',
                    'price' => 'nullable|numeric|min:0',
                    'vat' => 'nullable|numeric|min:0',
                    'min_amount' => 'nullable|integer|min:0',
                    'max_amount' => 'nullable|integer|min:0',
                    'quantity' => 'nullable|integer|min:0',
                    'visible' => 'nullable|boolean'
                ];
                break;

            case 'modifier_groups':
                $rules = [
                    'name' => 'required|string|max:255'
                ];
                break;

            case 'banners':
                $rules = [
                    'title' => 'nullable|string|max:255',
                    'description' => 'nullable|string',
                    'image' => 'nullable|string|max:255',
                    'link' => 'nullable|string|max:255',
                    'active' => 'nullable|boolean'
                ];
                break;

            case 'stories':
                $rules = [
                    'title' => 'nullable|string|max:255',
                    'content' => 'nullable|string',
                    'image' => 'nullable|string|max:255',
                    'active' => 'nullable|boolean'
                ];
                break;

            case 'settings':
                $rules = [
                    'key' => 'required|string|max:255',
                    'value' => 'nullable|string'
                ];
                break;
        }

        return $rules;
    }
}
