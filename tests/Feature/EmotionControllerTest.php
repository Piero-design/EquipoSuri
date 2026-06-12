<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use App\Models\Instance\Emotion\Emotion;
use App\Models\Instance\Emotion\PrimaryEmotion;
use App\Models\Instance\Emotion\SecondaryEmotion;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EmotionControllerTest extends FeatureTestCase
{
    use DatabaseTransactions;

    public function test_it_gets_primary_emotions()
    {
        $user = $this->signIn();

        $primary = factory(PrimaryEmotion::class)->create();

        $response = $this->get('/emotions');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $primary->id,
        ]);
    }

    public function test_it_gets_secondary_emotions()
    {
        $user = $this->signIn();

        $primary = factory(PrimaryEmotion::class)->create();
        $secondary = factory(SecondaryEmotion::class)->create([
            'emotion_primary_id' => $primary->id,
        ]);

        $response = $this->get('/emotions/primaries/' . $primary->id . '/secondaries');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $secondary->id,
        ]);
    }

    public function test_it_gets_emotions()
    {
        $user = $this->signIn();

        $primary = factory(PrimaryEmotion::class)->create();
        $secondary = factory(SecondaryEmotion::class)->create([
            'emotion_primary_id' => $primary->id,
        ]);
        $emotion = factory(Emotion::class)->create([
            'emotion_secondary_id' => $secondary->id,
        ]);

        $response = $this->get('/emotions/primaries/' . $primary->id . '/secondaries/' . $secondary->id . '/emotions');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $emotion->id,
        ]);
    }
}
