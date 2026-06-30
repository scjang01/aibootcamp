<?php
/**
 * login.php  (POST)
 * 입력: { "username": "...", "password": "..." }
 * 아이디/비밀번호 검증 후 세션 로그인.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_method('POST');

$in = read_input();
$username = trim((string)($in['username'] ?? ''));
$password = (string)($in['password'] ?? '');

if ($username === '' || $password === '') {
    json_fail('아이디와 비밀번호를 입력해 주세요.', 'username/password required', 422);
}

$stmt = db()->prepare('SELECT id, username, password, name FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

// 아이디 없음 / 비번 불일치를 동일 메시지로 처리 (계정 존재 여부 노출 방지)
if (!$user || !password_verify($password, $user['password'])) {
    json_fail('아이디 또는 비밀번호가 올바르지 않습니다.', 'invalid credentials', 401);
}

login_user((int)$user['id'], $user['username']);

json_ok([
    'id'       => (int)$user['id'],
    'username' => $user['username'],
    'name'     => $user['name'],
], '로그인 되었습니다.');
