<?php

namespace Tests\Feature\Api\Mobile\V1;

use App\Events\UserNotificationBroadcasted;
use App\Models\GamificationNotification;
use App\Models\MobileAppRelease;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class MobileAppVersionApiTest extends MobileApiMysqlTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // الجدول قد يحتوي إصدارات حقيقية في قاعدة التطوير؛ الاختبار يعمل داخل transaction.
        MobileAppRelease::query()->delete();
    }

    private function release(array $attrs = []): MobileAppRelease
    {
        return MobileAppRelease::create(array_merge([
            'platform' => 'android',
            'version_name' => '1.0.0',
            'build_number' => 1,
            'distribution' => 'store',
            'download_url' => 'https://play.google.com/store/apps/details?id=com.claudsoft',
            'is_forced' => false,
            'is_active' => true,
        ], $attrs));
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(Role::findOrCreate('admin', 'web'));

        return $user;
    }

    public function test_no_release_means_no_update(): void
    {
        $this->getJson('/api/mobile/v1/app/version?platform=android&build=1')
            ->assertOk()
            ->assertJsonPath('data.update_available', false)
            ->assertJsonPath('data.force_update', false)
            ->assertJsonPath('data.latest', null);
    }

    public function test_optional_update_uses_latest_active_build(): void
    {
        $this->release(['build_number' => 2, 'version_name' => '1.1.0', 'release_notes' => "- صفحة جديدة\n\n• تحسينات"]);
        $this->release(['build_number' => 3, 'version_name' => '1.2.0', 'is_active' => false]); // موقوف
        $this->release(['build_number' => 9, 'version_name' => '2.0.0', 'platform' => 'ios']); // منصة أخرى

        $this->getJson('/api/mobile/v1/app/version?platform=android&build=1')
            ->assertOk()
            ->assertJsonPath('data.update_available', true)
            ->assertJsonPath('data.force_update', false)
            ->assertJsonPath('data.latest.version_name', '1.1.0')
            ->assertJsonPath('data.latest.build_number', 2)
            ->assertJsonPath('data.latest.release_notes', ['صفحة جديدة', 'تحسينات']);

        $this->getJson('/api/mobile/v1/app/version?platform=android&build=2')
            ->assertOk()
            ->assertJsonPath('data.update_available', false);
    }

    public function test_skipped_forced_release_still_forces(): void
    {
        $this->release(['build_number' => 2, 'is_forced' => true]);
        $this->release(['build_number' => 3, 'version_name' => '1.2.0']);

        $this->getJson('/api/mobile/v1/app/version?platform=android&build=1')
            ->assertJsonPath('data.force_update', true)
            ->assertJsonPath('data.latest.build_number', 3);

        // already past the forced one: optional only
        $this->getJson('/api/mobile/v1/app/version?platform=android&build=2')
            ->assertJsonPath('data.update_available', true)
            ->assertJsonPath('data.force_update', false);
    }

    public function test_validation(): void
    {
        $this->getJson('/api/mobile/v1/app/version?platform=windows&build=1')->assertStatus(422);
        $this->getJson('/api/mobile/v1/app/version?platform=android')->assertStatus(422);
    }

    public function test_admin_can_create_and_notify_app_users(): void
    {
        Event::fake([UserNotificationBroadcasted::class]);

        $appUser = $this->completeStudent();
        $appUser->createToken('mobile:Pixel');
        $webOnly = $this->completeStudent();

        $this->actingAs($this->admin())
            ->post(route('admin.mobile-app-releases.store'), [
                'platform' => 'android',
                'version_name' => '1.1.0',
                'build_number' => 2,
                'distribution' => 'direct',
                'download_url' => 'https://claudsoft.com/downloads/app.apk',
                'release_notes' => 'تحسينات',
                'is_active' => 1,
                'notify_now' => 1,
                'app_users_only' => 1,
            ])
            ->assertRedirect(route('admin.mobile-app-releases.index'));

        $release = MobileAppRelease::firstWhere('build_number', 2);
        $this->assertNotNull($release->notified_at);

        $this->assertTrue(GamificationNotification::where('user_id', $appUser->id)->where('type', 'app_update')->exists());
        $this->assertFalse(GamificationNotification::where('user_id', $webOnly->id)->where('type', 'app_update')->exists());
    }

    public function test_admin_pages_and_duplicate_build(): void
    {
        $release = $this->release();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.mobile-app-releases.index'))->assertOk()->assertSee('v1.0.0');
        $this->actingAs($admin)->get(route('admin.mobile-app-releases.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.mobile-app-releases.edit', $release))->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.mobile-app-releases.store'), [
                'platform' => 'android',
                'version_name' => '1.0.1',
                'build_number' => 1,
                'distribution' => 'store',
                'download_url' => 'https://play.google.com/x',
            ])
            ->assertSessionHasErrors('build_number');

        $this->actingAs($admin)
            ->postJson(route('admin.mobile-app-releases.toggle-active', $release))
            ->assertOk()
            ->assertJsonPath('is_active', false);

        $this->actingAs($admin)
            ->deleteJson(route('admin.mobile-app-releases.destroy', $release))
            ->assertOk();
        $this->assertNull(MobileAppRelease::find($release->id));
    }

    public function test_admin_uploads_apk_and_students_download_it(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.mobile-app-releases.store'), [
                'platform' => 'android',
                'version_name' => '1.1.0',
                'build_number' => 2,
                'distribution' => 'direct',
                'apk' => UploadedFile::fake()->create('app-release.apk', 2048, 'application/vnd.android.package-archive'),
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.mobile-app-releases.index'));

        $release = MobileAppRelease::firstWhere('build_number', 2);
        $this->assertTrue($release->hasApk());
        $this->assertSame(route('mobile-app.download.release', $release), $release->download_url);
        Storage::disk('local')->assertExists($release->apk_path);

        // The app gets the internal link…
        $this->getJson('/api/mobile/v1/app/version?platform=android&build=1')
            ->assertJsonPath('data.latest.download_url', route('mobile-app.download.release', $release))
            ->assertJsonPath('data.latest.distribution', 'direct');

        // …which serves the APK as an installable Android package.
        auth()->logout();
        $this->get(route('mobile-app.download.release', $release))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.android.package-archive')
            ->assertDownload('ClaudSoft-1.1.0.apk');

        // The permanent link always gives the newest active build.
        $this->get(route('mobile-app.download'))->assertOk()->assertDownload('ClaudSoft-1.1.0.apk');
        $this->assertSame(2, $release->fresh()->download_count);

        // A paused release is no longer downloadable.
        $release->update(['is_active' => false]);
        $this->get(route('mobile-app.download.release', $release))->assertNotFound();
        $this->get(route('mobile-app.download'))->assertNotFound();
    }

    public function test_apk_requires_a_file_or_link_and_deleting_removes_the_file(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.mobile-app-releases.store'), [
                'platform' => 'android', 'version_name' => '1.1.0', 'build_number' => 2, 'distribution' => 'direct',
            ])
            ->assertSessionHasErrors('download_url');

        $this->actingAs($admin)
            ->post(route('admin.mobile-app-releases.store'), [
                'platform' => 'android', 'version_name' => '1.1.0', 'build_number' => 2, 'distribution' => 'direct',
                'apk' => UploadedFile::fake()->create('notes.zip', 10),
            ])
            ->assertSessionHasErrors('apk');

        $this->actingAs($admin)->post(route('admin.mobile-app-releases.store'), [
            'platform' => 'android', 'version_name' => '1.1.0', 'build_number' => 2, 'distribution' => 'direct',
            'apk' => UploadedFile::fake()->create('a.apk', 10), 'is_active' => 1,
        ]);
        $release = MobileAppRelease::firstWhere('build_number', 2);
        $path = $release->apk_path;

        // Editing without a new file keeps the uploaded APK and its link.
        $this->actingAs($admin)->put(route('admin.mobile-app-releases.update', $release), [
            'platform' => 'android', 'version_name' => '1.1.1', 'build_number' => 2, 'distribution' => 'direct', 'is_active' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertSame($path, $release->fresh()->apk_path);
        $this->assertSame(route('mobile-app.download.release', $release), $release->fresh()->download_url);

        $this->actingAs($admin)->deleteJson(route('admin.mobile-app-releases.destroy', $release))->assertOk();
        Storage::disk('local')->assertMissing($path);
    }

    public function test_students_cannot_open_admin_page(): void
    {
        $this->actingAs($this->completeStudent())
            ->get(route('admin.mobile-app-releases.index'))
            ->assertStatus(403);
    }
}
