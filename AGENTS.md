# Agent Instructions

## Stack
- Laravel 13 + Inertia.js (React 19) starter. Dual package managers: `composer` (PHP), `pnpm` (Node.js).
- **Single verification command:** `composer ci:check` (runs Pint lint → Prettier check → PHPStan → PHPUnit in order).

## Commands
- **Dev:** `npm run dev` (Laravel serve + queue:listen + pail + Vite concurrently)
- **Lint:** `composer lint` (Pint/PHP), `npm run lint` (ESLint/JS-TS)
- **Format:** `npm run format` (Prettier; skips `resources/js/components/ui/*`)
- **Types:** `npm run types:check` (tsc), `composer types:check` (PHPStan level 7)
- **Tests:** `php artisan test` (PHPUnit, SQLite in-memory per `phpunit.xml`)
- **Build:** `npm run build`

## Development Discipline
- **Security:** Follow OWASP top-10 (validate input, escape output, CSRF, authz checks).
- **TDD:** Write PHPUnit tests in `tests/Feature/` or `tests/Unit/` before implementation. No extra frameworks — PHPUnit defaults only.
- **Code style:** Simple, clean, efficient, self‑documenting. Comments only for complex logic. Use PHPDoc for documenting; no prose comments otherwise.
- **Static analysis required:** Both PHPStan and ESLint must pass before commit.

## Frontend Standards
- **Responsive:** Mobile‑first; verify across breakpoints.
- **Accessibility:** Best‑effort WCAG; semantic HTML, ARIA labels, keyboard navigation, Radix UI primitives. No dedicated a11y test tool yet.
- **Styling:** Tailwind CSS 4 + shadcn/ui (New York style, `components.json`) only. No hand‑written CSS outside `resources/css/app.css`.
- **Path alias:** `@/*` → `resources/js/*` (see `tsconfig.json`, `components.json`).

## Localization (i18n)
- Supported: `en`, `id` (set in `config/app.php` `supported_locales`). All user‑facing strings must exist in both.
- **PHP:** `__()` with keys in `lang/{locale}/...` files.
- **React:** `const { __ } = lang();` from `@erag/lang-sync-inertia/react`.
- After adding keys run `php artisan ts:generate-locale-types` → writes `resources/js/types/locales.ts`.
- Locale priority: cookie (`locale`) → `Accept-Language` → default.

---

## Permission System

**1. Core architecture**
- **Package:** `spatie/laravel-permission` (heavily customized).
- **Models:**
  - `App\Models\Permission` – extends Spatie's Permission, adds `module_id`, `title`, `description`.
  - `App\Models\Role` – standard Spatie Role (teams disabled).
  - `App\Models\Module` – groups permissions logically.
- **Definition:** Permissions live in **enum‑backed classes** that implement `DefinesPermissions`.
  - `module()` → returns a kebab‑case slug (e.g., `profile`).
  - `permissions()` → returns the enum class where each case is a permission (e.g., `ProfilePermission::View`).
  - `title()`/`description()` → return **translation keys**, not raw strings.

**2. Sync workflow**
- Command: `php artisan permissions:sync`.
- Reads the list of definition classes from `config/authorization.php`.
- For each enum case: creates/updates DB rows with `title`, `description`, linking to the correct `Module`.
- Flags **orphans** (permissions in DB not declared in code) and can **prune** them (`--prune`, optional confirmation).
- Clears the permission cache (`app(PermissionRegistrar::class)->forgetCachedPermissions()`).

**3. i18n integration**
- `title`/`description` are **translation‑key strings** (e.g., `profile.permissions.title.view`).
- Resolved at runtime via `__()` in both PHP backend and React (`lang()` hook).

**4. Current state**
- Only `ProfilePermissionsDefinition` is registered.
- `User` model **does not yet** use `Spatie\Permission\Traits\HasRoles`. Adding RBAC requires `use HasRoles;` on the model first.
- Teams feature disabled.

**5. Database schema**
- Custom migration adds `module_id`, `title`, `description` to the standard `permissions` table.
- Standard pivot tables (`model_has_permissions`, `model_has_roles`, `role_has_permissions`) handle many‑to‑many relations.

**6. Agent gotchas**
- **Never add permissions manually**; always define them in an enum and run `permissions:sync`.
- **Never store raw English text** in `title`/`description`; they must be translation keys.
- **Confirm module associations** before adding new permissions – the command auto‑creates/updates the `Module` row.
- **Check the `title`/`description` translation keys** are present in `lang/en` and `lang/id`; otherwise the UI will show blank strings.

---

## Architecture Overview
- **Backend:** Controllers in `app/Http/Controllers/`, models in `app/Models/`.
- **Frontend:** Inertia React in `resources/js/`, pages in `resources/js/pages/`.
- **Component paths:** Custom aliases in `components.json` (`@/components`, `@/lib/utils`, etc.).
- **Routes:** Inertia routes in `routes/web.php` and `routes/settings.php`.
- **Generated/ignored by tooling:** `resources/js/components/ui/*`, `resources/js/routes/**`, `resources/js/wayfinder/**` (ESLint ignores these).