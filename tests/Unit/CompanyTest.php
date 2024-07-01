
<?php

use Crater\Models\Company;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
});

test('company has many customers', function () {
    $company = Company::factory()->hasCustomers()->create();

    $this->assertTrue($company->customers()->exists());
});

test('company has many company settings', function () {
    $company = Company::factory()->hasSettings(5)->create();

    $this->assertCount(5, $company->settings);

    $this->assertTrue($company->settings()->exists());
});



test('create company', function () {
    $company = Company::factory()->create();

    $this->assertNotNull($company);
});


test('update company', function () {
    $company = Company::factory()->create();
    $newName = 'Updated Company Name';

    $company->update(['name' => $newName]);

    $this->assertEquals($newName, $company->fresh()->name);
});


test('delete company', function () {
    $company = Company::factory()->create();

    $company->delete();

    $this->assertDeleted($company);
});




test('company name is required', function () {
    $this->expectException(\Illuminate\Database\QueryException::class);

    Company::create([]);
});


// Teste de métodos personalizados
test('company custom method example', function () {
    $company = Company::factory()->create();

    // Supondo que a classe Company tenha um método customizado `exampleMethod`
    $result = $company->logo;
    $this->assertNull($result);
});

