# Техописание главной страницы (HomePage)

## Общая структура

```
MainLayout (shell)
└── main > div.mx-auto.max-w-7xl.px-4.sm:px-6.lg:px-8.py-6
    └── router-view
        └── HomePage
            ├── HomeJumbotron (hero-карусель)
            └── div.home-page.mt-4.space-y-10
                ├── HomePromotions (акции)
                └── section (меню)
                    ├── header (заголовок + кнопка bottom bar)
                    ├── CatalogCategories
                    └── CatalogProducts
```

## Компоненты и их роль

### 1. HomeJumbotron
- **Назначение**: Hero-блок с каруселью баннеров.
- **Зависимости**: Swiper (swiper/vue), изображения из `/images/banners/`.
- **Структура**:
  - Декоративные градиенты: `absolute inset-0 opacity-40 mix-blend-screen` — два blur-круга (amber-500/15, rose-500/10).
  - Swiper: loop, autoplay 4500ms, centered slides, breakpoints (1.1 / 1.3 / 1.6 slides).
  - Слайд: `rounded-2xl overflow-hidden bg-slate-900/60 border border-white/10`, активный — `scale-[1.15] opacity-100`, неактивный — `scale-90 opacity-60`.
  - Контент слайда: aspect-[16/9] изображение, градиент снизу `from-black/70 via-black/20`, текст: uppercase tracking, amber-300 заголовок.
- **Классы-маркеры**: `mt-12 mb-18`, `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`.

### 2. HomePromotions
- **Назначение**: Блок акций (3 карточки в grid).
- **Зависимости**: useFloatLoop (анимация), BaseModal.
- **Структура**:
  - Заголовок: `text-lg sm:text-xl font-semibold text-slate-100 mb-4`.
  - Grid: `grid gap-4 sm:grid-cols-3`.
  - Карточка: `rounded-2xl`, aspect-[16/9], изображение `grayscale group-hover:grayscale-0 group-hover:scale-105`.
  - Клик открывает BaseModal с деталями акции.
- **Анимация**: useFloatLoop(promoRefs, { y: 7, x: 5, duration: 3.2, stagger: 0.25 }).

### 3. Section header (меню)
- **Паттерн**:
  - h2: `text-xl sm:text-2xl font-semibold text-slate-50`.
  - Подзаголовок: `text-sm text-slate-400`.
  - Кнопка (опционально): `rounded-full border px-4 py-1.5`, активная — `border-amber-400/60 text-amber-200 bg-[rgba(0,0,0,0.75)]`.

### 4. CatalogCategories
- **Контейнер**: `rounded-2xl border border-amber-400/30 bg-[rgba(255,255,255,0.035)] px-4 sm:px-6 lg:px-8 py-3.5 shadow-[0_0_22px_rgba(0,0,0,0.65)] backdrop-blur`.
- **Кнопки**: `rounded-full border px-5 py-2`, активная — `border-amber-400/70 text-amber-100 shadow-[0_0_14px_rgba(251,191,36,0.45)]`, неактивная — `border-white/10 text-slate-300 hover:border-amber-400/50`.

### 5. CatalogProducts / ProductCard
- **Сетка**: grid, 1/2/3 колонки по breakpoints, на lg — nth-child паттерн для span 2 col/row.
- **Карточка**: `rounded-3xl bg-[rgba(255,255,255,0.02)]`, hover `-translate-y-1`, градиент overlay `from-black/80 via-black/45`.
- **Акценты**: amber-400 для цены, border-amber-400/30 для нижнего блока.

## Визуальные паттерны (палитра)

| Элемент | Классы |
|---------|--------|
| Фон карточки/острова | `bg-[rgba(255,255,255,0.02)]` — `bg-[rgba(255,255,255,0.04)]` |
| Граница | `border-white/10`, акцент — `border-amber-400/30` |
| Декоративные градиенты | `bg-amber-500/15` — `bg-amber-500/20`, `bg-rose-500/10` + `blur-3xl` |
| Заголовок h1/h2 | `text-amber-300` или `text-slate-50` |
| Вторичный текст | `text-slate-400`, `text-slate-200/90` |
| Контейнер | `mx-auto max-w-7xl px-4 sm:px-6 lg:px-8` |

## Анимации (animationManager)

- `playIntroScene` — интро с логотипом (MainLayout).
- `playPageEnter` / `playPageLeave` — переходы страниц (opacity).
- `playBannerSticks` — анимация палочек (SecondaryBanner).
- `playFloatLoop` — плавающие элементы (HomePromotions).
- `playCatalogItemsEnter` — появление карточек каталога.

## Отступы и spacing

- Между секциями: `space-y-10`, `my-12`, `mb-18`.
- Внутри секций: `gap-4`, `gap-6`, `mb-4`.
