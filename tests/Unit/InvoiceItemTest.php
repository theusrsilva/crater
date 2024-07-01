<?php

use Crater\Models\Invoice;
use Crater\Models\InvoiceItem;
use Crater\Models\Item;
use Illuminate\Support\Facades\Artisan;

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
    $company_id = 1; // Replace with a valid company ID from your seed data
    $invoiceItem = InvoiceItem::factory()->create(['company_id' => $company_id]);

    $filteredItems = InvoiceItem::whereCompany($company_id)->get();

    $this->assertTrue($filteredItems->contains($invoiceItem));
});


test('invoice item scopeApplyInvoiceFilters applies date filters', function () {
    $filters = [
        'from_date' => '2023-01-01',
        'to_date' => '2023-12-31',
    ];

    $filteredItems = InvoiceItem::applyInvoiceFilters($filters)->get();

    // Replace with your actual expectation based on your data and filter criteria
    $expectedCount = InvoiceItem::whereHas('invoice', function ($query) use ($filters) {
        $query->whereBetween('invoice_date', [$filters['from_date'], $filters['to_date']]);
    })->count();

    $this->assertCount($expectedCount, $filteredItems);
});


test('invoice item belongs to recurring invoice', function () {
    $invoiceItem = InvoiceItem::factory()->forRecurringInvoice()->create();

    $this->assertTrue($invoiceItem->recurringInvoice()->exists());
});
