<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use App\Services\AdImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $b2bUser;
    protected User $admin;
    protected User $maliciousUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->b2bUser = User::factory()->create(['role' => 'travel_agent']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->maliciousUser = User::factory()->create(['role' => 'hotel_provider']);
    }

    // ===========================================
    // XSS PREVENTION TESTS
    // ===========================================

    /** @test */
    public function it_sanitizes_xss_in_title()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => '<script>alert("XSS")</script>Test Ad',
                'cta_text' => 'Click',
                'cta_action' => 'https://example.com',
            ]);

        $response->assertCreated();
        
        $ad = Ad::latest()->first();
        $this->assertStringNotContainsString('<script>', $ad->title);
        $this->assertStringNotContainsString('alert', $ad->title);
    }

    /** @test */
    public function it_sanitizes_xss_in_cta_text()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => 'Test Ad',
                'cta_text' => '<img src=x onerror=alert(1)>Click Me',
                'cta_action' => 'https://example.com',
            ]);

        $response->assertCreated();
        
        $ad = Ad::latest()->first();
        $this->assertStringNotContainsString('<img', $ad->cta_text);
        $this->assertStringNotContainsString('onerror', $ad->cta_text);
    }

    /** @test */
    public function it_sanitizes_xss_in_description()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => 'Test Ad',
                'description' => '<iframe src="evil.com"></iframe>Description',
                'cta_text' => 'Click',
                'cta_action' => 'https://example.com',
            ]);

        $response->assertCreated();
        
        $ad = Ad::latest()->first();
        $this->assertStringNotContainsString('<iframe', $ad->description);
        $this->assertStringNotContainsString('evil.com', $ad->description);
    }

    /** @test */
    public function it_prevents_javascript_protocol_in_cta_action()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => 'Test Ad',
                'cta_text' => 'Click',
                'cta_action' => 'javascript:alert(document.cookie)',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cta_action']);
    }

    /** @test */
    public function it_prevents_data_protocol_in_cta_action()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => 'Test Ad',
                'cta_text' => 'Click',
                'cta_action' => 'data:text/html,<script>alert(1)</script>',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cta_action']);
    }

    // ===========================================
    // SQL INJECTION PREVENTION TESTS
    // ===========================================

    /** @test */
    public function it_prevents_sql_injection_in_search()
    {
        Ad::factory()->create([
            'title' => 'Legitimate Ad',
            'status' => Ad::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->getJson("/api/v1/ads?search=' OR '1'='1");

        // Should not return all ads or cause error
        $response->assertOk();
        
        // Database should still be intact
        $this->assertDatabaseHas('ads', ['title' => 'Legitimate Ad']);
    }

    /** @test */
    public function it_prevents_sql_injection_in_filters()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->getJson("/api/v1/ads?status='; DROP TABLE ads; --");

        $response->assertOk();
        
        // Table should still exist
        $this->assertDatabaseHas('ads', []);
    }

    // ===========================================
    // AUTHORIZATION BYPASS TESTS
    // ===========================================

    /** @test */
    public function it_prevents_viewing_other_users_draft_ads()
    {
        $otherUserAd = Ad::factory()->create([
            'owner_id' => $this->b2bUser->id,
            'owner_type' => get_class($this->b2bUser),
            'status' => Ad::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->maliciousUser, 'sanctum')
            ->getJson("/api/v1/ads/{$otherUserAd->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function it_prevents_editing_other_users_ads()
    {
        $otherUserAd = Ad::factory()->create([
            'owner_id' => $this->b2bUser->id,
            'owner_type' => get_class($this->b2bUser),
            'status' => Ad::STATUS_DRAFT,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($this->maliciousUser, 'sanctum')
            ->putJson("/api/v1/ads/{$otherUserAd->id}", [
                'title' => 'Hacked Title',
            ]);

        $response->assertForbidden();
        
        $otherUserAd->refresh();
        $this->assertEquals('Original Title', $otherUserAd->title);
    }

    /** @test */
    public function it_prevents_deleting_other_users_ads()
    {
        $otherUserAd = Ad::factory()->create([
            'owner_id' => $this->b2bUser->id,
            'owner_type' => get_class($this->b2bUser),
            'status' => Ad::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->maliciousUser, 'sanctum')
            ->deleteJson("/api/v1/ads/{$otherUserAd->id}");

        $response->assertForbidden();
        
        $this->assertDatabaseHas('ads', ['id' => $otherUserAd->id]);
    }

    /** @test */
    public function it_prevents_non_admin_from_approving_ads()
    {
        $ad = Ad::factory()->create([
            'status' => Ad::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson("/api/v1/ads/{$ad->id}/approve");

        $response->assertForbidden();
        
        $ad->refresh();
        $this->assertEquals(Ad::STATUS_PENDING, $ad->status);
    }

    /** @test */
    public function it_prevents_b2b_user_from_bypassing_approval_workflow()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->b2bUser->id,
            'owner_type' => get_class($this->b2bUser),
            'status' => Ad::STATUS_DRAFT,
        ]);

        // Try to directly set status to approved
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->putJson("/api/v1/ads/{$ad->id}", [
                'status' => Ad::STATUS_APPROVED,
            ]);

        $ad->refresh();
        $this->assertNotEquals(Ad::STATUS_APPROVED, $ad->status);
    }

    // ===========================================
    // FILE UPLOAD SECURITY TESTS
    // ===========================================

    /** @test */
    public function it_rejects_non_image_files()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads/1/upload-image', [
                'image' => $file,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function it_rejects_php_files_disguised_as_images()
    {
        $file = UploadedFile::fake()->createWithContent(
            'malicious.php.jpg',
            '<?php system($_GET["cmd"]); ?>'
        );

        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads/1/upload-image', [
                'image' => $file,
            ]);

        $response->assertUnprocessable();
    }

    /** @test */
    public function it_validates_image_mime_type()
    {
        $service = new AdImageService();
        
        $file = UploadedFile::fake()->create('test.exe', 1000);
        
        $errors = $service->validateImage($file);
        
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('format', implode(' ', $errors));
    }

    /** @test */
    public function it_validates_image_dimensions()
    {
        $service = new AdImageService();
        
        // Create image that's too small
        $smallImage = UploadedFile::fake()->image('small.jpg', 100, 100);
        
        $errors = $service->validateImage($smallImage);
        
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('dimensions', implode(' ', $errors));
    }

    /** @test */
    public function it_rejects_oversized_files()
    {
        $file = UploadedFile::fake()->create('large.jpg', 6000); // 6MB

        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads/1/upload-image', [
                'image' => $file,
            ]);

        $response->assertUnprocessable();
    }

    /** @test */
    public function it_sanitizes_uploaded_filenames()
    {
        $service = new AdImageService();
        
        $file = UploadedFile::fake()->image('../../../evil.jpg', 1200, 800);
        
        $result = $service->uploadImage($file, $this->b2bUser->id);
        
        // Should not contain path traversal
        $this->assertStringNotContainsString('..', $result['original_path']);
        $this->assertStringNotContainsString('/', basename($result['original_path']));
    }

    /** @test */
    public function it_validates_aspect_ratio()
    {
        $service = new AdImageService();
        
        // Create extremely narrow image
        $narrowImage = UploadedFile::fake()->image('narrow.jpg', 2000, 100);
        
        $errors = $service->validateImage($narrowImage);
        
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('aspect ratio', implode(' ', $errors));
    }

    // ===========================================
    // RATE LIMITING TESTS
    // ===========================================

    /** @test */
    public function it_rate_limits_impression_tracking()
    {
        $ad = Ad::factory()->create([
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);

        // Make 61 requests (limit is 60)
        $lastResponse = null;
        for ($i = 0; $i < 61; $i++) {
            $lastResponse = $this->postJson("/api/v1/ads/{$ad->id}/track/impression");
        }

        $lastResponse->assertStatus(429);
    }

    /** @test */
    public function it_rate_limits_click_tracking()
    {
        $ad = Ad::factory()->create([
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
        ]);

        // Make 31 requests (limit is 30)
        $lastResponse = null;
        for ($i = 0; $i < 31; $i++) {
            $lastResponse = $this->postJson("/api/v1/ads/{$ad->id}/track/click");
        }

        $lastResponse->assertStatus(429);
    }

    // ===========================================
    // CSRF PROTECTION TESTS
    // ===========================================

    /** @test */
    public function it_requires_csrf_token_for_web_routes()
    {
        // Web routes should require CSRF token
        $response = $this->post('/b2b/ads', [
            'title' => 'Test Ad',
        ]);

        // Should fail without CSRF token
        $response->assertStatus(419); // CSRF token mismatch
    }

    // ===========================================
    // DEEP LINK SECURITY TESTS
    // ===========================================

    /** @test */
    public function it_validates_cta_action_url_format()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => 'Test Ad',
                'cta_text' => 'Click',
                'cta_action' => 'not-a-valid-url',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['cta_action']);
    }

    /** @test */
    public function it_allows_https_urls()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => 'Test Ad',
                'cta_text' => 'Click',
                'cta_action' => 'https://example.com/package/123',
            ]);

        $response->assertCreated();
    }

    /** @test */
    public function it_allows_app_deep_links()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => 'Test Ad',
                'cta_text' => 'Click',
                'cta_action' => 'seferet://package/123',
            ]);

        // Assuming app deep links are allowed
        $response->assertCreated();
    }

    /** @test */
    public function it_prevents_file_protocol_in_cta_action()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => 'Test Ad',
                'cta_text' => 'Click',
                'cta_action' => 'file:///etc/passwd',
            ]);

        $response->assertUnprocessable();
    }

    // ===========================================
    // INFORMATION DISCLOSURE TESTS
    // ===========================================

    /** @test */
    public function it_does_not_leak_draft_ads_in_public_serving()
    {
        Ad::factory()->create([
            'status' => Ad::STATUS_DRAFT,
            'title' => 'Secret Draft Ad',
        ]);

        $response = $this->getJson('/api/v1/ads/serve');

        $response->assertOk();
        
        $ads = $response->json('ads');
        foreach ($ads as $ad) {
            $this->assertNotEquals('Secret Draft Ad', $ad['title']);
        }
    }

    /** @test */
    public function it_does_not_expose_sensitive_data_in_api_responses()
    {
        $ad = Ad::factory()->create([
            'status' => Ad::STATUS_APPROVED,
            'is_active' => true,
            'admin_notes' => 'Internal notes about advertiser',
        ]);

        $response = $this->getJson("/api/v1/ads/serve/{$ad->id}");

        $response->assertOk();
        
        // Should not expose internal fields
        $this->assertArrayNotHasKey('admin_notes', $response->json('ad'));
        $this->assertArrayNotHasKey('approved_by', $response->json('ad'));
        $this->assertArrayNotHasKey('rejection_reason', $response->json('ad'));
    }

    /** @test */
    public function it_hides_ip_addresses_from_non_admins()
    {
        $ad = Ad::factory()->create([
            'owner_id' => $this->b2bUser->id,
            'owner_type' => get_class($this->b2bUser),
        ]);

        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->getJson("/api/v1/ads/{$ad->id}");

        $response->assertOk();
        
        // IP addresses should not be exposed
        $json = $response->json();
        $this->assertArrayNotHasKey('ip_address', $json);
    }

    // ===========================================
    // MASS ASSIGNMENT PROTECTION TESTS
    // ===========================================

    /** @test */
    public function it_prevents_mass_assignment_of_protected_fields()
    {
        $response = $this->actingAs($this->b2bUser, 'sanctum')
            ->postJson('/api/v1/ads', [
                'title' => 'Test Ad',
                'cta_text' => 'Click',
                'cta_action' => 'https://example.com',
                'status' => Ad::STATUS_APPROVED, // Should not be mass assignable
                'impressions_count' => 10000, // Should not be mass assignable
                'clicks_count' => 5000, // Should not be mass assignable
            ]);

        $response->assertCreated();
        
        $ad = Ad::latest()->first();
        $this->assertEquals(Ad::STATUS_DRAFT, $ad->status);
        $this->assertEquals(0, $ad->impressions_count);
        $this->assertEquals(0, $ad->clicks_count);
    }
}
