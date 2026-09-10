<?php

namespace Tests\Feature;

use App\Enums\Community;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityEnumTest extends TestCase
{
    public function test_values(): void
    {
        $this->assertSame(['fad', 'genre'], array_column(Community::cases(), 'value'));
        $this->assertNull(Community::tryFrom('salah'));
        $this->assertSame('FAD Tanah Laut', Community::FAD->config()['title']);
        $this->assertSame('GENRE Tanah Laut', Community::GENRE->config()['title']);
    }
}
