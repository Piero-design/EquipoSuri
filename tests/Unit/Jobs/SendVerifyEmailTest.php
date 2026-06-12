<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\User\User;
use App\Jobs\SendVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;

class SendVerifyEmailTest extends TestCase
{
    public function test_handle()
    {
        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('notify')->once()->with(\Mockery::type(VerifyEmail::class));

        $job = new SendVerifyEmail($user);
        $job->handle();

        $this->assertTrue(true);
    }
}
