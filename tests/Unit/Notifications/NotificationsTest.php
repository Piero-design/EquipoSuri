<?php

namespace Tests\Unit\Notifications;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Models\Account\ExportJob;
use App\Models\Account\Invitation;
use App\Models\Contact\Contact;
use App\Models\Contact\Reminder;
use App\Notifications\NewUserAlert;
use App\Notifications\InvitationMail;
use App\Notifications\ExportAccountDone;
use App\Notifications\UserNotified;
use App\Notifications\UserReminded;
use App\Notifications\StayInTouchEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NotificationsTest extends TestCase
{
    use DatabaseTransactions;

    // =========================================================================
    // NewUserAlert
    // =========================================================================

    /** @test */
    public function new_user_alert_via_returns_mail()
    {
        $user = factory(User::class)->create();

        $notification = new NewUserAlert($user);

        $this->assertEquals(['mail'], $notification->via());
    }

    /** @test */
    public function new_user_alert_to_mail_returns_mail_message()
    {
        $user = factory(User::class)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $notification = new NewUserAlert($user);
        $mailMessage = $notification->toMail();

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    // =========================================================================
    // InvitationMail
    // =========================================================================

    /** @test */
    public function invitation_mail_via_returns_mail()
    {
        $notification = new InvitationMail();

        $this->assertEquals(['mail'], $notification->via());
    }

    /** @test */
    public function invitation_mail_to_mail_returns_mail_message()
    {
        $invitation = factory(Invitation::class)->create();

        $notification = new InvitationMail();
        $mailMessage = $notification->toMail($invitation);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    // =========================================================================
    // ExportAccountDone
    // =========================================================================

    /** @test */
    public function export_account_done_via_returns_mail()
    {
        $exportJob = ExportJob::factory()->create();

        $notification = new ExportAccountDone($exportJob);

        $this->assertEquals(['mail'], $notification->via());
    }

    /** @test */
    public function export_account_done_to_mail_returns_mail_message()
    {
        $user = factory(User::class)->create();
        $exportJob = ExportJob::factory()->create([
            'account_id' => $user->account_id,
            'user_id' => $user->id,
        ]);

        $notification = new ExportAccountDone($exportJob);
        $mailMessage = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    // =========================================================================
    // UserNotified
    // =========================================================================

    /** @test */
    public function user_notified_via_returns_mail()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);
        $reminder = factory(Reminder::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'title' => 'Test reminder',
            'initial_date' => '2050-01-01',
            'frequency_type' => 'year',
            'frequency_number' => 1,
        ]);

        $notification = new UserNotified($reminder, 7);

        $this->assertEquals(['mail'], $notification->via());
    }

    /** @test */
    public function user_notified_to_mail_returns_mail_message()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);
        $reminder = factory(Reminder::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'title' => 'Test reminder',
            'initial_date' => '2050-01-01',
            'frequency_type' => 'year',
            'frequency_number' => 1,
        ]);

        $notification = new UserNotified($reminder, 7);
        $mailMessage = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    /** @test */
    public function user_notified_to_mail_includes_description_when_present()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);
        $reminder = factory(Reminder::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'title' => 'Test reminder',
            'description' => 'Some extra details',
            'initial_date' => '2050-01-01',
            'frequency_type' => 'year',
            'frequency_number' => 1,
        ]);

        $notification = new UserNotified($reminder, 7);
        $mailMessage = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    /** @test */
    public function user_notified_stores_number_days_before()
    {
        $reminder = factory(Reminder::class)->create([
            'title' => 'Test reminder',
            'initial_date' => '2050-01-01',
            'frequency_type' => 'year',
            'frequency_number' => 1,
        ]);

        $notification = new UserNotified($reminder, 30);

        $this->assertEquals(30, $notification->numberDaysBefore);
    }

    // =========================================================================
    // UserReminded
    // =========================================================================

    /** @test */
    public function user_reminded_via_returns_mail()
    {
        $reminder = factory(Reminder::class)->create([
            'title' => 'Test reminder',
            'initial_date' => '2050-01-01',
            'frequency_type' => 'year',
            'frequency_number' => 1,
        ]);

        $notification = new UserReminded($reminder);

        $this->assertEquals(['mail'], $notification->via());
    }

    /** @test */
    public function user_reminded_to_mail_returns_mail_message()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);
        $reminder = factory(Reminder::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'title' => 'Test reminder',
            'initial_date' => '2050-01-01',
            'frequency_type' => 'year',
            'frequency_number' => 1,
        ]);

        $notification = new UserReminded($reminder);
        $mailMessage = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    /** @test */
    public function user_reminded_to_mail_includes_description_when_present()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
        ]);
        $reminder = factory(Reminder::class)->create([
            'account_id' => $user->account_id,
            'contact_id' => $contact->id,
            'title' => 'Test reminder',
            'description' => 'Some extra details about this reminder',
            'initial_date' => '2050-01-01',
            'frequency_type' => 'year',
            'frequency_number' => 1,
        ]);

        $notification = new UserReminded($reminder);
        $mailMessage = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    /** @test */
    public function user_reminded_stores_reminder()
    {
        $reminder = factory(Reminder::class)->create([
            'title' => 'Birthday reminder',
            'initial_date' => '2050-01-01',
            'frequency_type' => 'year',
            'frequency_number' => 1,
        ]);

        $notification = new UserReminded($reminder);

        $this->assertEquals($reminder->id, $notification->reminder->id);
    }

    // =========================================================================
    // StayInTouchEmail
    // =========================================================================

    /** @test */
    public function stay_in_touch_email_via_returns_mail()
    {
        $contact = factory(Contact::class)->create([
            'stay_in_touch_frequency' => 14,
        ]);

        $notification = new StayInTouchEmail($contact);

        $this->assertEquals(['mail'], $notification->via());
    }

    /** @test */
    public function stay_in_touch_email_to_mail_returns_mail_message()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create([
            'account_id' => $user->account_id,
            'stay_in_touch_frequency' => 14,
        ]);

        $notification = new StayInTouchEmail($contact);
        $mailMessage = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mailMessage);
    }

    /** @test */
    public function stay_in_touch_email_assert_sent_for_matches_correct_contact()
    {
        $contact = factory(Contact::class)->create([
            'stay_in_touch_frequency' => 14,
        ]);

        $notification = new StayInTouchEmail($contact);

        $this->assertTrue($notification->assertSentFor($contact));
    }

    /** @test */
    public function stay_in_touch_email_assert_sent_for_does_not_match_wrong_contact()
    {
        $contact1 = factory(Contact::class)->create([
            'stay_in_touch_frequency' => 14,
        ]);
        $contact2 = factory(Contact::class)->create([
            'stay_in_touch_frequency' => 7,
        ]);

        $notification = new StayInTouchEmail($contact1);

        $this->assertFalse($notification->assertSentFor($contact2));
    }
}
