<?php

use Crater\Models\CustomFieldValue;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
});

test('custom field value belongs to company', function () {
    $fieldValue = CustomFieldValue::factory()->create();

    $this->assertTrue($fieldValue->company()->exists());
});

test('custom field value belongs to custom field', function () {
    $fieldValue = CustomFieldValue::factory()->forCustomField()->create();

    $this->assertTrue($fieldValue->customField()->exists());
});


test('set time answer attribute', function () {
    $fieldValue = CustomFieldValue::factory()->create();

    $fieldValue->time_answer = '15:30:00';
    $this->assertEquals('15:30:00', $fieldValue->time_answer);

    $fieldValue->time_answer = null;
    $this->assertNull($fieldValue->time_answer);
});


test('get default answer attribute', function () {
    $fieldValue = CustomFieldValue::factory()->create([
        'type' => 'Input',
        "string_answer" => "teste"//
    ]);
    $this->assertEquals("teste", $fieldValue->defaultAnswer);
});


test('custom field value morphs to valuable', function () {
    // Crie um modelo válido, por exemplo, Invoice
    $invoice = \Crater\Models\Invoice::factory()->create();

    // Crie um CustomFieldValue com os campos necessários
    $fieldValue = \Crater\Models\CustomFieldValue::factory()->create([
        'custom_field_valuable_type' => \Crater\Models\Invoice::class,
        'custom_field_valuable_id' => $invoice->id,
    ]);

    // Verifique se o relacionamento polimórfico está funcionando corretamente
    $this->assertInstanceOf(\Crater\Models\Invoice::class, $fieldValue->customFieldValuable);
});



test('custom field value has guarded and dates properties', function () {
    $fieldValue = new CustomFieldValue();

    $this->assertContains('id', $fieldValue->getGuarded());
    $this->assertContains('date_answer', $fieldValue->getDates());
    $this->assertContains('date_time_answer', $fieldValue->getDates());
});

test('can set and get time answer attribute', function () {

    $customFieldValue = Mockery::mock(CustomFieldValue::class)->makePartial();

    $customFieldValue->setTimeAnswerAttribute('15:30:00');

    expect($customFieldValue->time_answer)->toBe('15:30:00');
});

test('can mock relationships and methods of CustomFieldValue', function () {

    $customFieldValue = Mockery::mock(CustomFieldValue::class)->makePartial();


    $companyMock = Mockery::mock(Company::class);
    $customFieldValue->shouldReceive('company')->andReturn($companyMock);


    $customFieldMock = Mockery::mock(CustomField::class);
    $customFieldValue->shouldReceive('customField')->andReturn($customFieldMock);


    expect($customFieldValue->company())->toBe($companyMock);
    expect($customFieldValue->customField())->toBe($customFieldMock);
});