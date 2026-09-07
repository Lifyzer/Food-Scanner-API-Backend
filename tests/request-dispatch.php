<?php
// Run with `php tests/request-dispatch.php`; no database, credentials or network required.
$cases = [
    ['result' => 'yes', 'status' => 'called'],
    ['result' => 'no', 'status' => 'failed'],
    ['result' => 'error', 'status' => 'failed'],
    ['result' => null, 'status' => 'failed'],
    ['result' => true, 'status' => 'failed'],
    ['result' => [], 'status' => 'failed'],
    ['result' => ['key' => 'Other', 'value' => 'token'], 'status' => 'failed'],
    ['result' => ['key' => 'User', 'value' => ''], 'status' => 'failed'],
    ['result' => ['key' => 'User', 'value' => 'token'], 'status' => 'called', 'tokenKey' => 'UserToken'],
    ['result' => ['key' => 'Temp', 'value' => 'token'], 'status' => 'called', 'tokenKey' => 'TempToken'],
];
$count = 0;
foreach (['Login', 'getProductDetails'] as $service) {
    foreach ($cases as $case) {
        $input = $case + ['service' => $service, 'body' => json_encode(['text' => 'food'])];
        $process = proc_open([PHP_BINARY, '-d', 'display_errors=stderr', __DIR__ . '/dispatch-worker.php', base64_encode(json_encode($input))],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $errors = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $exit = proc_close($process);
        $data = json_decode($output, true);
        if ($exit !== 0 || ($data['status'] ?? null) !== $case['status']) {
            throw new RuntimeException("Dispatch case failed for $service: $output $errors");
        }
        if (isset($case['tokenKey']) && ($data[$case['tokenKey']] ?? null) !== 'token') {
            throw new RuntimeException('Validated token response was lost.');
        }
        ++$count;
    }
}
echo "$count offline dispatch checks passed.\n";
