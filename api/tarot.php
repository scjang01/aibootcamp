<?php
declare(strict_types=1);

/*
 * 타로 카드 추첨 담당 파일입니다.
 * 현재 단계에서는 DB의 tarot_cards 테이블을 쓰지 않고, PHP 배열에 하드코딩한 78장을 사용합니다.
 * draw_three_cards($db = null)는 나중에 DB 카드 조회 방식으로 바꾸기 쉽도록 $db 인자를 미리 받습니다.
 * 현재는 history.php의 저장 테이블과 별개로, 카드 원본만 하드코딩 배열에서 가져옵니다.
 */

function get_hardcoded_tarot_cards(): array
{
    return [
        [
            'id' => 1,
            'name' => 'The Fool',
            'name_ko' => '바보',
            'description' => '새로운 시작, 자유로운 선택, 가능성을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 2,
            'name' => 'The Magician',
            'name_ko' => '마법사',
            'description' => '준비된 능력, 실행력, 기회를 현실로 만드는 힘을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 3,
            'name' => 'The High Priestess',
            'name_ko' => '여사제',
            'description' => '직관, 내면의 지혜, 차분한 관찰이 필요한 흐름을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 4,
            'name' => 'The Empress',
            'name_ko' => '여황제',
            'description' => '풍요, 성장, 관계 속 따뜻한 돌봄을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 5,
            'name' => 'The Emperor',
            'name_ko' => '황제',
            'description' => '질서, 책임감, 현실적인 기준과 안정감을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 6,
            'name' => 'The Hierophant',
            'name_ko' => '교황',
            'description' => '전통, 배움, 조언자나 공동체의 도움을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 7,
            'name' => 'The Lovers',
            'name_ko' => '연인',
            'description' => '관계, 선택, 마음의 조화와 가치 판단을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 8,
            'name' => 'The Chariot',
            'name_ko' => '전차',
            'description' => '의지, 추진력, 목표를 향해 나아가는 에너지를 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 9,
            'name' => 'Strength',
            'name_ko' => '힘',
            'description' => '인내, 부드러운 용기, 감정을 다루는 성숙함을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 10,
            'name' => 'The Hermit',
            'name_ko' => '은둔자',
            'description' => '성찰, 잠시 멈춤, 스스로 답을 찾는 시간을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 11,
            'name' => 'Wheel of Fortune',
            'name_ko' => '운명의 수레바퀴',
            'description' => '변화, 전환점, 흐름을 받아들이는 태도를 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 12,
            'name' => 'Justice',
            'name_ko' => '정의',
            'description' => '균형, 책임, 공정한 판단과 결과를 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 13,
            'name' => 'The Hanged Man',
            'name_ko' => '매달린 사람',
            'description' => '관점 전환, 기다림, 내려놓음에서 오는 깨달음을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 14,
            'name' => 'Death',
            'name_ko' => '죽음',
            'description' => '끝맺음, 변화, 새로운 단계로 넘어가는 전환을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 15,
            'name' => 'Temperance',
            'name_ko' => '절제',
            'description' => '조화, 조절, 서두르지 않고 균형을 찾는 태도를 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 16,
            'name' => 'The Devil',
            'name_ko' => '악마',
            'description' => '집착, 유혹, 반복되는 패턴을 알아차릴 필요를 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 17,
            'name' => 'The Tower',
            'name_ko' => '탑',
            'description' => '갑작스러운 변화, 기존 구조의 흔들림, 재정비를 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 18,
            'name' => 'The Star',
            'name_ko' => '별',
            'description' => '희망, 회복, 긴 호흡으로 바라보는 가능성을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 19,
            'name' => 'The Moon',
            'name_ko' => '달',
            'description' => '불확실성, 감정의 흔들림, 숨은 진실을 살피는 흐름을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 20,
            'name' => 'The Sun',
            'name_ko' => '태양',
            'description' => '활력, 명확함, 긍정적인 표현과 성장을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 21,
            'name' => 'Judgement',
            'name_ko' => '심판',
            'description' => '각성, 평가, 지난 일을 정리하고 다음 단계로 가는 결정을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 22,
            'name' => 'The World',
            'name_ko' => '세계',
            'description' => '완성, 통합, 긴 여정의 마무리와 확장을 의미합니다.',
            'arcana' => 'major',
            'suit' => null,
        ],
        [
            'id' => 23,
            'name' => 'Ace of Wands',
            'name_ko' => '완드 에이스',
            'description' => '새로운 열정, 시작하려는 의욕, 창의적인 불씨를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 24,
            'name' => 'Two of Wands',
            'name_ko' => '완드 2',
            'description' => '계획, 선택지 검토, 더 넓은 가능성을 바라보는 단계를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 25,
            'name' => 'Three of Wands',
            'name_ko' => '완드 3',
            'description' => '확장, 기다림, 준비한 일이 밖으로 뻗어 나가는 흐름을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 26,
            'name' => 'Four of Wands',
            'name_ko' => '완드 4',
            'description' => '안정, 축하, 함께 만든 기반에서 오는 기쁨을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 27,
            'name' => 'Five of Wands',
            'name_ko' => '완드 5',
            'description' => '경쟁, 의견 충돌, 에너지가 여러 방향으로 부딪히는 상황을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 28,
            'name' => 'Six of Wands',
            'name_ko' => '완드 6',
            'description' => '인정, 성취, 노력의 결과가 드러나는 흐름을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 29,
            'name' => 'Seven of Wands',
            'name_ko' => '완드 7',
            'description' => '방어, 입장 지키기, 압박 속에서도 기준을 세우는 태도를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 30,
            'name' => 'Eight of Wands',
            'name_ko' => '완드 8',
            'description' => '빠른 전개, 소식, 상황이 속도를 내는 흐름을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 31,
            'name' => 'Nine of Wands',
            'name_ko' => '완드 9',
            'description' => '경계심, 버티는 힘, 마지막 고비를 넘기기 위한 인내를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 32,
            'name' => 'Ten of Wands',
            'name_ko' => '완드 10',
            'description' => '부담, 책임 과중, 짐을 나누거나 정리할 필요를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 33,
            'name' => 'Page of Wands',
            'name_ko' => '완드 시종',
            'description' => '호기심, 새로운 시도, 가능성을 향한 가벼운 첫걸음을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 34,
            'name' => 'Knight of Wands',
            'name_ko' => '완드 기사',
            'description' => '열정적인 행동, 빠른 추진, 때로는 성급함을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 35,
            'name' => 'Queen of Wands',
            'name_ko' => '완드 여왕',
            'description' => '자신감, 매력, 따뜻하지만 주도적인 에너지를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 36,
            'name' => 'King of Wands',
            'name_ko' => '완드 왕',
            'description' => '리더십, 비전, 큰 방향을 잡고 이끄는 힘을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'wands',
        ],
        [
            'id' => 37,
            'name' => 'Ace of Cups',
            'name_ko' => '컵 에이스',
            'description' => '새로운 감정, 호감, 마음이 열리는 시작을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 38,
            'name' => 'Two of Cups',
            'name_ko' => '컵 2',
            'description' => '교감, 협력, 서로 마음이 맞는 관계를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 39,
            'name' => 'Three of Cups',
            'name_ko' => '컵 3',
            'description' => '기쁨, 우정, 함께 나누는 축하와 지지를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 40,
            'name' => 'Four of Cups',
            'name_ko' => '컵 4',
            'description' => '권태, 망설임, 이미 있는 기회를 다시 살펴볼 필요를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 41,
            'name' => 'Five of Cups',
            'name_ko' => '컵 5',
            'description' => '실망, 아쉬움, 잃은 것보다 남은 것을 보는 태도를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 42,
            'name' => 'Six of Cups',
            'name_ko' => '컵 6',
            'description' => '추억, 순수한 마음, 과거의 경험에서 오는 따뜻함을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 43,
            'name' => 'Seven of Cups',
            'name_ko' => '컵 7',
            'description' => '상상, 여러 선택지, 환상과 현실을 구분할 필요를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 44,
            'name' => 'Eight of Cups',
            'name_ko' => '컵 8',
            'description' => '떠남, 정서적 정리, 더 맞는 길을 찾기 위한 선택을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 45,
            'name' => 'Nine of Cups',
            'name_ko' => '컵 9',
            'description' => '만족, 소원, 스스로 느끼는 정서적 충만함을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 46,
            'name' => 'Ten of Cups',
            'name_ko' => '컵 10',
            'description' => '화목, 정서적 안정, 함께 느끼는 행복을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 47,
            'name' => 'Page of Cups',
            'name_ko' => '컵 시종',
            'description' => '섬세한 감정, 새로운 호감, 부드러운 메시지를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 48,
            'name' => 'Knight of Cups',
            'name_ko' => '컵 기사',
            'description' => '로맨틱한 제안, 감성적 접근, 마음을 따라 움직이는 태도를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 49,
            'name' => 'Queen of Cups',
            'name_ko' => '컵 여왕',
            'description' => '공감, 배려, 깊은 감정 이해와 돌봄을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 50,
            'name' => 'King of Cups',
            'name_ko' => '컵 왕',
            'description' => '감정의 성숙, 안정된 마음, 차분한 포용력을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'cups',
        ],
        [
            'id' => 51,
            'name' => 'Ace of Swords',
            'name_ko' => '소드 에이스',
            'description' => '명확한 판단, 진실, 새로운 생각의 시작을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 52,
            'name' => 'Two of Swords',
            'name_ko' => '소드 2',
            'description' => '결정 보류, 균형 잡힌 고민, 마음과 이성의 갈등을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 53,
            'name' => 'Three of Swords',
            'name_ko' => '소드 3',
            'description' => '상처, 실망, 아픈 진실을 마주하는 과정을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 54,
            'name' => 'Four of Swords',
            'name_ko' => '소드 4',
            'description' => '휴식, 회복, 생각을 멈추고 정리하는 시간을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 55,
            'name' => 'Five of Swords',
            'name_ko' => '소드 5',
            'description' => '갈등, 승패에 대한 집착, 관계 속 손익을 돌아볼 필요를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 56,
            'name' => 'Six of Swords',
            'name_ko' => '소드 6',
            'description' => '이동, 회복 과정, 어려움에서 조금씩 벗어나는 흐름을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 57,
            'name' => 'Seven of Swords',
            'name_ko' => '소드 7',
            'description' => '전략, 숨겨진 의도, 신중하게 상황을 살펴야 함을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 58,
            'name' => 'Eight of Swords',
            'name_ko' => '소드 8',
            'description' => '제한감, 두려움, 스스로 만든 생각의 틀을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 59,
            'name' => 'Nine of Swords',
            'name_ko' => '소드 9',
            'description' => '걱정, 불안, 생각이 커져 잠시 멈춤이 필요한 상태를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 60,
            'name' => 'Ten of Swords',
            'name_ko' => '소드 10',
            'description' => '끝, 피로한 상황의 종료, 다시 일어설 준비를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 61,
            'name' => 'Page of Swords',
            'name_ko' => '소드 시종',
            'description' => '관찰, 호기심, 정보를 모으고 배우는 태도를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 62,
            'name' => 'Knight of Swords',
            'name_ko' => '소드 기사',
            'description' => '빠른 판단, 직진하는 태도, 말과 행동의 속도를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 63,
            'name' => 'Queen of Swords',
            'name_ko' => '소드 여왕',
            'description' => '명료함, 독립성, 감정보다 사실을 보는 힘을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 64,
            'name' => 'King of Swords',
            'name_ko' => '소드 왕',
            'description' => '판단력, 원칙, 냉정하고 책임 있는 결정을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'swords',
        ],
        [
            'id' => 65,
            'name' => 'Ace of Pentacles',
            'name_ko' => '펜타클 에이스',
            'description' => '현실적 기회, 새로운 자원, 안정적인 시작을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 66,
            'name' => 'Two of Pentacles',
            'name_ko' => '펜타클 2',
            'description' => '균형, 일정과 자원의 조율, 유연한 대응을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 67,
            'name' => 'Three of Pentacles',
            'name_ko' => '펜타클 3',
            'description' => '협업, 실력 인정, 함께 결과물을 만드는 과정을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 68,
            'name' => 'Four of Pentacles',
            'name_ko' => '펜타클 4',
            'description' => '보유, 안정 추구, 놓지 못하는 마음을 돌아볼 필요를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 69,
            'name' => 'Five of Pentacles',
            'name_ko' => '펜타클 5',
            'description' => '부족함, 소외감, 도움을 요청할 필요를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 70,
            'name' => 'Six of Pentacles',
            'name_ko' => '펜타클 6',
            'description' => '나눔, 도움, 주고받는 균형을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 71,
            'name' => 'Seven of Pentacles',
            'name_ko' => '펜타클 7',
            'description' => '기다림, 중간 점검, 꾸준한 노력의 결과를 살피는 시기를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 72,
            'name' => 'Eight of Pentacles',
            'name_ko' => '펜타클 8',
            'description' => '연습, 숙련, 반복을 통해 실력을 쌓는 과정을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 73,
            'name' => 'Nine of Pentacles',
            'name_ko' => '펜타클 9',
            'description' => '자립, 여유, 스스로 만든 안정과 성취를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 74,
            'name' => 'Ten of Pentacles',
            'name_ko' => '펜타클 10',
            'description' => '장기적 안정, 가족과 공동체, 축적된 기반을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 75,
            'name' => 'Page of Pentacles',
            'name_ko' => '펜타클 시종',
            'description' => '배움, 현실적인 시작, 작은 가능성을 꾸준히 키우는 태도를 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 76,
            'name' => 'Knight of Pentacles',
            'name_ko' => '펜타클 기사',
            'description' => '성실함, 책임감, 느리지만 꾸준한 진행을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 77,
            'name' => 'Queen of Pentacles',
            'name_ko' => '펜타클 여왕',
            'description' => '돌봄, 실용성, 현실을 안정적으로 가꾸는 힘을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
        [
            'id' => 78,
            'name' => 'King of Pentacles',
            'name_ko' => '펜타클 왕',
            'description' => '안정, 성취, 현실적인 책임과 관리 능력을 의미합니다.',
            'arcana' => 'minor',
            'suit' => 'pentacles',
        ],
    ];
}

function draw_three_cards(?PDO $db = null): array
{
    /*
     * 현재 정책: 카드 원본은 PHP 하드코딩 배열을 사용합니다.
     * 추후 tarot_cards 테이블을 사용하게 되면 아래 한 줄을
     * return draw_three_cards_from_db($db); 형태로 전환하면 됩니다.
     */
    return draw_three_cards_from_hardcoded();
}

function draw_three_cards_from_hardcoded(): array
{
    return pick_three_cards(get_hardcoded_tarot_cards());
}

function draw_three_cards_from_db(PDO $db): array
{
    /*
     * TODO: 추후 tarot_cards 테이블을 실제 카드 원본으로 사용할 때 활성화합니다.
     * 예상 테이블 필드: id, name, name_ko, description, arcana, suit
     */
    $stmt = $db->query(
        'SELECT id, name, name_ko, description, arcana, suit
         FROM tarot_cards
         ORDER BY RAND()
         LIMIT 3'
    );

    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return add_card_positions($cards);
}

function pick_three_cards(array $deck): array
{
    $cards = array_values($deck);

    if (count($cards) < 3) {
        throw new RuntimeException('타로 카드는 최소 3장 이상 필요합니다.');
    }

    shuffle($cards);

    return add_card_positions(array_slice($cards, 0, 3));
}

function add_card_positions(array $cards): array
{
    if (count($cards) < 3) {
        throw new RuntimeException('선택된 타로 카드는 최소 3장 이상 필요합니다.');
    }

    $positions = ['현재 상황', '흐름', '조언'];
    $result = [];

    foreach (array_slice(array_values($cards), 0, 3) as $index => $card) {
        $card['position'] = $positions[$index];
        $result[] = $card;
    }

    return $result;
}
