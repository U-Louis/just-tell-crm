# Use cases uc4–uc31

A person using the acting-user dropdown lists, creates, updates, and deletes users, contacts, products, and estimates, and downloads the CSV and PDF for those records. Scenario 1 still creates Northwind, qualifies it, and sends the test email.

## Outcome and actor

The actor is the acting user chosen from the dropdown. There is no password. The outcome is draft use cases uc4 through uc31. Number order inside each CRUD group is create, read, update, delete. PDF, CSV, and count follow user, contact, product, estimate.

uc8 is the existing contact create, also shown as a create form on the contact list. uc16 is the existing send on the contact page. The estimate list does not grow a second create form. uc32 stays out.

## Done-means

- The user list, contact list, product list, and estimate list each show the total count, a search box whose query parameter is `q`, a CSV link, the rows for the current page, and previous and next links. The page size is 15.
- Search matches name only. On the estimate list that name is the contact name.
- The count is the whole scoped set. It ignores `q` and the current page. Users and products count every row. Contacts and estimates count the acting user's rows.
- CSV downloads the rows matching `q`, or the whole scoped set when `q` is empty. It is not limited to the current page. Columns match the list. Contact CSV is name and column. Email stays off the contact list and off the contact CSV.
- Each detail page shows the fields, an update form, delete, and a PDF link. The PDF is a real PDF. The contact page still qualifies a contact and validates an estimate.
- Any acting user may create, update, and delete users and products. A contact and its estimates stay with the user who created the contact.
- With no acting user, every list is empty and a write is refused with `user.required`.
- Deleting a user who still owns a contact is refused with `user.delete.blocked`. Deleting a user who owns none removes that user and clears `acting_user_id` when it was that user.
- Deleting a contact removes that contact, its estimates, its estimate lines, and its sent emails. Another user's contact is 404.
- Deleting a product that is on an estimate line is refused with `product.delete.blocked`. A price change does not rewrite stored line prices or past grand totals.
- Deleting an estimate removes it and its lines. The contact column does not move backward. The sent-email row stays.
- Contact update can change name, email, and company, including emptying email and company. The column does not change and no mail is sent.
- Estimate update replaces lines and recomputes the grand total from current product unit prices. At least one quantity must be 1 or more. No mail is sent and the column does not change.
- Scenario 1 still works: an empty contact name shows `contact.name.required`, Northwind can be qualified, Cable quantity 2 and Lamp quantity 1 show grand total 25, the column becomes `estimate sent`, and the page shows `This is a test.`

The next step is a fixture factory. This slice does not build it.

## Non-goals

Speech, a microphone, an LLM orchestrator, passwords, login, Fortify, a fancy email view, decorative effects, payments, attachments, teams, API tokens, a second mail channel, and uc32. `docs/product-specs/M1.md` stays the historical scenario-1 spec and is not rewritten.

## Assumptions, each confirmed or guess

- confirmed: the four objects are contact, product, estimate, and user. User is name only. Product is name and an unsigned integer unit price. Names of users and products are unique.
- confirmed: columns are `new`, `qualified`, and `estimate sent`.
- confirmed: the email body is `This is a test.` and it is shown as plain text on the contact. The contact PDF may include that text after a send.
- confirmed: storage is PostgreSQL and the application connection is `pgsql`. `login-mechanism` is none.
- confirmed: a contact belongs to the acting user who creates it. Products stay shared. Users stay shared.
- confirmed: page size is 15, recorded in the constitution.
- POC choice: one contact may have more than one estimate. The first successful send sets `estimate sent`. Later sends leave it there. Deleting an estimate does not move the column backward.
- POC choice: mail delivery is Laravel's log mailer. Contact update and estimate update do not send mail.

## Invariants this slice must not break

- A contact is created with column `new` and belongs to the acting user who created it.
- Qualify from `new` sets column `qualified`.
- The first successful send sets column `estimate sent`. A later successful send leaves that column. Contact update, estimate update, and estimate delete leave the column where it is.
- Grand total on send is the sum of quantity times that product's unit price at send time. Cable 10 times quantity 2, plus Lamp 5 times quantity 1, is 25. A later product price change does not rewrite that stored line price or that grand total.
- Estimate update stores the current product unit price on the saved lines and sets the grand total from those current prices.
- The sent body is exactly `This is a test.`
- Empty name writes no contact. Empty email on send writes no estimate and no email. Missing acting user writes no row.
- The CRM log does not include the email address or the body.
- The kanban still lists every contact for the acting user. `listContacts` and `listEstimates` stay unpaged.

## Data written and data read, with protection level

