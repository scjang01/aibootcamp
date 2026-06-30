<?php
/**
 * config.php
 * .env 를 로드한 뒤 상수로 정의한다.
 * 접속정보/키 같은 실제 값은 이 파일이 아니라 .env 에만 둔다. (하드코딩 금지)
 *
 * 우선순위: 이미 환경에 주입된 값(docker-compose 등) > .env 파일 > 기본값
 */

// ---- .env 로더 (외부 의존성 없음) ----
(function (): void {
    $path = __DIR__ . '/.env';
    if (!is_readable($path)) {
        return; // .env 없으면 환경변수/기본값만 사용
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        $pos = strpos($line, '=');
        if ($pos === false) {
            continue;
        }
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        // 값 양쪽 따옴표 제거
        $len = strlen($val);
        if ($len >= 2 && ($val[0] === '"' || $val[0] === "'") && $val[$len - 1] === $val[0]) {
            $val = substr($val, 1, -1);
        }
        // 이미 환경에 있으면(예: docker) 덮어쓰지 않음
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        putenv("$key=$val");
        $_ENV[$key] = $val;
    }
})();

/** 환경변수 조회 헬퍼. 없으면 $default. */
function env(string $key, ?string $default = null): ?string
{
    $v = getenv($key);
    return $v === false ? $default : $v;
}

// ---- DB ----
define('DB_HOST', env('DB_HOST', ''));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', ''));
define('DB_USER', env('DB_USER', ''));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// ---- 앱 ----
define('APP_DEBUG', env('APP_DEBUG') === 'true');
define('SESSION_COOKIE_SECURE', env('SESSION_SECURE') === 'true');
define('SESSION_NAME', env('SESSION_NAME', 'TAROTSESSID'));

// ---- LiteLLM (OpenAI 호환 프록시) ----
define('USE_MOCK', strtolower((string)env('USE_MOCK', '')) === 'true');
define('LITELLM_BASE', env('LITELLM_BASE', 'http://litellm:4000'));
define('LITELLM_KEY', env('LITELLM_KEY', ''));
define('LITELLM_MODEL', env('LITELLM_MODEL', 'HCX-005'));
define('LITELLM_TIMEOUT', (int)env('LITELLM_TIMEOUT', '20')); // 초
