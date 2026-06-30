<?php
declare(strict_types=1);

/*
 * category_tarot.php (POST)
 * 프론트의 category 입력을 내부 topic 값으로 매핑해 topic_readings 테이블에 저장합니다.
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tarot.php';
require_once __DIR__ . '/litellm.php';

require_method('POST');

$input = read_input();
$category = trim((string)($input['category'] ?? $input['topic'] ?? ''));
$question = trim((string)($input['question'] ?? ''));

if ($category === '' || $question === '') {
    json_fail('category와 question은 필수입니다.', 'category/question required', 422);
}

$user = require_login();
$userId = (int)$user['id'];

try {
    $pdo = db();
    $cards = draw_three_cards($pdo);
    $prompt = generate_tarot_prompt($category, $question, $cards);
    $answer = call_litellm($prompt);
    $readingId = insert_topic_reading($pdo, $userId, $category, $question, $cards, $answer);

    json_ok([
        'reading_id' => $readingId,
        'type' => 'topic',
        'category' => $category,
        'topic' => $category,
        'question' => $question,
        'cards' => $cards,
        'answer' => $answer,
    ], '주제별 타로 생성 성공');
} catch (Throwable $e) {
    error_log('[category_tarot.php] ' . $e->getMessage());
    json_fail('주제별 타로 저장 중 오류가 발생했습니다.', APP_DEBUG ? $e->getMessage() : 'category tarot failed', 500);
}

function insert_topic_reading(
    PDO $pdo,
    int $userId,
    string $topic,
    string $question,
    array $cards,
    string $answer
): int {
    $cardsJson = json_encode($cards, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    /*
     * history.php가 topic_readings 테이블에서 topic, question, cards, result를 조회합니다.
     * 프론트 입력명은 category여도 DB에는 topic 컬럼으로 저장합니다.
     */
    $stmt = $pdo->prepare(
        'INSERT INTO topic_readings
            (user_id, topic, question, cards, result)
         VALUES
            (:user_id, :topic, :question, :cards, :result)'
    );

    $stmt->execute([
        ':user_id' => $userId,
        ':topic' => $topic,
        ':question' => $question,
        ':cards' => $cardsJson,
        ':result' => $answer,
    ]);

    return (int)$pdo->lastInsertId();
}
