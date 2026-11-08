<?php

declare(strict_types=1);

// php tests/transport_standalone.php [path/to/project-or-payload]
$project = $argv[1] ?? dirname(__DIR__) . '/payload';
require $project . '/app/Services/TelemedicineProviderTransport.php';

use App\Services\TelemedicineProviderTransport as Transport;

function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
function rejects(callable $action, string $message): void {
    try { $action(); } catch (InvalidArgumentException) { return; }
    throw new RuntimeException($message);
}
function unb64url(string $value): string {
    $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), true);
    if ($decoded === false) throw new RuntimeException('Bad base64');
    return $decoded;
}

check(Transport::webrtcPage('HTTPS://video.example/rooms/demo') === 'HTTPS://video.example/rooms/demo', 'HTTPS case');
check(Transport::webrtcPage('https://video.example:8443/custom/page') === 'https://video.example:8443/custom/page', 'Path must be preserved');
foreach (['http://video.example/demo', 'https://user@video.example/demo', 'https://video.example/demo#old', 'https://video.example/demo?a=1', 'javascript:alert(1)'] as $url) {
    rejects(fn () => Transport::webrtcPage($url), 'Unsafe WebRTC URL accepted');
}
$main = 'https://telemost.yandex.ru/j/12345678901234';
$alias = 'https://telemost.360.yandex.ru/j/12345678901234';
check(Transport::telemostJoinUrl($alias) === $alias, 'Original alias should be preserved');
check(Transport::telemostUrlHash($main) === Transport::telemostUrlHash($alias), 'Aliases must not bypass room uniqueness');
check(Transport::telemostUrlHash($main) !== Transport::telemostUrlHash('https://telemost.yandex.ru/j/2'), 'Different meetings collided');
foreach (['https://telemost.yandex.ru.attacker.example/j/12', 'https://telemost.yandex.ru@attacker.example/j/12', 'https://telemost.yandex.ru/j/12?x=1', 'https://telemost.yandex.ru/j/12#x', 'https://telemost.yandex.ru/j/../12', 'https://telemost.yandex.ru/j/abc', 'http://telemost.yandex.ru/j/12'] as $url) {
    rejects(fn () => Transport::telemostJoinUrl($url), 'Unsafe Telemost URL accepted');
}

$now = 1800000000;
$start = $now + 300;
$secret = str_repeat('0123456789abcdef', 4);
$room = 'visit_0123456789abcdef0123456789abcdef';
$token = Transport::webrtcToken($secret, $room, 42, 'patient', $now + 3600, $start, $now);
check(substr_count($token, '.') === 1, 'This protocol is not JWT');
[$data, $signature] = explode('.', $token);
$payload = json_decode(unb64url($data), true, 512, JSON_THROW_ON_ERROR);
check(hash_equals(hash_hmac('sha256', $data, hex2bin($secret), true), unb64url($signature)), 'HMAC must decode hex key');
check($payload['aud'] === 'norhnaim-webrtc-dev-v1', 'Wrong audience');
check($payload['registry_record_id'] === 42 && $payload['appointment_start'] === $start, 'Missing immutable invitation binding');
check($payload['room'] === $room && $payload['role'] === 'patient', 'Wrong room or role');
check($payload['iat'] === $now && $payload['exp'] === $now + 3600, 'Wrong token clock');
check(strlen($payload['jti']) >= 16, 'Missing nonce');
check(count($payload) === 8, 'Unexpected metadata in bearer token');
$second = Transport::webrtcToken($secret, $room, 42, 'patient', $now + 3600, $start, $now);
check($token !== $second, 'Independent invitations need different nonces');
rejects(fn () => Transport::webrtcToken($secret, $room, 42, 'patient', $now + 59, $start, $now), 'TTL lower bound');
rejects(fn () => Transport::webrtcToken($secret, $room, 42, 'patient', $now + 14401, $start, $now), 'TTL upper bound');
rejects(fn () => Transport::webrtcToken('not-a-key', $room, 42, 'patient', $now + 60, $start, $now), 'Invalid key');
rejects(fn () => Transport::webrtcToken($secret, $room, 42, 'admin', $now + 60, $start, $now), 'Invalid role');
rejects(fn () => Transport::webrtcToken($secret, $room, 0, 'patient', $now + 60, $start, $now), 'Missing appointment');
rejects(fn () => Transport::webrtcToken($secret, $room, 42, 'patient', $now + 60, 0, $now), 'Missing scheduled UTC instant');
echo "Transport pure PHP checks passed\n";
if (getenv('TELEMEDICINE_TEST_VECTOR_OUT')) file_put_contents(getenv('TELEMEDICINE_TEST_VECTOR_OUT'), json_encode([
    'secret_hex' => $secret, 'now' => $now, 'token' => $token,
], JSON_THROW_ON_ERROR));
