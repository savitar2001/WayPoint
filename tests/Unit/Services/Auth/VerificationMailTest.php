<?php

use App\Mail\VerificationMail;
use PHPUnit\Framework\TestCase;

class VerificationMailTest extends TestCase {
    public function testVerificationMailConstruction() {
        $name = 'Test User';
        $url = 'https://example.com/verify?token=123';
        
        $mail = new VerificationMail($name, $url);
        
        $this->assertEquals($name, $mail->toName);
        $this->assertEquals($url, $mail->url);
    }
    
    public function testEnvelopeHasCorrectSubject() {
        $mail = new VerificationMail('Test User', 'https://example.com');
        $envelope = $mail->envelope();
        
        $this->assertEquals('Verification Mail', $envelope->subject);
    }
    
    public function testContentUsesCorrectView() {
        $name = 'Test User';
        $url = 'https://example.com/verify?token=123';
        
        $mail = new VerificationMail($name, $url);
        $content = $mail->content();
        
        $this->assertEquals('verification', $content->view);
        $this->assertEquals(['toName' => $name, 'url' => $url], $content->with);
    }
    
    public function testAttachmentsAreEmpty() {
        $mail = new VerificationMail('Test User', 'https://example.com');
        
        $this->assertEmpty($mail->attachments());
    }
}