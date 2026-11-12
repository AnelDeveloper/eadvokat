<?php

namespace App\Demo;

use App\Enums\CostSheetStatus;
use App\Enums\DeadlineStatus;
use App\Enums\DeliveryMethod;
use App\Enums\EventType;
use App\Enums\ItemStatus;
use App\Enums\Jurisdiction;
use App\Enums\MailDirection;
use App\Enums\ReminderKind;
use App\Models\AuditLog;
use App\Models\CostItem;
use App\Models\CostSheet;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Event;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Models\Office;
use App\Models\Reminder;
use App\Models\User;
use App\Support\CurrentOffice;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * The prototype's fictional matter (65 0 P 084512 25 P, Selimović protiv
 * Brezik Gradnja d.o.o.) as of the demo "today", 11.11.2026.
 * Source: fresh() and items() in design_handoff_rokovnik/Rokovnik.dc.html.
 */
final class DemoScenario
{
    public const TODAY = '2026-11-11';

    public const CASE_NUMBER = '65 0 P 084512 25 P';

    public function seed(Office $office, User $lawyer): Matter
    {
        return CurrentOffice::as($office->id, fn () => DB::transaction(fn () => $this->build($office, $lawyer)));
    }

    private function build(Office $office, User $lawyer): Matter
    {
        $at = fn (string $value) => CarbonImmutable::parse($value, config('app.timezone'));
        $court = 'Općinski sud u Sarajevu';

        $matter = Matter::query()->create([
            'office_id' => $office->id,
            'case_number' => self::CASE_NUMBER,
            'title' => 'Selimović protiv Brezik Gradnja d.o.o.',
            'subject' => 'Radi isplate duga',
            'court' => $court,
            'represented_side' => 'tužioca',
            'client_name' => 'Amra Selimović',
            'jurisdiction' => Jurisdiction::Fbih,
        ]);

        $header = [
            ['text' => 'OPĆINSKI SUD U SARAJEVU', 'align' => 'center', 'bold' => true],
            ['text' => 'Broj: '.self::CASE_NUMBER],
        ];

        $document = fn (string $name, string $date, array $paragraphs, string $kind = 'court_document') => Document::query()->create([
            'office_id' => $office->id,
            'matter_id' => $matter->id,
            'kind' => $kind,
            'name' => $name,
            'document_date' => $date,
            'paragraphs' => $paragraphs,
            'extracted_text' => collect($paragraphs)->pluck('text')->implode("\n"),
            'is_fictional' => true,
            'uploaded_by' => $lawyer->id,
            'created_at' => $at($date.' 12:00'),
        ]);

        $lawsuit = $document('Tužba · I-2025/044', '2025-11-17', [
            ['text' => 'OPĆINSKOM SUDU U SARAJEVU', 'align' => 'center', 'bold' => true],
            ['text' => 'Tužilac: Amra Selimović, koju zastupa adv. Lejla Karić'],
            ['text' => 'Tuženi: Brezik Gradnja d.o.o. Sarajevo'],
            ['text' => 'TUŽBA radi isplate duga', 'align' => 'center', 'bold' => true],
            ['text' => 'Vrijednost spora: 18.400,00 KM (demo podatak)'],
        ], 'submission');

        $response = $document('Odgovor na tužbu · U-2026/007', '2026-02-05', [
            ...$header,
            ['text' => 'ODGOVOR NA TUŽBU', 'align' => 'center', 'bold' => true],
            ['text' => 'Tuženi Brezik Gradnja d.o.o. Sarajevo u cijelosti osporava tužbeni zahtjev.'],
        ]);

        $quote = 'Sud poziva tužioca da se u roku od 15 dana od dana prijema izjasni na navode podneska.';
        $defendantFiling = $document('Podnesak tuženog · U-2026/038', '2026-10-27', [
            ...$header,
            ['text' => 'PODNESAK TUŽENOG', 'align' => 'center', 'bold' => true],
            ['text' => 'Tuženi Brezik Gradnja d.o.o. Sarajevo osporava visinu potraživanja i predlaže saslušanje svjedoka.'],
            ['text' => $quote],
        ]);

        $mail = fn (array $attributes) => MailEntry::query()->create([
            'office_id' => $office->id,
            'matter_id' => $matter->id,
            'court' => $court,
            'case_number' => self::CASE_NUMBER,
            'status' => ItemStatus::Confirmed,
            'confirmed_by' => $lawyer->id,
            ...$attributes,
        ]);

        $lawsuitEntry = $mail([
            'direction' => MailDirection::Out,
            'register_year' => 2025,
            'register_sequence' => 44,
            'register_number' => 'I-2025/044',
            'received_or_sent_on' => '2025-11-17',
            'document_date' => '2025-11-17',
            'doc_type' => 'Tužba',
            'summary' => 'Tužba predata sudu',
            'note' => 'preporučeno poštom',
            'delivery_method' => DeliveryMethod::RegisteredMail,
            'document_id' => $lawsuit->id,
            'proof_note' => 'potvrda o prijemu pošiljke',
            'confirmed_at' => $at('2025-11-17 10:00'),
        ]);

        $mail([
            'direction' => MailDirection::In,
            'register_year' => 2026,
            'register_sequence' => 7,
            'register_number' => 'U-2026/007',
            'received_or_sent_on' => '2026-02-09',
            'document_date' => '2026-02-05',
            'doc_type' => 'Odgovor na tužbu',
            'summary' => 'Zaprimljen odgovor na tužbu',
            'document_id' => $response->id,
            'confirmed_at' => $at('2026-02-09 09:30'),
        ]);

        $filingEntry = $mail([
            'direction' => MailDirection::In,
            'register_year' => 2026,
            'register_sequence' => 38,
            'register_number' => 'U-2026/038',
            'received_or_sent_on' => '2026-10-29',
            'document_date' => '2026-10-27',
            'doc_type' => 'Podnesak tuženog',
            'summary' => 'Zaprimljen podnesak tuženog',
            'note' => 'rok za izjašnjenje 15 dana',
            'document_id' => $defendantFiling->id,
            'source_quote' => $quote,
            'confirmed_at' => $at('2026-10-29 14:58'),
        ]);

        DB::table('register_counters')->insert([
            ['office_id' => $office->id, 'direction' => 'out', 'year' => 2025, 'last_number' => 44],
            ['office_id' => $office->id, 'direction' => 'in', 'year' => 2026, 'last_number' => 40],
            ['office_id' => $office->id, 'direction' => 'out', 'year' => 2026, 'last_number' => 28],
        ]);

        $event = fn (array $attributes) => Event::query()->create([
            'office_id' => $office->id,
            'matter_id' => $matter->id,
            'status' => ItemStatus::Confirmed,
            'confirmed_by' => $lawyer->id,
            'confirmed_at' => $at('2026-01-01 09:00'),
            ...$attributes,
        ]);

        $preparatory = $event([
            'type' => EventType::Hearing,
            'title' => 'Pripremno ročište',
            'outcome' => 'Pripremno ročište održano',
            'starts_at' => $at('2026-04-21 09:00'),
            'location' => 'Sudnica 12',
        ]);

        $mainHearing = $event([
            'type' => EventType::Hearing,
            'title' => 'Glavna rasprava',
            'outcome' => 'Glavna rasprava — određeno vještačenje',
            'starts_at' => $at('2026-09-15 10:00'),
            'location' => 'Sudnica 12',
        ]);

        $courtVisit = $event([
            'type' => EventType::CourtVisit,
            'title' => 'Uvid u spis na pisarnici',
            'description' => 'Poziv svjedoku K. Dizdaru još nije vraćen',
            'starts_at' => $at('2026-11-09 12:30'),
            'location' => 'Pisarnica',
            'venue' => $court,
        ]);

        $event([
            'type' => EventType::Hearing,
            'title' => 'Nastavak glavne rasprave',
            'starts_at' => $at('2026-11-11 11:00'),
            'location' => 'Sudnica 12',
            'venue' => $court,
        ]);

        $event([
            'type' => EventType::Meeting,
            'title' => 'Sastanak sa strankom A. Selimović',
            'starts_at' => $at('2026-11-12 14:00'),
            'location' => 'Kancelarija',
        ]);

        Deadline::query()->create([
            'office_id' => $office->id,
            'matter_id' => $matter->id,
            'title' => 'Izjašnjenje na podnesak tuženog',
            'rule_text' => 'Rok određen u samom dokumentu: 15 dana od dana prijema',
            'days' => 15,
            'basis' => '15 dana od prijema',
            'jurisdiction' => Jurisdiction::Fbih,
            'source_document_id' => $defendantFiling->id,
            'source_mail_entry_id' => $filingEntry->id,
            'source_quote' => $quote,
            'source_highlight' => 'u roku od 15 dana od dana prijema',
            'start_date' => '2026-10-29',
            'due_date' => '2026-11-13',
            'remind_3_days' => false,
            'remind_1_day' => false,
            'status' => DeadlineStatus::Confirmed,
            'rule_ack' => true,
            'start_ack' => true,
            'due_ack' => true,
            'confirmed_by' => $lawyer->id,
            'confirmed_at' => $at('2026-10-29 15:02'),
        ]);

        Reminder::query()->create([
            'office_id' => $office->id,
            'matter_id' => $matter->id,
            'remind_on' => self::TODAY,
            'text' => 'Provjeriti u pisarnici je li vraćena dostavnica za svjedoka',
            'detail' => 'Poziv svjedoku K. Dizdaru',
            'kind' => ReminderKind::Manual,
            'source_type' => $courtVisit->getMorphClass(),
            'source_id' => $courtVisit->id,
            'created_by' => $lawyer->id,
        ]);

        $sheet = CostSheet::query()->create([
            'office_id' => $office->id,
            'matter_id' => $matter->id,
            'jurisdiction' => Jurisdiction::Fbih,
            'tariff_source' => 'Demo tarifa (izmišljena, nije zvanični izvor)',
            'status' => CostSheetStatus::Draft,
        ]);

        $cost = fn (string $title, string $date, $source, string $label, string $amount, bool $confirmed, ?int $documentId = null) => CostItem::query()->create([
            'office_id' => $office->id,
            'cost_sheet_id' => $sheet->id,
            'title' => $title,
            'action_date' => $date,
            'source_type' => $source->getMorphClass(),
            'source_id' => $source->id,
            'source_label' => $label,
            'source_document_id' => $documentId,
            'amount' => $amount,
            'status' => $confirmed ? ItemStatus::Confirmed : ItemStatus::Proposed,
            'confirmed_by' => $confirmed ? $lawyer->id : null,
            'confirmed_at' => $confirmed ? $at('2026-11-01 10:00') : null,
        ]);

        $cost('Sastav tužbe', '2025-11-17', $lawsuitEntry, 'pošta I-2025/044', '480.00', true, $lawsuit->id);
        $cost('Zastupanje na pripremnom ročištu', '2026-04-21', $preparatory, 'kalendar', '480.00', true);
        $cost('Zastupanje na glavnoj raspravi', '2026-09-15', $mainHearing, 'kalendar', '480.00', true);
        $cost('Odlazak u sud — uvid u spis', '2026-11-09', $courtVisit, 'kalendar', '120.00', false);

        foreach ([
            ['2026-10-29 14:58', 'mail_entry.saved', 'Unos U-2026/038 sačuvan u knjigu pošte nakon pregleda.'],
            ['2026-10-29 15:02', 'deadline.confirmed', 'Rok 13.11.2026. potvrdio advokat. Početak izmijenjen ručno: 28.10. → 29.10.2026.'],
            ['2026-11-09 13:20', 'event.recorded', 'Evidentirana posjeta sudu — uvid u spis.'],
        ] as [$time, $action, $description]) {
            AuditLog::query()->create([
                'office_id' => $office->id,
                'matter_id' => $matter->id,
                'user_id' => $lawyer->id,
                'action' => $action,
                'description' => $description,
                'created_at' => $at($time),
            ]);
        }

        return $matter;
    }
}
