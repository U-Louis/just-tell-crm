# Just Tell CRM: questions the steward answers before G1

A person picks a user from a dropdown, creates a contact, qualifies it, and sends that contact a test email. Speech waits for v2.

The glimpse is the repository description plus the file `draft`. The description says: "A simple crm app with the most basic features. Except this one doesn't require a keyboard: you just tell it what to do." The draft is injected, unchanged, as the first spec at `docs/product-specs/draft.md`. The steward answered on 2026-11-13. This file records the draft and those answers. The steward accepts intent by writing `docs/intent.md` in a commit this session did not author.

S0 may write this file and `docs/progress.md`. The injected spec stays the draft. It is not an accepted slice spec. `docs/intent.md` stays the steward's G1 file.

The product name is Just Tell CRM. The GitHub repository is `just-tell-crm`. The workspace directory on disk is `just-tell-crm`.

Marks: **answered** means the record states it. **Partial** means the record states part and leaves a choice open. **Open** means the record does not state it. Quotes are the draft's words, the repository description, or the steward's words. A line that says "made up" is a property this session wrote because the steward said "make up some properties."

## 1. Outcome and actors — answered

- Scenario 1 is for the acting user: create a contact, qualify it, and send an estimate email.
- The four objects are contact, product, estimate, and user.
- The contact does not sign in. The contact is the kanban card and the recipient of the email.
- The steward: speech is v2. This version does not include it.
- The steward: the person uses a dropdown of users. A click makes that user the acting user. There is no password.

The draft's words "a logged in user" are this acting user.

## 2. Keyboard or speech — answered

This version uses the draft's forms: a search bar, a form, a click on validate, pagination, a top bar, and the user dropdown.

The steward: "speech will on v2, dont mind yet." Speech, a microphone, and telling the app what to do are out of this version.

## 3. The four objects and their properties — answered, made up

The steward said "make up some properties." The list below is that invention. It stays the record until the steward changes a line.

Contact:

- name. Set when the contact is created.
- email. The address the test email is sent to.
- company. The qualify blank, made up.
- column. `new`, `qualified`, or `estimate sent`.

Product:

- name.
- unit price. A number. The currency name is not chosen.

Estimate:

- the contact it belongs to.
- lines. Each line is one product and a quantity. Quantity is a whole number, made up. No maximum number of lines is named.
- grand total. Made up as the sum of each line's quantity multiplied by that product's unit price.

User:

- name. The dropdown shows this name. No email and no password.

Qualify means filling email and company and clicking validate. That stores those two and sets column to `qualified`. The draft states no refusal when a blank is left empty. No error code is invented here.

## 4. Kanban — answered

The steward: "it is a contact, the colum it is at in the kanban should be a prop."

The kanban card is the contact. `column` is a property of that contact. The three values are the draft's columns: `new`, `qualified`, `estimate sent`.

- uc1 creates the contact with a name and sets `column` to `new`.
- uc2 sets `column` to `qualified`.
- uc3 sets `column` to `estimate sent`.

The kanban shows contacts. The draft describes that view once, and this answer attaches the column to the contact.

Open: whether one contact can have more than one estimate. uc3 says "an estimate."

## 5. Scenario 1 — answered

- uc1: create a contact, give it a name, and it stands in the column `new`.
- uc2: fill email and company, click validate, and the contact stands in `qualified`.
- uc3: create an estimate for that qualified contact, with lines of products and the made-up grand total, and validate. That sends the email and sets `column` to `estimate sent`.

The steward: "the email should be on the contact, the text says that it is a test."

The message goes to the contact's email address. The body is the text `This is a test.` The page that shows a sent email shows that text, with no fancy view.

Open: what uc3 does when the contact's email is empty. No refusal code is named.

## 6. The other use cases — partial

Unchanged from the draft:

- "uc 4 5 6 7: crud user"
- "uc 8 9 10 11: crud contact"
- "uc 12 13 14 15: crud product"
- "uc 16 17 18 19: crud estimate"
- "uc 20 21 22 23 : export pdf of a model page"
- "uc 24 25 26 27 : export of a csv off a model list"
- "uc 28 29 30 31 : count total items of a model"
- "(todo later) uc 32 : complex query formation : a model query crossed with other model queries"

Update on a list line opens the detail page. Which of the four crud numbers is create, read, update, or delete is not stated. Whether each export number and each count number is one of the four objects is a reading.

Open: where the count is shown, whether it counts the whole object or the current search, and which fields a pdf or a csv contains.

## 7. Views and UI — partial

The list, the detail page, the kanban, the top bar, and pagination stay as the draft wrote them.

The top-right control is a dropdown of users. A click on one user makes that user the acting user.

Open: the page size. The draft says "pagination for the lists" and names no integer. Which fields the search bar matches is not stated.

## 8. Who may see a fact — partial

Answered: there is no login and no password. The dropdown chooses the acting user.

Open: whether that acting user sees every contact, product, and estimate, or only some of them. The steward's "no" answered the login. It did not name a visibility rule. That choice changes authorization.

## 9. What is stored, and each field's protection level — open on purpose

The steward: "all open for now." Every protection level stays unnamed. No field is marked `public`, `account`, or `secret`.

A level, when one is chosen later, answers four things: which page may receive the value, whether a server log may contain it, how long it is kept, and which actor may read or write it.

| Holds | Record says it holds | Who can see it | Protection level |
| --- | --- | --- | --- |
| User | name | The dropdown lists users | not named |
| Contact | name, email, company, column | The acting user. Whether another acting user sees the same contact is unstated | not named |
| Product | name, unit price | Unstated | not named |
| Estimate | contact, lines of product and quantity, grand total | Unstated | not named |
| Email body | The text `This is a test.` | Shown as text. The recipient is the contact's email | not named |

