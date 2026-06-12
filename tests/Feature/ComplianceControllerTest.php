<?php

namespace Tests\Feature;

use Tests\FeatureTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ComplianceControllerTest extends FeatureTestCase
{
    use DatabaseTransactions;

    public function test_it_displays_the_compliance_page()
    {
        $user = $this->signIn();

        $response = $this->get('/compliance');

        $response->assertStatus(200);
        $response->assertViewIs('compliance.index');
    }

    public function test_it_stores_the_compliance()
    {
        $user = $this->signIn();

        $response = $this->post('/compliance/sign');

        $response->assertStatus(302);
        $response->assertRedirect('/dashboard');
    }
}
