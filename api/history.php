<?php
/**
 * history.php  (GET)
 * 로그인 사용자의 타로 기록 조회. 본인 것만 반환.
 * daily_readings + topic_readings 두 테이블을 합쳐서 다룬다.
 *
 * 쿼리 파라미터(모두 선택):
 *   id     : 지정 시 단건 조회 (type 과 함께 주면 더 정확)
 *   type   : all(기본) | daily | topic
 *   topic  : type=topic 일 때 주제 필터 (love/career/study/relationship/money/health)
 *   filter : today  → 오늘 기록만
 *   limit  : 기본 20, 최대 100
 *   offset : 기본 0
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

require_method('GET');

$user   = require_login();
$userId = (int)$user['id'];

/** cards(JSON 문자열)를 실제 배열/객체로 디코드해서 응답에 싣는다. */
function decode_cards(array $row): array
{
    if (isset($row['cards']) && is_string($row['cards'])) {
        $decoded = json_decode($row['cards'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $row['cards'] = $decoded;
        }
    }
    return $row;
}

// 각 테이블의 컬럼을 동일한 형태로 맞춘 SELECT 조각 (UNION 용)
$dailySelect = "SELECT 'daily' AS type, id, user_id, NULL AS topic, NULL AS question, "
             . "reading_date, cards, result, created_at FROM daily_readings";
$topicSelect = "SELECT 'topic' AS type, id, user_id, topic, question, "
             . "NULL AS reading_date, cards, result, created_at FROM topic_readings";

// ---- 단건 조회 ----
if (isset($_GET['id']) && $_GET['id'] !== '') {
    $id   = (int)$_GET['id'];
    $type = $_GET['type'] ?? '';
    $row  = null;

    if ($type === 'daily' || $type === '') {
        $stmt = db()->prepare("$dailySelect WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
    }
    if (!$row && ($type === 'topic' || $type === '')) {
        $stmt = db()->prepare("$topicSelect WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
    }

    if (!$row) {
        json_fail('해당 기록을 찾을 수 없습니다.', 'reading not found', 404);
    }
    json_ok(['reading' => decode_cards($row)], '조회되었습니다.');
}

// ---- 목록 조회 ----
$type   = $_GET['type'] ?? 'all';
$today  = (($_GET['filter'] ?? '') === 'today');
$limit  = max(1, min(100, (int)($_GET['limit'] ?? 20)));
$offset = max(0, (int)($_GET['offset'] ?? 0));

// 포함할 테이블별 (SQL, 파라미터) 조각 구성
$branches = [];

if ($type === 'all' || $type === 'daily') {
    $sql    = "$dailySelect WHERE user_id = ?";
    $params = [$userId];
    if ($today) {
        $sql .= ' AND reading_date = CURDATE()';
    }
    $branches[] = [$sql, $params];
}

if ($type === 'all' || $type === 'topic') {
    $sql    = "$topicSelect WHERE user_id = ?";
    $params = [$userId];
    if ($today) {
        $sql .= ' AND DATE(created_at) = CURDATE()';
    }
    $topicFilter = trim((string)($_GET['topic'] ?? ''));
    if ($topicFilter !== '') {
        $sql     .= ' AND topic = ?';
        $params[] = $topicFilter;
    }
    $branches[] = [$sql, $params];
}

if (!$branches) {
    json_fail('type 값이 올바르지 않습니다.', 'type must be all|daily|topic', 422);
}

// 각 조각을 괄호로 감싸 UNION ALL 로 결합
$unionSql   = implode(' UNION ALL ', array_map(fn($b) => '(' . $b[0] . ')', $branches));
$bindParams = array_merge(...array_map(fn($b) => $b[1], $branches));

// 전체 개수
$countStmt = db()->prepare("SELECT COUNT(*) FROM ($unionSql) AS t");
$countStmt->execute($bindParams);
$total = (int)$countStmt->fetchColumn();

// 목록
$listStmt = db()->prepare("$unionSql ORDER BY created_at DESC, id DESC LIMIT $limit OFFSET $offset");
$listStmt->execute($bindParams);
$rows = array_map('decode_cards', $listStmt->fetchAll());

json_ok([
    'total'    => $total,
    'limit'    => $limit,
    'offset'   => $offset,
    'readings' => $rows,
], '조회되었습니다.');
