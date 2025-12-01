<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Event Booking System – Backend (Laravel)

### Overview

**Stack**: Laravel 11, Sanctum authentication, MySQL (or any supported DB), queues & notifications, caching.  
This backend implements an **Event Booking System** with:

- **Authentication** (register/login/logout, `auth:sanctum`)
- **Role-based access control** (`admin`, `organizer`, `customer`)
- **Event & Ticket management**
- **Bookings & mocked Payments**
- **Custom middleware**, **services**, **traits**
- **Notifications via queues** and **cached events listing**

Base API prefix is: **`/v1`**.  
Auth endpoints are under **`/v1/api/...`**; business endpoints are under **`/v1/...`**.

---

### Setup Instructions

- **Clone & install**

```bash
composer install
cp .env.example .env   # or copy manually on Windows
php artisan key:generate
```

- **Configure database**

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_booking
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

- **Run migrations & seeders**

```bash
php artisan migrate
php artisan db:seed
```

Seeded data includes:

- **Users**: 2 admins, 3 organizers, 10 customers
- **Events**: 5 events
- **Tickets**: 15 tickets (3 per event)
- **Bookings**: 20 bookings
- **Payments**: 20 payments (linked to bookings)

- **Queues & notifications**

Configure queue connection (e.g. in `.env`):

```env
QUEUE_CONNECTION=database
```

Run queue worker:

```bash
php artisan queue:work
```

- **Run tests**

```bash
php artisan test
```

This will run:

- Feature: registration, login, event creation, ticket booking, payment
- Unit: `PaymentService` behaviour

---

### Authentication & Authorization

- **Roles**
  - **admin**: manage all events, tickets, bookings, payments
  - **organizer**: manage **their own** events and tickets
  - **customer**: book tickets and manage **their own** bookings/payments

- **Guards & middleware**
  - `auth:sanctum` for all protected routes
  - `role:admin,organizer,customer` (custom `EnsureRole` middleware)
  - `prevent.double.booking` (custom `PreventDoubleBooking` middleware)

---

### API Endpoints

Base URL examples (local):

- `http://localhost:8000/v1/api/register` (if using `php artisan serve`)

#### 1. User APIs

- **POST `/v1/api/register`**

  - **Body**
    - `name` (string, required)
    - `email` (string, required, unique)
    - `phone` (string, required, unique)
    - `password` (string, required, min:8)
    - `password_confirmation` (string, must match)
    - `role` (`admin|organizer|customer`, optional, defaults to `customer`)

  - **Response**
    - `200 OK` with wrapper:
      - `result: true`
      - `payload`: `{ name, email, phone }`

- **POST `/v1/api/login`**

  - **Body**
    - `email` (string, required)
    - `password` (string, required)

  - **Response**
    - `200 OK` with `payload`:
      - `id`, `name`, `email`, `phone`, `access_token`

- **POST `/v1/api/logout`** (auth required)

  - **Header**: `Authorization: Bearer {access_token}`
  - **Effect**: revokes all tokens for current user.

- **GET `/v1/api/me`** (auth required)

  - Returns the currently authenticated user (raw user JSON).

---

#### 2. Event APIs

All protected by `auth:sanctum`.

- **GET `/v1/events`**

  - **Query params (optional)**:
    - `page` (int)
    - `search` (string – title LIKE search)
    - `from` (date – start date)
    - `to` (date – end date)
    - `location` (string – exact location)
  - **Caching**: Results cached for 60 seconds with key based on query.
  - **Response**:
    - `payload.data` = array of events with tickets (paginated)
    - `payload.links`, `payload.meta` = pagination meta

- **GET `/v1/events/{id}`**

  - Returns a single event with its tickets.

- **POST `/v1/events`** (roles: `organizer`, `admin`)

  - **Body**:
    - `title` (required)
    - `description` (optional)
    - `date` (required, date/datetime)
    - `location` (required)
  - **Behavior**:
    - `created_by` is automatically set to the authenticated user.
    - Clears event cache.

- **PUT `/v1/events/{id}`** (roles: `organizer`, `admin`)

  - **Organizer constraint**: can only update **their own** events; admin can update any.
  - **Body**: any of `title`, `description`, `date`, `location` (all optional but validated if present).

- **DELETE `/v1/events/{id}`** (roles: `organizer`, `admin`)

  - Same ownership rule as update.

---

#### 3. Ticket APIs

All protected by `auth:sanctum` and role (`organizer`, `admin`).

- **POST `/v1/events/{event_id}/tickets`**

  - **Body**:
    - `type` (string, required, e.g. `VIP`, `Standard`)
    - `price` (numeric, required, `>= 0`)
    - `quantity` (int, required, `>= 1`)
  - **Ownership rule**:
    - Organizer can only create tickets for events where `created_by = organizer.id`.
    - Admin can create for any event.

