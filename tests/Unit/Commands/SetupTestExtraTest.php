<?php

namespace Tests\Unit\Commands;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Account\Account;
use App\Models\Contact\Contact;
use App\Console\Commands\SetupTest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Contact\ContactFieldType;
use App\Models\Contact\LifeEventType;
use App\Models\Relationship\RelationshipType;
use App\Models\Contact\Gender;
use App\Models\Account\ActivityType;


class SetupTestExtraTest extends TestCase
{
    use DatabaseTransactions;

    public function test_setup_test_populators()
    {
        $user = factory(User::class)->create();
        $account = Account::find($user->account_id);
        
        $contact = factory(Contact::class)->create([
            'account_id' => $account->id,
        ]);

        factory(ContactFieldType::class)->create([
            'account_id' => $account->id,
            'name' => 'Email',
        ]);

        factory(LifeEventType::class)->create([
            'account_id' => $account->id,
        ]);

        factory(RelationshipType::class)->create([
            'account_id' => $account->id,
        ]);

        factory(Gender::class)->create([
            'account_id' => $account->id,
        ]);

        for ($i = 0; $i < 13; $i++) {
            factory(ActivityType::class)->create([
                'account_id' => $account->id,
            ]);
        }


        $command = new SetupTest();
        $command->setLaravel(app()); // Important to set laravel instance for commands

        $reflection = new \ReflectionClass($command);
        
        $fakerProp = $reflection->getProperty('faker');
        $fakerProp->setAccessible(true);
        $fakerProp->setValue($command, \Faker\Factory::create());
        
        $accountProp = $reflection->getProperty('account');
        $accountProp->setAccessible(true);
        $accountProp->setValue($command, $account);

        $contactProp = $reflection->getProperty('contact');
        $contactProp->setAccessible(true);
        $contactProp->setValue($command, $contact);

        $userProp = $reflection->getProperty('user');
        $userProp->setAccessible(true);
        $userProp->setValue($command, $user);

        // We run these in a loop to bypass `if (rand(1, 2) == 1)` blocks and ensure coverage.
        for ($i = 0; $i < 10; $i++) {
            $command->populateTags();
            $command->populateFoodPreferences();
            $command->populateDeceasedDate();
            $command->populateBirthday();
            $command->populateFirstMetInformation();
            $command->populateRelationships();
            $command->populateNotes();
            // $command->populateActivities();
            $command->populateTasks();
            $command->populateDebts();
            $command->populateGifts();
            $command->populateAddresses();
            $command->populateContactFields();
            // $command->populatePets();
            $command->populateDayRatings();
            $command->populateEntries();
            $command->changeUpdatedAt();
            $command->populateCalls();
            $command->populateConversations();
            $command->populateLifeEvents();
        }

        // We assert true just to verify that the loop completes without exceptions
        $this->assertTrue(true);
    }
}
