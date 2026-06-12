<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\Account\Place;
use App\Jobs\GetGPSCoordinate;

class GetGPSCoordinateTest extends TestCase
{
    public function test_constructor()
    {
        $place = \Mockery::mock(Place::class)->makePartial();
        $place->shouldReceive('withoutRelations')->once()->andReturnSelf();
        
        $job = new GetGPSCoordinate($place);
        $this->assertInstanceOf(GetGPSCoordinate::class, $job);
    }
}
