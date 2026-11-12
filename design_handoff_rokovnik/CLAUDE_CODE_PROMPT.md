# Prompt for Claude Code

Paste everything below the line into Claude Code, started in an empty project folder that contains this `design_handoff_rokovnik/` directory.

---

You are building **Rokovnik**, a mobile-first web app for a small law office in Bosnia and Herzegovina: mail register (knjiga pošte), deadline book (rokovnik), reminders, and a draft cost sheet (troškovnik).

## Read first
1. `design_handoff_rokovnik/README.md` — full spec: screens, tokens, business rules, data model.
2. `design_handoff_rokovnik/Rokovnik.dc.html` — the clickable HTML prototype. The markup is at the top; the state logic is the `class Component` block at the bottom. It is a design reference, not code to copy. Rebuild it with the stack below.

Read both completely before writing any code, then give me a short implementation plan and wait for my OK.

## Stack
- Latest stable **Laravel**, PHP 8.3+
- **Vue 3** (Composition API, `<script setup>`, TypeScript) with **Inertia.js** and Vite
- **PostgreSQL**
- **Tailwind CSS**, with the README's design tokens defined once in the theme (colours, fonts, radii) — no ad-hoc hex values in components
- **Pest** for tests, Laravel Pint for formatting
- Queue and scheduler on the database driver for now
- Local environment via Laravel Sail (Docker) with a `pgsql` service

Start from Laravel's official Vue starter kit so authentication is in place. Do not add other packages without asking.

## Non-negotiable rules
1. **The app only proposes.** Deadlines, events, notes and cost items are created as `proposed`; only an explicit user action makes them `confirmed`, storing who and when. Proposed items never generate reminders.
2. **Confirming a deadline requires three separate acknowledgements** (rule and jurisdiction, start date, last day). Enforce this in a Form Request / domain action on the server as well as in the UI.
3. **Deadline arithmetic is exactly what the README describes** and nothing more. Do not invent holiday calendars, statutory deadline rules, or legal interpretation. Always show the stated assumptions next to a proposal.
4. **No tariff data.** Cost amounts are entered or confirmed by the lawyer; the tariff source is free text.
5. **Every significant change writes an audit log entry** with old and new values. Implement this in one place (domain actions or model observers), not scattered through controllers.
6. **Every mail entry, deadline and cost item keeps a link to its source document** and quoted passage.
7. **Nothing is sent anywhere.** No e-mail to courts or clients, no external integrations.
8. **Document reading and speech-to-text sit behind interfaces** (`DocumentExtractor`, `SpeechTranscriber`) with a fake driver that returns the seed fixture. Do not call any external AI or OCR service.
9. **All UI copy is Bosnian (ijekavica, Latin script)** — use the prototype's wording verbatim. Dates display as `dd.mm.gggg.`, money as `1.234,56 KM`. Code, identifiers, commits and comments are in English. Put UI strings in `lang/bs` so they are not hard-coded.
10. Uploaded documents go to a private disk and are served only through authorised routes. Scope every query to the user's office.

## Architecture
- Thin controllers; business logic in single-purpose action classes (`ConfirmDeadline`, `SaveMailEntry`, `ApproveCostSheet`, …).
- PHP backed enums for statuses, jurisdiction, mail direction, event type.
- Policies for authorisation.
- Vue: pages under `resources/js/Pages`, shared pieces under `resources/js/Components` — at minimum `StatusChip`, `ListRow`, `SectionLabel`, `SegmentedControl`, `Checkbox`, `StickyActionBar`, `TabBar`, `Toast`, `BottomSheet`, `SourceQuote`.
- Mobile-first: design for 390px width, content column capped at about 480px and centred on larger screens. Every tap target at least 44px.

## Phases — stop after each one and show me what works
- **Phase 0 — Foundation.** Project scaffold, Sail with PostgreSQL, Tailwind theme from the tokens, self-hosted fonts, app shell with the four-tab bar, base components, `lang/bs`.
- **Phase 1 — Matters.** Migrations, models, factories and a seeder reproducing the prototype's demo matter; Predmeti list; matter page with Hronologija and Izmjene; source-document viewer.
- **Phase 2 — Knjiga pošte.** Incoming and outgoing registers, document upload from the phone camera (`<input type="file" accept="image/*" capture>`), the review screen with highlighted extracted passages, register numbering.
- **Phase 3 — Deadlines.** Proposal screen with the three acknowledgements, confirmation, reminders, Kalendar, and the Danas briefing including "Čeka vaš pregled".
- **Phase 4 — Hearing note.** Dictation flow with the three independent proposals and the uncertain-value check.
- **Phase 5 — Outgoing submission.** Proof of filing, deadline fulfilment, follow-up reminder.
- **Phase 6 — Troškovnik.** Suggested items, per-item confirmation, approval, PDF export for the lawyer.
- **Phase 7 — Landing page** from the prototype (static), and a scheduled morning briefing notification.

## Quality bar
- Each phase ships with Pest feature tests. The rules in "Non-negotiable rules" 1, 2, 3 and 5 each need explicit tests, including the failure paths (for example: confirming a deadline with only two acknowledgements returns a validation error and changes nothing).
- Migrations use foreign keys, sensible indexes, `numeric(12,2)` for money, `date` for dates without time.
- Before calling a phase done: run the tests, Pint, and the TypeScript check, and compare each screen against the prototype side by side.
- If the README and the prototype disagree, or a rule is unclear, ask me — do not guess, especially on anything that looks like legal logic.

Begin now with reading the two files and proposing the plan for Phase 0 and Phase 1.
