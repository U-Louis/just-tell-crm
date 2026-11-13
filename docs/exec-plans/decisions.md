# Decisions

POC choices for the scenario-1 proof of concept. These are not a steward acceptance line.

1. Scope is scenario 1 only, plus the seed data it needs. Ada, Ben, and Cam are the users. Cable at unit price 10 and Lamp at unit price 5 are the products. PDF export, CSV export, counts, uc32, user CRUD, product CRUD screens, and estimate CRUD beyond creating an estimate in scenario 1 are out of this proof of concept.
2. The acting user sees every contact, product, and estimate. The dropdown does not filter by owner. The selected user id is stored in the session under `acting_user_id`. If none is selected, the page asks for a choice and state-changing actions are refused with code `user.required` and no row is written.
3. uc3 with an empty contact email is refused with code `contact.email.required`. The column stays `qualified`. No email is recorded.
4. A contact may have more than one estimate. The first successful send sets column to `estimate sent`. Later sends leave it there.
5. Protection levels stay unnamed. The CRM log line does not include the email address or the body. The contact page may show name, email, company, column, and the text `This is a test.` after a send.
6. Mail delivery for the proof of concept is Laravel's log mailer. A sent-email row stores the to address and the body `This is a test.` The contact page shows that body as plain text. No SMTP server is required. The log mailer writes the message to Laravel's log as its delivery record. The CRM log line stays the five fields `feature_id`, `correlation_id`, `actor_id`, `outcome`, and `code`.
7. Non-goals of this proof of concept: speech, passwords, login, Fortify, a fancy email view, decorative effects, payments, attachments, teams, API tokens, a second mail channel, PDF, CSV, counts, uc32.
8. Leave complexity-score, complexity-ceiling, diff-posture, instruction-model, asvs-level, secret-scan-command, SLO fields, page size, payload, timeout, rate limit, near-duplicate-reporter, agents-md-line-budget, and codeowners as `unset` in the constitution. No numbers were invented for them.
9. Empty name on create is refused with code `contact.name.required` and no contact row. That is the one refusal paired with the happy path.

## Notes that follow from those choices

- `laravel/react-starter-kit` 1.0.1 does not ship the Fortify package. It ships hand-rolled login, registration, and password reset. Those routes and screens are removed. `login-mechanism` is none. The acting user is the session id, not the `auth` middleware, because that middleware would redirect to a login screen this product does not have.
- The kit's test runner is PHPUnit. The proof of concept uses that runner. `phpunit.xml` uses sqlite in memory. The application runtime connection stays `pgsql`.
- Nothing was listening on port 5432. `compose.yaml` starts only Postgres. The image is `postgres:18` with `pull_policy: never`, because that image was already on this machine. Docker Hub credentials could not pull `postgres:16`. The data directory is the host folder `.pgdata`, mounted at `/var/lib/postgresql`, which is the path Postgres 18 expects. A named Docker volume could not initialize because the Docker disk was full. `.pgdata` is gitignored. The install command does not start the database. `init.sh` runs `docker compose up -d` when docker is available and does not delete `.pgdata`.
- Qualify may store an empty email and still move a `new` contact to `qualified`, so a later send can hit `contact.email.required`. A contact that is already `qualified` or `estimate sent` keeps that column when qualify is submitted again.
