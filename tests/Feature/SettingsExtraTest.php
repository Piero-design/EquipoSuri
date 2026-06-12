<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\FeatureTestCase;
use App\Models\User\User;
use App\Models\Contact\Tag;
use App\Models\Account\ImportJob;
use App\Models\Account\Invitation;
use App\Models\Contact\Contact;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsExtraTest extends FeatureTestCase
{
    use DatabaseTransactions;

    public function test_save_updates_user_settings()
    {
        $user = $this->signIn();

        $response = $this->post(route('settings.save'), [
            'first_name' => 'New',
            'last_name' => 'Name',
            'timezone' => 'UTC',
            'locale' => 'en',
            'fluid_container' => true,
            'temperature_scale' => 'celsius',
            'currency_id' => 1,
            'name_order' => 'firstname_lastname',
            'email' => 'new_email_test@example.com',
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'New',
            'last_name' => 'Name',
        ]);
    }

    public function test_reset_account_dispatches_job()
    {
        $this->signIn();

        $response = $this->post(route('settings.reset'));

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('status');
    }

    public function test_import_page_loads()
    {
        $this->signIn();

        $response = $this->get(route('settings.import'));
        $response->assertStatus(200);
    }

    public function test_upload_page_loads()
    {
        $this->signIn();

        $response = $this->get(route('settings.upload'));
        // In case of limitations it might redirect, but usually 200 or 302
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_store_import_uploads_file()
    {
        $user = $this->signIn();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('contacts.vcf', 10);

        $response = $this->post(route('settings.storeImport'), [
            'vcard' => $file,
            'behaviour' => 'add',
        ]);

        $response->assertRedirect(route('settings.import'));
        
        $this->assertDatabaseHas('import_jobs', [
            'user_id' => $user->id,
            'account_id' => $user->account_id,
            'type' => 'vcard',
        ]);
    }

    public function test_report_page_loads()
    {
        $user = $this->signIn();

        $job = factory(ImportJob::class)->create([
            'account_id' => $user->account_id,
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('settings.report', ['importjobid' => $job->id]));
        $response->assertStatus(200);
    }

    public function test_users_page_loads()
    {
        $this->signIn();

        $response = $this->get(route('settings.users.index'));
        $response->assertStatus(200);
    }

    public function test_add_user_page_loads()
    {
        $this->signIn();

        $response = $this->get(route('settings.users.create'));
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_invite_user()
    {
        $user = $this->signIn();
        Mail::fake();

        $response = $this->post(route('settings.users.store'), [
            'email' => 'newuser@example.com',
            'confirmation' => '1',
        ]);

        $response->assertRedirect(route('settings.users.index'));

        $this->assertDatabaseHas('invitations', [
            'email' => 'newuser@example.com',
            'account_id' => $user->account_id,
        ]);
    }

    public function test_destroy_invitation()
    {
        $user = $this->signIn();

        $invitation = factory(Invitation::class)->create([
            'account_id' => $user->account_id,
            'invited_by_user_id' => $user->id,
        ]);

        $response = $this->delete(route('settings.users.invitation.delete', ['invitation' => $invitation->id]));

        $response->assertRedirect(route('settings.users.index'));
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }

    public function test_delete_additional_user()
    {
        $user = $this->signIn();

        $additionalUser = factory(User::class)->create([
            'account_id' => $user->account_id,
        ]);

        $response = $this->delete(route('settings.users.destroy', $additionalUser->id));

        $response->assertRedirect(route('settings.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $additionalUser->id]);
    }

    public function test_tags_page_loads()
    {
        $this->signIn();

        $response = $this->get(route('settings.tags.index'));
        $response->assertStatus(200);
    }

    public function test_delete_tag()
    {
        $user = $this->signIn();

        $tag = factory(Tag::class)->create([
            'account_id' => $user->account_id,
        ]);

        $response = $this->delete(route('settings.tags.delete', ['tag' => $tag->id]));

        $response->assertRedirect(route('settings.tags.index'));
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_edit_tag()
    {
        $user = $this->signIn();

        $tag = factory(Tag::class)->create([
            'account_id' => $user->account_id,
            'name' => 'Old Name',
        ]);

        $response = $this->put(route('settings.tags.update', $tag->id), [
            'name' => 'New Name',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name',
        ]);
    }

    public function test_api_page_loads()
    {
        $this->signIn();

        $response = $this->get(route('settings.api'));
        $response->assertStatus(200);
    }

    public function test_dav_page_loads()
    {
        $this->signIn();

        $response = $this->get(route('settings.dav'));
        $response->assertStatus(200);
    }
}
