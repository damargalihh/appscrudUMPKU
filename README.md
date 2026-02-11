# WiFi Manager UMPKU Surakarta

Monorepo project terpisah menjadi **backend** (Laravel) dan **frontend** (Vite + Tailwind + Alpine.js).

## Struktur

```
├── backend/          # Laravel 12 (API, Auth, Blade views, Services)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── public/       # Web root (build output dari frontend masuk ke sini)
│   ├── resources/
│   │   └── views/    # Blade templates
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── artisan
│   ├── composer.json
│   └── .env
│
├── frontend/         # Vite 6 (CSS, JS, Tailwind, Alpine.js)
│   ├── resources/
│   │   ├── css/
│   │   │   └── app.css
│   │   └── js/
│   │       ├── app.js
│   │       └── bootstrap.js
│   ├── package.json
│   ├── vite.config.js
│   ├── tailwind.config.js
│   └── postcss.config.js
│
├── .gitignore
└── README.md
```

## Cara Install

### Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Frontend
```bash
cd frontend
npm install
```

## Cara Menjalankan

### Development (sekaligus backend + frontend)
```bash
cd backend
composer dev
```

### Atau jalankan secara terpisah:

**Backend saja:**
```bash
cd backend
php artisan serve
```

**Frontend saja (Vite dev server):**
```bash
cd frontend
npm run dev
```

### Build Production
```bash
cd frontend
npm run build
```
Hasil build otomatis masuk ke `backend/public/build/`.

