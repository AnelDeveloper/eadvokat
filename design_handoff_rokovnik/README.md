# Handoff: Rokovnik — assistant for a small Bosnian law office

## Overview
Rokovnik is a mobile-first tool for a solo lawyer or small law office in Bosnia and Herzegovina. It covers four functions on one matter ("predmet"):

1. **Knjiga pošte** — register of incoming and outgoing mail, each entry linked to its source document.
2. **Rokovnik** — deadlines proposed from a document or a dictated note, active only after the lawyer confirms them.
3. **Podsjetnici** — reminders and a morning briefing.
4. **Troškovnik** — draft cost sheet built from recorded actions, approved item by item.

The governing product rule: **the app only proposes. A deadline, mail entry, calendar event, note or cost item counts only after the lawyer confirms it.** Every confirmed thing links back to its source document and leaves an audit entry.

## About the design files
`Rokovnik.dc.html` (with its runtime `support.js`) is a **design reference built in HTML** — a clickable prototype showing intended look and behaviour. It is not production code. The task is to **recreate it in Laravel + Vue 3 + PostgreSQL**, using that stack's own patterns. Open the file in a browser and click through it; the template markup is at the top of the file, the state logic is in the `class Component` block at the bottom.

All data in the prototype is fictional (matter `65 0 P 084512 25 P`, Selimović protiv Brezik Gradnja d.o.o.). Use it as seed data only.

## Fidelity
**High-fidelity** for the phone app: final colours, type, spacing, copy and interactions. Recreate it closely. The surrounding landing page is a presentation wrapper for the pitch, lower priority, and can be built last or kept static.

All UI copy is **Bosnian (ijekavica, Latin script)** and must stay verbatim. Dates are `dd.mm.gggg.` with a trailing dot; money is `1.234,56 KM`.

## Design tokens

### Colour
| Role | Hex |
|---|---|
| Ink (text, primary buttons, rules) | `#173247` |
| Ink deep (hover, dark screens, phone bezel) | `#0F2230` |
| Body secondary | `#2C4A5E` |
| Muted text | `#4F6676` |
| Faint (disabled, unchecked border, neutral dot) | `#8FA1AD` |
| Border strong (inputs) | `#C3CED5` |
| Border (cards, dividers) | `#D5DDE2` |
| Hairline (rows inside cards) | `#E6ECEF` |
| App background | `#F3F6F7` |
| Neutral chip background | `#EDF1F3` |
| Document viewer background | `#DDE4E8` |
| Surface | `#FFFFFF` |
| Confirmed — fill / text / background | `#3F776F` / `#2F5F58` / `#E4EFEC` |
| Proposed — border / text / background | `#B27B34` / `#7A4F17` / `#F7EEDF` |
| Urgent — fill / text / background | `#A34A45` / `#8A3B37` / `#F6E9E8` |

### Type
- **IBM Plex Sans** 400/500/600 — all app UI.
- **IBM Plex Mono** 400/500 — case numbers, register numbers, dates, times, amounts.
- **Newsreader** 500/600 and italic 400 — landing headlines, and every quotation or rendering of a source document.

App scale (px): screen title 30/600, line-height 1.1 · matter title 19/600 · card title 16–17/600 · row title 15–15.5/500, line-height 1.3 · body 14–14.5 · sub-line 12.5 · field label 12/500 · section label 11.5/600 uppercase, letter-spacing .06em · chip 11/600 · mono 12–13 (16–17 in date inputs and the case-number header).

### Radius, spacing, shadow
- Radius: 4 chips and paper · 5 checkbox · 8 inputs and secondary buttons · 10 cards and primary buttons · 16 notification · 18 top corners of bottom sheet · 999 pill.
- Screen padding 18px horizontal; card padding 12×14; row padding 10×14; gaps 6 / 8 / 10 / 12 / 14.
- Heights: inputs and secondary buttons 44 · primary action 52 · quick-input buttons 56 · list rows min 56 · tab bar 68 · status bar 50. Nothing tappable under 44px.
- Section tables open with a `2px solid #173247` top rule, rows separated by 1px `#D5DDE2`.
- Shadows: toast `0 8px 20px -8px rgba(15,34,48,.6)`; document page `0 1px 3px rgba(23,50,71,.2)`. Cards have no shadow, only a 1px border.

