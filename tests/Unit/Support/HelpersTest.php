<?php

namespace Tests\Unit\Support;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_can_get_array_value_or_default()
    {
        $user = [
            'name' => 'John Doe',
            'email_verified_at' => null,
        ];

        $this->assertEquals('John Doe', array_get($user, 'name'));
        $this->assertNull(array_get($user, 'email_verified_at'));
        $this->assertNull(array_get($user, 'created_at'));
        $this->assertFalse(array_get($user, 'is_enabled', false));
    }
}
