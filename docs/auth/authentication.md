# Authentication

JWT-based stateless authentication system for the API.

---

## Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/api/auth/register` | ❌ | Create new account |
| `POST` | `/api/auth/login` | ❌ | Login and receive token |
| `POST` | `/api/auth/logout` | ✅ | Invalidate current token |
| `POST` | `/api/auth/refresh` | ✅ | Get a new token |
| `GET` | `/api/auth/me` | ✅ | Get authenticated user |

---

## Structure

```
app/
├── Http/
│   ├── Controllers/Api/Auth/
│   │   └── AuthController.php
│   └── Requests/Auth/
│       ├── RegisterRequest.php
│       └── LoginRequest.php
├── Services/
│   └── AuthService.php
└── Models/
    └── User.php
```

---

## Request Validation

### Register

| Field | Rules |
|---|---|
| `f_name` | required, string, max:50 |
| `l_name` | required, string, max:50 |
| `email` | required, email, unique |
| `password` | required, min:8, confirmed |
| `gender` | required, in:male,female |
| `date_of_birth` | nullable, date, before:today |

### Login

| Field | Rules |
|---|---|
| `email` | required, email |
| `password` | required, string |

---

## Response Format

**Success:**
```json
{
    "status": "success",
    "message": "...",
    "user": { "id": 1, "f_name": "...", "role": "customer" },
    "token": "eyJ0eXAiOiJKV1Qi...",
    "token_type": "bearer",
    "expires_in": 3600
}
```

**Validation Error `422`:**
```json
{
    "message": "...",
    "errors": { "field": ["..."] }
}
```

**Unauthorized `401`:**
```json
{
    "status": "error",
    "message": "البريد الإلكتروني أو كلمة المرور غير صحيحة"
}
```

---

## Key Design Decisions

**Stateless auth** — Role is embedded in the JWT payload. No DB query needed to authorize requests.

**Role is server-assigned** — The `role` field is always set to `customer` on register, regardless of client input.

**Atomic registration** — User and Customer records are created inside `DB::transaction()`. If either fails, both roll back.

**Validation layer** — All input validation is handled in `FormRequest` classes before reaching the controller.

---

## Package

```
php-open-source-saver/jwt-auth
```

Config published at `config/jwt.php`. Secret key stored in `.env` as `JWT_SECRET`.
