<script>
export default{
    props:{
        goods: Object
    },
    data:()=>({
        pholder: [1,1,1,1]
    })
}
</script>

<template>
    <div class="container" v-if="goods.length">
        <div class="row">
            <div class="col-12" style="margin-bottom: 48px;">
                <ul class="list-unstyled p-0 m-0 categories">
                    <li v-for="category in goods" :key="category.id">
                        <button>
                            {{ category.name }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-12" v-for="category in goods" :key="category.id">
                <h4>{{ category.name }}</h4>
                <ul class="list-unstyled p-0 category-list">
                    <li v-for="product in category.products" :key="product.id">
                        <div class="product">
                            <div class="header"></div>
                            <div class="content">
                                <h5>{{ product.name }}</h5>
                                <p>{{ product.consist }}</p>
                            </div>
                            <div class="footer">
                                <button type="button" class="btn btn-primary">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-secondary">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="container" v-else>
        <div class="row">
            <div class="col-12" style="margin-bottom: 48px;">
                <ul class="list-unstyled p-0 m-0 categories placeholder-glow">
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                    <li><button class="placeholder bg-dark" style="min-width: 140px;min-height: 50px;"></button></li>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-12 placeholder-glow" v-for="item in pholder" :key="item">
                <h4 class="placeholder col-5 mb-4"></h4>
                <ul class="category-list list-unstyled p-0">
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="product">
                            <div class="header placeholder bg-dark"></div>
                            <div class="content">
                                <h5 class="placeholder col-5"></h5>
                                <p class="placeholder col-8"></p>
                            </div>
                            <div class="footer">
                                <button class="btn placeholder">asdasdasd</button>
                                <button class="btn placeholder mx-2">asdasdasd</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>