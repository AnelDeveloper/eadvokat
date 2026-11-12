<?php

namespace App\Models;

use App\Enums\Jurisdiction;
use App\Models\Concerns\BelongsToOffice;
use Database\Factories\MatterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $office_id
 * @property string $case_number
 * @property string $title
 * @property string $subject
 * @property string $court
 * @property string $represented_side
 * @property string $client_name
 * @property Jurisdiction $jurisdiction
 * @property-read CostSheet|null $costSheet
 */
class Matter extends Model
{
    /** @use HasFactory<MatterFactory> */
    use BelongsToOffice, HasFactory;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['jurisdiction' => Jurisdiction::class];
    }

    /** @return HasMany<Document, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /** @return HasMany<MailEntry, $this> */
    public function mailEntries(): HasMany
    {
        return $this->hasMany(MailEntry::class);
    }

    /** @return HasMany<Deadline, $this> */
    public function deadlines(): HasMany
    {
        return $this->hasMany(Deadline::class);
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /** @return HasMany<Reminder, $this> */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /** @return HasMany<Note, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /** @return HasOne<CostSheet, $this> */
    public function costSheet(): HasOne
    {
        return $this->hasOne(CostSheet::class);
    }

    /** @return HasMany<AuditLog, $this> */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /** "Radi isplate duga · Općinski sud u Sarajevu · zastupamo tužioca" */
    public function listMeta(): string
    {
        return $this->subject.' · '.$this->court.' · '.__('matters.represents', ['side' => $this->represented_side]);
    }

    /** Header meta line, adds jurisdiction and the client. */
    public function headerMeta(): string
    {
        return $this->subject.' · '.$this->court.' · '.$this->jurisdiction->label().' · '
            .__('matters.represents', ['side' => $this->represented_side]).' ('.$this->client_name.')';
    }
}
