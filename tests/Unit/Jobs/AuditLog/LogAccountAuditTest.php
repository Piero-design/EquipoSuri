<?php

namespace Tests\Unit\Jobs\AuditLog;

use Tests\TestCase;
use App\Jobs\AuditLog\LogAccountAudit;

class LogAccountAuditTest extends TestCase
{
    public function test_constructor()
    {
        $job = new LogAccountAudit(['test' => 'data']);
        $this->assertEquals(['test' => 'data'], $job->auditLog);
    }
}
