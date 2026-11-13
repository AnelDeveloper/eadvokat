<?php

namespace App\Http\Controllers\Api\V1;

use App\Demo\DemoProgress;
use App\Enums\DeadlineStatus;
use App\Enums\Jurisdiction;
use App\Enums\MailDirection;
use App\Http\Controllers\Controller;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\MailEntry;
use App\Models\Matter;
use App\Screens\CalendarScreen;
use App\Screens\CostsScreen;
use App\Screens\DeadlineScreen;
use App\Screens\DictationScreens;
use App\Screens\DocumentScreen;
use App\Screens\MailScreens;
use App\Screens\MatterScreens;
use App\Screens\SubmissionScreen;
use App\Screens\TodayScreen;
use App\Support\Clock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Read-only screen data for the mobile app. The payloads are the same ones the
 * web pages receive, built by App\Screens.
 */
class ScreenController extends Controller
{
    public function today(TodayScreen $screen): JsonResponse
    {
        return response()->json($screen->data($this->user()));
    }

    public function calendar(Request $request, CalendarScreen $screen, DemoProgress $demo): JsonResponse
    {
        $demo->markSeen($this->office(), 'calendar');

        return response()->json($screen->data(
            $request->query('mjesec') !== null ? (string) $request->query('mjesec') : null,
            $request->query('dan') !== null ? (int) $request->query('dan') : null,
        ));
    }

    public function matters(MatterScreens $screens): JsonResponse
    {
        return response()->json($screens->index((bool) $this->office()->is_demo));
    }

    public function matterCreate(): JsonResponse
    {
        return response()->json([
            'jurisdictions' => collect(Jurisdiction::cases())->map(fn (Jurisdiction $j) => ['value' => $j->value, 'label' => $j->shortLabel()]),
        ]);
    }

    public function deadlineCreate(Request $request, Matter $matter): JsonResponse
    {
        Gate::authorize('update', $matter);
        $entry = $request->integer('unos') ? MailEntry::query()->where('matter_id', $matter->id)->find($request->integer('unos')) : null;

        return response()->json([
            'matter' => ['id' => $matter->id, 'caseNumber' => $matter->case_number, 'title' => $matter->title],
            'entry' => $entry ? [
                'id' => $entry->id,
                'number' => $entry->register_number,
                'docType' => $entry->doc_type,
                'receivedOn' => $entry->received_or_sent_on->toDateString(),
                'documentId' => $entry->document_id,
            ] : null,
            'today' => Clock::today()->toDateString(),
        ]);
    }

    public function matter(Matter $matter, MatterScreens $screens): JsonResponse
    {
        Gate::authorize('view', $matter);

        return response()->json($screens->timeline($matter));
    }

    public function audit(Matter $matter, MatterScreens $screens): JsonResponse
    {
        Gate::authorize('view', $matter);

        return response()->json($screens->audit($matter));
    }

    public function costs(Matter $matter, CostsScreen $screen, DemoProgress $demo): JsonResponse
    {
        Gate::authorize('view', $matter);
        $demo->markSeen($this->office(), 'costs');

        return response()->json($screen->data($matter, (bool) $this->office()->is_demo));
    }

    public function mail(Request $request, MailScreens $screens): JsonResponse
    {
        $direction = $request->query('smjer') === 'izlazna' ? MailDirection::Out : MailDirection::In;

        return response()->json($screens->index($direction, $request->integer('broj', MailScreens::PAGE)));
    }

    public function mailReview(Document $document, MailScreens $screens): JsonResponse
    {
        Gate::authorize('view', $document);

        if ($existing = MailEntry::query()->where('document_id', $document->id)->first()) {
            return response()->json([
                'message' => __('mail.already_registered', ['number' => $existing->register_number]),
                'next' => '/posta',
            ], 409);
        }

        abort_if($document->extraction === null, 404);

        $data = $screens->review($document, $this->user()->office_id);
        // The app fetches the original with its token through the API.
        $data['fileUrl'] = $document->hasFile() ? "/api/v1/documents/{$document->id}/file" : null;

        return response()->json($data);
    }

    public function deadline(Deadline $deadline, DeadlineScreen $screen): JsonResponse
    {
        Gate::authorize('view', $deadline);

        if ($deadline->status === DeadlineStatus::Fulfilled) {
            return response()->json(['message' => null, 'next' => "/predmeti/{$deadline->matter_id}"], 409);
        }

        return response()->json($screen->data($deadline));
    }

    public function dictation(DictationScreens $screens): JsonResponse
    {
        return response()->json($screens->create((bool) $this->office()->is_demo));
    }

    public function dictationResult(Document $document, DictationScreens $screens): JsonResponse
    {
        Gate::authorize('view', $document);
        abort_unless($document->kind === 'recording' && is_array($document->extraction), 404);

        return response()->json($screens->show($document));
    }

    public function submission(SubmissionScreen $screen): JsonResponse
    {
        return response()->json($screen->create($this->user()->office_id, (bool) $this->office()->is_demo));
    }

    public function document(Document $document, DocumentScreen $screen): JsonResponse
    {
        Gate::authorize('view', $document);

        $data = $screen->data($document);
        $data['fileUrl'] = $document->hasFile() ? "/api/v1/documents/{$document->id}/file" : null;

        return response()->json(['document' => $data]);
    }
}
