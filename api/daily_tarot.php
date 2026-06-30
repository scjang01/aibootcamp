<?php
declare(strict_types=1);

/*
 * daily_tarot.php (POST)
 * 로그인 사용자의 오늘의 타로 결과를 daily_readings 테이블에 저장합니다.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tarot.php';

require_method('POST');

$user = require_login();
$userId = (int)$user['id'];

try {
    $pdo = db();
    $cards = draw_three_cards($pdo);
    $readingId = insert_daily_reading($pdo, $userId, $cards);

    json_ok([
        'reading_id' => $readingId,
        'type' => 'daily',
        'cards' => $cards,
    ], '오늘의 타로 생성 성공');
} catch (Throwable $e) {
    error_log('[daily_tarot.php] ' . $e->getMessage());
    json_fail('오늘의 타로 저장 중 오류가 발생했습니다.', APP_DEBUG ? $e->getMessage() : 'daily tarot failed', 500);
}

function insert_daily_reading(PDO $pdo, int $userId, array $cards): int
{
    $cardsJson = json_encode($cards, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    /*
     * history.php가 daily_readings 테이블에서 reading_date, cards, result를 조회합니다.
     * result가 NOT NULL인 DB라면 빈 문자열을 저장해 history와 충돌하지 않게 합니다.
     */
    $stmt = $pdo->prepare(
        'INSERT INTO daily_readings
            (user_id, reading_date, cards, result)
         VALUES
            (:user_id, CURDATE(), :cards, :result)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':cards' => $cardsJson,
        ':result' => '',
    ]);

    return (int)$pdo->lastInsertId();
}
