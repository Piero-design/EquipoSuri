<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\User\User;
use App\Jobs\SendNewUserAlert;
use Illuminate\Support\Facades\Notification;

class SendNewUserAlertTest extends TestCase
{
    public function test_handle()
    {
        Notification::fake();
        config(['monica.email_new_user_notification' => '']);
        
        $user = \Mockery::mock(User::class)->makePartial();
        $job = new SendNewUserAlert($user);
        $job->handle();

        $this->assertTrue(true);
    }
}
