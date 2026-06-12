<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Traits\AmountFormatter;
use App\Traits\DAVFormat;
use App\Traits\HasUuid;
use App\Traits\Hasher;
use App\Traits\Journalable;
use App\Traits\JsonRespondController;
use App\Traits\Searchable;
use App\Traits\StripeCall;
use App\Traits\Subscription;
use App\Traits\WithUser;

use App\Models\Settings\Currency;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\StripeException;
use Illuminate\Database\Eloquent\Builder;

// Dummy classes
class DummyAmountFormatterModel extends Model {
    use AmountFormatter;
    protected $table = 'users'; 
}

class DummyDAVFormat {
    use DAVFormat;
    public function format($value) {
        return $this->formatValue($value);
    }
}

class DummyHasUuidModel extends Model {
    use HasUuid;
    public function save(array $options = []) {
        return true;
    }
}

class DummyHasherBaseModel extends Model {
    public function getRouteKey() {
        return 123;
    }
}
class DummyHasherModel extends DummyHasherBaseModel {
    use Hasher;
}

class DummyJournalableModel extends Model {
    use Journalable;
}

class DummyJsonRespondController {
    use JsonRespondController;
}

class DummySearchableModel extends Model {
    use Searchable;
    protected $table = 'users';
    public $searchable_columns = ['first_name', 'last_name'];
    public $return_from_search = ['id', 'first_name'];
}

class DummyStripeCall {
    use StripeCall;
    public function call($callback) {
        return $this->stripeCall($callback);
    }
}

class DummySubscriptionModel extends Model {
    use Subscription;
    public $has_access_to_paid_version_for_free = false;
    public function getSubscribedPlan() {
        return null;
    }
}

class FakePlan {
    public $stripe_price = 'price_123';
    public $name = 'Pro';
    public function cancelNow() { return true; }
}

class DummySubscriptionModelWithPlan extends DummySubscriptionModel {
    public function getSubscribedPlan() {
        return new FakePlan();
    }
}

class DummyWithUser {
    use WithUser;
    public function getUser() {
        return $this->user;
    }
}

class TraitsExtraTest extends TestCase
{
    use DatabaseTransactions;

    public function test_amount_formatter_trait()
    {
        $currency = factory(Currency::class)->create();
        $model = new DummyAmountFormatterModel();
        
        $model->currency_id = $currency->id;
        $model->setRelation('currency', $currency);
        
        $model->amount = 100.50; 
        
        $this->assertNotNull($model->amount);
        $this->assertNotNull($model->value);
        $this->assertNotNull($model->display_value);
    }

    public function test_dav_format_trait()
    {
        $dummy = new DummyDAVFormat();
        $this->assertEquals('test;test', $dummy->format('test\;test'));
        $this->assertNull($dummy->format(''));
        $this->assertNull($dummy->format(null));
    }

    public function test_has_uuid_trait()
    {
        $model = new DummyHasUuidModel();
        $uuid = $model->uuid;
        $this->assertNotNull($uuid);
        $this->assertIsString($uuid);
        
        $model->attributes['uuid'] = 'existing-uuid';
        $this->assertEquals('existing-uuid', $model->uuid);
    }

    public function test_hasher_trait()
    {
        $model = new DummyHasherModel();
        $hashed = $model->hashID();
        $this->assertNotNull($hashed);
    }

    public function test_journalable_trait()
    {
        $model = new DummyJournalableModel();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $model->journalEntries());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphOne::class, $model->journalEntry());
        
        $this->assertFalse($model->deleteJournalEntry());
    }

    public function test_json_respond_controller_trait()
    {
        $dummy = new DummyJsonRespondController();
        $this->assertEquals(200, $dummy->getHTTPStatusCode());
        
        $dummy->setHTTPStatusCode(400);
        $this->assertEquals(400, $dummy->getHTTPStatusCode());
        
        $dummy->setErrorCode(10);
        $this->assertEquals(10, $dummy->getErrorCode());

        $response = $dummy->respondNotFound();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(31, $dummy->getErrorCode());

        $response = $dummy->respondInvalidQuery('test message');
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(40, $dummy->getErrorCode());

        $response = $dummy->respondInvalidParameters('test');
        $this->assertEquals(422, $response->getStatusCode());
        
        $response = $dummy->respondUnauthorized('test');
        $this->assertEquals(401, $response->getStatusCode());
        
        $response = $dummy->respondObjectDeleted(123);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['deleted']);
        $this->assertEquals(123, $data['id']);
    }

    public function test_searchable_trait()
    {
        $model = new DummySearchableModel();
        $builder = $model->newQuery();
        $result = $model->scopeSearch($builder, 'test', 1, 'first_name', 'asc');
        
        $this->assertInstanceOf(Builder::class, $result);
    }
    
    public function test_searchable_trait_no_columns()
    {
        $model = new DummySearchableModel();
        $model->searchable_columns = null;
        $builder = $model->newQuery();
        $result = $model->scopeSearch($builder, 'test', 1, 'first_name', 'asc');
        
        $this->assertNull($result);
    }

    public function test_stripe_call_trait()
    {
        $dummy = new DummyStripeCall();
        $result = $dummy->call(function() { return 'ok'; });
        $this->assertEquals('ok', $result);

        $this->expectException(StripeException::class);
        $dummy->call(function() { throw new \Exception('test'); });
    }

    public function test_subscription_trait()
    {
        $model = new DummySubscriptionModel();
        $this->assertFalse($model->isSubscribed());
        $this->assertEquals('', $model->getSubscribedPlanId());
        $this->assertNull($model->getSubscribedPlanName());
        $this->assertFalse($model->subscriptionCancel());
        
        $model->has_access_to_paid_version_for_free = true;
        $this->assertTrue($model->isSubscribed());
    }
    
    public function test_subscription_trait_with_plan()
    {
        $model = new DummySubscriptionModelWithPlan();
        $this->assertTrue($model->isSubscribed());
        $this->assertEquals('price_123', $model->getSubscribedPlanId());
        $this->assertEquals('Pro', $model->getSubscribedPlanName());
        $this->assertTrue($model->subscriptionCancel());
    }

    public function test_with_user_trait()
    {
        $dummy = new DummyWithUser();
        $user = factory(User::class)->create();
        $dummy->init($user);
        
        $this->assertEquals($user->id, $dummy->getUser()->id);
    }
}
