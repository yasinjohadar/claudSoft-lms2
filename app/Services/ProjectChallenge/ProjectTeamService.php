<?php

namespace App\Services\ProjectChallenge;

use App\Models\ProjectChallenge\ProjectChallenge;
use App\Models\ProjectChallenge\ProjectStage;
use App\Models\ProjectChallenge\ProjectTeam;
use App\Models\ProjectChallenge\ProjectTeamInvitation;
use App\Models\ProjectChallenge\ProjectTeamJoinRequest;
use App\Models\ProjectChallenge\ProjectTeamMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectTeamService
{
    public function __construct(
        protected ProjectActivityLogger $activityLogger,
        protected ProjectNotificationService $notifications
    ) {}

    public function resolveInitialTeamStatus(ProjectChallenge $challenge): string
    {
        return match ($challenge->team_approval_mode) {
            'auto', 'leader_approval' => 'active',
            'admin_approval', 'hybrid' => 'pending',
            default => 'pending',
        };
    }

    public function createTeam(ProjectChallenge $challenge, User $leader, array $data): ProjectTeam
    {
        if (! $challenge->isOpen()) {
            throw new \RuntimeException('هذا التحدي غير مفتوح حالياً');
        }

        if ($challenge->hasReachedTeamLimit()) {
            throw new \RuntimeException('تم الوصول إلى الحد الأقصى للفرق');
        }

        $existingMembership = ProjectTeamMember::query()
            ->where('user_id', $leader->id)
            ->where('status', 'active')
            ->whereHas('team', fn ($q) => $q->where('project_challenge_id', $challenge->id))
            ->exists();

        if ($existingMembership) {
            throw new \RuntimeException('أنت عضو بالفعل في فريق لهذا التحدي');
        }

        return DB::transaction(function () use ($challenge, $leader, $data) {
            $name = $data['name'];
            $slug = $this->uniqueTeamSlug($challenge, $name);
            $status = $this->resolveInitialTeamStatus($challenge);

            $team = ProjectTeam::create([
                'project_challenge_id' => $challenge->id,
                'name' => $name,
                'slug' => $slug,
                'logo' => $data['logo'] ?? null,
                'description' => $data['description'] ?? null,
                'leader_id' => $leader->id,
                'status' => $status,
            ]);

            ProjectTeamMember::create([
                'project_team_id' => $team->id,
                'user_id' => $leader->id,
                'role' => 'leader',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $this->activityLogger->log($team, 'team.created', $leader, [
                'team_name' => $team->name,
                'status' => $status,
            ]);

            return $team->fresh(['members', 'leader']);
        });
    }

    public function requestJoin(ProjectTeam $team, User $user, ?string $message = null): ProjectTeamJoinRequest|ProjectTeamMember
    {
        $team->loadMissing('challenge');

        if (! $team->challenge->isOpen()) {
            throw new \RuntimeException('هذا التحدي غير مفتوح حالياً');
        }

        if (! $team->isActive()) {
            throw new \RuntimeException('هذا الفريق غير نشط');
        }

        if (! $team->canAcceptMembers()) {
            throw new \RuntimeException('الفريق ممتلئ');
        }

        if ($team->hasMember($user->id)) {
            throw new \RuntimeException('أنت عضو بالفعل في هذا الفريق');
        }

        $existingRequest = ProjectTeamJoinRequest::query()
            ->where('project_team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            throw new \RuntimeException('لديك طلب انضمام قيد المراجعة');
        }

        if ($team->challenge->team_approval_mode === 'auto') {
            return $this->addMember($team, $user, 'member', $user);
        }

        $joinRequest = ProjectTeamJoinRequest::create([
            'project_team_id' => $team->id,
            'user_id' => $user->id,
            'message' => $message,
            'status' => 'pending',
        ]);

        $this->activityLogger->log($team, 'team.join_requested', $user, [
            'join_request_id' => $joinRequest->id,
        ]);

        $recipients = $this->joinRequestRecipients($team);
        $this->notifications->notifyMany($recipients, ProjectNotificationService::EVENT_TEAM_JOIN_REQUESTED, [
            'team_name' => $team->name,
            'challenge_title' => $team->challenge->title,
            'user_name' => $user->name,
            'action_url' => route('admin.project-challenges.manage-teams', $team->project_challenge_id),
        ]);

        return $joinRequest;
    }

    public function approveJoinRequest(ProjectTeamJoinRequest $joinRequest, User $reviewer): ProjectTeamMember
    {
        if (! $joinRequest->isPending()) {
            throw new \RuntimeException('طلب الانضمام ليس قيد المراجعة');
        }

        $joinRequest->loadMissing('team.challenge');

        $this->assertCanReviewJoinRequest($joinRequest->team, $reviewer);

        return DB::transaction(function () use ($joinRequest, $reviewer) {
            $team = $joinRequest->team;

            if (! $team->canAcceptMembers()) {
                throw new \RuntimeException('الفريق ممتلئ');
            }

            $joinRequest->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $member = $this->addMember($team, $joinRequest->user, 'member', $reviewer);

            $this->notifications->notifyJoinApproved($joinRequest->user, [
                'team_name' => $team->name,
                'challenge_title' => $team->challenge->title,
                'action_url' => route('student.project-teams.workspace', $team->id),
            ]);

            return $member;
        });
    }

    public function rejectJoinRequest(
        ProjectTeamJoinRequest $joinRequest,
        User $reviewer,
        ?string $reason = null
    ): ProjectTeamJoinRequest {
        if (! $joinRequest->isPending()) {
            throw new \RuntimeException('طلب الانضمام ليس قيد المراجعة');
        }

        $joinRequest->loadMissing('team.challenge');
        $this->assertCanReviewJoinRequest($joinRequest->team, $reviewer);

        $joinRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'reject_reason' => $reason,
        ]);

        $this->activityLogger->log($joinRequest->team, 'team.join_rejected', $reviewer, [
            'join_request_id' => $joinRequest->id,
            'user_id' => $joinRequest->user_id,
        ]);

        $this->notifications->notifyJoinRejected($joinRequest->user, [
            'team_name' => $joinRequest->team->name,
            'challenge_title' => $joinRequest->team->challenge->title,
            'reject_reason' => $reason,
            'action_url' => route('student.project-challenges.show', $joinRequest->team->project_challenge_id),
        ]);

        return $joinRequest->fresh();
    }

    public function inviteMember(ProjectTeam $team, User $inviter, User $invitedUser): ProjectTeamInvitation
    {
        $team->loadMissing('challenge');

        if (! $team->isActive()) {
            throw new \RuntimeException('هذا الفريق غير نشط');
        }

        if (! $team->hasMember($inviter->id)) {
            throw new \RuntimeException('يجب أن تكون عضواً في الفريق لإرسال دعوة');
        }

        if ($team->hasMember($invitedUser->id)) {
            throw new \RuntimeException('المستخدم عضو بالفعل في الفريق');
        }

        if (! $team->canAcceptMembers()) {
            throw new \RuntimeException('الفريق ممتلئ');
        }

        $existing = ProjectTeamInvitation::query()
            ->where('project_team_id', $team->id)
            ->where('invited_user_id', $invitedUser->id)
            ->where('status', 'pending')
            ->first();

        if ($existing && $existing->isValid()) {
            throw new \RuntimeException('توجد دعوة معلقة لهذا المستخدم');
        }

        $invitation = ProjectTeamInvitation::create([
            'project_team_id' => $team->id,
            'invited_user_id' => $invitedUser->id,
            'invited_by' => $inviter->id,
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        $this->activityLogger->log($team, 'team.invitation_sent', $inviter, [
            'invited_user_id' => $invitedUser->id,
            'invitation_id' => $invitation->id,
        ]);

        return $invitation;
    }

    public function acceptInvitation(ProjectTeamInvitation $invitation, User $user): ProjectTeamMember
    {
        if ($invitation->invited_user_id !== $user->id) {
            throw new \RuntimeException('هذه الدعوة ليست لك');
        }

        if (! $invitation->isValid()) {
            throw new \RuntimeException('الدعوة غير صالحة أو منتهية');
        }

        $invitation->loadMissing('team.challenge');
        $team = $invitation->team;

        if (! $team->canAcceptMembers()) {
            throw new \RuntimeException('الفريق ممتلئ');
        }

        return DB::transaction(function () use ($invitation, $user, $team) {
            $invitation->update(['status' => 'accepted']);

            return $this->addMember($team, $user, 'member', $user);
        });
    }

    public function removeMember(ProjectTeam $team, User $actor, User $memberToRemove): void
    {
        $membership = ProjectTeamMember::query()
            ->where('project_team_id', $team->id)
            ->where('user_id', $memberToRemove->id)
            ->where('status', 'active')
            ->first();

        if (! $membership) {
            throw new \RuntimeException('العضو غير موجود في الفريق');
        }

        $isLeader = $team->leader_id === $actor->id;
        $isSelf = $actor->id === $memberToRemove->id;

        if (! $isLeader && ! $isSelf && ! $actor->hasRole('admin')) {
            throw new \RuntimeException('غير مصرح بإزالة هذا العضو');
        }

        if ($membership->isLeader() && ! $isSelf && ! $actor->hasRole('admin')) {
            throw new \RuntimeException('لا يمكن إزالة قائد الفريق');
        }

        $membership->update(['status' => 'removed']);

        $this->activityLogger->log($team, 'team.member_removed', $actor, [
            'user_id' => $memberToRemove->id,
        ]);
    }

    public function activateTeam(ProjectTeam $team, User $actor): ProjectTeam
    {
        if (! $team->isPending()) {
            throw new \RuntimeException('الفريق ليس في انتظار الموافقة');
        }

        $team->update(['status' => 'active']);

        $this->activityLogger->log($team, 'team.activated', $actor, []);

        return $team->fresh();
    }

    public function createTeamAsAdmin(ProjectChallenge $challenge, array $data, User $admin): ProjectTeam
    {
        $leader = User::findOrFail($data['leader_id']);

        return DB::transaction(function () use ($challenge, $leader, $data, $admin) {
            $this->detachUserFromChallengeTeams($challenge, $leader->id, $admin);

            $team = ProjectTeam::create([
                'project_challenge_id' => $challenge->id,
                'name' => $data['name'],
                'slug' => $this->uniqueTeamSlug($challenge, $data['name']),
                'description' => $data['description'] ?? null,
                'leader_id' => $leader->id,
                'status' => $data['status'] ?? 'active',
            ]);

            ProjectTeamMember::create([
                'project_team_id' => $team->id,
                'user_id' => $leader->id,
                'role' => 'leader',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $this->activityLogger->log($team, 'team.created_by_admin', $admin, [
                'leader_id' => $leader->id,
            ]);

            return $team->fresh(['leader', 'activeMembers.user']);
        });
    }

    public function updateTeamAsAdmin(ProjectTeam $team, array $data, User $admin): ProjectTeam
    {
        return DB::transaction(function () use ($team, $data, $admin) {
            $updates = [];

            if (isset($data['name']) && $data['name'] !== $team->name) {
                $updates['name'] = $data['name'];
                $updates['slug'] = $this->uniqueTeamSlug($team->challenge, $data['name'], $team->id);
            }

            if (array_key_exists('description', $data)) {
                $updates['description'] = $data['description'];
            }

            if (isset($data['status'])) {
                $updates['status'] = $data['status'];
            }

            if (! empty($updates)) {
                $team->update($updates);
            }

            if (! empty($data['leader_id']) && (int) $data['leader_id'] !== (int) $team->leader_id) {
                $this->assignLeaderAsAdmin($team, User::findOrFail($data['leader_id']), $admin);
            }

            $this->activityLogger->log($team, 'team.updated_by_admin', $admin, $updates);

            return $team->fresh(['leader', 'activeMembers.user', 'challenge.stages']);
        });
    }

    public function addMemberAsAdmin(ProjectTeam $team, User $user, string $role, User $admin): ProjectTeamMember
    {
        if (! $team->canAcceptMembers()) {
            throw new \RuntimeException('الفريق ممتلئ');
        }

        if ($team->hasMember($user->id)) {
            throw new \RuntimeException('الطالب عضو بالفعل في هذا الفريق');
        }

        return DB::transaction(function () use ($team, $user, $role, $admin) {
            $this->detachUserFromChallengeTeams($team->challenge, $user->id, $admin);

            $member = ProjectTeamMember::create([
                'project_team_id' => $team->id,
                'user_id' => $user->id,
                'role' => $role === 'leader' ? 'member' : $role,
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $this->activityLogger->log($team, 'member.added_by_admin', $admin, [
                'user_id' => $user->id,
                'role' => $member->role,
            ]);

            return $member->load('user');
        });
    }

    public function changeMemberRole(ProjectTeam $team, ProjectTeamMember $member, string $role, User $admin): ProjectTeamMember
    {
        if ($member->project_team_id !== $team->id || $member->status !== 'active') {
            throw new \RuntimeException('العضو غير صالح');
        }

        $validRoles = array_keys(config('project_challenges.team_roles', []));
        if (! in_array($role, $validRoles, true)) {
            throw new \InvalidArgumentException('دور غير صالح');
        }

        if ($role === 'leader') {
            $this->assignLeaderAsAdmin($team, $member->user, $admin);

            return $member->fresh('user');
        }

        if ($member->user_id === $team->leader_id) {
            throw new \RuntimeException('لا يمكن تغيير دور القائد. عيّن قائداً جديداً أولاً.');
        }

        $member->update(['role' => $role]);

        $this->activityLogger->log($team, 'member.role_changed', $admin, [
            'user_id' => $member->user_id,
            'role' => $role,
        ]);

        return $member->fresh('user');
    }

    public function assignLeaderAsAdmin(ProjectTeam $team, User $newLeader, User $admin): void
    {
        DB::transaction(function () use ($team, $newLeader, $admin) {
            $this->detachUserFromChallengeTeams($team->challenge, $newLeader->id, $admin, $team->id);

            $member = ProjectTeamMember::query()
                ->where('project_team_id', $team->id)
                ->where('user_id', $newLeader->id)
                ->first();

            if (! $member) {
                if (! $team->canAcceptMembers()) {
                    throw new \RuntimeException('الفريق ممتلئ. لا يمكن إضافة القائد الجديد كعضو.');
                }

                $member = ProjectTeamMember::create([
                    'project_team_id' => $team->id,
                    'user_id' => $newLeader->id,
                    'role' => 'leader',
                    'status' => 'active',
                    'joined_at' => now(),
                ]);
            } else {
                $member->update([
                    'status' => 'active',
                    'role' => 'leader',
                    'joined_at' => $member->joined_at ?? now(),
                ]);
            }

            if ($team->leader_id && $team->leader_id !== $newLeader->id) {
                ProjectTeamMember::query()
                    ->where('project_team_id', $team->id)
                    ->where('user_id', $team->leader_id)
                    ->where('status', 'active')
                    ->update(['role' => 'member']);
            }

            $team->update(['leader_id' => $newLeader->id]);

            $this->activityLogger->log($team, 'leader.changed_by_admin', $admin, [
                'leader_id' => $newLeader->id,
            ]);
        });
    }

    public function unlockStageForTeam(ProjectTeam $team, ProjectStage $stage, User $admin): ProjectTeam
    {
        if ($stage->project_challenge_id !== $team->project_challenge_id) {
            throw new \RuntimeException('المرحلة لا تنتمي لهذا التحدي');
        }

        $unlocked = $team->admin_unlocked_stage_ids ?? [];
        if (! in_array($stage->id, $unlocked, true)) {
            $unlocked[] = $stage->id;
            $team->update(['admin_unlocked_stage_ids' => array_values($unlocked)]);
        }

        $this->activityLogger->log($team, 'stage.unlocked_by_admin', $admin, [
            'stage_id' => $stage->id,
        ]);

        return $team->fresh();
    }

    protected function detachUserFromChallengeTeams(
        ProjectChallenge $challenge,
        int $userId,
        User $admin,
        ?int $exceptTeamId = null
    ): void {
        $memberships = ProjectTeamMember::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->whereHas('team', function ($q) use ($challenge, $exceptTeamId) {
                $q->where('project_challenge_id', $challenge->id);
                if ($exceptTeamId) {
                    $q->where('id', '!=', $exceptTeamId);
                }
            })
            ->with('team')
            ->get();

        foreach ($memberships as $membership) {
            $membership->update(['status' => 'removed']);
            $this->activityLogger->log($membership->team, 'member.removed_by_admin', $admin, [
                'user_id' => $userId,
                'reason' => 'moved_to_another_team',
            ]);
        }
    }

    protected function addMember(
        ProjectTeam $team,
        User $user,
        string $role,
        ?User $actor = null
    ): ProjectTeamMember {
        $team->loadMissing('challenge');

        $existingInChallenge = ProjectTeamMember::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('team', fn ($q) => $q->where('project_challenge_id', $team->project_challenge_id))
            ->where('project_team_id', '!=', $team->id)
            ->first();

        if ($existingInChallenge) {
            if ($actor?->hasRole('admin')) {
                $existingInChallenge->update(['status' => 'removed']);
            } else {
                throw new \RuntimeException('الطالب عضو في فريق آخر لهذا التحدي');
            }
        }

        $member = ProjectTeamMember::create([
            'project_team_id' => $team->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->activityLogger->log($team, 'team.member_joined', $actor ?? $user, [
            'user_id' => $user->id,
            'role' => $role,
        ]);

        return $member;
    }

    /**
     * @return array<int, User>
     */
    protected function joinRequestRecipients(ProjectTeam $team): array
    {
        $mode = $team->challenge->team_approval_mode;
        $recipients = collect();

        if (in_array($mode, ['admin_approval', 'hybrid'], true)) {
            $recipients = $recipients->merge(User::role('admin')->get());
        }

        if (in_array($mode, ['leader_approval', 'hybrid'], true) && $team->leader) {
            $recipients->push($team->leader);
        }

        return $recipients->unique('id')->values()->all();
    }

    protected function assertCanReviewJoinRequest(ProjectTeam $team, User $reviewer): void
    {
        if ($reviewer->hasRole('admin')) {
            return;
        }

        $mode = $team->challenge->team_approval_mode;

        if ($team->leader_id === $reviewer->id && in_array($mode, ['leader_approval', 'hybrid'], true)) {
            return;
        }

        throw new \RuntimeException('غير مصرح بمراجعة طلب الانضمام');
    }

    protected function uniqueTeamSlug(ProjectChallenge $challenge, string $name, ?int $exceptTeamId = null): string
    {
        $base = Str::slug($name) ?: 'team';
        $slug = $base;
        $counter = 1;

        while (
            ProjectTeam::where('project_challenge_id', $challenge->id)
                ->where('slug', $slug)
                ->when($exceptTeamId, fn ($q) => $q->where('id', '!=', $exceptTeamId))
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
