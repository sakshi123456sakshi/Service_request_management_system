# Service Request & Complaint Management System

A full-stack web application where **Users** raise service requests/complaints,
**Admins** assign and track them, and **Technicians** work and resolve them.

- **Frontend:** React 18 + Vite (plain CSS, `react-router-dom`, `axios`)
- **Backend:** PHP 8 (plain PHP with PDO, no framework) — REST-style JSON API
- **Database:** MySQL / MariaDB (relational schema with FKs, constraints, timestamps)
- **Auth:** Session/cookie based, bcrypt password hashing, role-based access control

---

## 1. Project Structure

```
project/
├── backend/                     PHP API (place this on your PHP/Apache server)
│   ├── api/
│   │   ├── auth/                register.php, login.php, logout.php, me.php
│   │   ├── categories/          list.php
│   │   ├── technicians/         list.php
│   │   ├── requests/            create.php, list.php, detail.php, assign.php,
│   │   │                        priority.php, status.php, remark.php
│   │   └── dashboard/           summary.php
│   ├── config/                  db.php, bootstrap.php (CORS + session + errors)
│   ├── helpers/                 response.php, validate.php, auth.php, request_helpers.php
│   ├── database/                schema.sql, seed.sql, generate_hash.php
│   └── uploads/                 uploaded attachments land here (write-permission needed)
├── frontend/                    React + Vite SPA
│   └── src/
│       ├── api/                 axios client + endpoint wrappers
│       ├── context/              AuthContext (session-based)
│       ├── components/          Navbar, tables, badges, history timeline, etc.
│       └── pages/                Login, Register, dashboards, request detail, etc.
└── screenshots/                 (add your own screenshots here — see note below)
```

---

## 2. Database Setup

1. Create the database and tables:
   ```bash
   mysql -u root -p < backend/database/schema.sql
   ```
2. Load seed data (categories + 4 test accounts):
   ```bash
   mysql -u root -p < backend/database/seed.sql
   ```

This creates the `service_request_system` database with tables:
`users`, `categories`, `service_requests`, `request_assignments`,
`request_history`, `remarks`, `auth_tokens` — all with primary/foreign keys,
`ENUM` constraints for status/priority/role, and `created_at` / `updated_at`
timestamps.

### Test Login Credentials
All seeded accounts use the same password: **`Password@123`**

| Role       | Email                    | Password       |
|------------|---------------------------|----------------|
| Admin      | admin@srms.com            | Password@123   |
| Technician | technician@srms.com       | Password@123   |
| Technician | technician2@srms.com      | Password@123   |
| User       | user@srms.com              | Password@123   |

> New self-registered accounts are always created with the `user` role.
> To create additional admins/technicians, register normally, then promote
> the account in MySQL: `UPDATE users SET role='technician' WHERE email='...';`

---

## 3. Backend Setup (PHP)

