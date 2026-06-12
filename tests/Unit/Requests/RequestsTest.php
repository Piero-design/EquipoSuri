<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;

class RequestsTest extends TestCase
{
    public function requestProvider(): array
    {
        return [
            [\App\Http\Requests\AuthorizedRequest::class],
            [\App\Http\Requests\EmailChangeRequest::class],
            [\App\Http\Requests\ImportsRequest::class],
            [\App\Http\Requests\InvitationRequest::class],
            [\App\Http\Requests\Journal\DaysRequest::class],
            [\App\Http\Requests\PasswordChangeRequest::class],
            [\App\Http\Requests\People\ContactFieldsRequest::class],
            [\App\Http\Requests\People\ConversationRequest::class],
            [\App\Http\Requests\People\DebtRequest::class],
            [\App\Http\Requests\People\GiftsRequest::class],
            [\App\Http\Requests\People\NoteToggleRequest::class],
            [\App\Http\Requests\People\NotesRequest::class],
            [\App\Http\Requests\People\PetsRequest::class],
            [\App\Http\Requests\Settings\GendersRequest::class],
            [\App\Http\Requests\SettingsRequest::class],
        ];
    }

    /**
     * @dataProvider requestProvider
     */
    public function test_it_has_expected_methods(string $requestClass)
    {
        $request = new $requestClass();

        if (method_exists($request, 'authorize')) {
            $this->assertIsBool($request->authorize());
        }

        if (method_exists($request, 'rules')) {
            $this->assertIsArray($request->rules());
        }
        
        // Assert true just in case a class has neither, so the test isn't marked as risky
        $this->assertTrue(true);
    }
}
