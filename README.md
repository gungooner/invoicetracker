
# Invoice & Payment Tracker — API Documentation

**Base URL (local dev):** `http://127.0.0.1:8000/api`
**Auth type:** Bearer token (Laravel Sanctum)

All endpoints except `/register` and `/login` require an `Authorization: Bearer {token}` header. Every request/response body is JSON — send `Content-Type: application/json` and `Accept: application/json` on all requests.

---

**Authentication**

** Register a new business**
Creates a new business account and returns an access token.

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/register`
- **Auth required:** No

**Request body**
{
    "name": "Acme Business",
    "email": "owner@acme.com",
    "password": "password123"
}

**Response `201 Created`**
{
    "user": {
        "id": 1,
        "name": "Acme Business",
        "email": "owner@acme.com"
    },
    "token": "1|abcdef123456..."
}


**Validation rules:** `name` required. `email` required, valid email, unique in `users`. `password` required, minimum 8 characters.

---

### Log in
Authenticates an existing business and returns a fresh access token.

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/login`
- **Auth required:** No

**Request body**

{
    "email": "owner@acme.com",
    "password": "password123"
}
```

**Response `200 OK`** — same shape as register.

**Errors:** `422` if email/password don't match an existing account.

---

### Log out
Revokes the token used to make this request. Other tokens/devices remain logged in.

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/logout`
- **Auth required:** Yes

**Response:** `204 No Content`

---

### Get current user
Returns the profile of the currently authenticated business.

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/user`
- **Auth required:** Yes

**Response `200 OK`**
{
    "id": 1,
    "name": "Acme Business",
    "email": "owner@acme.com"
}
```

---

## Clients

All client endpoints are scoped to the logged-in business — a business can only ever see, edit, or delete its own clients.

### List clients

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/clients`
- **Auth required:** Yes

**Response `200 OK`** — array of client objects belonging to the current business.

---

### Create a client

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/clients`
- **Auth required:** Yes

**Request body**
{
    "name": "Client Co",
    "email": "billing@clientco.com",
    "phone": "555-1234",
    "address": "123 Main St"
}
```

**Validation rules:** `name` required. `email`, `phone`, `address` optional.

**Response `201 Created`** — the new client object.

---

### Get one client

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/clients/{client}`
- **Auth required:** Yes

**Response `200 OK`** — the client object.

**Errors:** `403` if the client belongs to a different business. `404` if it doesn't exist.

---

### Update a client

- **Method:** `PUT` / `PATCH`
- **URL:** `http://127.0.0.1:8000/api/clients/{client}`
- **Auth required:** Yes

**Request body** — any subset of `name`, `email`, `phone`, `address`.

**Response `200 OK`** — the updated client object.

---

### Delete a client

- **Method:** `DELETE`
- **URL:** `http://127.0.0.1:8000/api/clients/{client}`
- **Auth required:** Yes

**Response:** `204 No Content`

**Note:** deleting a client does **not** delete their invoices automatically unless cascading rules dictate otherwise — check your migration's `onDelete` behavior.

---

## Invoices

All invoice endpoints are scoped to the logged-in business.

### List invoices
Supports optional filtering by status and/or client.

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/invoices`
- **Auth required:** Yes

**Query parameters (optional)**
- `status` — example: `?status=overdue` — filter by `draft`, `sent`, `paid`, or `overdue`
- `client_id` — example: `?client_id=5` — only invoices for one specific client

Both can be combined: `?status=overdue&client_id=5`

**Response `200 OK`** — array of invoices, each with nested `client` and `items`.

---

### Create an invoice

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/invoices`
- **Auth required:** Yes

**Request body**
{
    "client_id": 1,
    "invoice_number": "INV-0001",
    "issue_date": "2026-09-10",
    "due_date": "2026-09-24"
}
```

**Validation rules:** `client_id` required, must belong to the logged-in business. `invoice_number` required, unique. `issue_date` required, valid date. `due_date` required, must be on or after `issue_date`.

**Response `201 Created`** — the new invoice, `status: "draft"`, `total: 0.00`.

---

### Get one invoice

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/invoices/{invoice}`
- **Auth required:** Yes

