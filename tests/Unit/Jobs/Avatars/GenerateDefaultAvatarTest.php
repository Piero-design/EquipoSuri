<?php

namespace Tests\Unit\Jobs\Avatars;

use Tests\TestCase;
use App\Models\Contact\Contact;
use App\Jobs\Avatars\GenerateDefaultAvatar;

class GenerateDefaultAvatarTest extends TestCase
{
    public function test_it_creates_job()
    {
        $contact = \Mockery::mock(Contact::class)->makePartial();
        $job = new GenerateDefaultAvatar($contact);

        $this->assertInstanceOf(GenerateDefaultAvatar::class, $job);
    }
}