## 10. Non-goals — partial

The question "list of what?" asks for the list of things this version will not build. A later session treats a named non-goal as out of scope. The list is those exclusions, not a list of screens or a list of the four objects.

Already out of this version, from the draft or from the steward:

- Speech. The steward put it on v2.
- A password, and any login.
- A fancy view of a sent email. The draft says "just text."
- Decorative effects. The draft says "minimum effects."

These are candidates only. None is accepted:

- Taking payment.
- File attachments.
- Teams.
- An API token for another program.
- A second way to send mail.
- uc32, the crossed-model query. The draft says "todo later."

## Constitution blanks

The steward named PostgreSQL, told this session to choose the install command, and said "no login mech." Every other field stays `unset`.

| Field | Legal values | While `unset` | This record |
| --- | --- | --- | --- |
| `storage-engine` | PostgreSQL, MySQL, or SQLite | M1 does not start | PostgreSQL. The install command does not start the database. The application connection is `pgsql` |
| `install-command` | Exact non-interactive command, `laravel/framework` ^12, into this repository root | M1 plan stops | From this repository root: `d=$(mktemp -d) && composer create-project laravel/react-starter-kit:^1.0.1 "$d" --no-interaction --prefer-dist && cp -a "$d"/. . && rm -rf "$d"`. The steward said "install cmd whatever." This session wrote the command. Packagist release 1.0.1 requires `laravel/framework` ^12.0 and `inertiajs/inertia-laravel` ^2.0, and the kit's React is 19 with TypeScript. The root is not empty, so the command installs beside the tree and copies in. `docs/` stays. The kit also ships Fortify, with login, registration, and password reset. That Fortify login is not the product. `login-mechanism` stays none |
| `login-mechanism` | Breeze, Jetstream, Fortify, or hand-rolled | M1 does not keep an unwired starter login, and M2 does not start | none. The steward said "no login mech." The acting user is the dropdown. None of Breeze, Jetstream, Fortify, or hand-rolled is this answer |
| `complexity-score` | `crap` or `cognitive` | Report cyclomatic complexity, CRAP, and cognitive complexity. Neither blocks | `unset` |
| `complexity-ceiling` | A number the steward names | The score does not block | `unset` |
| `complexity-tools` | The commands two sessions will run | Evidence says the tool is unset. No package is added in order to measure | `unset` |
| `diff-posture` | `read` or `metrics` | G4 does not merge | `unset` |
| `instruction-model` | The model the compensatory lines target | The deletion pass has no baseline | `unset` |
| `asvs-level` | The level the organization adopts | M6 cannot claim a level. Secret, dependency, and validation checks still run | `unset` |
| `secret-scan-command` | The binary the steward confirms | The sensor is absent in evidence. No scanner flag is invented | `unset` |
| SLO `request` | The request the steward names | The latency gate stays off | `unset` |
| SLO `proportion` | The proportion the steward names | The latency gate stays off | `unset` |
| SLO `threshold` | The threshold the steward names | The latency gate stays off | `unset` |
| SLO `measurement point` | The point the steward names | The latency gate stays off | `unset` |
| SLO `window` | The window the steward names | The latency gate stays off | `unset` |
| Page size | An integer in the spec that enforces it | That slice does not start | `unset`. The draft says "pagination for the lists" and names no integer |
| Payload max | An integer in the spec that enforces it | That slice does not start | `unset` |
| Timeout seconds | An integer in the spec that enforces it | That slice does not start | `unset` |
| Rate limit | An integer in the spec that enforces it | That slice does not start | `unset` |
| `near-duplicate-reporter` | The reporter the steward names, or `unset` | No cutoff is installed | `unset` |
| `agents-md-line-budget` | A line count, or `unset` | Schema gate uses 120 | `unset` |
| `codeowners` | A human for each risk-class row | Forge ownership is a release blocker | `unset` |

## Still open before G1

The outcome is scenario 1, with the properties above. `docs/intent.md` does not exist yet.

1. The non-goal list in section 10. Speech, passwords, a fancy email view, and decorative effects are already out. The candidates under them are not accepted.
2. Whether the acting user sees every record.
3. What happens when uc3 runs and the contact's email is empty.
4. Whether one contact can have more than one estimate.
5. Protection levels. The steward left every one open on purpose.
6. The constitution fields that are still `unset` in the table above. `storage-engine`, `install-command`, and `login-mechanism` now have values.

## Already fixed by the recipe this session was told to apply

These are the Laravel React boilerplate locks. They are not answers from the steward, and they are not choices this session made.

- One Laravel 12 application. Inertia v2 renders React 19 and TypeScript. Same origin, session cookie. One session guard: `web`.
- Laravel owns the route, the actor, the authorization decision, input validation, domain rules, persistence, and the error code. React renders the props and the URLs that decision already computed.
- Product routes live in `routes/web.php`. Pest and Playwright.
- Authorization is one Policy, called from the Form Request.
- Error shape: `{ code, feature_id, fields? }`. Log shape: `feature_id`, `correlation_id`, `actor_id`, `outcome`, `code`.
- `app/Domain` has no HTTP, Inertia, Eloquent, or facade imports. One repository binding in `AppServiceProvider`.
- The M1 workflow installs no issue, clock, or failed-check starter. A job that calls a model stays at `contents: read`. A secret-bearing workflow on a branch an unattended run opened waits for a person who did not write the triggering text. A push from that job stays off until `docs/exec-plans/decisions.md` records `unattended branch push`.
