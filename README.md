# TCG Engineering Hub

MVP SaaS para ejecutar el TCG Engineering Framework en proyectos de software.

**Stack:** Laravel 10 (API) + Vue 3 (Frontend)

---

## Requisitos

- PHP 8.1+
- Composer
- Node.js 18+
- MySQL 8+
- Laragon (recomendado en Windows)

---

## Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/tcg-engineering-hub.git
cd tcg-engineering-hub
```

### 2. Backend (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Edita `.env` con tus credenciales de base de datos:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tcg_engineering_hub
DB_USERNAME=root
DB_PASSWORD=
```

Ejecuta migraciones y seeders:
```bash
php artisan migrate:fresh --seed
php artisan serve
```

El backend estará disponible en: `http://localhost:8000`

---

### 3. Frontend (Vue 3)
```bash
cd frontend
npm install
npm run dev
```

El frontend estará disponible en: `http://localhost:5173`

---

## Usuarios de prueba

Todos los usuarios tienen la misma contraseña: `password`

| Nombre          | Email               | Rol      | Permisos                          |
|-----------------|---------------------|----------|-----------------------------------|
| Admin TCG       | admin@tcg.com       | admin    | Acceso total                      |
| Project Manager | pm@tcg.com          | pm       | Gestionar proyectos y artefactos  |
| Engineer        | engineer@tcg.com    | engineer | Editar y validar módulos          |
| Viewer          | viewer@tcg.com      | viewer   | Solo lectura                      |

---

## Ejecutar tests
```bash
cd backend
php artisan test
```

Output esperado:
```
PASS  Tests\Feature\GateOneTest
✓ cannot complete domain breakdown if big picture not done
✓ can complete domain breakdown if big picture is done

PASS  Tests\Feature\ModuleValidationTest
✓ cannot validate module with missing fields
✓ can validate module with all required fields

PASS  Tests\Feature\ProjectStatusGateTest
✓ cannot move to execution if required artifacts not done
✓ can move to execution if all required artifacts done

PASS  Tests\Feature\AuthorizationTest
✓ viewer cannot edit modules
✓ viewer cannot edit artifacts
✓ viewer cannot validate modules
✓ engineer can edit modules

Tests: 10 passed
```

---

## Endpoints principales

### Auth
```
POST   /api/v1/login
POST   /api/v1/logout
GET    /api/v1/me
```

### Proyectos
```
GET    /api/v1/projects
POST   /api/v1/projects
GET    /api/v1/projects/{id}
PUT    /api/v1/projects/{id}
PATCH  /api/v1/projects/{id}/status
DELETE /api/v1/projects/{id}
```

### Artefactos
```
GET    /api/v1/projects/{id}/artifacts
GET    /api/v1/projects/{id}/artifacts/{artifactId}
PUT    /api/v1/projects/{id}/artifacts/{artifactId}
PATCH  /api/v1/projects/{id}/artifacts/{artifactId}/status
```

### Módulos
```
GET    /api/v1/projects/{id}/modules
POST   /api/v1/projects/{id}/modules
GET    /api/v1/projects/{id}/modules/{moduleId}
PUT    /api/v1/projects/{id}/modules/{moduleId}
PATCH  /api/v1/projects/{id}/modules/{moduleId}/validate
DELETE /api/v1/projects/{id}/modules/{moduleId}
```

### Auditoría
```
GET    /api/v1/projects/{id}/audit
```