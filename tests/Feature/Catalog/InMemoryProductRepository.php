<?php

namespace Tests\Feature\Catalog;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Repository\ProductRepository;

final class InMemoryProductRepository implements ProductRepository
{
    /** @var array<int, Product> */
    private array $products = [];

    private int $autoIncrement = 1;

    public function findById(int $id): ?Product
    {
        return $this->products[$id] ?? null;
    }

    public function findByIds(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            if (isset($this->products[$id])) {
                $result[] = $this->products[$id];
            }
        }

        return $result;
    }

    public function findByCategoryId(int $categoryId): array
    {
        // Для in-memory реализации findByCategoryId не используется,
        // так как связки живут в InMemoryCategoryRepository.
        return [];
    }

    public function save(Product $product): void
    {
        $id = $product->id() ?? $this->autoIncrement++;

        $ref = new \ReflectionClass($product);
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($product, $id);

        $this->products[$id] = $product;
    }

    public function delete(Product $product): void
    {
        $id = $product->id();
        if ($id === null) {
            return;
        }

        unset($this->products[$id]);
    }

    // Утилита для тестов

    public function addProduct(Product $product): Product
    {
        $this->save($product);

        return $product;
    }
}

