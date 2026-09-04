<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminUiTest extends TestCase
{
    public function test_admin_shell_is_served(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('id="app"', false)
            ->assertSee('Admin', false);
    }

    public function test_admin_login_path_is_served_by_the_spa(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('id="app"', false);
    }
}
