<?php

namespace Tests\Unit\ViewHelpers;

use Tests\TestCase;
use App\ViewHelpers\ContactHelper;
use App\Models\Instance\AuditLog;
use App\Models\User\User;
use App\Models\Account\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ContactHelperTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_returns_empty_collection_when_no_logs()
    {
        $logs = collect();

        $result = ContactHelper::getListOfAuditLogs($logs);

        $this->assertCount(0, $result);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
    }

    /** @test */
    public function it_returns_audit_log_with_author_name_when_author_exists()
    {
        $account = factory(Account::class)->create();
        $user = factory(User::class)->create([
            'account_id' => $account->id,
            'first_name' => 'Michael',
            'last_name' => 'Scott',
        ]);

        $auditLog = factory(AuditLog::class)->create([
            'account_id' => $account->id,
            'author_id' => $user->id,
            'author_name' => 'Fallback Name',
            'action' => 'account_created',
            'audited_at' => Carbon::parse('2020-05-15 10:30:00'),
        ]);

        // Reload with author relationship
        $logs = AuditLog::with('author')->where('id', $auditLog->id)->get();

        $result = ContactHelper::getListOfAuditLogs($logs);

        $this->assertCount(1, $result);
        // When author exists, it should use $log->author->name instead of $log->author_name
        $this->assertEquals($user->name, $result[0]['author_name']);
        $this->assertArrayHasKey('description', $result[0]);
        $this->assertArrayHasKey('audited_at', $result[0]);
    }

    /** @test */
    public function it_falls_back_to_author_name_field_when_author_is_null()
    {
        $account = factory(Account::class)->create();

        $auditLog = factory(AuditLog::class)->create([
            'account_id' => $account->id,
            'author_id' => null,
            'author_name' => 'Deleted User',
            'action' => 'account_created',
            'audited_at' => Carbon::parse('2021-03-10 14:00:00'),
        ]);

        $logs = AuditLog::with('author')->where('id', $auditLog->id)->get();

        $result = ContactHelper::getListOfAuditLogs($logs);

        $this->assertCount(1, $result);
        $this->assertEquals('Deleted User', $result[0]['author_name']);
    }

    /** @test */
    public function it_processes_multiple_audit_logs()
    {
        $account = factory(Account::class)->create();
        $user = factory(User::class)->create([
            'account_id' => $account->id,
        ]);

        factory(AuditLog::class)->create([
            'account_id' => $account->id,
            'author_id' => $user->id,
            'action' => 'account_created',
            'audited_at' => Carbon::now(),
        ]);

        factory(AuditLog::class)->create([
            'account_id' => $account->id,
            'author_id' => null,
            'author_name' => 'Another User',
            'action' => 'account_created',
            'audited_at' => Carbon::now(),
        ]);

        $logs = AuditLog::with('author')
            ->where('account_id', $account->id)
            ->get();

        $result = ContactHelper::getListOfAuditLogs($logs);

        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_returns_a_collection_instance()
    {
        $logs = collect();
        $result = ContactHelper::getListOfAuditLogs($logs);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
    }

    /** @test */
    public function each_log_entry_contains_required_keys()
    {
        $account = factory(Account::class)->create();
        $user = factory(User::class)->create([
            'account_id' => $account->id,
        ]);

        $auditLog = factory(AuditLog::class)->create([
            'account_id' => $account->id,
            'author_id' => $user->id,
            'action' => 'account_created',
            'audited_at' => Carbon::now(),
        ]);

        $logs = AuditLog::with('author')->where('id', $auditLog->id)->get();
        $result = ContactHelper::getListOfAuditLogs($logs);

        $entry = $result->first();
        $this->assertArrayHasKey('author_name', $entry);
        $this->assertArrayHasKey('description', $entry);
        $this->assertArrayHasKey('audited_at', $entry);
    }
}
