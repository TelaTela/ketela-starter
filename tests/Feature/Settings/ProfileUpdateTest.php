<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_name_is_trimmed_before_saving()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => '  Test User  ',
                'email' => $user->email,
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('Test User', $user->refresh()->name);
    }

    public function test_verification_email_is_sent_when_email_changes()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'new-email@example.com',
        ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_email_is_not_sent_when_email_is_unchanged()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        Notification::assertNothingSent();
    }

    public function test_avatar_can_be_uploaded()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 400, 400),
            ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertTrue($user->hasMedia('avatar'));
        $this->assertNotNull($user->getFirstMediaUrl('avatar', 'thumb'));
        $this->assertNotNull($user->getFirstMediaUrl('avatar', 'preview'));
    }

    public function test_uploading_a_new_avatar_replaces_the_previous_one()
    {
        $user = User::factory()->create();
        $user->addMedia(UploadedFile::fake()->image('old.jpg', 400, 400))
            ->toMediaCollection('avatar');

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('new.jpg', 400, 400),
        ]);

        $this->assertCount(1, $user->refresh()->getMedia('avatar'));
    }

    public function test_avatar_must_be_an_image()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('not-an-image.pdf', 100),
        ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertFalse($user->refresh()->hasMedia('avatar'));
    }

    public function test_profile_update_is_logged()
    {
        Log::spy();

        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 400, 400),
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Settings/Profile: Profile updated.', \Mockery::on(function (array $context) use ($user) {
                return $context['user_id'] === $user->id
                    && $context['name_changed'] === true
                    && $context['email_changed'] === false
                    && $context['avatar_changed'] === true;
            }));
    }

    public function test_profile_update_flashes_success_toast()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'New Name',
                'email' => $user->email,
            ]);

        $response
            ->assertRedirect(route('profile.edit'))
            ->assertInertiaFlash('toast.type', 'success')
            ->assertInertiaFlash('toast.message', __('settings/profile.update.success'));
    }

    public function test_user_can_delete_their_account()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->delete(route('profile.destroy'), [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect(route('profile.edit'));

        $this->assertNotNull($user->fresh());
    }
}
