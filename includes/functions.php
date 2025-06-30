<?php

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
