<?php
/**
 * Refund V2 API
 *
 * Initiates a full or partial refund for a completed transaction.
 *
 * REQUIRED PARAMS: easebuzz_id, refund_amount, merchant_refund_id
 * ENDPOINT: POST {dashboardBaseUrl}/transaction/v2/refund
 * HASH SEQUENCE: key|merchant_refund_id|easebuzz_id|refund_amount|SALT
 * CONTENT-TYPE: application/x-www-form-urlencoded
 */

include_once(__DIR__ . '/utils.php');

$params = _sanitizeParams($postData);

// Validate
$error = _validateMandatoryFields($params, array('easebuzz_id', 'refund_amount', 'merchant_refund_id'));
if ($error !== null) {
    displayResponse(array('status' => 0, 'data' => $error), $params);
    return;
}

// Add key and generate hash
$params['key'] = $merchantKey;
$params['hash'] = generateHashValue($params, $salt, 'refund');
$params = _removeEmptyParams($params);

// Call API
$url = fetchBaseUrl($env) . 'transaction/v2/refund';
$response = _curlCall($url, http_build_query($params));

// Return complete response
$status = (isset($response->status)) ? (int)$response->status : 0;
displayResponse(array('status' => $status, 'data' => $response), $postData);
