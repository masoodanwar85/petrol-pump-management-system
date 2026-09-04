<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_url_redirects_to_the_admin_ui(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }
}
