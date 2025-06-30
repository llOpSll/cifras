<?php
// functions.php
function isChordToken(string $token): bool
{
  // Remove inversão temporariamente (ex: C/E → C)
  $parts = explode('/', $token);
  $base = $parts[0];

  // Regex mais permissivo e robusto
  return preg_match('/^[A-G](#|b)?[a-zA-Z0-9()#b+º\-]*$/', $base);
}


function isTabLine(string $line): bool
{
  return preg_match('/^[EADGBe]\|/', trim($line)) === 1;
}

function highlightTablatureLine(string $line): string
{
  return preg_replace_callback(
    '/(\d+)/',
    fn($matches) => "<span class=\"fret\" data-fret=\"{$matches[1]}\">{$matches[1]}</span>",
    htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
  );
}

function highlightChordsInLine(string $line): string
{
  $parts = preg_split('/(\s+)/', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
  foreach ($parts as &$part) {
    $token = trim($part);
    if (isChordToken($token)) {
      $escaped = htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      $part = str_replace($token, "<span class=\"chord\"><strong>{$escaped}</strong></span>", $part);
    } else {
      // mantém o token como está, sem escapar
    }
  }
  return implode('', $parts);
}

function parseCifraText(string $rawText): string
{
  $lines = explode("\n", $rawText);
  $output = [];

  foreach ($lines as $line) {
    if (isTabLine($line)) {
      $output[] = highlightTablatureLine($line);
    } else {
      $tokens = preg_split('/\s+/', trim($line));
      $chordCount = 0;
      $tokenCount = 0;
      foreach ($tokens as $t) {
        if ($t !== '') {
          $tokenCount++;
          if (isChordToken($t)) {
            $chordCount++;
          }
        }
      }
      if ($tokenCount > 0 && $chordCount / $tokenCount >= 0.5) {
        $output[] = highlightChordsInLine($line);
      } else {
        $output[] = htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      }
    }
  }

  return implode("\n", $output);
}

function extractCifraMetadata(string $rawText): array
{
  $lines = explode("\n", $rawText);
  $metadata = [
    'titulo'   => '',
    'artista'  => '',
    'tom'      => '',
    'bpm'      => '',
    'afinacao' => '',
  ];
  $bodyLines = [];

  foreach ($lines as $line) {
    $trim = trim($line);
    if (stripos($trim, 'Título:') === 0) {
      $metadata['titulo'] = trim(substr($trim, 7));
    } elseif (stripos($trim, 'Artista:') === 0) {
      $metadata['artista'] = trim(substr($trim, 8));
    } elseif (stripos($trim, 'Tom:') === 0) {
      $metadata['tom'] = trim(substr($trim, 4));
    } elseif (stripos($trim, 'BPM:') === 0) {
      $metadata['bpm'] = trim(substr($trim, 4));
    } elseif (stripos($trim, 'Afinação:') === 0 || stripos($trim, 'Afinacao:') === 0) {
      $metadata['afinacao'] = trim(substr($trim, 11));
    } elseif (stripos($trim, 'Capotraste:') === 0) {
      $metadata['capo'] = trim(substr($trim, 11));
    } elseif ($trim === '' || preg_match('/^\[.+\]/', $trim)) {
      $bodyLines[] = $line;
    } else {
      $bodyLines[] = $line;
    }
  }

  return [
    'metadata' => $metadata,
    'body' => implode("\n", $bodyLines),
  ];
}

function getUniqueChordsFromHtml($html) {
    $unique = [];
    // Regex para pegar acordes dentro de tags com class contendo "chord"
    preg_match_all('/<[^>]*class=["\']?[^"\'>]*chord[^"\'>]*["\']?[^>]*>(.*?)<\/[^>]+>/i', $html, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $chord) {
            $chord = trim(strip_tags($chord)); // tira tags internas se houver
            if ($chord !== '') {
                $unique[$chord] = true;
            }
        }
    }
    $chords = array_keys($unique);
    sort($chords);
    return $chords;
}

function renderChordDiagram($chordName, $diagram) {
    // diagram é uma string tipo x32010 (6 cordas, x=muda, números=casas)
    // vamos desenhar 6 cordas e 5 casas com marcação na casa correta

    $strings = str_split($diagram);
    $svg = '<div class="chord-diagram" style="display:inline-block; margin:0 1em 1em 0; font-family: monospace; text-align:center;">';
    $svg .= "<div style='font-weight:bold; color:#b8265e;'>{$chordName}</div>";
    $svg .= '<svg width="60" height="80" xmlns="http://www.w3.org/2000/svg" style="background:#fff; border:1px solid #b8265e; border-radius:8px;">';

    // linhas verticais (cordas)
    for ($i = 0; $i < 6; $i++) {
        $x = 10 + $i * 10;
        $svg .= "<line x1='{$x}' y1='10' x2='{$x}' y2='70' stroke='#b8265e' stroke-width='1'/>";
    }

    // linhas horizontais (casas)
    for ($j = 0; $j < 6; $j++) {
        $y = 10 + $j * 12;
        $strokeWidth = ($j === 0 && strpos($diagram, '1') !== false) ? 4 : 1; // 1ª casa - pestana (se houver)
        $svg .= "<line x1='10' y1='{$y}' x2='60' y2='{$y}' stroke='#b8265e' stroke-width='{$strokeWidth}'/>";
    }

    // desenhar marcas das casas (bolinhas) onde for número > 0
    for ($i = 0; $i < 6; $i++) {
        $fret = $strings[$i];
        if (is_numeric($fret) && intval($fret) > 0) {
            $x = 10 + $i * 10;
            $y = 10 + intval($fret) * 12 - 6;
            $svg .= "<circle cx='{$x}' cy='{$y}' r='4' fill='#b8265e'/>";
        }
        else if ($fret === 'x' || $fret === 'X') {
            // marcar corda muda com X acima das cordas
            $x = 10 + $i * 10;
            $svg .= "<text x='{$x}' y='8' font-size='10' text-anchor='middle' fill='#b8265e'>x</text>";
        }
        else if ($fret === '0') {
            // corda solta (marcar com O acima)
            $x = 10 + $i * 10;
            $svg .= "<text x='{$x}' y='8' font-size='10' text-anchor='middle' fill='#b8265e'>o</text>";
        }
    }

    $svg .= '</svg></div>';

    return $svg;
}
