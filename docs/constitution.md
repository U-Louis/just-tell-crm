# Constitution

Just Tell CRM is a forms CRM. A person picks an acting user, creates a contact, qualifies it, and sends the test email. Speech is v2.

## Fields

| Field | Value |
| --- | --- |
| `storage-engine` | PostgreSQL. The application connection is `pgsql`. The database name is `just_tell_crm`. |
| `install-command` | From this repository root: `d=$(mktemp -d) && composer create-project laravel/react-starter-kit:^1.0.1 "$d" --no-interaction --prefer-dist && cp -a "$d"/. . && rm -rf "$d"`. The copy excludes the temporary project's `.git` when it has one. `docs/` and the root file `draft` stay. The command does not start the database. |
| `login-mechanism` | none |
| `complexity-score` | unset |
| `complexity-ceiling` | unset |
| `complexity-tools` | unset |
| `diff-posture` | unset |
| `instruction-model` | unset |
| `asvs-level` | unset |
| `secret-scan-command` | unset |
| SLO `request` | unset |
| SLO `proportion` | unset |
| SLO `threshold` | unset |
| SLO `measurement point` | unset |
| SLO `window` | unset |
| `page-size` | 15 |
| `payload-max` | unset |
| `timeout-seconds` | unset |
| `rate-limit` | unset |
| `near-duplicate-reporter` | unset |
| `agents-md-line-budget` | unset |
| `codeowners` | unset |

An unset `complexity-score` means later sessions report cyclomatic complexity, CRAP, and cognitive complexity, and neither blocks. An unset `diff-posture` means G4 does not merge. Empty SLO fields stay the word unset.

## Stack locks

- One Laravel 12 application. Inertia v2 renders React 19 and TypeScript. Same origin, session cookie. One session guard name remains `web`, and this version does not sign a user in.
- Laravel owns the route, the actor, the authorization decision, input validation, domain rules, persistence, and the error code. React renders the props and the URLs that decision already computed.
- Product routes live in `routes/web.php`. `routes/api.php` has no product route.
- Authorization is one Policy, called from the Form Request.
- Error shape: `{ code, feature_id, fields? }`.
- Log shape: `feature_id`, `correlation_id`, `actor_id`, `outcome`, `code`.
- `app/Domain` has no HTTP, Inertia, Eloquent, or facade imports.
- `app/Http` controllers do not use Eloquent or `DB::`.
- `resources/js` does not call product `fetch` or axios.
- One repository interface, one Eloquent implementation, one binding in `AppServiceProvider`.
- The M1 workflow installs no issue, clock, or failed-check starter. A job that calls a model stays at `contents: read`. A secret-bearing workflow on a branch an unattended run opened waits for a person who did not write the triggering text. A push from that job stays off until `docs/exec-plans/decisions.md` records `unattended branch push`.

## Data protection

Protection levels are unnamed. The table of stored fields is in `docs/intent-questions.md`. This proof of concept does not mark a field `public`, `account`, or `secret`.

## Non-goals

Speech, passwords, login, Fortify, a fancy email view, decorative effects, payments, attachments, teams, API tokens, a second mail channel, and uc32.