### Status chips (the core visual language)
| Status | Label | Style |
|---|---|---|
| `proposed` | Predloženo · čeka pregled | amber text, amber bg, **1px dashed** amber border |
| `confirmed` | Potvrdio advokat | green text, green bg, 1px solid green border |
| `urgent` (confirmed, due ≤ 3 days) | Potvrdio advokat · ističe uskoro | red text, red bg, 1px solid red border |
| `recorded` | Evidentirano | muted text, neutral bg, 1px solid `#C3CED5` |
| `fulfilled` | Ispunjeno | same as recorded |

Dashed amber always means "not active yet". Never show a proposed item with a solid border or a green colour.

## Screens

Viewport reference is 390×844. Structure of every tabbed screen: status bar · scrollable content · optional sticky action bar (white, top border `#D5DDE2`, padding 10/14/12) · bottom tab bar.

**Tab bar** — four equal text tabs: Danas, Predmeti, Kalendar, Pošta. Active: weight 600, ink, 3px ink top border. Inactive: weight 500, muted. Hidden on all full-screen flows.

### 1. Danas (morning briefing)
- Mono date line (`srijeda, 11. novembar 2026.`), title "Danas", button "Prikaz obavijesti" on the right.
- One card "Jutarnji pregled" (lawyer name in mono on the right), grouped under grey section labels:
  - **Ročišta danas** — today's hearings.
  - **Rokovi u naredna tri dana** — unfulfilled deadlines due today..today+3; empty state "Nema rokova u naredna tri dana."
  - **Provjeriti u sudu** — today's reminders; tapping toggles checked.
  - **Čeka vaš pregled** — every proposed item plus a row for pending cost items. Shown only when non-empty.
  - **Sutra** — tomorrow's items.
- Row: grid `52px | 1fr | 10px` — mono time or date, title + sub-line + status chip, chevron.
- Sticky "Brzi unos" bar: **Fotografiši** (primary, filled, 1.35fr), **Diktiraj**, **Podnesak** (outlined, 1fr each).

### 2. Predmeti → Predmet
- List: card per matter — mono case number above a 2px ink rule, title, sub-line (type · court · side represented), "Naredno:" next confirmed hearing or deadline, "Troškovnik:" status line.
- Matter header (white): back link, case number mono 17, title 19/600, meta line, full-width ink button "Troškovnik" with status sub-line.
- Sticky three-way sub-tabs: **Hronologija**, **Troškovnik**, **Izmjene**.
- **Hronologija**: two groups, "Predstoji" (ascending) and "Evidentirano" (descending). Row grid `88px | 12px | 1fr`: mono date + time, vertical 1px rail with a 9px dot coloured by status (proposed = white dot with dashed amber border), then type label (uppercase 11), title, sub-line, chip, and "Izvorni dokument ›" link when a document is attached.
- **Izmjene**: audit log, newest first — mono timestamp + user, then one sentence.

### 3. Kalendar
- Month title, prev/next buttons (44×44). Weekday header pon–ned (Monday first).
- 7-column grid, cells 46px high: day number plus up to three 6px markers. Selected day: ink fill, white text. Today: 1px ink border, weight 600.
- Markers: ink = hearing or meeting · green = confirmed deadline · red = urgent deadline · hollow amber ring = proposal · grey = reminder or past item. Legend below the grid.
- Day card listing that day's items (same row as Danas, with type label); empty state "Nema unosa za ovaj dan."

### 4. Knjiga pošte
- Segmented control Ulazna / Izlazna.
- Rows: register number (`U-2026/041`, `I-2026/029`) and date in mono, title, court · case number, linked-deadline or proof-of-filing line (green when confirmed or present, amber otherwise), "Izvorni dokument ›".
- Sticky action: "Fotografiši primljeni dokument" or "Evidentiraj izlazni podnesak".

### 5. Fotografisanje (full screen, dark `#0F2230`)
Cancel link, document preview inside an amber frame, hint text, 74px round shutter. After capture: overlay "Čitam dokument…" then go to review.

