# Just Tell CRM

Forms CRM: pick an acting user, create a contact, qualify it, and send the test email.

Architecture: one Laravel 12 application, Inertia v2, React 19, TypeScript, one origin, session cookie. Product routes live in `routes/web.php`.

Read `docs/constitution.md` before editing.

## Boot and checks

- `./init.sh` is the boot. Docker runs Postgres, the PHP app, and the Vite dev server. A second run migrates and serves and does not drop the database. It prints `origin http://127.0.0.1:8000`.
- The host does not need PHP, Composer, or Node for that boot.
- `docker compose exec app php artisan test`
- `npx playwright test`
- `docker compose exec app vendor/bin/pint --test`

The app image needs PHP `mbstring`, `dom`, `pdo_pgsql`, and `pdo_sqlite`.

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
- A product text or number input is checked on the page when the form is submitted, and a failing check shows a message on that field and does not post.
- The verifier sets `passes`. The implementer leaves it false.
