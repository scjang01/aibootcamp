<?php
declare(strict_types=1);

/*
 * LiteLLM 연동 담당 파일입니다.
 * config.php의 USE_MOCK, LITELLM_BASE, LITELLM_KEY, LITELLM_MODEL, LITELLM_TIMEOUT 상수를 사용합니다.
 * 실제 키와 URL은 .env 또는 서버 환경변수에서 들어오며, 이 파일에 하드코딩하지 않습니다.
 */

require_once __DIR__ . '/config.php';

function generate_tarot_prompt(string $category, string $question, array $cards): string
{
    $cardLines = [];

    foreach ($cards as $index => $card) {
        $number = $index + 1;
        $position = $card['position'] ?? '';
        $nameKo = $card['name_ko'] ?? '';
        $name = $card['name'] ?? '';
        $description = $card['description'] ?? '';

        $cardLines[] = "{$number}. {$position} - {$nameKo} ({$name}): {$description}";
    }

    $cardsText = implode("\n", $cardLines);

    return <<<PROMPT
너는 친절한 타로 리더입니다.
이 서비스는 오락과 자기성찰 목적의 타로 해석 서비스입니다.
단정적으로 미래를 예언하지 말고, 사용자가 스스로 생각을 정리할 수 있도록 조언 중심으로 답하세요.
불안감을 키우는 표현이나 반드시 일어난다는 식의 표현은 피하세요.

카테고리: {$category}
사용자 질문: {$question}

뽑힌 카드:
{$cardsText}

위 카드들을 바탕으로 한국어로 5~8문장 정도의 타로 해석을 작성하세요.
각 카드의 의미를 질문과 연결해서 설명하고, 마지막에는 현실적인 조언을 1~2문장 덧붙이세요.
PROMPT;
}

function call_litellm_mock(string $prompt): string
{
    $category = extract_prompt_value($prompt, '카테고리');
    $question = extract_prompt_value($prompt, '사용자 질문');
    $cards = extract_prompt_card_names($prompt);

    $cardSummary = count($cards) > 0 ? implode(', ', $cards) : '선택된 카드들';
    $categoryText = $category !== '' ? $category : '선택한 주제';
    $questionText = $question !== '' ? $question : '사용자 질문';

    return "mock 타로 해석입니다. {$categoryText}에 대한 질문 '{$questionText}'은 {$cardSummary}의 흐름으로 임시 해석했습니다. "
        . "첫 번째 카드는 현재 상황을 돌아보게 하고, 두 번째 카드는 지금 이어지는 분위기를 보여주며, 세 번째 카드는 조언의 방향을 제안합니다. "
        . "지금은 실제 LiteLLM 호출 전 단계이므로 단정적인 예언 대신 자기성찰용 안내 문장만 반환합니다. "
        . "USE_MOCK을 false로 바꾸고 LiteLLM 설정이 준비되면 실제 응답으로 교체됩니다.";
}

function call_litellm(string $prompt): string
{
    if (USE_MOCK || trim((string)LITELLM_KEY) === '') {
        return call_litellm_mock($prompt);
    }

    if (!function_exists('curl_init')) {
        error_log('[litellm.php] PHP cURL extension is not available. Falling back to mock response.');
        return call_litellm_mock($prompt);
    }

    $url = build_litellm_chat_url((string)LITELLM_BASE);
    $payload = [
        'model' => LITELLM_MODEL,
        'messages' => [
            [
                'role' => 'system',
                'content' => '너는 오락과 자기성찰 목적의 타로 해석을 제공하는 조언형 한국어 어시스턴트입니다.',
            ],
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ],
        'temperature' => 0.7,
    ];

    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($body === false) {
        error_log('[litellm.php] Failed to encode LiteLLM payload. Falling back to mock response.');
        return call_litellm_mock($prompt);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . LITELLM_KEY,
        ],
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_CONNECTTIMEOUT => min(10, max(1, (int)LITELLM_TIMEOUT)),
        CURLOPT_TIMEOUT => max(1, (int)LITELLM_TIMEOUT),
    ]);

    $responseBody = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpStatus = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($responseBody === false || $curlError !== '') {
        $logBody = $responseBody === false ? '' : (string)$responseBody;
        error_log("[litellm.php] curl error: {$curlError}, HTTP status: {$httpStatus}, response body: {$logBody}");
        return call_litellm_mock($prompt);
    }

    if ($httpStatus < 200 || $httpStatus >= 300) {
        error_log("[litellm.php] HTTP status: {$httpStatus}, response body: {$responseBody}");
        return call_litellm_mock($prompt);
    }

    $decoded = json_decode((string)$responseBody, true);
    if (!is_array($decoded)) {
        error_log('[litellm.php] Invalid JSON response body: ' . $responseBody);
        return call_litellm_mock($prompt);
    }

    $content = $decoded['choices'][0]['message']['content'] ?? null;
    if (!is_string($content) || trim($content) === '') {
        error_log('[litellm.php] Missing choices[0].message.content. response body: ' . $responseBody);
        return call_litellm_mock($prompt);
    }

    return trim($content);
}

function build_litellm_chat_url(string $baseUrl): string
{
    $baseUrl = rtrim($baseUrl, '/');

    if (str_ends_with($baseUrl, '/v1')) {
        return $baseUrl . '/chat/completions';
    }

    return $baseUrl . '/v1/chat/completions';
}

function extract_prompt_value(string $prompt, string $label): string
{
    $pattern = '/^' . preg_quote($label, '/') . ':\s*(.+)$/mu';

    if (preg_match($pattern, $prompt, $matches) === 1) {
        return trim($matches[1]);
    }

    return '';
}

function extract_prompt_card_names(string $prompt): array
{
    preg_match_all('/^\d+\.\s*.+?\s-\s(.+?)\s\(.+?\):/mu', $prompt, $matches);

    if (!isset($matches[1])) {
        return [];
    }

    return array_map('trim', $matches[1]);
}
