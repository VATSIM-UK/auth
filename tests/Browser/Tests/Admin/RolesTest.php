<?php

namespace Tests\Browser\Tests\Admin\Roles;

use App\Models\Role;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\RolePage;
use Tests\Browser\Pages\RolesPage;
use Tests\DuskTestCase;

class RolesTest extends DuskTestCase
{
    public function testItShowsRoles()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superUser)
                ->visit(new RolesPage)
                ->waitFor('@table')
                ->assertVisible('@table');

            factory(Role::class, 3)->create();

            $this->assertCount(0, $browser->driver->findElements(WebDriverBy::cssSelector('table tbody tr')));

            $browser
                ->loginAs($this->superUser)
                ->visit(new RolesPage)
                ->waitFor('@table')
                ->assertVisible('@table');
            $this->assertCount(3, $browser->driver->findElements(WebDriverBy::cssSelector('table tbody tr')));
        });
    }

    public function testItCanNavigateToARole()
    {
        $role = factory(Role::class)->create();
        $this->browse(function (Browser $browser) use ($role) {
            $browser->loginAs($this->superUser)
                ->visit(new RolesPage)
                ->waitFor('@table')
                ->with('table tbody tr:first-of-type', function (Browser $table) use ($role) {
                    $table->assertVisible('.btn')
                        ->press('.btn')
                        ->assertPathIs((new RolePage($role->id))->url());
                });
        });
    }
}
