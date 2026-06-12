<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\ExportAllAsSQL;

class ExportAllAsSQLTest extends TestCase
{
    public function test_it_can_be_instantiated()
    {
        $job = new ExportAllAsSQL();
        $this->assertInstanceOf(ExportAllAsSQL::class, $job);
    }
}
