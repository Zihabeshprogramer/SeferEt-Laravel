<?php

namespace Tests\Unit;

use App\Models\Ad;
use App\Models\User;
use App\Models\AdAuditLog;
use App\Models\AdImpression;
use App\Models\AdClick;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Ad $ad;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->owner = User::factory()->create([
            'role' => 'travel_agent',
        ]);
        
        $this->ad = Ad::factory()->create([
            'owner_id' => $this->owner->id,
            'owner_type' => get_class($this->owner),
            'status' => Ad::STATUS_DRAFT,
        ]);
    }

    /** @test */
    public function it_can_check_if_ad_is_draft()
    {
        $this->assertTrue($this->ad->isDraft());
        $this->assertFalse($this->ad->isPending());
        $this->assertFalse($this->ad->isApproved());
        $this->assertFalse($this->ad->isRejected());
    }

    /** @test */
    public function it_can_submit_draft_ad_for_approval()
    {
        $result = $this->ad->submitForApproval();
        
        $this->assertTrue($result);
        $this->assertTrue($this->ad->isPending());
        $this->assertEquals(Ad::STATUS_PENDING, $this->ad->status);
    }

    /** @test */
    public function it_cannot_submit_non_draft_ad()
    {
        $this->ad->update(['status' => Ad::STATUS_PENDING]);
        
        $result = $this->ad->submitForApproval();
        
        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_approve_pending_ad()
    {
        $this->ad->update(['status' => Ad::STATUS_PENDING]);
        $admin = User::factory()->create(['role' => 'admin']);
        
        $result = $this->ad->approve($admin);
        
        $this->assertTrue($result);
        $this->assertTrue($this->ad->isApproved());
        $this->assertEquals($admin->id, $this->ad->approved_by);
        $this->assertNotNull($this->ad->approved_at);
        $this->assertNull($this->ad->rejection_reason);
    }

    /** @test */
    public function it_can_reject_pending_ad()
    {
        $this->ad->update(['status' => Ad::STATUS_PENDING]);
        $admin = User::factory()->create(['role' => 'admin']);
        $reason = 'Image quality is too low';
        
        $result = $this->ad->reject($admin, $reason);
        
        $this->assertTrue($result);
        $this->assertTrue($this->ad->isRejected());
        $this->assertEquals($admin->id, $this->ad->approved_by);
        $this->assertEquals($reason, $this->ad->rejection_reason);
    }

    /** @test */
    public function it_can_withdraw_pending_ad()
    {
        $this->ad->update(['status' => Ad::STATUS_PENDING]);
        
        $result = $this->ad->withdraw();
        
        $this->assertTrue($result);
        $this->assertTrue($this->ad->isDraft());
    }

    /** @test */
    public function it_can_activate_and_deactivate_ad()
    {
        $this->ad->update(['is_active' => false]);
        
        $this->ad->activate();
        $this->assertTrue($this->ad->is_active);
        
        $this->ad->deactivate();
        $this->assertFalse($this->ad->is_active);
    }

    /** @test */
    public function it_checks_if_ad_is_currently_active()
    {
        // Not active if not approved
        $this->assertFalse($this->ad->isCurrentlyActive());
        
        // Active if approved, toggle on, and in date range
        $this->ad->update([
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
            'start_at' => null,
            'end_at' => null,
        ]);
        $this->assertTrue($this->ad->isCurrentlyActive());
        
        // Not active if start date is in future
        $this->ad->update(['start_at' => now()->addDay()]);
        $this->assertFalse($this->ad->isCurrentlyActive());
        
        // Not active if end date is in past
        $this->ad->update([
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDay(),
        ]);
        $this->assertFalse($this->ad->isCurrentlyActive());
    }

    /** @test */
    public function it_checks_if_ad_is_expired()
    {
        $this->ad->update([
            'status' => Ad::STATUS_APPROVED,
            'end_at' => now()->subDay(),
        ]);
        
        $this->assertTrue($this->ad->isExpired());
    }

    /** @test */
    public function active_scope_returns_only_active_ads()
    {
        // Create various ads
        $activeAd = Ad::factory()->create([
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);
        
        $inactiveAd = Ad::factory()->create([
            'status' => Ad::STATUS_APPROVED,
            'is_active' => false,
        ]);
        
        $pendingAd = Ad::factory()->create([
            'status' => Ad::STATUS_PENDING,
            'is_active' => true,
        ]);
        
        $activeAds = Ad::active()->get();
        
        $this->assertTrue($activeAds->contains($activeAd));
        $this->assertFalse($activeAds->contains($inactiveAd));
        $this->assertFalse($activeAds->contains($pendingAd));
    }

    /** @test */
    public function by_owner_scope_filters_by_owner()
    {
        $anotherUser = User::factory()->create(['role' => 'hotel_provider']);
        $anotherAd = Ad::factory()->create([
            'owner_id' => $anotherUser->id,
            'owner_type' => get_class($anotherUser),
        ]);
        
        $ownerAds = Ad::byOwner($this->owner->id, get_class($this->owner))->get();
        
        $this->assertTrue($ownerAds->contains($this->ad));
        $this->assertFalse($ownerAds->contains($anotherAd));
    }

    /** @test */
    public function it_prioritizes_local_owners_first()
    {
        $localAd = Ad::factory()->create([
            'is_local_owner' => true,
            'priority' => 1,
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);
        
        $highPriorityAd = Ad::factory()->create([
            'is_local_owner' => false,
            'priority' => 10,
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);
        
        $ads = Ad::active()->prioritized()->get();
        
        $this->assertEquals($localAd->id, $ads->first()->id);
    }

    /** @test */
    public function it_records_impression_and_updates_counter()
    {
        $initialCount = $this->ad->impressions_count;
        
        $this->ad->recordImpression();
        
        $this->ad->refresh();
        $this->assertEquals($initialCount + 1, $this->ad->impressions_count);
    }

    /** @test */
    public function it_records_click_and_updates_counter()
    {
        $initialCount = $this->ad->clicks_count;
        
        $this->ad->recordClick();
        
        $this->ad->refresh();
        $this->assertEquals($initialCount + 1, $this->ad->clicks_count);
    }

    /** @test */
    public function it_calculates_ctr_correctly()
    {
        $this->ad->update([
            'impressions_count' => 0,
            'clicks_count' => 0,
        ]);
        
        // Record 100 impressions
        for ($i = 0; $i < 100; $i++) {
            $this->ad->increment('impressions_count');
        }
        
        // Record 5 clicks
        for ($i = 0; $i < 5; $i++) {
            $this->ad->recordClick();
        }
        
        $this->ad->refresh();
        $this->assertEquals(5.0, (float)$this->ad->ctr);
    }

    /** @test */
    public function it_checks_impression_limit()
    {
        $this->ad->update([
            'max_impressions' => 1000,
            'impressions_count' => 999,
        ]);
        
        $this->assertFalse($this->ad->hasReachedImpressionLimit());
        
        $this->ad->update(['impressions_count' => 1000]);
        $this->assertTrue($this->ad->hasReachedImpressionLimit());
    }

    /** @test */
    public function it_checks_click_limit()
    {
        $this->ad->update([
            'max_clicks' => 100,
            'clicks_count' => 99,
        ]);
        
        $this->assertFalse($this->ad->hasReachedClickLimit());
        
        $this->ad->update(['clicks_count' => 100]);
        $this->assertTrue($this->ad->hasReachedClickLimit());
    }

    /** @test */
    public function it_creates_audit_log_on_creation()
    {
        $newAd = Ad::factory()->create([
            'owner_id' => $this->owner->id,
            'owner_type' => get_class($this->owner),
        ]);
        
        $this->assertDatabaseHas('ad_audit_logs', [
            'ad_id' => $newAd->id,
            'event_type' => 'created',
        ]);
    }

    /** @test */
    public function it_creates_audit_log_on_status_change()
    {
        $this->ad->update(['status' => Ad::STATUS_PENDING]);
        
        $this->assertDatabaseHas('ad_audit_logs', [
            'ad_id' => $this->ad->id,
            'event_type' => 'submitted',
        ]);
    }

    /** @test */
    public function it_has_image_url_attribute()
    {
        $this->ad->update(['image_path' => 'ads/1/test.jpg']);
        
        $this->assertNotNull($this->ad->image_url);
        $this->assertStringContainsString('test.jpg', $this->ad->image_url);
    }

    /** @test */
    public function it_returns_correct_status_badge_color()
    {
        $this->ad->update(['status' => Ad::STATUS_DRAFT]);
        $this->assertEquals('secondary', $this->ad->status_badge);
        
        $this->ad->update(['status' => Ad::STATUS_PENDING]);
        $this->assertEquals('warning', $this->ad->status_badge);
        
        $this->ad->update(['status' => Ad::STATUS_APPROVED]);
        $this->assertEquals('success', $this->ad->status_badge);
        
        $this->ad->update(['status' => Ad::STATUS_REJECTED]);
        $this->assertEquals('danger', $this->ad->status_badge);
    }

    /** @test */
    public function device_type_scope_filters_correctly()
    {
        $mobileAd = Ad::factory()->create([
            'device_type' => 'mobile',
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);
        
        $allDevicesAd = Ad::factory()->create([
            'device_type' => 'all',
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);
        
        $desktopAd = Ad::factory()->create([
            'device_type' => 'desktop',
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);
        
        $mobileAds = Ad::active()->forDevice('mobile')->get();
        
        $this->assertTrue($mobileAds->contains($mobileAd));
        $this->assertTrue($mobileAds->contains($allDevicesAd));
        $this->assertFalse($mobileAds->contains($desktopAd));
    }

    /** @test */
    public function placement_scope_filters_correctly()
    {
        $homeAd = Ad::factory()->create([
            'placement' => 'home_top',
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);
        
        $anyPlacementAd = Ad::factory()->create([
            'placement' => null,
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);
        
        $detailsAd = Ad::factory()->create([
            'placement' => 'package_details',
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);
        
        $homeAds = Ad::active()->forPlacement('home_top')->get();
        
        $this->assertTrue($homeAds->contains($homeAd));
        $this->assertTrue($homeAds->contains($anyPlacementAd));
        $this->assertFalse($homeAds->contains($detailsAd));
    }
}
