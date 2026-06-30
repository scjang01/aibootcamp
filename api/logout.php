<?php
/**
 * logout.php  (POST)
 * 세션 종료.
 */
require_once __DIR__ . '/auth.php';

require_method('POST');

logout_user();
json_ok([], '로그아웃 되었습니다.');
