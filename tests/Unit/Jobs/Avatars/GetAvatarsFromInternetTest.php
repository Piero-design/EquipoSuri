<?php

namespace Tests\Unit\Jobs\Avatars;

use Tests\TestCase;
use App\Models\Contact\Contact;
use App\Jobs\Avatars\GetAvatarsFromInternet;

class GetAvatarsFromInternetTest extends TestCase
{
    public function test_it_creates_job()
    {
        $contact = \Mockery::mock(Contact::class)->makePartial();
        $job = new GetAvatarsFromInternet($contact);

        $this->assertInstanceOf(GetAvatarsFromInternet::class, $job);
    }
}
