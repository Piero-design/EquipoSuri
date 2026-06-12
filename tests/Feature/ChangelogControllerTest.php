<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ChangelogControllerTest extends FeatureTestCase
{
    use DatabaseTransactions;

    public function test_it_displays_the_changelog_page()
    {
        $user = $this->signIn();

        $response = $this->get('/changelog');

        $response->assertStatus(200);
        $response->assertViewIs('changelog.index');
        $response->assertViewHas('changelogs');
    }
}
