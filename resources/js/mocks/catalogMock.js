export const mockCategories = [
    { id: 1, uri: "rolls", name: "Роллы" },
    { id: 2, uri: "sets", name: "Сеты" },
    { id: 3, uri: "wok", name: "WOK" },
    { id: 4, uri: "pizza", name: "Пицца" },
    { id: 5, uri: "burgers", name: "Бургеры" },
    { id: 6, uri: "drinks", name: "Напитки" },
];

export const mockProducts = [
    // Роллы
    {
        id: 101,
        name: "Филадельфия классика",
        consist: "Лосось, сливочный сыр, огурец, рис, нори",
        weight: 250,
        price: 520,
        categoryId: 1,
        thumbs: [
            {
                small: "/uploads/mock/philadelphia-sm.jpg",
                medium: "/uploads/mock/philadelphia-md.jpg",
                large: "/uploads/mock/philadelphia-lg.jpg",
            },
        ],
    },
    {
        id: 102,
        name: "Калифорния креветка",
        consist: "Креветка, огурец, икра масаго, рис, нори",
        weight: 230,
        price: 490,
        categoryId: 1,
        thumbs: [
            {
                small: "/uploads/mock/california-sm.jpg",
                medium: "/uploads/mock/california-md.jpg",
                large: "/uploads/mock/california-lg.jpg",
            },
        ],
    },
    {
        id: 103,
        name: "Спайси тунец",
        consist: "Тунец, спайси-соус, огурец, рис, нори",
        weight: 220,
        price: 470,
        categoryId: 1,
        thumbs: [
            {
                small: "/uploads/mock/spicy-tuna-sm.jpg",
                medium: "/uploads/mock/spicy-tuna-md.jpg",
                large: "/uploads/mock/spicy-tuna-lg.jpg",
            },
        ],
    },
    {
        id: 104,
        name: "Гангстер ролл",
        consist: "Лосось, угорь, чукка, соус унаги, рис, нори",
        weight: 260,
        price: 580,
        categoryId: 1,
        thumbs: [
            {
                small: "/uploads/mock/gangster-roll-sm.jpg",
                medium: "/uploads/mock/gangster-roll-md.jpg",
                large: "/uploads/mock/gangster-roll-lg.jpg",
            },
        ],
    },
    {
        id: 105,
        name: "Филадельфия с огурцом",
        consist: "Лосось, сливочный сыр, огурец, рис, нори",
        weight: 240,
        price: 510,
        categoryId: 1,
        thumbs: [
            {
                small: "/uploads/mock/philadelphia-cucumber-sm.jpg",
                medium: "/uploads/mock/philadelphia-cucumber-md.jpg",
                large: "/uploads/mock/philadelphia-cucumber-lg.jpg",
            },
        ],
    },

    // Сеты
    {
        id: 201,
        name: "Комбо «Гангстерский сет»",
        consist: "Ассорти из хитов меню, рассчитано на 3–4 человек",
        weight: 900,
        price: 1890,
        categoryId: 2,
        thumbs: [
            {
                small: "/uploads/mock/gangster-set-sm.jpg",
                medium: "/uploads/mock/gangster-set-md.jpg",
                large: "/uploads/mock/gangster-set-lg.jpg",
            },
        ],
    },
    {
        id: 202,
        name: "Сет «Вечер в городе»",
        consist: "Филадельфия, Калифорния, спайси роллы, гарнир",
        weight: 1100,
        price: 2190,
        categoryId: 2,
        thumbs: [
            {
                small: "/uploads/mock/city-night-set-sm.jpg",
                medium: "/uploads/mock/city-night-set-md.jpg",
                large: "/uploads/mock/city-night-set-lg.jpg",
            },
        ],
    },
    {
        id: 203,
        name: "Сет «Для двоих»",
        consist: "Лёгкая подборка роллов и закусок на 2 человек",
        weight: 700,
        price: 1390,
        categoryId: 2,
        thumbs: [
            {
                small: "/uploads/mock/for-two-set-sm.jpg",
                medium: "/uploads/mock/for-two-set-md.jpg",
                large: "/uploads/mock/for-two-set-lg.jpg",
            },
        ],
    },
    {
        id: 204,
        name: "Сет «Офисный»",
        consist: "Микс роллов и горячих закусок для команды",
        weight: 1500,
        price: 2590,
        categoryId: 2,
        thumbs: [
            {
                small: "/uploads/mock/office-set-sm.jpg",
                medium: "/uploads/mock/office-set-md.jpg",
                large: "/uploads/mock/office-set-lg.jpg",
            },
        ],
    },
    {
        id: 205,
        name: "Сет «Ночной дозор»",
        consist: "Плотный набор для позднего вечера",
        weight: 1300,
        price: 2390,
        categoryId: 2,
        thumbs: [
            {
                small: "/uploads/mock/night-watch-set-sm.jpg",
                medium: "/uploads/mock/night-watch-set-md.jpg",
                large: "/uploads/mock/night-watch-set-lg.jpg",
            },
        ],
    },

    // WOK
    {
        id: 301,
        name: "WOK с курицей в соусе терияки",
        consist: "Курица, лапша, овощи, терияки-соус, кунжут",
        weight: 400,
        price: 430,
        categoryId: 3,
        thumbs: [
            {
                small: "/uploads/mock/wok-chicken-sm.jpg",
                medium: "/uploads/mock/wok-chicken-md.jpg",
                large: "/uploads/mock/wok-chicken-lg.jpg",
            },
        ],
    },
    {
        id: 302,
        name: "WOK с говядиной и овощами",
        consist: "Говядина, лапша, овощи, острый соус, кунжут",
        weight: 420,
        price: 470,
        categoryId: 3,
        thumbs: [
            {
                small: "/uploads/mock/wok-beef-sm.jpg",
                medium: "/uploads/mock/wok-beef-md.jpg",
                large: "/uploads/mock/wok-beef-lg.jpg",
            },
        ],
    },
    {
        id: 303,
        name: "WOK вегетарианский",
        consist: "Овощи, лапша, лёгкий соус, кунжут",
        weight: 380,
        price: 390,
        categoryId: 3,
        thumbs: [
            {
                small: "/uploads/mock/wok-veggie-sm.jpg",
                medium: "/uploads/mock/wok-veggie-md.jpg",
                large: "/uploads/mock/wok-veggie-lg.jpg",
            },
        ],
    },
    {
        id: 304,
        name: "WOK с креветками",
        consist: "Креветки, лапша, овощи, чесночный соус",
        weight: 410,
        price: 520,
        categoryId: 3,
        thumbs: [
            {
                small: "/uploads/mock/wok-shrimp-sm.jpg",
                medium: "/uploads/mock/wok-shrimp-md.jpg",
                large: "/uploads/mock/wok-shrimp-lg.jpg",
            },
        ],
    },
    {
        id: 305,
        name: "WOK «Городской»",
        consist: "Микс мяса и овощей в фирменном соусе",
        weight: 430,
        price: 490,
        categoryId: 3,
        thumbs: [
            {
                small: "/uploads/mock/wok-city-sm.jpg",
                medium: "/uploads/mock/wok-city-md.jpg",
                large: "/uploads/mock/wok-city-lg.jpg",
            },
        ],
    },

    // Пицца
    {
        id: 401,
        name: "Пицца «Маргарита»",
        consist: "Соус, моцарелла, томаты, базилик",
        weight: 500,
        price: 490,
        categoryId: 4,
        thumbs: [
            {
                small: "/uploads/mock/pizza-margherita-sm.jpg",
                medium: "/uploads/mock/pizza-margherita-md.jpg",
                large: "/uploads/mock/pizza-margherita-lg.jpg",
            },
        ],
    },
    {
        id: 402,
        name: "Пицца «Пепперони»",
        consist: "Соус, моцарелла, пепперони",
        weight: 520,
        price: 530,
        categoryId: 4,
        thumbs: [
            {
                small: "/uploads/mock/pizza-pepperoni-sm.jpg",
                medium: "/uploads/mock/pizza-pepperoni-md.jpg",
                large: "/uploads/mock/pizza-pepperoni-lg.jpg",
            },
        ],
    },
    {
        id: 403,
        name: "Пицца «Четыре сыра»",
        consist: "Микс сыров, соус, ароматные травы",
        weight: 510,
        price: 560,
        categoryId: 4,
        thumbs: [
            {
                small: "/uploads/mock/pizza-four-cheese-sm.jpg",
                medium: "/uploads/mock/pizza-four-cheese-md.jpg",
                large: "/uploads/mock/pizza-four-cheese-lg.jpg",
            },
        ],
    },
    {
        id: 404,
        name: "Пицца «Гангстерская»",
        consist: "Мясной микс, перец, соус барбекю, моцарелла",
        weight: 550,
        price: 620,
        categoryId: 4,
        thumbs: [
            {
                small: "/uploads/mock/pizza-gangster-sm.jpg",
                medium: "/uploads/mock/pizza-gangster-md.jpg",
                large: "/uploads/mock/pizza-gangster-lg.jpg",
            },
        ],
    },
    {
        id: 405,
        name: "Пицца «Овощная»",
        consist: "Овощи, соус, моцарелла, зелень",
        weight: 500,
        price: 510,
        categoryId: 4,
        thumbs: [
            {
                small: "/uploads/mock/pizza-veggie-sm.jpg",
                medium: "/uploads/mock/pizza-veggie-md.jpg",
                large: "/uploads/mock/pizza-veggie-lg.jpg",
            },
        ],
    },

    // Бургеры
    {
        id: 501,
        name: "Чизбургер классический",
        consist: "Говяжья котлета, сыр, солёный огурец, соус",
        weight: 230,
        price: 310,
        categoryId: 5,
        thumbs: [
            {
                small: "/uploads/mock/burger-cheese-sm.jpg",
                medium: "/uploads/mock/burger-cheese-md.jpg",
                large: "/uploads/mock/burger-cheese-lg.jpg",
            },
        ],
    },
    {
        id: 502,
        name: "Бургер «Гангстер»",
        consist: "Двойная котлета, сыр, бекон, соус, лук",
        weight: 320,
        price: 430,
        categoryId: 5,
        thumbs: [
            {
                small: "/uploads/mock/burger-gangster-sm.jpg",
                medium: "/uploads/mock/burger-gangster-md.jpg",
                large: "/uploads/mock/burger-gangster-lg.jpg",
            },
        ],
    },
    {
        id: 503,
        name: "Чикенбургер",
        consist: "Куриная котлета, соус, салат, помидор",
        weight: 240,
        price: 330,
        categoryId: 5,
        thumbs: [
            {
                small: "/uploads/mock/burger-chicken-sm.jpg",
                medium: "/uploads/mock/burger-chicken-md.jpg",
                large: "/uploads/mock/burger-chicken-lg.jpg",
            },
        ],
    },
    {
        id: 504,
        name: "Бургер вегетарианский",
        consist: "Овощная котлета, сыр, соус, салат",
        weight: 220,
        price: 320,
        categoryId: 5,
        thumbs: [
            {
                small: "/uploads/mock/burger-veggie-sm.jpg",
                medium: "/uploads/mock/burger-veggie-md.jpg",
                large: "/uploads/mock/burger-veggie-lg.jpg",
            },
        ],
    },
    {
        id: 505,
        name: "Бургер BBQ",
        consist: "Говяжья котлета, бекон, BBQ-соус, сыр, лук",
        weight: 310,
        price: 440,
        categoryId: 5,
        thumbs: [
            {
                small: "/uploads/mock/burger-bbq-sm.jpg",
                medium: "/uploads/mock/burger-bbq-md.jpg",
                large: "/uploads/mock/burger-bbq-lg.jpg",
            },
        ],
    },

    // Напитки
    {
        id: 601,
        name: "Лимонад малина-базилик",
        consist: "Малиновый сироп, свежий базилик, газированная вода, лёд",
        weight: 350,
        price: 190,
        categoryId: 6,
        thumbs: [
            {
                small: "/uploads/mock/lemonade-raspberry-sm.jpg",
                medium: "/uploads/mock/lemonade-raspberry-md.jpg",
                large: "/uploads/mock/lemonade-raspberry-lg.jpg",
            },
        ],
    },
    {
        id: 602,
        name: "Лимонад цитрусовый",
        consist: "Апельсин, лимон, лайм, газированная вода, лёд",
        weight: 350,
        price: 190,
        categoryId: 6,
        thumbs: [
            {
                small: "/uploads/mock/lemonade-citrus-sm.jpg",
                medium: "/uploads/mock/lemonade-citrus-md.jpg",
                large: "/uploads/mock/lemonade-citrus-lg.jpg",
            },
        ],
    },
    {
        id: 603,
        name: "Морс ягодный",
        consist: "Ягодное ассорти, вода, сахар",
        weight: 300,
        price: 150,
        categoryId: 6,
        thumbs: [
            {
                small: "/uploads/mock/berry-mors-sm.jpg",
                medium: "/uploads/mock/berry-mors-md.jpg",
                large: "/uploads/mock/berry-mors-lg.jpg",
            },
        ],
    },
    {
        id: 604,
        name: "Кола",
        consist: "Газированный напиток",
        weight: 330,
        price: 130,
        categoryId: 6,
        thumbs: [
            {
                small: "/uploads/mock/cola-sm.jpg",
                medium: "/uploads/mock/cola-md.jpg",
                large: "/uploads/mock/cola-lg.jpg",
            },
        ],
    },
    {
        id: 605,
        name: "Чай холодный персиковый",
        consist: "Чай, персиковый сироп, лёд",
        weight: 350,
        price: 170,
        categoryId: 6,
        thumbs: [
            {
                small: "/uploads/mock/iced-tea-sm.jpg",
                medium: "/uploads/mock/iced-tea-md.jpg",
                large: "/uploads/mock/iced-tea-lg.jpg",
            },
        ],
    },
];

