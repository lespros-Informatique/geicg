$path = 'C:\Users\USER\.gemini\antigravity-ide\brain\6c2da438-01be-447e-89c9-47c05e653c18\.system_generated\logs\transcript.jsonl'
$lines = [System.IO.File]::ReadAllLines($path)
for ($i = 0; $i -lt $lines.Length; $i++) {
    $line = $lines[$i]
    if ($line.Contains('"step_index":1438') -or $line.Contains('"step_index":1505')) {
        Write-Output "Line $i has step"
    }
}
$start = [Math]::Max(0, $lines.Length - 150)
for ($i = 1440; $i -lt [Math]::Min($lines.Length, 1515); $i++) {
    $data = $lines[$i] | ConvertFrom-Json
    if ($data.type -eq 'PLANNER_RESPONSE' -and $data.content) {
        Write-Output ("=== Line $i Step " + $data.step_index + " ===")
        Write-Output $data.content
    }
}
