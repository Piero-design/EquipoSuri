<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\Account\ExportJob;
use App\Jobs\ExportAccount;

class ExportAccountTest extends TestCase
{
    public function test_constructor_updates_status()
    {
        $exportJob = \Mockery::mock(ExportJob::class)->makePartial();
        $exportJob->shouldReceive('save')->once();
        $exportJob->shouldReceive('withoutRelations')->once()->andReturnSelf();

        $job = new ExportAccount($exportJob, 'test-path');
        
        $this->assertInstanceOf(ExportAccount::class, $job);
    }
}
