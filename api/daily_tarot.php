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
    $readingDate = date('Y-m-d');
    $existingReading = find_daily_reading_for_date($pdo, $userId, $readingDate);

    if ($existingReading !== null) {
        json_ok([
            'reading_id' => (int)$existingReading['id'],
            'type' => 'daily',
            'reading_date' => $existingReading['reading_date'],
            'already_drawn' => true,
            'cards' => decode_cards_json((string)$existingReading['cards']),
            'result' => $existingReading['result'],
        ], '오늘의 타로는 이미 생성되었습니다.');
    }

    $cards = draw_three_cards($pdo);
    $result = '오늘의 타로 카드가 생성되었습니다.';

    try {
        $readingId = insert_daily_reading($pdo, $userId, $readingDate, $cards, $result);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            $existingReading = find_daily_reading_for_date($pdo, $userId, $readingDate);
            if ($existingReading !== null) {
                json_ok([
                    'reading_id' => (int)$existingReading['id'],
                    'type' => 'daily',
                    'reading_date' => $existingReading['reading_date'],
                    'already_drawn' => true,
                    'cards' => decode_cards_json((string)$existingReading['cards']),
                    'result' => $existingReading['result'],
                ], '오늘의 타로는 이미 생성되었습니다.');
            }
        }

        throw $e;
    }

    json_ok([
        'reading_id' => $readingId,
        'type' => 'daily',
        'reading_date' => $readingDate,
        'already_drawn' => false,
        'cards' => $cards,
        'result' => $result,
    ], '오늘의 타로 생성 성공');
} catch (Throwable $e) {
    error_log('[daily_tarot.php] ' . $e->getMessage());
    json_fail('오늘의 타로 저장 중 오류가 발생했습니다.', APP_DEBUG ? $e->getMessage() : 'daily tarot failed', 500);
}

function find_daily_reading_for_date(PDO $pdo, int $userId, string $readingDate): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id, user_id, reading_date, cards, result, created_at
         FROM daily_readings
         WHERE user_id = :user_id AND reading_date = :reading_date
         LIMIT 1'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':reading_date' => $readingDate,
    ]);

    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

function insert_daily_reading(PDO $pdo, int $userId, string $readingDate, array $cards, string $result): int
{
    $cardsJson = json_encode($cards, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    /*
     * history.php가 daily_readings 테이블에서 reading_date, cards, result를 조회합니다.
     * TODO: 실제 DB에서 SHOW CREATE TABLE daily_readings 결과를 확인한 뒤 컬럼 타입/제약을 최종 보정합니다.
     */
    $stmt = $pdo->prepare(
        'INSERT INTO daily_readings
            (user_id, reading_date, cards, result)
         VALUES
            (:user_id, :reading_date, :cards, :result)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':reading_date' => $readingDate,
        ':cards' => $cardsJson,
        ':result' => $result,
    ]);

    return (int)$pdo->lastInsertId();
}

function decode_cards_json(string $cardsJson): array
{
    $cards = json_decode($cardsJson, true);

    return is_array($cards) ? $cards : [];
}
