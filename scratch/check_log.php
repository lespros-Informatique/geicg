<?php
$lines = file('C:/Users/USER/.gemini/antigravity-ide/brain/6c2da438-01be-447e-89c9-47c05e653c18/.system_generated/logs/transcript.jsonl');
$total = count($lines);
for ($i = max(0, $total - 30); $i < $total; $i++) {
    $row = json_decode($lines[$i], true);
    if (!empty($row['type']) && in_array($row['type'], ['USER_INPUT', 'PLANNER_RESPONSE'])) {
        echo "=== Step " . ($row['step_index'] ?? $i) . " (" . $row['type'] . ") ===\n";
        if (!empty($row['content'])) {
            echo substr($row['content'], 0, 300) . "\n...\n";
        }
        if (!empty($row['tool_calls'])) {
            foreach ($row['tool_calls'] as $tc) {
                echo "  Tool: " . $tc['name'] . "\n";
            }
        }
    }
}
