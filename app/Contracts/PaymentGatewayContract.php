<?php

namespace App\Contracts;

use Illuminate\Http\Request;

/**
 * Every payment gateway integration implements this so PaymentController's
 * verification pipeline (race-condition claim, authoritative re-verification,
 * amount check, fulfillment) stays gateway-agnostic. Adding a gateway means
 * writing one class that implements this and registering it in
 * PaymentController::resolveGatewayService() — the pipeline itself never changes.
 */
interface PaymentGatewayContract
{
    public function generateTxnId(): string;

    public function isConfigured(): bool;

    public function isProduction(): bool;

    /**
     * @return array{success: bool, redirect_url?: string, error?: string}
     */
    public function initiatePayment(array $params): array;

    /**
     * Authoritative server-to-server check — callback data is never trusted alone,
     * this call is the source of truth for whether a transaction actually succeeded.
     *
     * @return array{success: bool, amount: ?string, raw: mixed}
     */
    public function verifyTransaction(string $identifier): array;

    /**
     * Cheaply pull identifying info out of an inbound callback request — no
     * authenticity check yet. Used only to locate the Payment row before the
     * atomic claim; verifyCallbackAuthenticity() runs after the claim succeeds,
     * mirroring each gateway's original verification order.
     *
     * - txnid: our merchant transaction ID, used to find the Payment row.
     * - identifier: value passed to verifyTransaction() — may differ from txnid
     *   (e.g. PayGlocal's gid).
     * - payment_id: the gateway's own reference for this transaction, stored on
     *   the Payment record for reconciliation (may equal identifier).
     *
     * @return array{txnid: ?string, identifier: ?string, payment_id: ?string, data: array}
     */
    public function parseCallback(Request $request): array;

    /**
     * Verify the callback genuinely came from this gateway (hash/signature check
     * against $data). Called only after the atomic claim succeeds.
     */
    public function verifyCallbackAuthenticity(array $data, Request $request): bool;
}
