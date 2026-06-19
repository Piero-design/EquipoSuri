<?php

namespace Tests\Unit\ExportResources;

use Tests\TestCase;
use App\Models\User\User;
use App\Models\Contact\Contact;
use App\Models\Contact\Address;
use App\Models\Contact\Call;
use App\Models\Account\Account;
use App\Models\Account\AddressBook;
use App\Models\Account\AddressBookSubscription;
use Illuminate\Foundation\Testing\DatabaseTransactions;

// Import the resources
use App\ExportResources\Account\Addressbook as ExportAddressbook;
use App\ExportResources\Account\AddressbookSubscription as ExportAddressbookSubscription;
use App\ExportResources\Contact\Address as ExportAddress;
use App\ExportResources\Contact\Call as ExportCall;
use App\ExportResources\Contact\Contact as ExportContact;

class ExportResourcesExtraTest extends TestCase
{
    use DatabaseTransactions;



    public function test_contact_address()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create(['account_id' => $user->account_id]);
        $model = factory(Address::class)->create(['contact_id' => $contact->id, 'account_id' => $user->account_id]);
        
        $resource = new ExportAddress($model);
        $this->assertIsArray($resource->toArray(request()));
    }

    public function test_contact_call()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create(['account_id' => $user->account_id]);
        $model = factory(Call::class)->create(['contact_id' => $contact->id, 'account_id' => $user->account_id]);
        
        $resource = new ExportCall($model);
        $this->assertIsArray($resource->toArray(request()));
    }

    public function test_contact_contact()
    {
        $user = factory(User::class)->create();
        $contact = factory(Contact::class)->create(['account_id' => $user->account_id]);
        
        $resource = new ExportContact($contact);
        $this->assertIsArray($resource->toArray(request()));
    }
}
