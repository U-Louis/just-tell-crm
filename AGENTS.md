# Just Tell CRM

Forms CRM: pick an acting user, create a contact, qualify it, and send the test email.

Architecture: one Laravel 12 application, Inertia v2, React 19, TypeScript, one origin, session cookie. Product routes live in `routes/web.php`.

Read `docs/constitution.md` before editing.

## Boot and checks

- `docker compose up -d` starts Postgres when it is not already running. `init.sh` does this when docker is available.
- `./init.sh` prints `origin http://127.0.0.1:8000`, migrates, seeds, and serves. A second run does not drop the database.
- `php artisan test`
- `npx playwright test`
- `vendor/bin/pint --test`

PHP needs `mbstring`, `dom`, `pdo_pgsql`, and `pdo_sqlite`.

## Where to read

- `docs/features.json`
- `docs/progress.md`
- `docs/product-specs/`
- `docs/exec-plans/decisions.md`
- `docs/intent-questions.md`

## Rules that are easy to miss

- `app/Domain` has no Eloquent and no HTTP imports.
- The Policy is the allow decision. Form Requests call it.
- React mutations use Inertia `useForm` and URLs the server put in props. Pages do not call `fetch` or axios for product data.
- The acting user is the session dropdown. There is no login screen.
- The verifier sets `passes`. The implementer leaves it false.
