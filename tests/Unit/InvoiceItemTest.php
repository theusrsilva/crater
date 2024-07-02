<?php

use Crater\Models\Invoice;
use Crater\Models\InvoiceItem;
use Crater\Models\Item;
use Illuminate\Support\Facades\Artisan;
$invoiceItem = null;
beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
});

test('invoice item belongs to invoice', function () {
    $invoiceItem = InvoiceItem::factory()->forInvoice()->create();

    $this->assertTrue($invoiceItem->invoice()->exists());
});

test('invoice item belongs to item', function () {
    $invoiceItem = InvoiceItem::factory()->create([
        'item_id' => Item::factory(),
        'invoice_id' => Invoice::factory(),
    ]);

    $this->assertTrue($invoiceItem->item()->exists());
});


test('invoice item has many taxes', function () {
    $invoiceItem = InvoiceItem::factory()->hasTaxes(5)->create([
        'invoice_id' => Invoice::factory(),
    ]);

    $this->assertCount(5, $invoiceItem->taxes);

    $this->assertTrue($invoiceItem->taxes()->exists());
});

test('invoice item scopeWhereCompany filters correctly', function () {
    $company_id = 1;
    $invoiceItem = InvoiceItem::factory()->create(['company_id' => $company_id]);

    $filteredItems = InvoiceItem::whereCompany($company_id)->get();

    $this->assertTrue($filteredItems->contains($invoiceItem));
});


test('invoice item scopeApplyInvoiceFilters applies date filters', function () {
    $filters = [
        'from_date' => '2024-06-30',
        'to_date' => '2024-07-01',
    ];

    $filteredItems = InvoiceItem::applyInvoiceFilters($filters)->get();

    $expectedCount = InvoiceItem::whereHas('invoice', function ($query) use ($filters) {
        $query->whereBetween('invoice_date', [$filters['from_date'], $filters['to_date']]);
    })->count();

    $this->assertCount($expectedCount, $filteredItems);
});


test('invoice item belongs to recurring invoice', function () {
    $invoiceItem = InvoiceItem::factory()->forRecurringInvoice()->create();

    $this->assertTrue($invoiceItem->recurringInvoice()->exists());
});

test('can mock relationships and methods of InvoiceItem', function () {

    $invoiceItem = Mockery::mock(InvoiceItem::class)->makePartial();

    $invoiceMock = Mockery::mock(Invoice::class);
    $invoiceItem->shouldReceive('invoice')->andReturn($invoiceMock);

    $itemMock = Mockery::mock(Item::class);
    $invoiceItem->shouldReceive('item')->andReturn($itemMock);

    $taxesMock = Mockery::mock(\Crater\Models\Tax::class);
    $invoiceItem->shouldReceive('taxes')->andReturn($taxesMock);


    $recurringInvoiceMock = Mockery::mock(\Crater\Models\RecurringInvoice::class);
    $invoiceItem->shouldReceive('recurringInvoice')->andReturn($recurringInvoiceMock);

    expect($invoiceItem->invoice())->toBe($invoiceMock);
    expect($invoiceItem->item())->toBe($itemMock);
    expect($invoiceItem->taxes())->toBe($taxesMock);
    expect($invoiceItem->recurringInvoice())->toBe($recurringInvoiceMock);
});

test('Invoice Item mock time', function () {
    $filters = [
        'from_date' => now()->subDays(10)->format('Y-m-d'),
        'to_date' => now()->subDays(9)->format('Y-m-d'),
    ];

    $this->travelTo(now()->subDays(10), function () use ($filters) {

        $filteredItems = InvoiceItem::applyInvoiceFilters($filters)->get();

        $expectedCount = InvoiceItem::whereHas('invoice', function ($query) {
            $query->whereBetween('invoice_date', [now(), now()->addDay()]);
        })->count();

        expect($filteredItems)->toHaveCount($expectedCount);
    });
});







