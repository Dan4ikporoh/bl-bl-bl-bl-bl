# FRIDE RP — сайт + личный кабинет + автодонат (каркас)

## Что внутри
- `index.html` — ваш сайт (не сломаны существующие функции: скролл-анимации, Forbes, заявки через FormSubmit, мобильное меню).
- `api/` — backend на PHP (логин/профиль/выход + донат UnitPay).
- `api/schema.sql` — таблица заказов доната `donate_orders`.

> Важно: GitHub Pages **не умеет** запускать PHP. Репозиторий можно хранить на GitHub, но для работы `api/` нужен хостинг/сервер с PHP и доступом к MySQL.

---

## Быстрый старт

### 1) Создайте `api/config.php`
1. Откройте `api/config.example.php`
2. Скопируйте как `api/config.php`
3. Заполните:
   - доступ к MySQL (host/port/db/user/pass)
   - настройки cookie (если нет HTTPS → поставьте `cookie_secure` в `false`)
   - ключи UnitPay (если нужен донат)

⚠️ `api/config.php` добавлен в `.gitignore`, чтобы вы **случайно не залили пароль/ключи** в GitHub.

---

### 2) Создайте таблицу заказов доната
Выполните SQL из файла:
- `api/schema.sql`

---

### 3) Настройка UnitPay (если используете донат)
В личном кабинете UnitPay у проекта:
- Укажите **Payment Handler URL**:
  - `https://ВАШ_ДОМЕН/api/unitpay_handler.php`

Если сайт находится в подпапке (например `https://domain.com/site/`), то пути в `index.html` уже **относительные** и обычно всё работает.

---

## Что где менять (коротко)

### Favicon (иконка вкладки)
Уже встроен в `<head>` как SVG data-url (оранжевая “F”).

### Личный кабинет
- UI: в `index.html` добавлена модалка `#accountModal`
- API:
  - `api/login.php` — вход по `accounts.name` и `accounts.password`
  - `api/profile.php` — отдаёт данные аккаунта по сессии
  - `api/logout.php` — выход

> Ваша БД из дампа: таблица `accounts` (name/password/level/money/bank/donate_current/donate_total и т.д.).  
> Пароли в дампе лежат **в открытом виде** — backend тоже сравнивает как строку.


### Заявки (FormSubmit)
Почта для получения заявок задаётся в одном месте в `index.html`:
- константа `APPLY_EMAIL` (по умолчанию стоит `Dan4ikporoh@gmail.com`).

Также эта почта автоматически подставляется в скрытое поле формы `_to`.

### Донат
- UI: модалка “Донат” → кнопка “Перейти к оплате”
- `api/donate_create.php` — создаёт заказ и отдаёт ссылку UnitPay
- `api/unitpay_handler.php` — принимает callback `check/pay/error`
- Начисление делается в таблицу `accounts`:
  - `donate_current += coins`
  - `donate_total += coins`

#### Если хотите начислять не донат, а игровую валюту
Откройте `api/unitpay_handler.php` и поменяйте SQL обновления (там есть комментарий).

---

## Требования
- PHP 7.4+ (лучше 8.x)
- PDO MySQL включен
- MySQL/MariaDB доступен с сервера, где стоит сайт

---

## Подсказка по безопасности
Вы **обязательно** смените пароль от базы данных (и ключи платежей), если вы когда-либо публиковали их в чатах/скриншотах.
