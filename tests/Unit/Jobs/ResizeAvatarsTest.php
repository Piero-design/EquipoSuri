<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\Contact\Contact;
use App\Jobs\ResizeAvatars;

class ResizeAvatarsTest extends TestCase
{
    public function test_handle_returns_if_no_avatar()
    {
        $contact = \Mockery::mock(Contact::class)->makePartial();
        $contact->shouldReceive('getAttribute')->with('has_avatar')->andReturn(false);

        $job = new ResizeAvatars($contact);
        $job->handle();

        $this->assertTrue(true);
    }
}
