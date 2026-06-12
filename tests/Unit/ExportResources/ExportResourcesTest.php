<?php

namespace Tests\Unit\ExportResources;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use App\Models\Account\Account;
use App\Models\Account\Activity;
use App\Models\Account\ActivityType;
use App\Models\Account\ActivityTypeCategory;
use App\Models\Account\Photo;
use App\Models\Contact\Contact;
use App\Models\Contact\ContactField;
use App\Models\Contact\ContactFieldType;
use App\Models\Contact\Conversation;
use App\Models\Contact\Debt;
use App\Models\Contact\Document;
use App\Models\Contact\Gender;
use App\Models\Contact\Gift;
use App\Models\Contact\LifeEvent;
use App\Models\Contact\LifeEventCategory;
use App\Models\Contact\LifeEventType;
use App\Models\Contact\Message;
use App\Models\Contact\Note;
use App\Models\Contact\Pet;
use App\Models\Contact\Reminder;
use App\Models\Contact\ReminderRule;
use App\Models\Contact\Task;
use App\Models\Contact\Call;
use App\Models\Instance\AuditLog;
use App\Models\Instance\SpecialDate;
use App\Models\Relationship\Relationship;
use App\Models\User\Module;
use App\Models\User\SyncToken;
use App\ExportResources\CountResourceCollection;
use App\ExportResources\MapUuidResourceCollection;
use App\ExportResources\Account\ActivityTypeCategory as ActivityTypeCategoryResource;
use App\ExportResources\Account\ReminderRule as ReminderRuleResource;
use App\ExportResources\Contact\ContactFieldType as ContactFieldTypeResource;
use App\ExportResources\Contact\Gender as GenderResource;
use App\ExportResources\Contact\Message as MessageResource;
use App\ExportResources\Contact\Note as NoteResource;
use App\ExportResources\Contact\Reminder as ReminderResource;
use App\ExportResources\Contact\Task as TaskResource;
use App\ExportResources\Contact\Debt as DebtResource;
use App\ExportResources\Instance\SpecialDate as SpecialDateResource;
use App\ExportResources\User\Module as ModuleResource;
use App\ExportResources\User\SyncToken as SyncTokenResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExportResourcesTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \Illuminate\Http\Request */
    private $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new Request();
    }

    /**
     * Resolve a resource to a plain array, properly handling mergeWhen/when/MissingValue.
     *
     * @param  \Illuminate\Http\Resources\Json\JsonResource  $resource
     * @return array
     */
    private function resolveResource($resource): array
    {
        return $resource->resolve($this->request);
    }

    // =========================================================================
    // ExportResource base class tests
    // =========================================================================

    /** @test */
    public function it_returns_empty_array_when_resource_is_null()
    {
        $resource = new ActivityTypeCategoryResource(null);
        $result = $this->resolveResource($resource);

        $this->assertEquals([], $result);
    }

    /** @test */
    public function it_returns_resource_array_when_resource_is_array()
    {
        $data = ['foo' => 'bar'];
        $resource = new ActivityTypeCategoryResource($data);
        $result = $this->resolveResource($resource);

        $this->assertEquals($data, $result);
    }

    /** @test */
    public function export_resource_returns_null_for_non_existent_model()
    {
        $freshModel = new Task();
        $freshModel->title = 'Test';
        // Not saved — exists() returns false
        $resource = new TaskResource($freshModel);
        $result = $resource->toArray($this->request);

        $this->assertNull($result);
    }

    // =========================================================================
    // Simple resource tests: Account subdirectory
    // =========================================================================

    /** @test */
    public function activity_type_category_resource_returns_expected_structure()
    {
        $model = factory(ActivityTypeCategory::class)->create([
            'translation_key' => 'test_key',
            'name' => 'Test Category',
        ]);
        $resource = new ActivityTypeCategoryResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertEquals($model->uuid, $result['uuid']);
        $this->assertArrayHasKey('translation_key', $result['properties']);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertEquals('test_key', $result['properties']['translation_key']);
        $this->assertEquals('Test Category', $result['properties']['name']);
    }

    /** @test */
    public function reminder_rule_resource_returns_expected_structure()
    {
        $model = factory(ReminderRule::class)->create([
            'number_of_days_before' => 7,
            'active' => true,
        ]);
        $resource = new ReminderRuleResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('number_of_days_before', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertEquals(7, $result['number_of_days_before']);
        $this->assertArrayHasKey('active', $result['properties']);
        $this->assertTrue($result['properties']['active']);
    }

    // =========================================================================
    // Simple resource tests: Contact subdirectory
    // =========================================================================

    /** @test */
    public function contact_field_type_resource_returns_expected_structure()
    {
        $model = factory(ContactFieldType::class)->create([
            'name' => 'Email',
            'protocol' => 'mailto:',
            'type' => 'email',
        ]);
        $resource = new ContactFieldTypeResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayHasKey('protocol', $result['properties']);
        $this->assertArrayHasKey('type', $result['properties']);
        $this->assertEquals('Email', $result['properties']['name']);
        $this->assertEquals('email', $result['properties']['type']);
    }

    /** @test */
    public function gender_resource_returns_expected_structure()
    {
        $model = factory(Gender::class)->create([
            'name' => 'Male',
            'type' => 'M',
        ]);
        $resource = new GenderResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayHasKey('type', $result['properties']);
        $this->assertEquals('Male', $result['properties']['name']);
        $this->assertEquals('M', $result['properties']['type']);
    }

    /** @test */
    public function gender_resource_omits_null_properties()
    {
        $model = factory(Gender::class)->create([
            'name' => 'Other',
            'type' => null,
        ]);
        $resource = new GenderResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayNotHasKey('type', $result['properties']);
    }

    /** @test */
    public function message_resource_returns_expected_structure()
    {
        $model = factory(Message::class)->create([
            'content' => 'Hello there',
            'written_at' => '2023-01-01',
            'written_by_me' => true,
        ]);
        $resource = new MessageResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('content', $result['properties']);
        $this->assertArrayHasKey('written_at', $result['properties']);
        $this->assertArrayHasKey('written_by_me', $result['properties']);
    }

    /** @test */
    public function note_resource_returns_expected_structure()
    {
        $model = factory(Note::class)->create([
            'is_favorited' => true,
            'favorited_at' => now(),
        ]);
        $resource = new NoteResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('body', $result['properties']);
        $this->assertArrayHasKey('favorited_at', $result['properties']);
    }

    /** @test */
    public function reminder_resource_returns_expected_structure()
    {
        $model = factory(Reminder::class)->create([
            'initial_date' => '2023-01-01',
            'title' => 'Test reminder',
            'description' => 'Test description',
            'frequency_type' => 'one_time',
            'frequency_number' => 1,
            'delible' => true,
            'inactive' => false,
        ]);
        $resource = new ReminderResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('initial_date', $result['properties']);
        $this->assertArrayHasKey('title', $result['properties']);
        $this->assertArrayHasKey('description', $result['properties']);
        $this->assertArrayHasKey('frequency_type', $result['properties']);
        $this->assertArrayHasKey('frequency_number', $result['properties']);
    }

    /** @test */
    public function task_resource_returns_expected_structure()
    {
        $model = factory(Task::class)->create([
            'title' => 'Buy groceries',
            'description' => 'From the store',
            'completed' => true,
            'completed_at' => now(),
        ]);
        $resource = new TaskResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('title', $result['properties']);
        $this->assertArrayHasKey('description', $result['properties']);
        $this->assertArrayHasKey('completed', $result['properties']);
        $this->assertArrayHasKey('completed_at', $result['properties']);
        $this->assertEquals('Buy groceries', $result['properties']['title']);
    }

    /** @test */
    public function task_resource_omits_null_properties()
    {
        $model = factory(Task::class)->create([
            'title' => 'Test',
            'description' => 'Desc',
            'completed' => false,
            'completed_at' => null,
        ]);
        $resource = new TaskResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('title', $result['properties']);
        // completed_at is null so it gets filtered out by MissingValue
        $this->assertArrayNotHasKey('completed_at', $result['properties']);
    }

    /** @test */
    public function debt_resource_returns_in_debt_true()
    {
        $model = factory(Debt::class)->create([
            'in_debt' => 'yes',
            'amount' => 100,
            'status' => 'inprogress',
        ]);
        $resource = new DebtResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('in_debt', $result['properties']);
        $this->assertTrue($result['properties']['in_debt']);
        $this->assertArrayHasKey('amount', $result['properties']);
        $this->assertArrayHasKey('status', $result['properties']);
    }

    /** @test */
    public function debt_resource_returns_in_debt_false()
    {
        $model = factory(Debt::class)->create([
            'in_debt' => 'no',
            'amount' => 50,
            'status' => 'inprogress',
        ]);
        $resource = new DebtResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('in_debt', $result['properties']);
        $this->assertFalse($result['properties']['in_debt']);
    }

    // =========================================================================
    // Simple resource tests: Instance subdirectory
    // =========================================================================

    /** @test */
    public function special_date_resource_returns_expected_structure()
    {
        $model = factory(SpecialDate::class)->create();
        $resource = new SpecialDateResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('is_age_based', $result);
        $this->assertArrayHasKey('is_year_unknown', $result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        // SpecialDate has $properties = null, so no 'properties' key
        $this->assertArrayNotHasKey('properties', $result);
    }

    // =========================================================================
    // Simple resource tests: User subdirectory
    // =========================================================================

    /** @test */
    public function module_resource_returns_expected_structure()
    {
        $model = factory(Module::class)->create([
            'key' => 'notes',
            'translation_key' => 'notes_key',
            'active' => true,
            'delible' => false,
        ]);
        $resource = new ModuleResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('key', $result);
        $this->assertArrayHasKey('translation_key', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('active', $result['properties']);
        $this->assertArrayHasKey('delible', $result['properties']);
        $this->assertEquals('notes', $result['key']);
        $this->assertTrue($result['properties']['active']);
    }

    /** @test */
    public function sync_token_resource_returns_expected_structure()
    {
        $model = factory(SyncToken::class)->create([
            'name' => 'test-token',
        ]);
        $resource = new SyncTokenResource($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayHasKey('timestamp', $result['properties']);
        $this->assertEquals('test-token', $result['properties']['name']);
    }

    // =========================================================================
    // CountResourceCollection tests
    // =========================================================================

    /** @test */
    public function count_collection_returns_missing_value_for_empty_collection()
    {
        $account = factory(Account::class)->create();
        $emptyCollection = $account->activities;

        $result = ActivityTypeCategoryResource::countCollection($emptyCollection);

        $this->assertInstanceOf(MissingValue::class, $result);
    }

    /** @test */
    public function count_collection_returns_count_type_and_values()
    {
        $account = factory(Account::class)->create();
        $contact = factory(Contact::class)->create(['account_id' => $account->id]);

        factory(Task::class)->create([
            'account_id' => $account->id,
            'contact_id' => $contact->id,
            'title' => 'Task 1',
            'description' => 'Desc 1',
        ]);
        factory(Task::class)->create([
            'account_id' => $account->id,
            'contact_id' => $contact->id,
            'title' => 'Task 2',
            'description' => 'Desc 2',
        ]);

        $tasks = Task::where('account_id', $account->id)->get();
        $collection = TaskResource::countCollection($tasks);

        $this->assertInstanceOf(CountResourceCollection::class, $collection);

        $result = $collection->resolve($this->request);

        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('values', $result);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals('task', $result['type']);
        $this->assertCount(2, $result['values']);
    }

    /** @test */
    public function count_collection_type_is_derived_from_resource_class_name()
    {
        $account = factory(Account::class)->create();
        factory(ContactFieldType::class)->create([
            'account_id' => $account->id,
            'name' => 'Phone',
            'type' => 'phone',
        ]);

        $items = ContactFieldType::where('account_id', $account->id)->get();
        $collection = ContactFieldTypeResource::countCollection($items);

        $this->assertInstanceOf(CountResourceCollection::class, $collection);

        $result = $collection->resolve($this->request);

        $this->assertEquals('contact_field_type', $result['type']);
        $this->assertEquals(1, $result['count']);
    }

    // =========================================================================
    // MapUuidResourceCollection tests
    // =========================================================================

    /** @test */
    public function uuid_collection_returns_missing_value_for_empty_collection()
    {
        $account = factory(Account::class)->create();
        $emptyCollection = $account->activities;

        $result = TaskResource::uuidCollection($emptyCollection);

        $this->assertInstanceOf(MissingValue::class, $result);
    }

    /** @test */
    public function uuid_collection_returns_count_type_and_uuid_values()
    {
        $account = factory(Account::class)->create();
        $contact = factory(Contact::class)->create(['account_id' => $account->id]);

        $task1 = factory(Task::class)->create([
            'account_id' => $account->id,
            'contact_id' => $contact->id,
            'title' => 'Task A',
            'description' => 'Desc A',
        ]);
        $task2 = factory(Task::class)->create([
            'account_id' => $account->id,
            'contact_id' => $contact->id,
            'title' => 'Task B',
            'description' => 'Desc B',
        ]);

        $tasks = Task::where('account_id', $account->id)->get();
        $collection = TaskResource::uuidCollection($tasks);

        $this->assertInstanceOf(MapUuidResourceCollection::class, $collection);

        $result = $collection->resolve($this->request);

        $this->assertArrayHasKey('count', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('values', $result);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals('task', $result['type']);

        // values should be an array of UUID strings
        $this->assertCount(2, $result['values']);
        $this->assertContains((string) $task1->uuid, $result['values']);
        $this->assertContains((string) $task2->uuid, $result['values']);
    }

    // =========================================================================
    // Resources with data() method tests
    // =========================================================================

    /** @test */
    public function audit_log_resource_with_author_returns_expected_structure()
    {
        $model = factory(AuditLog::class)->create([
            'author_name' => 'John Doe',
            'action' => 'account_created',
            'objects' => '{"user": 1}',
            'should_appear_on_dashboard' => true,
        ]);
        $resource = new \App\ExportResources\Instance\AuditLog($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('author_name', $result['properties']);
        $this->assertArrayHasKey('action', $result['properties']);
        $this->assertArrayHasKey('objects', $result['properties']);
        $this->assertArrayHasKey('should_appear_on_dashboard', $result['properties']);
        // author is set (via factory), so 'author' should be present
        $this->assertArrayHasKey('author', $result['properties']);
    }

    /** @test */
    public function audit_log_resource_without_author_omits_author_key()
    {
        $model = factory(AuditLog::class)->create([
            'author_id' => null,
            'about_contact_id' => null,
        ]);
        $resource = new \App\ExportResources\Instance\AuditLog($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('properties', $result);
        // When author is null, 'when' returns a falsy result → excluded via resolve()
        $this->assertArrayNotHasKey('author', $result['properties']);
        $this->assertArrayNotHasKey('contact', $result['properties']);
    }

    /** @test */
    public function life_event_resource_returns_expected_structure()
    {
        $model = factory(LifeEvent::class)->create();
        $resource = new \App\ExportResources\Contact\LifeEvent($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayHasKey('note', $result['properties']);
        $this->assertArrayHasKey('happened_at', $result['properties']);
        // LifeEvent has lifeEventType set via factory
        $this->assertArrayHasKey('type', $result['properties']);
    }

    /** @test */
    public function call_resource_returns_expected_structure()
    {
        $model = factory(Call::class)->create([
            'called_at' => now(),
            'content' => 'Test call content',
            'contact_called' => 1,
        ]);
        $resource = new \App\ExportResources\Contact\Call($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('called_at', $result['properties']);
        $this->assertArrayHasKey('content', $result['properties']);
        $this->assertArrayHasKey('contact_called', $result['properties']);
    }

    /** @test */
    public function contact_field_resource_returns_expected_structure()
    {
        $model = factory(ContactField::class)->create([
            'data' => 'john@doe.com',
        ]);
        $resource = new \App\ExportResources\Contact\ContactField($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('data', $result['properties']);
        // contactFieldType is set via factory
        $this->assertArrayHasKey('type', $result['properties']);
    }

    /** @test */
    public function gift_resource_returns_expected_structure()
    {
        $model = factory(Gift::class)->create([
            'name' => 'Watch',
            'comment' => 'Nice watch',
            'url' => 'https://example.com',
            'amount' => 200,
            'status' => 'offered',
            'date' => now(),
        ]);
        $resource = new \App\ExportResources\Contact\Gift($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayHasKey('comment', $result['properties']);
        $this->assertArrayHasKey('url', $result['properties']);
        $this->assertArrayHasKey('amount', $result['properties']);
        $this->assertArrayHasKey('status', $result['properties']);
        $this->assertArrayHasKey('date', $result['properties']);
    }

    /** @test */
    public function pet_resource_returns_expected_structure()
    {
        $model = factory(Pet::class)->create([
            'name' => 'Buddy',
        ]);
        $resource = new \App\ExportResources\Contact\Pet($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result['properties']);
        // petCategory is set via factory
        $this->assertArrayHasKey('category', $result['properties']);
    }

    /** @test */
    public function activity_type_resource_returns_expected_structure()
    {
        $model = factory(ActivityType::class)->create([
            'translation_key' => 'just_hung_out',
            'location_type' => 'outside',
        ]);
        $resource = new \App\ExportResources\Account\ActivityType($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('translation_key', $result['properties']);
        $this->assertArrayHasKey('location_type', $result['properties']);
        // category is set via factory
        $this->assertArrayHasKey('category', $result['properties']);
    }

    /** @test */
    public function activity_resource_returns_expected_structure()
    {
        $model = factory(Activity::class)->create([
            'summary' => 'Went hiking',
            'description' => 'At the park',
        ]);
        $resource = new \App\ExportResources\Account\Activity($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('summary', $result['properties']);
        $this->assertArrayHasKey('description', $result['properties']);
        $this->assertArrayHasKey('happened_at', $result['properties']);
        // activity_type_id is set via factory, so 'type' should be present
        $this->assertArrayHasKey('type', $result['properties']);
    }

    /** @test */
    public function photo_resource_returns_expected_structure()
    {
        $model = factory(Photo::class)->create([
            'original_filename' => 'photo.jpg',
            'filesize' => 1024,
            'mime_type' => 'image/jpeg',
        ]);
        $resource = new \App\ExportResources\Account\Photo($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('original_filename', $result['properties']);
        $this->assertArrayHasKey('filesize', $result['properties']);
        $this->assertArrayHasKey('mime_type', $result['properties']);
        $this->assertArrayHasKey('dataUrl', $result['properties']);
    }

    /** @test */
    public function life_event_category_resource_returns_expected_structure()
    {
        $model = factory(LifeEventCategory::class)->create([
            'core_monica_data' => true,
        ]);
        $resource = new \App\ExportResources\Account\LifeEventCategory($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('core_monica_data', $result['properties']);
        $this->assertArrayHasKey('translation_key', $result['properties']);
    }

    /** @test */
    public function life_event_type_resource_returns_expected_structure()
    {
        $model = factory(LifeEventType::class)->create([
            'name' => 'Got married',
            'core_monica_data' => true,
        ]);
        $resource = new \App\ExportResources\Account\LifeEventType($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('name', $result['properties']);
        $this->assertArrayHasKey('core_monica_data', $result['properties']);
        $this->assertArrayHasKey('translation_key', $result['properties']);
        // lifeEventCategory is set via factory
        $this->assertArrayHasKey('category', $result['properties']);
    }

    /** @test */
    public function relationship_resource_returns_expected_structure()
    {
        $model = factory(Relationship::class)->create();
        $resource = new \App\ExportResources\Relationship\Relationship($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('type', $result['properties']);
        $this->assertArrayHasKey('contact_is', $result['properties']);
        $this->assertArrayHasKey('of_contact', $result['properties']);
    }

    /** @test */
    public function conversation_resource_returns_expected_structure()
    {
        $model = factory(Conversation::class)->create([
            'happened_at' => now(),
        ]);
        $resource = new \App\ExportResources\Contact\Conversation($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('happened_at', $result['properties']);
        // contactFieldType is set via factory
        $this->assertArrayHasKey('contact_field_type', $result['properties']);
        $this->assertArrayHasKey('messages', $result['properties']);
    }

    /** @test */
    public function document_resource_returns_expected_structure()
    {
        $model = factory(Document::class)->create([
            'original_filename' => 'doc.pdf',
            'filesize' => 2048,
            'type' => 'document',
            'mime_type' => 'application/pdf',
            'number_of_downloads' => 3,
        ]);
        $resource = new \App\ExportResources\Contact\Document($model);
        $result = $this->resolveResource($resource);

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('properties', $result);
        $this->assertArrayHasKey('original_filename', $result['properties']);
        $this->assertArrayHasKey('filesize', $result['properties']);
        $this->assertArrayHasKey('type', $result['properties']);
        $this->assertArrayHasKey('mime_type', $result['properties']);
        $this->assertArrayHasKey('number_of_downloads', $result['properties']);
    }
}
