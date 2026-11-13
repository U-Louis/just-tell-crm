# Progress

## 2026-11-13 S0

commit: the commit that adds this entry
contract: S0
passed: `docs/intent-questions.md` exists and opens with a one-line headline. The file `draft` is injected as `docs/product-specs/draft.md`, the first spec. Steward G2 is not written on it. The working tree has no application code, which is the boot this session has.
failed: none
dead end: none
artifacts: `docs/intent-questions.md`, `docs/progress.md`, `docs/product-specs/index.md`, `docs/product-specs/draft.md`
next: the steward writes G1 in `docs/intent.md`, including the line `Steward G1: accepted`, in a commit this session did not author. Contract S1 does not start until that commit exists. `storage-engine`, `install-command`, and `login-mechanism` are still questions. The injected spec is not an accepted walking-skeleton spec.

## 2026-11-13 S0 steward answers

commit: the commit that adds this entry
contract: S0
passed: `docs/intent-questions.md` records the steward's answers. Speech is v2. The properties were made up at the steward's request. The kanban card is the contact, and `column` is its property. The contact's email receives the text `This is a test.` The acting user is a dropdown click, with no password. `storage-engine` is PostgreSQL. `install-command` is the non-interactive `laravel/react-starter-kit:^1.0.1` copy into this root. `login-mechanism` is none. Protection levels stay unnamed. `docs/intent.md` is absent. The working tree has no application code.
failed: none
dead end: none
artifacts: `docs/intent-questions.md`, `docs/progress.md`
next: the steward writes G1 in `docs/intent.md`, including the line `Steward G1: accepted`, in a commit this session did not author. Contract S1 does not start until that commit exists. The non-goal candidates in section 10 of `docs/intent-questions.md` are still open, and so is whether one acting user sees every record.

## 2026-11-13 scenario 1 proof of concept

commit: the commit that adds this entry
contract: POC
passed: `php artisan test` passed, 14 tests, 186 assertions. Playwright `e2e/scenario-1.spec.ts` passed. In the browser, Ada was selected. An empty name showed `contact.name.required`. Northwind was created in column `new`, qualified with northwind@example.com and company Northwind, and the column became `qualified`. Cable quantity 2 and Lamp quantity 1 showed grand total 25. Validate sent the estimate, the column became `estimate sent`, and the page showed `This is a test.` The board then listed Northwind under `estimate sent`. A second contact, Blank, was qualified with an empty email. Send was refused with `contact.email.required` and the column stayed `qualified`.
failed: none
dead end: `php artisan serve` re-executes PHP_BINARY and drops a wrapper's ini flags, so `init.sh` serves with the current `php -S` from `public`. Postgres 18 uses the gitignored bind mount `.pgdata` because a named volume could not initialize. Docker Hub could not pull a new image, so the local `postgres:18` image is used.
artifacts: the Laravel app, `docs/constitution.md`, `docs/product-specs/M1.md`, `docs/features.json`, `docs/exec-plans/decisions.md`, `AGENTS.md`, `init.sh`, `compose.yaml`
next: every `passes` value in `docs/features.json` stays false. Boot with `./init.sh`. The origin is `http://127.0.0.1:8000`.

## 2026-11-13 contact belongs to the acting user

commit: the commit that adds this entry
contract: POC
passed: A contact stores the user who created it. The kanban, the contact list, and the estimate list show only that user's contacts and estimates. Products stay shared. `php artisan test` passed, 16 tests, 306 assertions. Playwright `e2e/scenario-1.spec.ts` passed, including Ben not seeing Northwind. In the browser, Ada saw Northwind on the board, the contact list, and the estimate list, with grand total 25 and the text `This is a test.` Ben's board, contact list, and estimate list did not show Northwind. Opening Ada's contact as Ben showed 404. Cable and Lamp were still on Ada's contact page.
failed: none
dead end: none
artifacts: `docs/intent-questions.md`, `docs/exec-plans/decisions.md`, `docs/features.json`, `docs/product-specs/M1.md`
next: every `passes` value in `docs/features.json` stays false. Boot with `./init.sh`. The origin is `http://127.0.0.1:8000`.

## 2026-11-13 container boot

commit: the commit that adds this entry
contract: POC
passed: `./init.sh` starts Postgres, the PHP app, and the Vite dev server, and prints `origin http://127.0.0.1:8000`. A second run migrated, seeded, and left Northwind in column `estimate sent`. `docker compose exec app php artisan test` passed, 16 tests, 306 assertions, on sqlite. The page loaded its script from `http://127.0.0.1:5173`. In the browser, Ada was selected. Northwind was created, qualified with northwind@example.com and company Northwind, then sent with Cable quantity 2 and Lamp quantity 1. The column became `estimate sent`, the page showed `This is a test.`, and the saved grand total was 25. Ada's contact list and estimate list showed Northwind. Ben was selected. Ben's board, contact list, and estimate list did not show Northwind. Opening `/contacts/6` as Ben showed 404.
failed: none
dead end: The first in-container test run had `DB_CONNECTION=pgsql` in the process environment. Laravel reads `$_SERVER` before PHPUnit's sqlite value, so RefreshDatabase wiped the Postgres rows. Compose now sets only `DB_HOST=postgres`, and `tests/bootstrap.php` copies the PHPUnit database values onto `$_SERVER`. `artisan serve` needs `--no-reload`, or it drops `DB_HOST` and the app dials 127.0.0.1. Docker Hub still has no username, so the image is built from the local PHP 8.4 image and Node 22 is downloaded from nodejs.org.
artifacts: `Dockerfile`, `.dockerignore`, `docker-compose.yml`, `init.sh`, `vite.config.js`, `phpunit.xml`, `tests/bootstrap.php`, `.env.example`, `AGENTS.md`, `docs/exec-plans/decisions.md`, `docs/product-specs/M1.md`
next: every `passes` value in `docs/features.json` stays false. Boot with `./init.sh`. The origin is `http://127.0.0.1:8000`.
