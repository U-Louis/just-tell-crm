# Draft

The first spec. Steward G2 is not written. This file injects the repository file `draft`. It is not the walking-skeleton spec, and the planner does not start from it. `storage-engine` is unset, so M1 does not start.

## Draft, as written

```text
4 Objets : contact, product, estimate, user. each with very few properties.

views available for each model :
- simple model list with search bar, export csv, pdf, crud per line (update sends to the detail page)
- detail page of a model with export PDF, crud, form for updating

view kanban : 3 columns : new, qualified, estimate sent

The goal of the scenario 1 is for a logged in user :
  - uc1: create a new contact and give it a name (it adds a contact post it to the new kanban view column)
  - uc2: qualify a contact (which means filling the blank in the form and click validate) (it moves the contact post it to the column qualified)
  - uc3: create an estimate for the qualified contact with a few lines of products and a grand total, and validate to send an email (it moves the contact to the estimate sent)

uc 4 5 6 7: crud user
uc 8 9 10 11: crud contact
uc 12 13 14 15: crud product
uc 16 17 18 19: crud estimate

uc 20 21 22 23 : export pdf of a model page
uc 24 25 26 27 : export of a csv off a model list

uc 28 29 30 31 : count total items of a model

(todo later) uc 32 : complex query formation : a model query crossed with other model queries


UI should be very simple, minimum effects. a top bar for navigation. a top right button for selecting the current acting user in the list. pagination for the lists. no fancy view for the emails sent, just text.
```

## Outcome and actor

The actor is "a logged in user." The outcome the draft calls the goal is scenario 1: uc1, uc2, and uc3, quoted above.

## Done-means

- A new contact with a name appears as a contact post in the kanban column "new."
- Filling the blank in the form and clicking validate moves that contact post to the column "qualified."
- An estimate for that qualified contact, with a few lines of products and a grand total, validated so that it sends an email, moves the contact to "estimate sent."
- The sent email is text.

The draft states no refusal and no error code. Those lines are absent on purpose.

## Non-goals

"minimum effects." "no fancy view for the emails sent, just text." uc32 is "todo later." A closed non-goal list is not in the draft. `docs/intent-questions.md` holds the candidates.

## Assumptions, each confirmed or guess

- confirmed: the four objects are contact, product, estimate, and user.
- confirmed: the kanban columns are "new", "qualified", and "estimate sent."
- confirmed: scenario 1 is uc1, then uc2, then uc3, for a logged-in user.
- confirmed: update on a list line sends the user to the detail page.
- confirmed: lists paginate. The page size is not stated.
- guess: the contact post is the contact, and the column is a property of that contact. This changes the data class.
- guess: one user can or cannot see another user's records. This changes authorization.
- guess: the button that selects the acting user is, or is not, the login. This changes authorization.

Guesses that would change authorization or a data class go back to the steward. This spec is not accepted.

## Invariants this slice must not break

No constitution is written yet. This injected spec names no invariant of its own. The recipe's locks are listed in `docs/intent-questions.md` and are not product rules from the draft.

## Data written and data read, with protection level

Written and read, in the draft's words: a contact name, the blanks of the qualify form, estimate lines of products, a grand total, an email that is text, and the acting user selected from the list. Protection level of each field: not named. The table is in `docs/intent-questions.md`.

## Security-critical paths touched, or "none"

The draft names a logged-in user and a button that selects the current acting user. Which of those is the authorization decision is not stated.

## Allowed paths and forbidden paths

This file does not name a slice's paths. Application code stays unwritten. `laravel new` stays unrun.

## New dependencies, or "none"

None. The draft names no package.

## Acceptance examples

- Given a logged-in user, when they create a new contact and give it a name, then a contact post is in the kanban column "new."
- Given that contact, when they fill the blank in the form and click validate, then the contact post is in the column "qualified."
- Given that qualified contact, when they create an estimate with a few lines of products and a grand total and validate to send an email, then the contact is in "estimate sent" and the email is text.

No refusal example is in the draft. No expected error code is fixed here.

## Test seams

None accepted. The draft names no seam. Domain, HTTP, and journey seams wait for a later spec the steward accepts.

## Quality budgets that apply, or "unchanged"

Unchanged. Every constitution budget is `unset`. Pagination is named without an integer.

## Risk class

The acting user, because the draft does not say how login works, and the stored fields, because the draft does not name them.

## How a failure is rolled back

No slice commit exists. The recipe's rollback, when a slice exists, is `git revert` of that commit.