### 6. Pregled unosa (step 1/2)
- Source document panel (Newsreader, max-height 210, scrolls) with the extracted passages highlighted (`#F7EEDF` background, 1px amber underline).
- Heading "Unos u knjigu pošte" + proposed chip + register number.
- Editable fields: Sud, Broj predmeta, Vrsta dokumenta, Datum dokumenta, Datum prijema. A field filled by assumption gets an amber border and a note ("Pretpostavljen današnji datum — provjerite s dostavnicom.").
- "Izdvojeni navodi iz dokumenta": quoted passages in italic Newsreader, each with a note. The app **quotes** legally significant sentences (e.g. "Protiv ovog rješenja nije dozvoljena posebna žalba.") and explicitly does not interpret them.
- Sticky: "Potvrdi i sačuvaj u knjigu pošte" → saves entry, opens deadline proposal.

### 7. Prijedlog roka (step 2/2)
- Amber dashed banner: "Predloženo — rok nije aktivan. …"
- Source quote card with the triggering phrase highlighted, document reference, "Otvori ›".
- Three cards, each ending in a checkbox the lawyer must tick:
  1. **Pravilo** — rule text + jurisdiction selector (FBiH / RS / Brčko distrikt) → "Pravilo i nadležnost su tačni"
  2. **Početak (datum prijema)** — editable date → "Datum početka je tačan"
  3. **Posljednji dan roka** — editable date + computed weekday line → "Posljednji dan je tačan"
- "Pretpostavke obračuna — provjerite" list (receipt day not counted; holidays not checked; no assessment of deadline type or consequences).
- Reminder toggles: 3 days before, 1 day before (both on by default).
- Sticky: confirm button is grey `#8FA1AD` reading "Označite sve tri provjere (n/3)" until all three are ticked, then green `#3F776F` "Potvrdi rok dd.mm.gggg." Secondary: "Ne sada — ostavi kao prijedlog".

### 8. Bilješka s ročišta (voice)
- Idle: 112px round mic button, ink; recording: red `#A34A45`, pulsing dot, "Snimam… 0:19".
- Result: transcript (Newsreader) with any uncertain word highlighted, then three independent proposal cards — **Događaj u kalendaru** (date, time, courtroom), **Rok** (last day), **Bilješka u predmetu** (textarea). Each has its own chip and "Potvrdi" / "Potvrđeno ✓ — vrati na pregled" button. Editing a field resets that card to proposed.
- Uncertain value ("Sudnica nije jasna u snimku: 12 ili 21?") must be resolved before the event can be confirmed.
- Sticky: "Sačuvaj · potvrđeno n od 3". Unconfirmed cards are saved as proposals.

### 9. Izlazni podnesak
Fields: Vrsta podneska, Datum predaje, Način predaje (Lično na pisarnici / Preporučeno poštom), Dokaz o predaji (photo slot; dashed when empty, green when attached), optional checkbox "Ovim podneskom ispunjen je rok …", and a follow-up reminder with free text and a day choice when the computed day falls on a weekend. Sticky: "Sačuvaj i postavi podsjetnik".

### 10. Troškovnik (matter sub-tab)
- Title + draft/approved chip; amber dashed disclaimer.
- Jurisdiction selector and a free-text "Izvor i verzija tarife (unosi advokat)".
- Item rows: title, mono date · source, chip; then amount input (mono, right-aligned, suffix "KM"), "Potvrdi"/"Vrati" button, remove (×).
- Total row; "Odobri nacrt" (disabled grey until every item is confirmed and a tariff source is entered); "Izvezi nacrt (PDF)".

### 11. Izvorni dokument
White top bar with back and document name; grey `#DDE4E8` background; white page, Newsreader 14.5/1.5.

### 12. Other
- **Toast**: ink, white text, radius 10, 14px from the sides, above the tab bar, auto-dismiss ~3.4s.
- **Bottom sheet** over a `rgba(15,34,48,.5)` scrim.
- **Notification preview** (lock-screen mock) — presentation only; replace with real push later.

## Behaviour and business rules

