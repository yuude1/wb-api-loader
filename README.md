# WB API Loader

Консольная команда для импорта данных с Wildberries API в базу данных Laravel.  
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

---

## Требования

- PHP >= 8.x  
- Laravel >= 10.x  
- MySQL / MariaDB  
- Расширение `curl` для HTTP-запросов  

---

## Установка

1. Клонируйте репозиторий:  

```bash
git clone https://github.com/yuude1/wb-api-loader.git
cd wb-api-loader
```

2. Доступы в .env:  
```dotenv
DB_CONNECTION=mysql
DB_HOST=sql7.freesqldatabase.com
DB_PORT=3306
DB_DATABASE=sql7804100
DB_USERNAME=sql7804100
DB_PASSWORD=XJ1BULtnVT

- Так же добавьте

WB_API_HOST=(host)
WB_API_KEY=(key)
```

3. Примените миграции для создания таблиц:
```bash
php artisan migrate
```
4. Команда для импорта данных
```bash
php artisan import:wbdata --from=YYYY-MM-DD --to=YYYY-MM-DD --limit=500
```
Параметры:

--from — дата начала импорта (по умолчанию 7 дней назад)

--to — дата окончания импорта (по умолчанию сегодня)

--limit — количество записей за один запрос (по умолчанию 500)
