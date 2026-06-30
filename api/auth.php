<?php
/**
 * auth.php
 * 세션 기반 로그인 상태 확인 헬퍼.
 */

require_once __DIR__ . '/_bootstrap.php';

/** 로그인 되어 있으면 사용자 정보 배열, 아니면 null */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? null,
    ];
}

/** 로그인 필수 엔드포인트에서 호출. 비로그인 시 401 로 종료. */
function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        json_fail('로그인이 필요합니다.', 'Unauthorized', 401);
    }
    return $user;
}

/** 로그인 성공 시 세션 확정. 세션 고정 공격 방지를 위해 ID 재발급. */
function login_user(int $userId, string $username): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']  = $userId;
    $_SESSION['username'] = $username;
}

/** 세션 완전 종료. */
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'domain'   => $p['domain'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}
