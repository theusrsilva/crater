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

    assertInstanceOf(Transaction::class, $transaction);
    assertNotNull($transaction->unique_hash);
    assertEquals(Transaction::PENDING, $transaction->status);
});

test('can complete a transaction', function () {
    // Cria uma transação manualmente
    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    // Chama o método para completar a transação
    $transaction->completeTransaction();

    // Assertiva para verificar se o status da transação foi atualizado para SUCCESS
    assertEquals(Transaction::SUCCESS, $transaction->status);
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

    assertEquals(Transaction::FAILED, $transaction->status);
});

test('transaction has many payments', function () {
    // Cria uma transação manualmente
    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    // Cria três pagamentos associados à transação
    Payment::factory()->count(3)->create(['transaction_id' => $transaction->id]);

    // Assertiva para verificar se a transação possui três pagamentos
    assertCount(3, $transaction->payments);
});

test('transaction belongs to invoice', function () {
    // Cria uma fatura usando o factory
    $invoice = Invoice::factory()->create();

    // Cria uma transação associada à fatura criada
    $transaction = new Transaction([
        'invoice_id' => $invoice->id,
        'company_id' => Company::factory()->create()->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    // Assertiva para verificar se a transação pertence a uma fatura
    assertInstanceOf(Invoice::class, $transaction->invoice);
});

test('transaction belongs to company', function () {
    // Cria uma empresa usando o factory
    $company = Company::factory()->create();

    // Cria uma transação associada à empresa criada
    $transaction = new Transaction([
        'invoice_id' => Invoice::factory()->create()->id,
        'company_id' => $company->id,
        'amount' => 100.00,
        'transaction_date' => now(),
        'status' => Transaction::PENDING,
    ]);

    $transaction->save();

    // Assertiva para verificar se a transação pertence a uma empresa
    assertInstanceOf(Company::class, $transaction->company);
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

    assertTrue($transaction->isExpired());
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

    assertFalse($transaction->isExpired());
});

test('can get company setting', function () {
    $company = Company::factory()->create();
    CompanySetting::setSettings(['link_expiry_days' => 7], $company->id);

    $expiryDays = (int) CompanySetting::getSetting('link_expiry_days', $company->id);

    assertEquals(7, $expiryDays);
});

test('returns null for non-existent setting', function () {
    $company = Company::factory()->create();

    $setting = CompanySetting::getSetting('non_existent_setting', $company->id);

    assertNull($setting);
});

test('throws an exception when creating a transaction with missing data', function () {
    $this->expectException(Exception::class);

    $data = [
        // missing required fields
    ];

    Transaction::createTransaction($data);
});
