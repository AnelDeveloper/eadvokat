<?php

namespace App\Demo;

use App\Models\Office;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Every visitor of /demo gets a private, temporary office seeded with the
 * fictional matter. It is pinned to the demo "today" and deleted after 24 hours.
 */
final class DemoSessions
{
    public const LIFETIME_HOURS = 24;

    public function __construct(private readonly DemoScenario $scenario) {}

    public function current(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($user === null || ! $user->office->is_demo || $user->office->pinned_today === null) {
            return null;
        }

        $expires = $user->office->expires_at;

        return $expires !== null && $expires->isFuture() ? $user : null;
    }

    public function ensure(Request $request): User
    {
        return $this->current() ?? $this->start($request);
    }

    public function reset(Request $request): User
    {
        if ($user = $this->current()) {
            $this->destroy($user->office);
        }

        return $this->start($request);
    }

    public function destroy(Office $office): void
    {
        if (! $office->is_demo) {
            return;
        }

        Storage::disk('local')->deleteDirectory("documents/{$office->id}");
        Storage::disk('local')->deleteDirectory("recordings/{$office->id}");
        $office->delete();
    }

    /**
     * Deletes expired demo offices with their data and files.
     */
    public function prune(): int
    {
        $expired = Office::query()
            ->where('is_demo', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', CarbonImmutable::now())
            ->get();

        $expired->each(fn (Office $office) => $this->destroy($office));

        return $expired->count();
    }

    private function start(Request $request): User
    {
        $user = DB::transaction(function () {
            $office = Office::query()->create([
                'name' => 'Demo kancelarija',
                'is_demo' => true,
                'pinned_today' => DemoScenario::TODAY,
                'expires_at' => CarbonImmutable::now()->addHours(self::LIFETIME_HOURS),
            ]);

            $user = User::query()->create([
                'office_id' => $office->id,
                'title' => 'adv.',
                'name' => 'Lejla Karić',
                'email' => 'demo-'.Str::uuid().'@demo.rokovnik.test',
                // Nobody can sign in with this; the visitor is logged in directly.
                'password' => Str::random(64),
            ]);

            $this->scenario->seed($office, $user);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget(['demo.seen_calendar', 'demo.seen_costs']);

        return $user;
    }
}
