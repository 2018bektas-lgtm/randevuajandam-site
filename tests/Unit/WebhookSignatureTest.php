<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Outbound webhook HMAC must match receiver expectation:
 * signature = hash_hmac('sha256', timestamp + body, secret)
 */
class WebhookSignatureTest extends TestCase
{
    public function test_hmac_signature_is_deterministic(): void
    {
        $secret = 'test_webhook_secret_key';
        $timestamp = 1700000000;
        $body = json_encode([
            'event' => 'appointment.created',
            'timestamp' => $timestamp,
            'data' => ['id' => 1],
        ], JSON_UNESCAPED_UNICODE);

        $sig1 = hash_hmac('sha256', $timestamp.$body, $secret);
        $sig2 = hash_hmac('sha256', $timestamp.$body, $secret);

        $this->assertSame($sig1, $sig2);
        $this->assertSame(64, strlen($sig1));
    }

    public function test_wrong_secret_fails_verification(): void
    {
        $timestamp = 1700000000;
        $body = '{"event":"x"}';
        $valid = hash_hmac('sha256', $timestamp.$body, 'correct-secret');
        $invalid = hash_hmac('sha256', $timestamp.$body, 'wrong-secret');

        $this->assertFalse(hash_equals($valid, $invalid));
    }

    public function test_tampered_body_fails_verification(): void
    {
        $secret = 's';
        $timestamp = 1;
        $body = '{"a":1}';
        $sig = hash_hmac('sha256', $timestamp.$body, $secret);
        $tampered = '{"a":2}';
        $recomputed = hash_hmac('sha256', $timestamp.$tampered, $secret);

        $this->assertFalse(hash_equals($sig, $recomputed));
    }
}
