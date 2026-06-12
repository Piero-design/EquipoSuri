<?php

namespace Tests\Unit\Jobs\Avatars;

use Tests\TestCase;
use App\Models\Contact\Contact;
use App\Jobs\Avatars\MoveContactAvatarToPhotosDirectory;

class MoveContactAvatarToPhotosDirectoryTest extends TestCase
{
    public function test_it_creates_job()
    {
        $contact = \Mockery::mock(Contact::class)->makePartial();
        $job = new MoveContactAvatarToPhotosDirectory($contact, true);

        $this->assertInstanceOf(MoveContactAvatarToPhotosDirectory::class, $job);
    }
}
