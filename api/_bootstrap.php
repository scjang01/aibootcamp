<?php
/**
 * _bootstrap.php
 * 모든 엔드포인트의 맨 위에서 1회 require 한다.
 * - 공통 헤더(JSON, CORS)
 * - 세션 시작
 * - 공통 응답 함수 json_ok / json_fail
 * - 전역 예외/에러 핸들러
 */

require_once __DIR__ . '/config.php';

// 에러 표시
ini_set('display_errors', '0');
error_reporting(E_ALL);
mb_internal_encoding('UTF-8');

// 공통 응답 헤더
header('Content-Type: application/json; charset=utf-8');

// 프론트가 다른 오리진이면 아래를 환경에 맞게 조정
header('Access-Control-Allow-Credentials: true');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    header('Access-Control-Allow-Origin: ' . $origin);
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 프리플라이트는 바로 종료
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/**
 * 성공 응답.
 */
function json_ok($data = [], string $message = '성공', int $status = 200): void
{
    http_response_code($status);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 실패 응답.
 */
function json_fail(string $message, string $error = '', int $status = 400): void
{
    http_response_code($status);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'data'    => null,
        'error'   => $error,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/** HTTP 메서드 강제. 다르면 405 로 종료. */
function require_method(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== strtoupper($method)) {
        json_fail('허용되지 않은 요청 방식입니다.', 'Method Not Allowed', 405);
    }
}

/**
 * 요청 본문 파싱: JSON body 또는 form-urlencoded 둘 다 허용.
 * @return array<string,mixed>
 */
function read_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw !== '' && $raw !== false) {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            return $json;
        }
    }
    return $_POST;
}

// 전역 예외/에러 핸들러: 항상 JSON 으로 응답
set_exception_handler(function (\Throwable $e) {
    $detail = APP_DEBUG ? $e->getMessage() : 'Internal Server Error';
    json_fail('서버 오류가 발생했습니다.', $detail, 500);
});

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            $detail = APP_DEBUG ? $err['message'] : 'Internal Server Error';
            json_fail('서버 오류가 발생했습니다.', $detail, 500);
        }
    }
});

// 세션 시작
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'secure'   => SESSION_COOKIE_SECURE,
    'samesite' => 'Lax',
]);
session_start();
