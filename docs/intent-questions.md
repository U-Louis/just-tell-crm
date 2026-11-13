# Just Tell CRM: questions the steward answers before G1

A logged-in user creates a contact, qualifies it, and sends an estimate. The draft leaves the keyboard, the fields, and the login open.

The glimpse is the repository description plus the file `draft`. The description says: "A simple crm app with the most basic features. Except this one doesn't require a keyboard: you just tell it what to do." The draft is injected, unchanged, as the first spec at `docs/product-specs/draft.md`. This file records what that text states and what it leaves open. The steward accepts intent by writing `docs/intent.md` in a commit this session did not author.

S0 may write this file and `docs/progress.md`. The injected spec is the draft the steward asked to place. It is not an accepted slice spec. `docs/intent.md` stays the steward's G1 file.

The product name is Just Tell CRM. The GitHub repository is `just-tell-crm`. The workspace directory on disk is `just-tell-crm`.

Marks: **answered** means the record states it. **Partial** means the record states part and leaves a choice open. **Open** means the record does not state it. Quotes are the draft's words, or the repository description where noted.

## 1. Outcome and actors — partial

Answered:

- Scenario 1 is "for a logged in user."
- That user creates a contact, qualifies it, and creates an estimate that sends an email. The three sentences are uc1, uc2, and uc3 in section 5.
- The draft names four objects: "contact, product, estimate, user. each with very few properties."
- A control selects "the current acting user in the list."

The contact is the thing that moves across the kanban. The draft does not say the contact signs in, or that the contact is a user of the app. The user is a separate object.

Open: whether anyone besides that logged-in user acts in the product. A customer who only receives the email is not named as an actor.

## 2. Keyboard or speech — open

The repository description says the app does not require a keyboard: "you just tell it what to do."

The draft describes a search bar, a form, a click on validate, pagination, a top bar, and a button. It never uses the words speech, microphone, voice, or keyboard.

Which surface scenario 1 uses is open. The two texts disagree, and this session does not pick one.

## 3. The four objects and their properties — partial

Answered: the objects are contact, product, estimate, and user. "each with very few properties."

The draft names one property: the contact's name, in uc1 ("give it a name"). An estimate has "a few lines of products and a grand total." Qualify means "filling the blank in the form."

Open, and this changes the data class:

- Which properties each object stores, beyond the contact's name and the estimate's lines and grand total.
- Which blanks the qualify form fills.
- What a product line stores (which product, a quantity, a price, or something else).
- How the grand total is obtained.
- What "a few" lines means, as a number or as unbounded.
- What the user object stores (a name, an email, a password, or something else).

## 4. Kanban — partial

Answered: "view kanban : 3 columns : new, qualified, estimate sent."

- uc1 "adds a contact post it to the new kanban view column."
- uc2 "moves the contact post it to the column qualified."
- uc3 "moves the contact to the estimate sent."

The words "contact post it" are the draft's. One reading is a post-it on the board. Another is that the app posts the contact into the column. This session does not pick a reading.

Open:

- Whether the column is stored on the contact.
- Whether a contact can sit in a column without uc1, uc2, or uc3.
- Whether the kanban is only for contacts. The draft describes that view once, and every move names the contact.
- Whether a contact can have more than one estimate. uc3 creates "an estimate" and then moves the contact.

## 5. Scenario 1 — answered as the goal

The draft calls these three the goal of scenario 1.

- uc1: "create a new contact and give it a name (it adds a contact post it to the new kanban view column)."
- uc2: "qualify a contact (which means filling the blank in the form and click validate) (it moves the contact post it to the column qualified)."
- uc3: "create an estimate for the qualified contact with a few lines of products and a grand total, and validate to send an email (it moves the contact to the estimate sent)."

Open: an example name, who receives the email, and what the email text says. The draft says the sent email is "just text" and that there is "no fancy view for the emails sent." It does not show a sample sentence.

The draft states no refusal. It states no error code, no empty-name rule, and no case where validate does nothing.

## 6. The other use cases — partial

The draft lists these without assigning a verb to a number:

- "uc 4 5 6 7: crud user"
- "uc 8 9 10 11: crud contact"
- "uc 12 13 14 15: crud product"
- "uc 16 17 18 19: crud estimate"
- "uc 20 21 22 23 : export pdf of a model page"
- "uc 24 25 26 27 : export of a csv off a model list"
- "uc 28 29 30 31 : count total items of a model"
- "(todo later) uc 32 : complex query formation : a model query crossed with other model queries"

The list view says "crud per line (update sends to the detail page)." Update is one of the crud actions, and it opens the detail page. Which of the four numbers is create, read, update, or delete is not stated.

Four numbers are given for pdf, four for csv, and four for the count, and four models are named. Whether each number is one model is a reading. The draft does not say it.

uc32 is marked "todo later." It is deferred by the draft. It is not a confirmed non-goal until the steward says this version excludes it.

