<?php
header('Content-Type: application/json');
require_once '../includes/functions.php';

function normalizeChordName(string $chord): string
{
  // if (strpos($chord, '/') !== false) {
  //   $chord = explode('/', $chord)[0];
  // }
  $chord = trim($chord);
  $chord = str_replace(' ', '', $chord);
  preg_match('/^[A-G](#|b)?/i', $chord, $matches);
  $root = $matches[0] ?? '';
  $suffix = substr($chord, strlen($root));
  $root = strtoupper($root[0]) . (isset($root[1]) ? strtolower($root[1]) : '');

  $map = [
    '7M' => 'maj7',
    'M7' => 'maj7',
    '(9)' => '9',
    '(11)' => '11',
    '(13)' => '13',
    '4(6)' => '6add11',
    '6(9)' => '6add9',
    'sus4' => 'sus4',
    'sus2' => 'sus2',
    'sus'  => 'sus4',
    'm7' => 'm7',
    'm9' => 'm9',
    'm' => 'm',
    'min' => 'm',
    'dim' => 'dim',
    '°' => 'dim',
    'º' => 'dim',
    '+' => 'aug',
    'aug' => 'aug',
    'add9' => 'add9',
    'add11' => 'add11',
  ];

  foreach ($map as $pattern => $replacement) {
    if (stripos($suffix, $pattern) !== false) {
      $suffix = preg_replace('/' . preg_quote($pattern, '/') . '/i', $replacement, $suffix);
    }
  }

  return $root . $suffix;
}

$input = file_get_contents('php://input');
$chords = json_decode($input, true);

if (!is_array($chords)) {
  echo json_encode(['html' => '<p>Entrada inválida</p>']);
  exit;
}

$jsonPath = __DIR__ . '/../includes/chords.complete.json';
if (!file_exists($jsonPath)) {
  echo json_encode(['html' => '<p>Arquivo de acordes não encontrado.</p>']);
  exit;
}

$json = json_decode(file_get_contents($jsonPath), true);
if (!$json) {
  echo json_encode(['html' => '<p>Erro ao carregar JSON de acordes.</p>']);
  exit;
}

$html = '<div id="dictionary-diagrams">';

foreach ($chords as $chord) {
  $norm = normalizeChordName($chord);

  if (isset($json[$norm]) && isset($json[$norm][0]['positions'])) {
    $positions = $json[$norm][0]['positions'];
    $diagram = implode('', $positions);
    $html .= renderChordDiagram($chord, $diagram);
  } else {
    $html .= "<div class='chord-diagram' style='margin:0 1em 1em 0; display:inline-block; font-family:monospace; color:#121214;'>
            <strong>{$chord}</strong><br>
            <span style='font-size:12px;'>Acorde não encontrado</span>
        </div>";
  }
}

$html .= '</div>';

echo json_encode(['html' => $html]);
