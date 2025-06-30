<?php
// ajax/getChordDiagram.php

header('Content-Type: application/json');
require_once '../includes/functions.php';

// Lê corpo JSON (lista de acordes) ou vazio
$input = file_get_contents('php://input');
$chords = json_decode($input, true);

// Se inválido ou vazio, gerar a sequência padrão C → B
if (!is_array($chords) || empty($chords)) {
  $chords = ['C', 'Cm', 'C7', 'Cmaj7', 'C9',
             'D', 'Dm', 'D7', 'Dmaj7', 'D9',
             'E', 'Em', 'E7', 'Emaj7', 'E9',
             'F', 'Fm', 'F7', 'Fmaj7', 'F9',
             'G', 'Gm', 'G7', 'Gmaj7', 'G9',
             'A', 'Am', 'A7', 'Amaj7', 'A9',
             'B', 'Bm', 'B7', 'Bmaj7', 'B9'];
}

// Mini dicionário — você pode expandir
$dictionary = [
  'C' => 'x32010',
  'Cm' => 'x35543',
  'C7' => 'x32310',
  'Cmaj7' => 'x32000',
  'C9' => 'x3233x',

  'D' => 'xx0232',
  'Dm' => 'xx0231',
  'D7' => 'xx0212',
  'Dmaj7' => 'xx0222',
  'D9' => 'x5455x',

  'E' => '022100',
  'Em' => '022000',
  'E7' => '020100',
  'Emaj7' => '021100',
  'E9' => '020102',

  'F' => '133211',
  'Fm' => '133111',
  'F7' => '131211',
  'Fmaj7' => '133210',
  'F9' => '1x1211',

  'G' => '320003',
  'Gm' => '355333',
  'G7' => '320001',
  'Gmaj7' => '320002',
  'G9' => '3x0201',

  'A' => 'x02220',
  'Am' => 'x02210',
  'A7' => 'x02020',
  'Amaj7' => 'x02120',
  'A9' => 'x02423',

  'B' => 'x24442',
  'Bm' => 'x24432',
  'B7' => 'x21202',
  'Bmaj7' => 'x24342',
  'B9' => 'x21222',
];

$html = '';

foreach ($chords as $chord) {
  $diagram = $dictionary[$chord] ?? null;
  if ($diagram) {
    $html .= renderChordDiagram($chord, $diagram);
  } else {
    $html .= "<div class='chord-diagram' style='margin:0 1em 1em 0; display:inline-block; font-family:monospace; color:#121214;'>
      <strong>{$chord}</strong><br>
      <span style='font-size:12px;'>Acorde não encontrado</span>
    </div>";
  }
}

echo json_encode(['html' => $html]);
