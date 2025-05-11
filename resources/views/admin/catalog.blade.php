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
                        <div class='flex items-center justify-end'>
                            <span class="product-count">{{ $category->products()->count() }} шт.</span>
                            <button type="button" class="btn btn-sm btn-success add-product-btn mx-2"
                                data-category-id="{{ $category->id }}">
                                <i class="fa fa-plus"></i>
                            </button>
                            <button type="button" class="toggle-products">
                                <i class="fa fa-chevron-up"></i>
                            </button>
                        </div>

                    </div>
                    <div class="products-container collapsed" data-category-id="{{ $category->id }}">
                        @foreach ($category->products as $product)
                            <div class="product-item" data-id="{{ $product->id }}">
                                <i class="fa fa-bars handle"></i>
                                <img src="{{ $product->thumbs[0]['small'] }}" alt="{{ $product->name }}"
                                    class="product-thumbnail">
                                <span class="product-name">{{ $product->name }} ({{ $product->price }} руб./
                                    {{ $product->weight }} гр)</span>
                                <button type="button" class="btn btn-sm btn-danger remove-product-btn"
                                    data-product-id="{{ $product->id }}" data-category-id="{{ $category->id }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Модальное окно для выбора товара -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить товар в категорию</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="productSearch" placeholder="Поиск товара...">
                </div>
                <div id="productsList" class="list-group">
                    <!-- Здесь будет список товаров -->
                </div>
            </div>
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
        justify-content: space-between;
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

    .remove-product-btn {
        margin-left: auto;
    }

    .mx-2 {
        margin-left: 0.5rem;
        margin-right: 0.5rem;
    }

    #productsList {
        max-height: 400px;
        overflow-y: auto;
    }

    .product-list-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .product-list-item:hover {
        background-color: #f8f9fa;
    }

    .product-list-item img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        margin-right: 15px;
        border-radius: 4px;
    }
</style>


<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentCategoryId = null;

        document.querySelectorAll('.add-product-btn').forEach(button => {
            button.addEventListener('click', function() {
                currentCategoryId = this.dataset.categoryId;
                $('#addProductModal').modal('show');
                loadAvailableProducts();
            });
        });
        let searchTimeout;
        document.getElementById('productSearch').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadAvailableProducts(this.value);
            }, 300);
        });

        function loadAvailableProducts(search = '') {
            $.get('/api/available-products', {
                search: search,
                _token: LA.token
            }).done(function(products) {
                const productsList = document.getElementById('productsList');
                products.forEach(prod => {
                    prod.thumbs = prod.thumbs && prod.thumbs[0] && prod.thumbs[0].small ? prod
                        .thumbs[0].small : '';
                })
                productsList.innerHTML = products.map(product => `
                    <div class="product-list-item" data-product-id="${product.id}">
                        <img src="${product.thumbs}" alt="${product.name}">
                        <div>
                            <h5>${product.name}</h5>
                            <p>${product.price} руб. / ${product.weight} гр</p>
                        </div>
                    </div>
                `).join('');

                productsList.querySelectorAll('.product-list-item').forEach(item => {
                    item.addEventListener('click', function() {
                        addProductToCategory(this.dataset.productId);
                    });
                });
            });
        }

        function addProductToCategory(productId) {
            $.post('/api/add-product-to-category', {
                product_id: productId,
                category_id: currentCategoryId,
                _token: LA.token
            }).done(function(response) {
                $('#addProductModal').modal('hide');
                reloadCatalog();
            });
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-product-btn')) {
                const btn = e.target.closest('.remove-product-btn');
                if (confirm('Вы уверены, что хотите удалить этот товар из категории?')) {
                    $.post('/api/remove-product-from-category', {
                        product_id: btn.dataset.productId,
                        category_id: btn.dataset.categoryId,
                        _token: LA.token
                    }).done(function(response) {
                        reloadCatalog();
                    });
                }
            }
        });

        function reloadCatalog() {
            $.get(window.location.href).done(function(response) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = response;
                const newCatalog = tempDiv.querySelector('#categories-container');
                document.getElementById('categories-container').innerHTML = newCatalog.innerHTML;


                initializeEventHandlers();
            });
        }

        function initializeEventHandlers() {
            // Инициализация кнопок добавления товара
            document.querySelectorAll('.add-product-btn').forEach(button => {
                button.addEventListener('click', function() {
                    currentCategoryId = this.dataset.categoryId;
                    $('#addProductModal').modal('show');
                    loadAvailableProducts();
                });
            });

            // Инициализация переключателей
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

            const categoriesContainer = document.getElementById('categories-container');
            if (categoriesContainer && categoriesContainer.children.length > 0) {
                new Sortable(categoriesContainer, {
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
            }

            document.querySelectorAll('.products-container').forEach(container => {
                if (container && container.children && container.children.length > 0) {
                    new Sortable(container, {
                        handle: '.handle',
                        animation: 150,
                        group: 'products',
                        onEnd: function(evt) {
                            if (evt.to && evt.to.children) {
                                const productOrder = Array.from(evt.to.children).map((el,
                                    index) => ({
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
                        }
                    });
                }
            });
        }

        initializeEventHandlers();
    });
</script>
