<?php

namespace Tests\Unit;

use Tests\TestCase;
use Crater\Mail\TestMail;
use Illuminate\Support\Facades\Mail;

class TestMailTest extends TestCase
{
    public function testBuild()
    {
        // Dados de exemplo para construir o e-mail
        $subject = 'Test Subject';
        $message = 'This is a test message.';

        // Cria uma nova instância de TestMail
        $mail = new TestMail($subject, $message);

        // Chama o método build para construir o e-mail
        $builtMail = $mail->build();

        // Verifica se o objeto retornado é do tipo TestMail
        $this->assertInstanceOf(TestMail::class, $builtMail);

        // Verifica se o assunto do e-mail está correto
        $this->assertEquals($subject, $mail->subject);

        // Verifica se a view markdown está correta
        $this->assertEquals('emails.test', $builtMail->markdown);

        // Verifica se os dados estão corretos na view
        $this->assertEquals($message, $builtMail->viewData['my_message']);
    }


    public function testSerialization()
    {
        // Dados de exemplo para construir o e-mail
        $subject = 'Test Subject';
        $message = 'This is a test message.';

        // Cria uma nova instância de TestMail
        $mail = new TestMail($subject, $message);

        // Serializa o objeto
        $serialized = serialize($mail);

        // Desserializa o objeto
        $deserializedMail = unserialize($serialized);

        // Verifica se o objeto desserializado é uma instância de TestMail
        $this->assertInstanceOf(TestMail::class, $deserializedMail);

        // Verifica se os atributos foram restaurados corretamente após a desserialização
        $this->assertEquals($subject, $deserializedMail->subject);
        $this->assertEquals($message, $deserializedMail->message);
    }

    public function testBuildsTheEmailWithCorrectMessage()
    {
        $subject = 'Test Subject';
        $message = 'This is a test message.';

        $mail = \Mockery::mock(TestMail::class, [$subject, $message])->makePartial();

        $mail->shouldReceive('build')
            ->andReturnSelf();
        $builtMail = $mail->build();

        $this->assertInstanceOf(TestMail::class, $builtMail);

        $this->assertEquals('Test Subject', $builtMail->subject);

    }
    public function testBuildsTheEmailWithCorrectSubject()
    {
        $subject = 'Test Subject';
        $message = 'This is a test message.';

        $mail = \Mockery::mock(TestMail::class, [$subject, $message])->makePartial();

        $mail->shouldReceive('build')
            ->andReturnSelf();

        $builtMail = $mail->build();

        $this->assertInstanceOf(TestMail::class, $builtMail);

        $this->assertEquals($subject, $builtMail->subject);
    }


}
