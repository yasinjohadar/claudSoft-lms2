<?php

namespace Tests\Unit;

use App\Models\BulkEmailCampaign;
use App\Models\User;
use App\Services\BulkEmail\BulkEmailAudienceResolver;
use App\Services\Notifications\StudentSegmentResolverService;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class BulkEmailAudienceResolverTest extends TestCase
{
    public function test_individual_with_empty_student_ids_returns_zero(): void
    {
        $mock = $this->mock(StudentSegmentResolverService::class);
        $mock->shouldNotReceive('resolve');

        $resolver = app(BulkEmailAudienceResolver::class);

        $this->assertSame(0, $resolver->countFromParams(BulkEmailCampaign::AUDIENCE_INDIVIDUAL, []));
    }

    public function test_selected_with_empty_student_ids_returns_zero(): void
    {
        $mock = $this->mock(StudentSegmentResolverService::class);
        $mock->shouldNotReceive('resolve');

        $resolver = app(BulkEmailAudienceResolver::class);

        $this->assertSame(0, $resolver->countFromParams(BulkEmailCampaign::AUDIENCE_SELECTED, []));
    }

    public function test_group_without_group_id_returns_zero(): void
    {
        $mock = $this->mock(StudentSegmentResolverService::class);
        $mock->shouldNotReceive('resolve');

        $resolver = app(BulkEmailAudienceResolver::class);

        $this->assertSame(0, $resolver->countFromParams(BulkEmailCampaign::AUDIENCE_GROUP, [], null, null));
    }

    public function test_course_without_course_id_returns_zero(): void
    {
        $mock = $this->mock(StudentSegmentResolverService::class);
        $mock->shouldNotReceive('resolve');

        $resolver = app(BulkEmailAudienceResolver::class);

        $this->assertSame(0, $resolver->countFromParams(BulkEmailCampaign::AUDIENCE_COURSE, [], null, null));
    }

    public function test_individual_with_student_id_delegates_to_segment_resolver(): void
    {
        $student = new User(['id' => 42]);

        $mock = $this->mock(StudentSegmentResolverService::class);
        $mock->shouldReceive('resolve')
            ->once()
            ->with(['student_ids' => [42]])
            ->andReturn(new Collection([$student]));

        $resolver = app(BulkEmailAudienceResolver::class);

        $this->assertSame(1, $resolver->countFromParams(BulkEmailCampaign::AUDIENCE_INDIVIDUAL, [42]));
    }

    public function test_resolve_from_params_returns_empty_collection_without_calling_segment_resolver(): void
    {
        $mock = $this->mock(StudentSegmentResolverService::class);
        $mock->shouldNotReceive('resolve');

        $resolver = app(BulkEmailAudienceResolver::class);

        $users = $resolver->resolveFromParams(BulkEmailCampaign::AUDIENCE_INDIVIDUAL, []);

        $this->assertCount(0, $users);
    }
}