**Requirements:** PHP 8.0+, the `pdo_mysql` extension, and Apache/Nginx (or PHP's built-in server).

1. Copy the `backend/` folder to your web server, e.g. `htdocs/service-request-system/backend` (XAMPP/WAMP) or any PHP host.
2. Configure DB credentials — either edit the constants directly in
   `backend/config/db.php`, or set environment variables `DB_HOST`, `DB_PORT`,
   `DB_NAME`, `DB_USER`, `DB_PASS` (defaults: `127.0.0.1:3306`, `service_request_system`, `root`, empty password).
3. Make sure `backend/uploads/` is writable by the web server (for attachments):
   ```bash
   chmod 775 backend/uploads
   ```
4. Start it:
   - **XAMPP/WAMP/shared hosting:** just place the folder under the web root — no extra command needed.
   - **PHP built-in server (for local dev/testing):**
     ```bash
     cd backend
     php -S localhost:8000
     ```
     Then the API base URL is `http://localhost:8000`.

5. Update `backend/config/bootstrap.php` → `$allowedOrigins` if your frontend
   runs on a different host/port than `http://localhost:5173`.

### API Overview (all return JSON `{ success, message, data }`)

| Method | Endpoint                          | Access                | Purpose |
|--------|-------------------------------------|------------------------|---------|
| POST   | `/api/auth/register.php`            | Public                 | Register a new user |
| POST   | `/api/auth/login.php`               | Public                 | Log in (sets session cookie) |
| POST   | `/api/auth/logout.php`              | Authenticated          | Log out |
| GET    | `/api/auth/me.php`                  | Authenticated          | Current user |
| GET    | `/api/categories/list.php`          | Authenticated          | List categories |
| GET    | `/api/technicians/list.php`         | Admin                  | List technicians (for assignment) |
| POST   | `/api/requests/create.php`          | User                   | Create a request (multipart, optional file) |
| GET    | `/api/requests/list.php`            | Authenticated          | List requests (role-scoped) + filters `q,status,priority,category_id` |
| GET    | `/api/requests/detail.php?id=`      | Authenticated          | Full detail incl. history + remarks |
| POST   | `/api/requests/assign.php`          | Admin                  | Assign/reassign a technician |
| POST   | `/api/requests/priority.php`        | Admin                  | Change priority |
| POST   | `/api/requests/status.php`          | Admin, Technician       | Move status forward (validated transitions) |
| POST   | `/api/requests/remark.php`          | Authenticated          | Add a remark |
| GET    | `/api/dashboard/summary.php`        | Authenticated          | Role-scoped dashboard counts |

**Status flow enforced server-side:** `New → Assigned → In Progress → Resolved → Closed`
(admin can also reassign while Assigned, and reopen Resolved → In Progress).

---

## 4. Frontend Setup (React + Vite)

**Requirements:** Node.js 18+.

```bash
cd frontend
npm install
cp .env.example .env      # edit VITE_API_URL to point at your backend
npm run dev                # starts on http://localhost:5173
```

`VITE_API_URL` should be the backend base URL with **no trailing slash**, e.g.:
```
VITE_API_URL=http://localhost:8000
```
or, on XAMPP:
```
VITE_API_URL=http://localhost/service-request-system/backend
```

Build for production:
```bash
npm run build      # outputs static files to frontend/dist
npm run preview    # preview the production build locally
```

---

## 5. Security Notes

- Passwords hashed with bcrypt (`password_hash` / `password_verify`), never stored or logged in plain text.
- All SQL uses **PDO prepared statements** (`ATTR_EMULATE_PREPARES` disabled) — no string-concatenated queries anywhere, preventing SQL injection.
- Auth is **session-cookie based** (`httpOnly`, `SameSite=Lax`), with `session_regenerate_id()` on login to prevent session fixation.
- Every request/admin/technician endpoint calls `require_auth()` / `require_role()` and, for request-specific actions, `guard_request_access()` so a user can only see their own requests and a technician only their assigned ones.
- Status changes are validated against a strict allowed-transition table (`helpers/request_helpers.php`) — you cannot jump straight from `New` to `Closed`, for example.
- File uploads are restricted by extension whitelist and 5MB size limit, renamed to random filenames, and the `uploads/` folder has an `.htaccess` that blocks script execution.
- All error output goes through a global exception/error handler — raw PHP errors/stack traces are never sent to the client.

---

## 6. Screenshots

Run the app locally (steps above), then log in as each role and capture:
`Login`, `Register`, `User Dashboard`, `New Request form`, `Request Detail
(with history)`, `Admin Dashboard`, `Admin Requests + filters`, `Technician
Dashboard`. Save them into the `screenshots/` folder before submitting.

---

## 7. Tech Summary (for evaluation)

- Backend development: plain PHP 8, PDO ✅
- Database connectivity & CRUD: full CRUD on requests, remarks, assignments ✅
- Authentication: bcrypt + PHP sessions ✅
- Role-based authorization: `user` / `admin` / `technician` guards on every endpoint ✅
- Business logic: status-flow validation, request-code generation, dashboard aggregation ✅
- Database relationships: FKs across 7 tables with `ON DELETE CASCADE`/`RESTRICT` ✅
- Server-side validation: `helpers/validate.php` on every write endpoint ✅
- Clean project structure: `config/ helpers/ api/` separation on the backend, `api/ context/ components/ pages/` on the frontend ✅