- **PUT `/v1/tickets/{id}`**

  - **Body** (optional fields):
    - `type`
    - `price`
    - `quantity`
  - **Ownership**:
    - Organizer may only update tickets of **their own event**.

- **DELETE `/v1/tickets/{id}`**

  - Same ownership rule as update.

---

#### 4. Booking APIs

Protected by `auth:sanctum` and roles (`customer`, `admin`).  
Custom middleware `prevent.double.booking` is applied on booking creation.

- **POST `/v1/tickets/{id}/bookings`**

  - **Body**:
    - `quantity` (int, required, `>=1`)

  - **Rules**:
    - `PreventDoubleBooking` checks if the same user already has a `pending` or `confirmed` booking for this ticket.
    - Availability check ensures requested `quantity` does not exceed available quantity (`ticket.quantity - sum(pending/confirmed)`).

- **GET `/v1/bookings`**

  - Lists bookings for the authenticated customer/admin:
    - Includes `ticket`, `event`, and `payment` relationships.
    - Paginated.

- **PUT `/v1/bookings/{id}/cancel`**

  - Cancels the user’s own booking:
    - Sets `booking.status = cancelled`
    - If there is an associated payment, sets `payment.status = refunded`

---

#### 5. Payment APIs

Protected by `auth:sanctum` and roles (`customer`, `admin`).

- **POST `/v1/bookings/{id}/payment`**

  - **Behavior**:
    - Looks up booking by ID for the current user.
    - Calculates amount as `ticket.price * booking.quantity`.
    - Uses `PaymentService::process()` to simulate payment:
      - 80% chance of `success`, 20% `failed`.
      - Creates `payments` row.
      - Sets `booking.status` to `confirmed` on success, `pending` on failure.
    - **Notification**:
      - On success, sends `BookingConfirmed` notification to the user, **queued**.

- **GET `/v1/payments/{id}`**

  - Returns the payment with its booking, ticket, and event.

---

### Middleware, Services & Traits

- **Middleware**
  - `EnsureRole`: checks `user.role` against roles provided in route (e.g. `role:organizer,admin`).
  - `PreventDoubleBooking`:
    - Ensures a user cannot have more than one `pending`/`confirmed` booking for the same ticket.

- **Service**
  - `PaymentService` (`app/Services/PaymentService.php`):
    - `process(Booking $booking, float $amount, array $meta = [])`:
      - Creates a `Payment` record.
      - Randomly sets status to `success` or `failed` (80/20).
      - Updates `booking.status` accordingly.

- **Traits**
  - `ApiResponserTraits`: consistent REST response structure with helpers for status codes.
  - `ApiResourceTrait`: pagination meta & link helpers for resource collections.
  - `CommonQueryScopes`:
    - `scopeSearchByTitle($q, $search)` – used in event listing.
    - `scopeFilterByDate($q, $from, $to)` – date range filter for events.

---

### Notifications, Queues & Caching

- **Notifications**
  - `BookingConfirmed`:
    - Implements `ShouldQueue`, uses `Queueable`.
    - Sent when a booking is successfully paid (`status=success`).
    - Email body includes booking ID, event title, and quantity.

- **Queues**
  - Use `QUEUE_CONNECTION=database` (or other) and run:

```bash
php artisan queue:work
```

- **Caching**
  - `EventController@index` caches the paginated list of events (with filters) for 60 seconds.
  - Cache is flushed when events are created, updated, or deleted to avoid stale lists.

---

### Testing

- **Feature tests**
  - `AuthAndBookingFlowTest` covers:
    - Registration
    - Login
    - Organizer event & ticket creation
    - Customer booking & payment flow

- **Unit tests**
  - `PaymentServiceTest` verifies:
    - Payment creation
    - Amount and booking linkage
    - Status handling (`success`/`failed`)
    - Booking status transitions

Run all tests with:

```bash
php artisan test
```

---

### Postman Collection

You can export a Postman collection by hitting these endpoints and saving them as a collection with environment variables:

- **Base URL variable**: `{{base_url}}` → e.g. `http://localhost:8000`
- Example request paths:
  - `{{base_url}}/v1/api/register`
  - `{{base_url}}/v1/api/login`
  - `{{base_url}}/v1/events`
  - `{{base_url}}/v1/events/{id}`
  - `{{base_url}}/v1/events/{id}/tickets`
  - `{{base_url}}/v1/tickets/{id}/bookings`
  - `{{base_url}}/v1/bookings/{id}/payment`
  - `{{base_url}}/v1/payments/{id}`

Make sure to store the `access_token` from login as a Postman variable and apply it as a `Bearer` token for all protected endpoints.

