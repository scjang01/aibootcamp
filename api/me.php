<?php
/**
 * me.php  (GET)
 * 현재 로그인한 사용자 정보 반환. 세션 유효성 확인용.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_method('GET');

$sessionUser = require_login();

$stmt = db()->prepare('SELECT id, username, name, birth_date, gender, created_at FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$sessionUser['id']]);
$user = $stmt->fetch();

// 세션은 있는데 계정이 사라진 경우(삭제 등) → 세션 정리 후 401
if (!$user) {
    logout_user();
    json_fail('로그인이 필요합니다.', 'user not found', 401);
}

json_ok([
    'id'         => (int)$user['id'],
    'username'   => $user['username'],
    'name'       => $user['name'],
    'birth_date' => $user['birth_date'],
    'gender'     => $user['gender'],
    'created_at' => $user['created_at'],
], '로그인 상태입니다.');
