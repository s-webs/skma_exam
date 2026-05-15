# Система авторизации Psihotest

## Обзор

Реализована система авторизации с тремя ролями для сотрудников и отдельной таблицей для абитуриентов.

## Роли пользователей (сотрудники)

### 1. Developer (Разработчик)
**Полный доступ ко всей системе:**
- Управление пользователями (создание, редактирование, удаление)
- Управление абитуриентами
- Управление вопросами и экзаменами
- Просмотр и экспорт результатов
- Доступ к системным настройкам

### 2. KTBO (Контрольно-технический отдел)
**Управление учебным процессом:**
- Просмотр и одобрение абитуриентов
- Управление попытками экзаменов
- Создание и редактирование вопросов
- Просмотр и экспорт результатов
- Доступ к админ-панели

### 3. Registrator (Регистратор)
**Базовый доступ:**
- Просмотр списка абитуриентов
- Доступ к админ-панели

## Структура базы данных

### Таблица `users` (сотрудники)
```sql
- id
- name
- email (unique)
- password (hashed)
- remember_token
- created_at, updated_at
```

### Таблица `applicants` (абитуриенты)
```sql
- id
- name
- email (unique)
- identifier (ИИН, unique)
- address
- phone
- graduate_organization
- graduate_year
- speciality
- language (1=kz, 2=ru, 3=en)
- document_front (путь к файлу)
- document_back (путь к файлу)
- diplom (путь к файлу)
- certificate (путь к файлу)
- viewed (boolean)
- allowed (boolean)
- old (boolean, для архива)
- created_at, updated_at
```

### Таблицы Spatie Permission
- `roles` - роли
- `permissions` - разрешения
- `model_has_roles` - связь пользователей и ролей
- `model_has_permissions` - связь пользователей и разрешений
- `role_has_permissions` - связь ролей и разрешений

## Команды Artisan

### Создание пользователя через CLI
```bash
php artisan user:create --name="John Doe" --email="john@example.com" --password="SecurePass123!" --role="developer"
```

Или интерактивно:
```bash
php artisan user:create
```

### Инициализация ролей и разрешений
```bash
php artisan db:seed --class=RoleSeeder
```

## API Endpoints

### Авторизация
- `GET /login` - Страница входа
- `POST /login` - Вход в систему
- `POST /logout` - Выход из системы

### Управление пользователями (только developer)
- `GET /admin/users` - Список пользователей
- `GET /admin/users/create` - Форма создания
- `POST /admin/users` - Создание пользователя
- `GET /admin/users/{id}/edit` - Форма редактирования
- `PUT /admin/users/{id}` - Обновление пользователя
- `DELETE /admin/users/{id}` - Удаление пользователя

### Dashboard
- `GET /admin/dashboard` - Главная страница админ-панели

## Безопасность

### Реализованные меры:
1. **Rate Limiting** - ограничение попыток входа (5 попыток в минуту)
2. **Password Hashing** - bcrypt для хеширования паролей
3. **CSRF Protection** - Laravel автоматически защищает от CSRF
4. **Session Regeneration** - регенерация сессии после входа
5. **Role-based Access Control** - проверка ролей через middleware
6. **Permission-based Access** - детальный контроль через разрешения

### Middleware
- `auth` - проверка авторизации
- `role:developer` - проверка роли
- `permission:manage users` - проверка разрешения

## React компоненты

### Созданные страницы:
- `resources/js/pages/Auth/Login.tsx` - Страница входа
- `resources/js/pages/Admin/Dashboard.tsx` - Dashboard
- `resources/js/pages/Admin/Users/Index.tsx` - Список пользователей
- `resources/js/pages/Admin/Users/Create.tsx` - Создание пользователя

## Использование

### 1. Запуск миграций
```bash
php artisan migrate
```

### 2. Инициализация ролей
```bash
php artisan db:seed --class=RoleSeeder
```

### 3. Создание первого пользователя
```bash
php artisan user:create --name="Developer" --email="dev@skma.edu.kz" --password="DevPassword123!" --role="developer"
```

### 4. Вход в систему
- Перейти на `/login`
- Ввести email и пароль
- После входа будет редирект на `/admin/dashboard`

## Следующие шаги

1. Создать страницу редактирования пользователя (Edit.tsx)
2. Добавить управление абитуриентами
3. Реализовать систему экзаменов
4. Добавить управление вопросами
5. Создать систему результатов

## Тестовые данные

**Developer аккаунт:**
- Email: `dev@skma.edu.kz`
- Password: `DevPassword123!`
- Role: `developer`

## Примечания

- Пароли должны быть минимум 8 символов
- Email должен быть уникальным
- Нельзя удалить самого себя
- Все действия логируются (TODO: добавить логирование)