Open: where the count is shown, whether the count is the whole model or the current search, which fields a pdf or a csv contains, and which list the csv exports (the full list, or the search result).

## 7. Views and UI — answered as far as the draft goes

"views available for each model :

- simple model list with search bar, export csv, pdf, crud per line (update sends to the detail page)
- detail page of a model with export PDF, crud, form for updating"

"UI should be very simple, minimum effects. a top bar for navigation. a top right button for selecting the current acting user in the list. pagination for the lists. no fancy view for the emails sent, just text."

Open: the page size, which fields the search bar matches, and where the email text is shown. No pixel width and no component library are named. This session does not choose one.

## 8. Who may see a fact — open

The draft says the actor is "a logged in user" and that a button selects "the current acting user in the list."

It does not say whether selecting a user is the login, or whether a password exists beside that button. It does not say whether one user sees another user's contacts, products, or estimates.

This choice changes authorization. It stays a question.

## 9. What is stored, and each field's protection level — open

The record names what is held. No protection level is chosen.

Each stored row needs four answers. The names `public`, `account`, and `secret` are labels for a bundle of those four. A product may keep those names. The gate wants the four answers.

- **Return.** Which page is allowed to receive the value.
- **Log.** Whether a server log may contain it.
- **Retention.** How long it is kept, or that no duration is named.
- **Access.** Which actor may read or write it.

| Holds | Record says it holds | Who can see it | Protection level |
| --- | --- | --- | --- |
| Contact | A name. Other properties are "very few" and unnamed. A kanban column after uc1, uc2, or uc3 | The logged-in user. Whether another user sees it is unstated | not named |
| Product | Unnamed properties. It appears as a line on an estimate | Unstated | not named |
| Estimate | A few lines of products, a grand total, and a sent email that is text | Unstated. uc3 sends an email | not named |
| User | Unnamed. The acting user is chosen from "the list" | Unstated | not named |
| Email text | "just text" | Unstated. Who receives it is unstated | not named |

## 10. Non-goals — open

The draft states two limits: "minimum effects," and "no fancy view for the emails sent, just text." uc32 is "todo later."

A closed non-goal list is absent. These are candidates only, and none is accepted here: payments, file attachments, teams, an API token, a second mail channel, and a speech surface. Speech stays a candidate until section 2 is answered. Confirming any of them is the steward's G1 text.

## Constitution blanks

The record states no legal value for any field below. Each stays `unset`. "A logged in user" and the button that selects the acting user are not a value of `login-mechanism`. The legal values are Breeze, Jetstream, Fortify, or hand-rolled. This session does not pick one, and it does not pick PostgreSQL, MySQL, SQLite, or an install command.

| Field | Legal values | While `unset` | This record |
| --- | --- | --- | --- |
| `storage-engine` | PostgreSQL, MySQL, or SQLite | M1 does not start | `unset` |
| `install-command` | Exact non-interactive command, `laravel/framework` ^12, into this repository root | M1 plan stops | `unset` |
| `login-mechanism` | Breeze, Jetstream, Fortify, or hand-rolled | M1 does not keep an unwired starter login, and M2 does not start | `unset` |
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

The recipe's stage-0 hard stop is an outcome or non-goals missing from `docs/intent.md`. The draft supplies scenario 1 as an outcome. It does not supply a closed non-goal list.

1. Keyboard or speech. The description says you just tell it. The draft describes forms and a validate click.
2. The properties of contact, product, estimate, and user, including the qualify blanks, a product line, and the grand total.
3. Whether "contact post it" is a post-it, and whether the kanban column is stored on the contact.
4. Who receives the email, and the text that is shown.
5. Whether selecting the acting user is the login, and whether one user sees another user's records.
6. A protection level for each stored field.
7. Which non-goals are accepted, including whether uc32 stays out of this version.
8. The constitution fields in the table above. `storage-engine`, `install-command`, and `login-mechanism` stop M1 while they stay `unset`.

## Already fixed by the recipe this session was told to apply

These are the Laravel React boilerplate locks. They are not answers from the steward, and they are not choices this session made.

- One Laravel 12 application. Inertia v2 renders React 19 and TypeScript. Same origin, session cookie. One session guard: `web`.
- Laravel owns the route, the actor, the authorization decision, input validation, domain rules, persistence, and the error code. React renders the props and the URLs that decision already computed.
- Product routes live in `routes/web.php`. Pest and Playwright.
- Authorization is one Policy, called from the Form Request.
- Error shape: `{ code, feature_id, fields? }`. Log shape: `feature_id`, `correlation_id`, `actor_id`, `outcome`, `code`.
- `app/Domain` has no HTTP, Inertia, Eloquent, or facade imports. One repository binding in `AppServiceProvider`.
- The M1 workflow installs no issue, clock, or failed-check starter. A job that calls a model stays at `contents: read`. A secret-bearing workflow on a branch an unattended run opened waits for a person who did not write the triggering text. A push from that job stays off until `docs/exec-plans/decisions.md` records `unattended branch push`.
