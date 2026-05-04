<?php
function badge(string $text, string $color = 'blue'): string {
    return '<span class="badge badge-' . $color . '">' . htmlspecialchars($text) . '</span>';
}
function gradeBadge(string $grade): string {
    $color = match(true) {
        in_array($grade, ['A+','A'])  => 'green',
        in_array($grade, ['B+','B']) => 'blue',
        in_array($grade, ['C+','C']) => 'amber',
        default => 'red',
    };
    return badge($grade, $color);
}
function attBadge(string $status): string {
    $color = match($status) { 'Present'=>'green','Absent'=>'red','Late'=>'amber', default=>'blue' };
    return badge($status, $color);
}
function payBadge(string $status): string {
    return badge($status, $status === 'Paid' ? 'green' : 'amber');
}
function progressBar(int $pct): string {
    $color = $pct >= 80 ? '#34d399' : ($pct >= 60 ? '#fbbf24' : '#f87171');
    return '<div class="prog-bg"><div class="prog-fill" style="width:' . $pct . '%;background:' . $color . ';"></div></div>';
}
function avgMarks(array $results): float {
    if (!$results) return 0;
    $sum = array_sum(array_map(fn($r) => ($r['marks_obtained'] / $r['total_marks']) * 100, $results));
    return round($sum / count($results), 1);
}
function fmtDate(?string $dt): string {
    if (!$dt) return '—';
    return date('Y-m-d', strtotime($dt));
}
?>