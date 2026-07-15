<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SingleSessionService
{
    public function __construct(
        protected DeviceSecuritySettingsService $settingsService,
    ) {}

    /**
     * Make the current request session the only active login for this user.
     */
    public function enforce(User $user, Request $request): void
    {
        if (! $this->settingsService->isSingleSessionActiveForUser($user)) {
            return;
        }

        $sessionId = $request->session()->getId();
        if ($sessionId === '') {
            return;
        }

        // Update only this column — avoid dirty attributes like last_login_* left on the
        // in-memory user after a failed login stamp in finalizeAuthenticatedLogin.
        User::query()
            ->whereKey($user->id)
            ->update(['active_session_id' => $sessionId]);

        $user->active_session_id = $sessionId;
        $user->syncOriginalAttribute('active_session_id');

        $this->deleteOtherFrameworkSessions($user->id, $sessionId);
        $this->disconnectOtherTrackingSessions(
            $user->id,
            $request->session()->get('user_session_id')
        );
    }

    /**
     * Clear the stored active session (e.g. on explicit logout).
     */
    public function clear(User $user): void
    {
        DB::table('users')
            ->where('id', $user->id)
            ->update(['active_session_id' => null]);

        $user->active_session_id = null;
        $user->syncOriginalAttribute('active_session_id');
    }

    protected function deleteOtherFrameworkSessions(int $userId, string $currentSessionId): void
    {
        if (! Schema::hasTable('sessions')) {
            return;
        }

        try {
            DB::table('sessions')
                ->where('user_id', $userId)
                ->where('id', '!=', $currentSessionId)
                ->delete();
        } catch (\Throwable $e) {
            Log::warning('Failed to delete competing framework sessions', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function disconnectOtherTrackingSessions(int $userId, mixed $exceptTrackingSessionId = null): void
    {
        if (! Schema::hasTable('user_sessions')) {
            return;
        }

        try {
            $query = DB::table('user_sessions')
                ->where('user_id', $userId)
                ->where('status', 'active');

            if ($exceptTrackingSessionId) {
                $query->where('id', '!=', (int) $exceptTrackingSessionId);
            }

            $query->update([
                'status' => 'disconnected',
                'ended_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to disconnect competing tracking sessions', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
