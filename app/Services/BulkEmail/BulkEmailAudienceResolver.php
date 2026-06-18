<?php

namespace App\Services\BulkEmail;

use App\Models\BulkEmailCampaign;
use App\Services\Notifications\StudentSegmentResolverService;
use Illuminate\Database\Eloquent\Collection;

class BulkEmailAudienceResolver
{
    public function __construct(
        private StudentSegmentResolverService $segmentResolver
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function resolve(BulkEmailCampaign $campaign): Collection
    {
        return $this->resolveFromParams(
            $campaign->audience_type,
            $campaign->student_ids ?? [],
            $campaign->course_id,
            $campaign->group_id
        );
    }

    /**
     * @param  array<int|string>  $studentIds
     * @return Collection<int, User>
     */
    public function resolveFromParams(
        string $audienceType,
        array $studentIds = [],
        ?int $courseId = null,
        ?int $groupId = null
    ): Collection {
        $filters = [];

        switch ($audienceType) {
            case BulkEmailCampaign::AUDIENCE_INDIVIDUAL:
                $ids = array_values(array_filter($studentIds));
                if (empty($ids)) {
                    return new Collection;
                }
                $filters['student_ids'] = [reset($ids)];
                break;

            case BulkEmailCampaign::AUDIENCE_SELECTED:
                $ids = array_values(array_filter($studentIds));
                if (empty($ids)) {
                    return new Collection;
                }
                $filters['student_ids'] = $ids;
                break;

            case BulkEmailCampaign::AUDIENCE_GROUP:
                if (! $groupId) {
                    return new Collection;
                }
                $filters['group_id'] = $groupId;
                break;

            case BulkEmailCampaign::AUDIENCE_COURSE:
                if (! $courseId) {
                    return new Collection;
                }
                $filters['course_id'] = $courseId;
                break;

            case BulkEmailCampaign::AUDIENCE_COURSE_GROUP:
                if (! $courseId || ! $groupId) {
                    return new Collection;
                }
                $filters['course_id'] = $courseId;
                $filters['group_id'] = $groupId;
                break;
        }

        return $this->segmentResolver->resolve($filters);
    }

    public function countFromParams(
        string $audienceType,
        array $studentIds = [],
        ?int $courseId = null,
        ?int $groupId = null
    ): int {
        return $this->resolveFromParams($audienceType, $studentIds, $courseId, $groupId)->count();
    }
}
