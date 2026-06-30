<?php
/**
 * health.php  (GET)
 * 서버/DB 상태 확인용. 배포 후 접속 점검에 사용.
 */
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/db.php';

require_method('GET');

$dbOk = false;
$dbError = '';
try {
    db()->query('SELECT 1');
    $dbOk = true;
} catch (\Throwable $e) {
    $dbError = APP_DEBUG ? $e->getMessage() : 'DB connection failed';
}

if ($dbOk) {
    json_ok(['db' => 'up', 'time' => date('c')], '정상 동작 중입니다.');
}
json_fail('DB 연결에 실패했습니다.', $dbError, 503);
