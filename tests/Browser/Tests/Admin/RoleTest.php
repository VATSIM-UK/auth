<?php

namespace Tests\Browser\Tests\Admin\Roles;

use App\Models\Role;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\RolePage;
use Tests\Browser\Pages\RolesPage;
use Tests\DuskTestCase;

class RoleTest extends DuskTestCase
{
    private $role;

    const EDIT_PERMISSIONS_BUTTON_TEXT = 'Edit Permissions';

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = factory(Role::class)->create([
            'name' => 'My Role',
            'require_password' => true,
        ]);
        $this->role->givePermissionTo('auth.roles.create');
    }

    public function testItCanBeCreated()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superUser)
                ->visit(new RolePage('new'))
                ->waitUntilMissing('@loadingCover');

            $this->assertEquals('true', $browser->attribute('.navbar .btn-warning', 'disabled'));

            $browser
                ->with('@role-name-input', function (Browser $input) {
                    $input->type('input', 'Second Role');
                })
                ->press('Create')
                ->waitForText('Role Created!')
                ->waitForLocation((new RolesPage())->url());

            $this->assertDatabaseHas('roles', [
                'name' => 'Second Role',
            ]);
        });
    }

    public function testItCanBeModified()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superUser)
                ->visit(new RolePage($this->role->id))
                ->waitForText($this->role->name)
                ->click('.btn-group label:last-of-type') //No Password Required Button
                ->type('password_refresh_rate', '10')
                ->click('@role-name-input')
                ->type('.editable-text input', 'My Updated Role')
                ->click('.editable-text button:first-of-type')
                ->press(self::EDIT_PERMISSIONS_BUTTON_TEXT)
                ->assertChecked('permission:auth.roles.create')
                ->check('permission:auth.roles.delete')
                ->press('Done')
                ->press('Update')
                ->waitForText('Role Updated!')
                ->waitForText('My Updated Role')
                ->assertRadioSelected('require_password', 'false')
                ->assertInputValue('password_refresh_rate', '')
                ->press(self::EDIT_PERMISSIONS_BUTTON_TEXT)
                ->assertChecked('permission:auth.roles.delete');
        });
    }

    public function testItShowsRole()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superUser)
                ->visit(new RolePage($this->role->id))
                ->waitForText($this->role->name)
                ->assertRadioSelected('require_password', 'true')
                ->press(self::EDIT_PERMISSIONS_BUTTON_TEXT)
                ->assertChecked('permission:auth.roles.create')
                ->assertNotChecked('permission:auth.roles')
                ->assertNotChecked('permission:auth.roles.delete');
        });
    }

    public function testItCanBeDeleted()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->superUser)
                ->visit(new RolePage($this->role->id))
                ->waitForText($this->role->name)
                ->press('@toolbarDangerButton')
                ->press('@toolbarDangerButton') // Cancel Deletion
                ->assertVisible('@toolbarDangerButton')
                ->assertSee('Delete')
                ->press('@toolbarDangerButton')
                ->press('.navbar .btn-success')
                ->waitForText('Role Deleted!')
                ->waitForLocation((new RolesPage)->url());

            $this->assertDatabaseMissing('roles', [
                'id' => $this->role->id,
            ]);
        });
    }
}