**Response `200 OK`** — invoice object with nested `client` and `items`.

---

### Update an invoice
Limited to `issue_date` and `due_date`. `status` and `total` cannot be set directly here — see the action endpoints below.

- **Method:** `PUT` / `PATCH`
- **URL:** `http://127.0.0.1:8000/api/invoices/{invoice}`
- **Auth required:** Yes

**Request body** — any subset of `issue_date`, `due_date`.

**Response `200 OK`** — the updated invoice.

---

### Delete an invoice

- **Method:** `DELETE`
- **URL:** `http://127.0.0.1:8000/api/invoices/{invoice}`
- **Auth required:** Yes

**Response:** `204 No Content`

**Note:** deleting an invoice cascades — all of its line items are deleted automatically.

---

### Send an invoice
Emails the invoice to the client and transitions status `draft → sent`.

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/invoices/{invoice}/send`
- **Auth required:** Yes

**Response `200 OK`** — the updated invoice, `status: "sent"`, `sent_at` populated.

**Errors:** `422` if the invoice isn't currently in `draft` status.

---

### Mark an invoice as paid

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/invoices/{invoice}/mark-paid`
- **Auth required:** Yes

**Response `200 OK`** — the updated invoice, `status: "paid"`, `paid_at` populated.

**Errors:** `422` if the invoice isn't currently `sent` or `overdue`.

---

### Download invoice as PDF

- **Method:** `GET`
- **URL:** `http://127.0.0.1:8000/api/invoices/{invoice}/pdf`
- **Auth required:** Yes

**Response `200 OK`** — binary PDF file download (`invoice-{invoice_number}.pdf`).

---

## Invoice Line Items

Line items always exist in the context of a parent invoice. Creating or deleting an item automatically recalculates the parent invoice's `total`.

### Add a line item to an invoice

- **Method:** `POST`
- **URL:** `http://127.0.0.1:8000/api/invoices/{invoice}/items`
- **Auth required:** Yes

**Request body**
{
    "description": "Web design services",
    "quantity": 10,
    "unit_price": 50
}
```

**Validation rules:** `description` required. `quantity` required, integer, minimum 1. `unit_price` required, numeric, minimum 0.

**Response `201 Created`**
{
    "id": 1,
    "invoice_id": 1,
    "description": "Web design services",
    "quantity": 10,
    "unit_price": "50.00",
    "line_total": "500.00"
}
```

**Side effect:** the parent invoice's `total` is recalculated as the sum of all its items' `line_total`.

---

### Update a line item

- **Method:** `PUT`
- **URL:** `http://127.0.0.1:8000/api/invoice-items/{invoiceItem}`
- **Auth required:** Yes

**Request body** — any subset of `description`, `quantity`, `unit_price`. `line_total` is always recalculated server-side and cannot be set directly.

**Response `200 OK`** — the updated line item.

**Side effect:** parent invoice's `total` is recalculated.

---

### Delete a line item

- **Method:** `DELETE`
- **URL:** `http://127.0.0.1:8000/api/invoice-items/{invoiceItem}`
- **Auth required:** Yes

**Response:** `204 No Content`

**Side effect:** parent invoice's `total` is recalculated.

---

## Authorization model

Every resource (`Client`, `Invoice`, `InvoiceItem`) is protected by a Laravel Policy that enforces one rule: **a business can only view, update, or delete records it owns.**

- `Client` / `Invoice` — ownership checked directly via each record's `user_id` column.
- `InvoiceItem` — has no `user_id` of its own; ownership is checked through its parent invoice (`$invoiceItem->invoice->user_id`).

Any request attempting to access another business's data returns `403 Forbidden`. A request for a record that doesn't exist at all returns `404 Not Found`.


---

## Status lifecycle reference

```
draft → sent → paid
          ↓
       overdue → paid
```

- `draft` — default status when an invoice is created.
- `sent` — set via `POST /invoices/{invoice}/send`.
- `overdue` — set automatically by the scheduled job, once `due_date` has passed on a `sent` invoice.
- `paid` — set via `POST /invoices/{invoice}/mark-paid`, valid from either `sent` or `overdue`.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# Invoice_Tracker