1. **Proposal → confirmation.** Deadlines, events, notes and cost items are created with status `proposed`. Only an explicit user action sets `confirmed`, recording `confirmed_by` and `confirmed_at`. Proposed items appear in "Čeka vaš pregled" and on the calendar as hollow amber rings; they never trigger reminders.
2. **Deadline confirmation needs three separate acknowledgements** (rule + jurisdiction, start date, last day). Enforce on the server, not only in the UI.
3. **Deadline arithmetic in the prototype:** last day = receipt date + N calendar days (receipt day not counted). Changing the start date recomputes the last day and clears acknowledgements 2 and 3. Manually changing the last day clears acknowledgement 3 and flags "izmijenjeno ručno". The proposal must always show its assumptions. **Do not add holiday or non-working-day logic, or any statutory deadline rules, without the lawyer's specification** — the prototype deliberately states these are not checked.
4. **Reminders** are generated from a confirmed deadline (3 days and 1 day before, each optional) and from outgoing-mail follow-ups. Re-confirming with a new date regenerates them.
5. **Fulfilment:** an outgoing submission can mark a deadline as fulfilled; it then shows "Ispunjeno" and leaves the briefing.
6. **Cost items are suggested** when an action is recorded (hearing attended, submission filed). Any change to an amount, item, jurisdiction or tariff source reverts the sheet from approved to draft.
7. **No tariff data is bundled.** Amounts are entered or confirmed by the lawyer; the tariff source is free text. The prototype's amounts are placeholders.
8. **Audit log** entry for: mail entry saved, deadline confirmed or changed (with old → new values), value corrected from a transcript, cost item confirmed/reverted/removed, cost sheet approved, jurisdiction changed.
9. **Source traceability:** every mail entry, deadline and cost item keeps a reference to the document (and quoted passage) it came from.
10. **Nothing is sent** to a court, a party or any third person. Export produces a file for the lawyer only.
11. **Register numbers:** `U-YYYY/NNN` incoming, `I-YYYY/NNN` outgoing, sequential per office per year.

## Suggested data model (PostgreSQL)

- `users` — lawyer accounts; `office_id`.
- `offices`
- `matters` — `case_number`, `title`, `court`, `subject`, `represented_side`, `jurisdiction` (`fbih` | `rs` | `brcko`).
- `documents` — `matter_id`, stored file, `kind`, `document_date`, `extracted_text`.
- `mail_entries` — `direction` (`in` | `out`), `register_number`, `document_id`, `received_or_sent_on`, `court`, `doc_type`, `delivery_method`, `proof_document_id`, `status`.
- `deadlines` — `matter_id`, `title`, `rule_text`, `source_document_id`, `source_quote`, `start_date`, `due_date`, `due_date_manually_changed`, `status` (`proposed` | `confirmed` | `fulfilled`), `rule_ack`, `start_ack`, `due_ack`, `confirmed_by`, `confirmed_at`, `fulfilled_by_mail_entry_id`.
- `events` — `type` (`hearing` | `meeting` | `court_visit`), `starts_at`, `location`, `status`, `outcome`.
- `reminders` — `remind_on`, `text`, polymorphic source (deadline or mail entry), `checked_at`.
- `notes` — `matter_id`, `body`, `transcript`, `source` (`voice` | `manual`), `status`.
- `cost_sheets` — `matter_id`, `jurisdiction`, `tariff_source`, `status` (`draft` | `approved`), `approved_by`, `approved_at`.
- `cost_items` — `cost_sheet_id`, `title`, `action_date`, polymorphic source, `amount` (numeric 12,2), `status`.
- `audit_logs` — `matter_id`, `user_id`, `action`, `description`, `old_values` jsonb, `new_values` jsonb, `created_at`.

Scope every query by `office_id`.

## Document reading and dictation
The prototype simulates both with a prepared document and a prepared transcript. In the build, put them behind interfaces (`DocumentExtractor`, `SpeechTranscriber`) with a **fake driver that returns the seed fixture**. Do not wire a real AI or OCR provider until the office has agreed on data storage and on a provider contract that excludes training on client documents — this is an explicit precondition in the pitch.

## Seed data
Take it from the prototype's `fresh()` and `items()` methods: one matter, three earlier mail entries, two past hearings, one court visit, a confirmed deadline on 13.11.2026., a hearing on 11.11.2026. at 11:00, a client meeting on 12.11.2026. at 14:00, four cost items. "Today" in the demo is 11.11.2026.

## Assets
No images or icon files. The camera, microphone and battery glyphs are drawn with CSS; replace them with an icon set of your choice. Fonts are loaded from Google Fonts — self-host them in production.

## Files
- `Rokovnik.dc.html` — the prototype (template + logic).
- `support.js` — runtime needed only to open the prototype in a browser.
- `CLAUDE_CODE_PROMPT.md` — starting prompt for Claude Code.
