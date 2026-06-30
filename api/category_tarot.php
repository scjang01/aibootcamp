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
$categoryLabel = trim((string)($input['category'] ?? $input['topic'] ?? ''));
$question = trim((string)($input['question'] ?? ''));

if ($categoryLabel === '' || $question === '') {
    json_fail('category와 question은 필수입니다.', 'category/question required', 422);
}

$topic = normalize_topic_code($categoryLabel);
if ($topic === null) {
    json_fail(
        '지원하지 않는 타로 주제입니다.',
        'topic must be one of love|career|study|relationship|money|health',
        422
    );
}

$user = require_login();
$userId = (int)$user['id'];

try {
    $pdo = db();
    $cards = draw_three_cards($pdo);
    $prompt = generate_tarot_prompt($categoryLabel, $question, $cards);
    $answer = call_litellm($prompt);
    $readingId = insert_topic_reading($pdo, $userId, $topic, $question, $cards, $answer);

    json_ok([
        'reading_id' => $readingId,
        'type' => 'topic',
        'category' => $categoryLabel,
        'category_label' => $categoryLabel,
        'topic' => $topic,
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
     * TODO: 실제 DB에서 SHOW CREATE TABLE topic_readings 결과를 확인한 뒤 컬럼 타입/제약을 최종 보정합니다.
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

function normalize_topic_code(string $categoryOrTopic): ?string
{
    $value = trim($categoryOrTopic);
    $normalized = strtolower($value);

    $allowedTopics = ['love', 'career', 'study', 'relationship', 'money', 'health'];
    if (in_array($normalized, $allowedTopics, true)) {
        return $normalized;
    }

    $koreanTopicMap = [
        '연애' => 'love',
        '진로' => 'career',
        '학업' => 'study',
        '인간관계' => 'relationship',
        '금전' => 'money',
        '건강' => 'health',
    ];

    return $koreanTopicMap[$value] ?? null;
}
