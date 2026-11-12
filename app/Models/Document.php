<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $office_id
 * @property int|null $matter_id
 * @property string $kind
 * @property string $name
 * @property string|null $disk
 * @property string|null $path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property int|null $size
 * @property Carbon|null $document_date
 * @property string|null $extracted_text
 * @property array<int, array{text: string, align?: string, bold?: bool, highlighted?: bool}>|null $paragraphs
 * @property array<string, mixed>|null $extraction
 * @property bool $is_fictional
 * @property-read Matter|null $matter
 */
class Document extends Model
{
    use BelongsToOffice;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'paragraphs' => 'array',
            'extraction' => 'array',
            'is_fictional' => 'boolean',
        ];
    }

    /** @return BelongsTo<Matter, $this> */
    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class);
    }

    public function hasFile(): bool
    {
        return $this->disk !== null && $this->path !== null;
    }
}
