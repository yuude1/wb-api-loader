# WB API Loader

Консольная команда для импорта данных с Wildberries API в базу данных Laravel в Docker.
Проект позволяет автоматически получать и сохранять данные о заказах, продажах, остатках и доходах.

---

## Описание

Команда `import:wbdata` позволяет импортировать данные с Wildberries API в базу данных:

- **Заказы** (`orders`)
- **Продажи** (`sales`)
- **Остатки на складах** (`stocks`)
- **Доходы** (`incomes`)

Особенности:

- Поддержка лимита записей за один запрос (`limit`)
- Пагинация для обработки больших объёмов данных
- Форматирование даты и времени (`Y-m-d H:i:s`)
- Вывод отчёта о количестве добавленных и обновлённых записей
- Обновление данных по расписанию через Laravel Scheduler

---

## Требования

- PHP >= 8.x
- Laravel >= 10.x
- Docker / Docker Compose
- MySQL / MariaDB внутри контейнера
- Расширение `curl` для HTTP-запросов

---

## Установка

1. Клонируйте репозиторий:

```bash
git clone https://github.com/yuude1/wb-api-loader.git
cd wb-api-loader
```

2. Создайте файл `.env` (или скопируйте `.env.example`) и проверьте настройки Docker:

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=wb_api
DB_USERNAME=laravel
DB_PASSWORD=laravel

WB_API_HOST=(host)
WB_API_KEY=(key)
```
`DB_HOST=db` — это имя сервиса базы данных в Docker Compose.

3. Запустите контейнеры и миграции:

```bash
docker compose up -d
docker compose exec app php artisan migrate
```

4. Добавьте данные для API (команды можно запускать внутри контейнера):

```bash
docker compose exec app php artisan add:company "My Company" "email@example.com"
docker compose exec app php artisan add:account 1 "Main Account" "acc@example.com"
docker compose exec app php artisan add:apiservice "WB API" "API сервиса Wildberries" "https://suppliers-stats.wildberries.ru"
docker compose exec app php artisan add:tokentype "Bearer" "Тип токена Bearer"
docker compose exec app php artisan add:apitoken 1 1 1 "abcdef123456"
```

---

## Импорт данных

Для ручного запуска обновления данных:

```bash
docker compose exec app php artisan import:wbdata --from=YYYY-MM-DD --to=YYYY-MM-DD --limit=500
```

Параметры:

- `--from` — дата начала импорта (по умолчанию 7 дней назад)
- `--to` — дата окончания импорта (по умолчанию сегодня)
- `--limit` — количество записей за один запрос (по умолчанию 500)

---

## Автоматическое обновление

Обновление данных настроено дважды в день через Laravel Scheduler (08:00 и 20:00).  
Чтобы протестировать планировщик:

```bash
docker compose exec app php artisan schedule:work
```

