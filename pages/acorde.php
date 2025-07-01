<?php
// acorde.php
// Exibe todas as variações de um acorde, destacando a mais confortávele espaçando o título do diagrama

function splitChordName(string $name): array
{
  if (strlen($name) > 1 && ($name[1] === '#' || $name[1] === 'b')) {
    $root = substr($name, 0, 2);
    $suffix = substr($name, 2);
  } else {
    $root = substr($name, 0, 1);
    $suffix = substr($name, 1);
  }
  return [$root, $suffix];
}

function convertRoot(string $r): string
{
  $map = ['C#' => 'Csharp', 'D#' => 'Dsharp', 'F#' => 'Fsharp', 'G#' => 'Gsharp', 'A#' => 'Asharp'];
  return $map[$r] ?? $r;
}

function normalizeSuffix(string $s): string
{
  if ($s === '7M') {
    $s = 'maj7';
  }
  $m = [
    '' => 'major',
    'm' => 'minor',
    'min' => 'minor',
    'maj' => 'major',
    '7' => '7',
    'm7' => 'm7',
    'maj7' => 'maj7',
    '5' => '5',
    '6' => '6',
    '9' => '9',
    '11' => '11',
    '13' => '13',
    'dim' => 'dim',
    'dim7' => 'dim7',
    'sus' => 'sus',
    'sus2' => 'sus2',
    'sus4' => 'sus4',
    '7sus4' => '7sus4',
    'aug' => 'aug',
    'aug7' => 'aug7',
    'alt' => 'alt',
    'add9' => 'add9',
    'add11' => 'add11',
  ];
  $key = strtolower($s);
  return $m[$key] ?? $s;
}

function getVariations(array $data, string $root, string $suffix): array
{
  $equiv = ['C#' => 'Db', 'Db' => 'C#', 'D#' => 'Eb', 'Eb' => 'D#', 'F#' => 'Gb', 'Gb' => 'F#', 'G#' => 'Ab', 'Ab' => 'G#', 'A#' => 'Bb', 'Bb' => 'A#'];
  $r = convertRoot($root);
  $suf = normalizeSuffix($suffix);
  $candidates = [$r];
  if (isset($equiv[$root])) $candidates[] = convertRoot($equiv[$root]);

  $results = [];
  foreach ($candidates as $rk) {
    if (!isset($data['chords'][$rk])) continue;
    foreach ($data['chords'][$rk] as $ch) {
      if (strtolower($ch['suffix']) === strtolower($suf)) {
        foreach ($ch['positions'] as $pos) {
          $results[] = ['key' => $ch['key'], 'suffix' => $ch['suffix'], 'pos' => $pos];
        }
      }
    }
  }
  return $results;
}

function scoreVariation(array $pos): float
{
  $frets = $pos['frets'];
  $played = array_filter($frets, fn($f) => $f !== -1);
  $min = min($played);
  $max = max($played);
  $span = $max - $min;
  $barres = count($pos['barres']);
  $fingers = count(array_filter($pos['fingers'], fn($f) => $f > 0));
  $opens = count(array_filter($frets, fn($f) => $f === 0));
  return $barres * 5 + $span * 2 + max(0, $fingers - 4) * 3 - $opens * 0.5 + ($min - 1);
}

function findBestIndex(array $variations): int
{
  $best = 0;
  $bestScore = INF;
  foreach ($variations as $i => $v) {
    $score = scoreVariation($v['pos']);
    if ($score < $bestScore) {
      $bestScore = $score;
      $best = $i;
    }
  }
  return $best;
}

function renderDiagram(array $item, bool $highlight = false): void
{
  switch ($item['suffix']) {
    case 'minor':
      $item['suffix'] = 'm';
      break;
    case 'major':
      $item['suffix'] = '';
      break;
    case 'maj7':
      $item['suffix'] = '7M';
      break;
  }

  $key = $item['key'] . $item['suffix'];
  $pos = $item['pos'];
  $frets = $pos['frets'];
  $barres = $pos['barres'] ?? [];
  $base = $pos['baseFret'];

  $w = 120;
  $h = 180;
  $titleSpace = 50;
  $pad = 20;
  $strings = 6;
  $rows = 4;
  $dx = ($w - 2 * $pad) / ($strings - 1);
  $dy = ($h - $titleSpace - $pad) / $rows;

  echo "<div class='card" . ($highlight ? " highlight" : "") . "'>";
  echo "<svg viewBox='0 0 {$w} {$h}' class='svg'>";
  // Title
  echo "<text x='" . ($w / 2) . "' y='24' class='title'>" . htmlspecialchars($key) . "</text>";
  // Frets
  for ($i = 0; $i <= $rows; $i++) {
    $y = $titleSpace + $i * $dy;
    $sw = ($i === 0 && $base === 1) ? 4 : 2;
    echo "<line x1='{$pad}' y1='{$y}' x2='" . ($w - $pad) . "' y2='{$y}' stroke='#333' stroke-width='{$sw}'/>";
  }
  // Strings
  for ($i = 0; $i < $strings; $i++) {
    $x = $pad + $i * $dx;
    echo "<line x1='{$x}' y1='{$titleSpace}' x2='{$x}' y2='" . ($h - $pad) . "' stroke='#333' stroke-width='1'/>";
  }
  // X/O & dots
  for ($i = 0; $i < $strings; $i++) {
    $x = $pad + $i * $dx;
    $f = $frets[$i];
    if ($f === -1) echo "<text x='{$x}' y='" . ($titleSpace - 10) . "' class='ox'>X</text>";
    elseif ($f === 0) echo "<text x='{$x}' y='" . ($titleSpace - 10) . "' class='ox'>O</text>";
    elseif ($f > 0) {
      $y = $titleSpace + ($f - 0.5) * $dy;
      echo "<circle cx='{$x}' cy='{$y}' r='6' class='dot'/>";
    }
  }
  // Barres
  foreach ($barres as $b) {
    $y = $titleSpace + ($b - 0.5) * $dy;
    echo "<rect x='{$pad}' y='" . ($y - 4) . "' width='" . ($w - 2 * $pad) . "' height='8' rx='4' class='barre'/>";
  }
  // Base
  if ($base > 1) echo "<text x='" . ($w - 5) . "' y='" . ($titleSpace + $dy / 2) . "' class='base'>{$base}</text>";
  echo "</svg></div>";
}

// Main
$n = $_GET['chord'] ?? '';
if (!$n) exit('Informe chord=');
$file = __DIR__ . '/../includes/guitar.json';
if (!file_exists($file)) exit('JSON missing');
$data = json_decode(file_get_contents($file), true);
if (!isset($data['chords'])) exit('JSON err');
list($r, $s) = splitChordName($n);
$vars = getVariations($data, $r, $s);
if (empty($vars)) exit();
$best = findBestIndex($vars);
?>

<?php foreach ($vars as $i => $v) {
  renderDiagram($v, $i === $best);
} ?>