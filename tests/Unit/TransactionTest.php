<?php

use Crater\Models\Transaction;
use Crater\Models\Payment;
use Crater\Models\Invoice;
use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Illuminate\Support\Facades\Artisan;


beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);
});



test('can create a transaction', function () {
    $data = [
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ];

    $transaction = Transaction::createTransaction($data);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->unique_hash)->not->toBeNull();
    expect($transaction->status)->toBe(Transaction::PENDING);
});

test('can complete a transaction', function () {
    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    $transaction->completeTransaction();

    expect($transaction->status)->toBe(Transaction::SUCCESS);
});

test('can fail a transaction', function () {
    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    $transaction->failedTransaction();

    expect($transaction->status)->toBe(Transaction::FAILED);
});

test('transaction has many payments', function () {
    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    Payment::factory()->count(3)->create(['transaction_id' => $transaction->id]);

    expect($transaction->payments)->toHaveCount(3);
});

test('transaction belongs to invoice', function () {
    $invoice = Invoice::factory()->create();

    $transaction = new Transaction([
        'invoice_id' => $invoice->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    expect($transaction->invoice)->toBeInstanceOf(Invoice::class);
});

test('transaction belongs to company', function () {
    $company = Company::factory()->create();

    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => $company->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    expect($transaction->company)->toBeInstanceOf(Company::class);
});

test('checks if a transaction is expired', function () {
    $company = Company::factory()->create();
    CompanySetting::setSettings([
        'link_expiry_days' => 7,
        'automatically_expire_public_links' => 'YES'
    ], $company->id);

    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => $company->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::SUCCESS,
        'updated_at' => now()->subDays(8)
    ]);

    $transaction->save();

    expect($transaction->isExpired())->toBeTrue();
});

test('transaction is not expired when settings are disabled', function () {
    $company = Company::factory()->create();
    CompanySetting::setSettings([
        'link_expiry_days' => 7,
        'automatically_expire_public_links' => 'NO'
    ], $company->id);

    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => $company->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::SUCCESS,
        'updated_at' => now()->subDays(8)
    ]);

    $transaction->save();

    expect($transaction->isExpired())->toBeFalse();
});

test('can get company setting', function () {
    $company = Company::factory()->create();
    CompanySetting::setSettings(['link_expiry_days' => 7], $company->id);

    $expiryDays = (int) CompanySetting::getSetting('link_expiry_days', $company->id);

    expect($expiryDays)->toBe(7);
});

test('returns null for non-existent setting', function () {
    $company = Company::factory()->create();

    $setting = CompanySetting::getSetting('non_existent_setting', $company->id);

    expect($setting)->toBeNull();
});

test('throws an exception when creating a transaction with missing data', function () {
    $this->expectException(Exception::class);

    $data = [
        // missing required fields
    ];

    Transaction::createTransaction($data);
});

// Teste de Integração
test('integration: can create transaction with invoice and company', function () {
    $invoice = Invoice::factory()->create();
    $company = Company::factory()->create();

    $data = [
        'invoice_id' => $invoice->id,
        'company_id' => $company->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ];

    $transaction = Transaction::createTransaction($data);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->invoice_id)->toBe($invoice->id);
    expect($transaction->company_id)->toBe($company->id);
});

// Medidas de Atributos de Qualidade da ISO 25010
// Funcionalidade
test('functionality: create transaction with valid data', function () {
    $data = [
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ];

    $transaction = Transaction::createTransaction($data);

    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->unique_hash)->not->toBeNull();
    expect($transaction->status)->toBe(Transaction::PENDING);
});

// Usabilidade
test('usability: methods clarity and simplicity', function () {
    $methods = ['createTransaction', 'completeTransaction', 'failedTransaction', 'isExpired'];

    foreach ($methods as $method) {
        expect(method_exists(Transaction::class, $method))->toBeTrue();
    }
});

// Confiabilidade
test('reliability: transaction integrity under different conditions', function () {
    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    $transaction->completeTransaction();
    expect($transaction->status)->toBe(Transaction::SUCCESS);

    $transaction->failedTransaction();
    expect($transaction->status)->toBe(Transaction::FAILED);
});
