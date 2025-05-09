<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Сортировка категорий и товаров</h3>
    </div>
    <div class="box-body">
        <div id="categories-container">
            @foreach ($categories as $category)
                <div class="category-item" data-id="{{ $category->id }}">
                    <div class="category-header">
                        <div class="left-section">
                            <i class="fa fa-bars handle"></i>
                            <h4>{{ $category->name }}</h4>
                        </div>
                        <button type="button" class="toggle-products">
                            <i class="fa fa-chevron-up"></i>
                        </button>
                    </div>
                    <div class="products-container collapsed" data-category-id="{{ $category->id }}">
                        @foreach ($category->products as $product)
                            <div class="product-item" data-id="{{ $product->id }}">
                                <i class="fa fa-bars handle"></i>
                                <img src="{{ $product->thumbs[0]['small'] }}" alt="{{ $product->name }}"
                                    class="product-thumbnail">
                                <span class="product-name">{{ $product->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    .category-item {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .category-header {
        padding: 15px;
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
        display: flex;
        align-items: center;
        cursor: move;
        justify-content: space-between;
    }

    .category-header .left-section {
        display: flex;
        align-items: center;
    }

    .category-header h4 {
        margin: 0 0 0 10px;
    }

    .toggle-products {
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        padding: 5px;
    }

    .products-container {
        padding: 15px;
        min-height: 50px;
        transition: all 0.3s ease;
    }

    .products-container.collapsed {
        padding: 0;
        height: 0;
        min-height: 0;
        overflow: hidden;
    }

    .product-item {
        display: flex;
        align-items: center;
        padding: 10px;
        background: #fff;
        border: 1px solid #eee;
        margin-bottom: 8px;
        border-radius: 4px;
        cursor: move;
    }

    .product-thumbnail {
        width: 40px;
        height: 40px;
        object-fit: cover;
        margin: 0 10px;
        border-radius: 4px;
    }

    .handle {
        color: #999;
        padding: 0 8px;
        cursor: move;
    }

    .product-item:hover {
        background: #f8f9fa;
    }

    .sortable-ghost {
        opacity: 0.4;
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Добавляем обработчики для кнопок сворачивания
        document.querySelectorAll('.toggle-products').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const container = this.closest('.category-item').querySelector(
                    '.products-container');
                const icon = this.querySelector('i');

                container.classList.toggle('collapsed');
                if (container.classList.contains('collapsed')) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                } else {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            });
        });

        new Sortable(document.getElementById('categories-container'), {
            handle: '.category-header',
            animation: 150,
            onEnd: function(evt) {
                const categoryOrder = Array.from(evt.to.children).map((el, index) => ({
                    id: el.dataset.id,
                    order: index
                }));

                $.post('/api/update-category-order', {
                    order: categoryOrder,
                    _token: LA.token
                });
            }
        });


        document.querySelectorAll('.products-container').forEach(container => {
            new Sortable(container, {
                handle: '.handle',
                animation: 150,
                group: 'products',
                onEnd: function(evt) {
                    const productOrder = Array.from(evt.to.children).map((el, index) => ({
                        id: el.dataset.id,
                        order: index,
                        category_id: evt.to.dataset.categoryId
                    }));

                    $.post('/api/update-product-order', {
                        order: productOrder,
                        category_id: evt.to.dataset.categoryId,
                        _token: LA.token
                    });
                }
            });
        });
    });
</script>