Written: user name; contact name, email, company, column, and the user the contact belongs to; product name and unit price; estimate contact, lines of product, quantity, and stored unit price, and grand total; sent email to address and body `This is a test.`

Read on the kanban and the contact list: the acting user's contact name and column. Read on the estimate list: that user's contact name and grand total. Read on the contact page and contact PDF: name, email, company, column, grand total, and `This is a test.` after a send. Read on the user list: every user name. Read on the product list: every product name and unit price. The dropdown reads user names.

Protection level of each field: not named.

## Security-critical paths touched, or "none"

The acting-user session, the Policy check that refuses a write when no user is selected, the Policy check that refuses another user's contact or estimate, and the contact email that is stored and shown on the contact page and the contact PDF. There is no password and no login route.

## Allowed paths and forbidden paths

Allowed: `app/Domain`, `app/Http`, `app/Policies`, `app/Infrastructure`, `app/Providers`, `resources/js/Pages`, `resources/js/Layouts`, `resources/js/types.ts`, `resources/views`, `routes/web.php`, `tests/`, `docs/constitution.md`, `docs/features.json`, `docs/product-specs/use-cases.md`, `docs/product-specs/index.md`, `docs/exec-plans/decisions.md`, `docs/progress.md`, `composer.json`, `composer.lock`.

Forbidden: a product route in `routes/api.php`, Fortify or other login screens, speech, a second repository binding, rewriting `docs/product-specs/M1.md`, and a fixture factory in this slice.

## New dependencies, or "none"

`barryvdh/laravel-dompdf` renders the one PDF view. The Docker image already provides `ext-dom`. No other Composer or npm dependency is added.

## Acceptance examples

- Given Ada is the acting user, when they create a user named Dana, then Dana is on the user list and the count includes Dana.
- Given Ada is the acting user, when they submit an empty user name, then the code is `user.name.required` and no user row is written.
- Given a user named Ada, when they submit another user named Ada, then the code is `user.name.taken`.
- Given Ada owns a contact, when they delete Ada, then the code is `user.delete.blocked` and Ada remains.
- Given Dana owns no contact and Dana is the acting user, when Dana is deleted, then Dana is gone and `acting_user_id` is cleared.
- Given a contact in `estimate sent`, when its name, email, and company are updated, then those fields change, the column stays `estimate sent`, and no email is sent.
- Given a contact, when the update name is empty, then the code is `contact.name.required`.
- Given Ada's contact Northwind and its estimate and sent email, when Ada deletes that contact, then the contact, estimates, lines, and sent emails are gone. When Ben opens it, the status is 404.
- Given Cable is on an estimate line at unit price 10 with grand total 25, when Cable's unit price becomes 12, then the stored line unit price stays 10 and the grand total stays 25.
- Given that price change, when the estimate is updated to Cable quantity 1, then the stored line unit price is 12, the grand total is 12, the column stays `estimate sent`, and no email is sent.
- Given an estimate update whose quantities are all below 1, then the code is `estimate.lines.required`.
- Given an estimate, when it is deleted, then the estimate and its lines are gone, the column stays `estimate sent`, and the sent email remains.
- Given a product on no estimate line, when it is deleted, then the product is gone. Given a product on a line, when it is deleted, then the code is `product.delete.blocked`.
- Given 16 users, when the user list is opened, then the page shows 15 rows, the count is 16, and the CSV contains every user. A search downloads only the matching names. The count stays 16.
- Given a contact with an email, when the contact CSV is downloaded, then the header is name and column and the email is absent.
- Given a user, contact, product, or estimate detail, when the PDF link is opened, then the response is `application/pdf`.
- Given no acting user, when a list is opened, then it is empty. When a write is submitted, then the code is `user.required`.
- Given Ada created Northwind, when Ben is the acting user, then Ben does not see Northwind on the kanban, the contact list, or the estimate list.

## Test seams

- Domain: the new actions called with an in-memory repository. Middleware is absent. This catches the new refusals, the blocked deletes, the recomputed grand total, and the column staying put. It misses HTTP status and the page.
- HTTP: `php artisan test` on the sqlite test database. This catches status, error code, ownership 404, page size 15, the CSV header row, and `application/pdf`. It misses the browser.
- Journey: the existing scenario-1 browser check. This catches the visible scenario-1 steps and the new list, update, CSV, and PDF links exercised in the browser.

## Quality budgets that apply, or "unchanged"

Unchanged, except constitution `page-size`, which is 15. Every other constitution budget stays `unset`.

## Risk class

The acting-user session, because a write depends on the session, and the stored contact email, because the contact page and the contact PDF show it.

## How a failure is rolled back

`git revert` of the slice commit.

The next step is a fixture factory. This slice does not build it.
