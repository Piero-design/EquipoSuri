<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\Account\ImportJob;
use App\Jobs\AddContactFromVCard;

class AddContactFromVCardTest extends TestCase
{
    public function test_it_calls_process()
    {
        $importJob = \Mockery::mock(ImportJob::class);
        $importJob->shouldReceive('process')->once()->with('behaviour_add');

        $job = new AddContactFromVCard($importJob);
        $job->handle();

        $this->assertTrue(true);
    }
}
