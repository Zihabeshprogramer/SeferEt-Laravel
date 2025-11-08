<?php

namespace Tests\Unit\Models;

use App\Models\Ad;
use App\Models\AdAuditLog;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => User::ROLE_HOTEL_PROVIDER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    /** @test */
    public function it_can_create_an_ad()
    {
        $ad = Ad::create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'title' => 'Test Ad',
            'description' => 'Test description',
            'status' => Ad::STATUS_DRAFT,
        ]);

        $this->assertInstanceOf(Ad::class, $ad);
        $this->assertEquals('Test Ad', $ad->title);
        $this->assertEquals(Ad::STATUS_DRAFT, $ad->status);
    }

    /** @test */
    public function it_belongs_to_an_owner()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
        ]);

        $this->assertInstanceOf(User::class, $ad->owner);
        $this->assertEquals($this->user->id, $ad->owner->id);
    }

    /** @test */
    public function it_can_have_a_product()
    {
        $hotel = Hotel::factory()->create();
        
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'product_id' => $hotel->id,
            'product_type' => Ad::PRODUCT_TYPE_HOTEL,
        ]);

        $this->assertInstanceOf(Hotel::class, $ad->product);
        $this->assertEquals($hotel->id, $ad->product->id);
    }

    /** @test */
    public function it_has_many_audit_logs()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
        ]);

        // Audit log is automatically created on creation
        $this->assertInstanceOf(AdAuditLog::class, $ad->auditLogs->first());
        $this->assertGreaterThan(0, $ad->auditLogs->count());
    }

    /** @test */
    public function it_can_check_if_draft()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_DRAFT,
        ]);

        $this->assertTrue($ad->isDraft());
        $this->assertFalse($ad->isPending());
        $this->assertFalse($ad->isApproved());
        $this->assertFalse($ad->isRejected());
    }

    /** @test */
    public function it_can_submit_for_approval()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_DRAFT,
        ]);

        $result = $ad->submitForApproval();

        $this->assertTrue($result);
        $this->assertTrue($ad->isPending());
        $this->assertEquals(Ad::STATUS_PENDING, $ad->status);
    }

    /** @test */
    public function it_cannot_submit_non_draft_ad()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_APPROVED,
        ]);

        $result = $ad->submitForApproval();

        $this->assertFalse($result);
    }

    /** @test */
    public function it_can_approve_pending_ad()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_PENDING,
        ]);

        $result = $ad->approve($this->admin);

        $this->assertTrue($result);
        $this->assertTrue($ad->isApproved());
        $this->assertEquals($this->admin->id, $ad->approved_by);
        $this->assertNotNull($ad->approved_at);
    }

    /** @test */
    public function it_can_reject_pending_ad()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_PENDING,
        ]);

        $reason = 'Does not meet quality standards';
        $result = $ad->reject($this->admin, $reason);

        $this->assertTrue($result);
        $this->assertTrue($ad->isRejected());
        $this->assertEquals($this->admin->id, $ad->approved_by);
        $this->assertEquals($reason, $ad->rejection_reason);
        $this->assertNotNull($ad->approved_at);
    }

    /** @test */
    public function it_can_withdraw_pending_ad()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_PENDING,
        ]);

        $result = $ad->withdraw();

        $this->assertTrue($result);
        $this->assertTrue($ad->isDraft());
    }

    /** @test */
    public function scope_draft_filters_draft_ads()
    {
        Ad::factory()->count(3)->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_DRAFT,
        ]);

        Ad::factory()->count(2)->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_PENDING,
        ]);

        $draftAds = Ad::draft()->get();

        $this->assertCount(3, $draftAds);
        $this->assertTrue($draftAds->every(fn($ad) => $ad->isDraft()));
    }

    /** @test */
    public function scope_approved_filters_approved_ads()
    {
        Ad::factory()->count(2)->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_APPROVED,
        ]);

        Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_DRAFT,
        ]);

        $approvedAds = Ad::approved()->get();

        $this->assertCount(2, $approvedAds);
        $this->assertTrue($approvedAds->every(fn($ad) => $ad->isApproved()));
    }

    /** @test */
    public function scope_active_filters_active_ads()
    {
        // Active: approved + is_active + within date range
        $active = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        // Not active: not approved
        Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_DRAFT,
            'is_active' => true,
        ]);

        // Not active: is_active = false
        Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_APPROVED,
            'is_active' => false,
        ]);

        $activeAds = Ad::active()->get();

        $this->assertCount(1, $activeAds);
        $this->assertEquals($active->id, $activeAds->first()->id);
    }

    /** @test */
    public function scope_by_owner_filters_by_owner()
    {
        $anotherUser = User::factory()->create([
            'role' => User::ROLE_PARTNER,
        ]);

        Ad::factory()->count(2)->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
        ]);

        Ad::factory()->create([
            'owner_id' => $anotherUser->id,
            'owner_type' => get_class($anotherUser),
        ]);

        $userAds = Ad::byOwner($this->user->id)->get();

        $this->assertCount(2, $userAds);
        $this->assertTrue($userAds->every(fn($ad) => $ad->owner_id === $this->user->id));
    }

    /** @test */
    public function it_checks_if_currently_active()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
            'start_at' => now()->subDay(),
            'end_at' => now()->addDay(),
        ]);

        $this->assertTrue($ad->isCurrentlyActive());
    }

    /** @test */
    public function it_checks_if_expired()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'status' => Ad::STATUS_APPROVED,
            'end_at' => now()->subDay(),
        ]);

        $this->assertTrue($ad->isExpired());
    }

    /** @test */
    public function it_can_toggle_active_status()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
            'is_active' => true,
        ]);

        $ad->deactivate();
        $this->assertFalse($ad->is_active);

        $ad->activate();
        $this->assertTrue($ad->is_active);
    }

    /** @test */
    public function it_logs_audit_events()
    {
        $this->actingAs($this->user);

        $ad = Ad::factory()->create([
            'owner_id' => $this->user->id,
            'owner_type' => get_class($this->user),
        ]);

        // Should have created event
        $logs = $ad->auditLogs;
        $this->assertGreaterThan(0, $logs->count());
        
        $firstLog = $logs->first();
        $this->assertEquals('created', $firstLog->event_type);
        $this->assertEquals($this->user->id, $firstLog->user_id);
    }
}
