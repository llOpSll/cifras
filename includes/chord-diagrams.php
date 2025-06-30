<?php
header('Content-Type: application/json');
require_once '../includes/functions.php';

function normalizeChordName(string $chord): string {
    // Se tem baixo: ex G/B
    $parts = explode('/', $chord);
    $baseChordRaw = $parts[0];
    $bassNoteRaw = $parts[1] ?? null; // para exibir, se precisar

    $baseChordRaw = trim($baseChordRaw);

    // Remove espaços e padroniza
    $baseChordRaw = str_replace(' ', '', $baseChordRaw);

    // Isola raiz A-G + # ou b
    preg_match('/^[A-G](#|b)?/i', $baseChordRaw, $matches);
    $root = $matches[0] ?? '';
    $suffix = substr($baseChordRaw, strlen($root));

    // Normaliza a raiz maiúscula + sustenido/ bemol minúsculo
    $root = strtoupper($root[0]) . (isset($root[1]) ? strtolower($root[1]) : '');

    // Normalize extensões comuns e parênteses
    // Remover parênteses envolvendo números ou termos
    $suffix = preg_replace('/[\(\)]/', '', $suffix);

    // Mapas para substituir extensões (mais detalhado para casos com parênteses removidos)
    $map = [
        '7M' => 'maj7',
        'M7' => 'maj7',
        '79' => '79',       // ex: Am7(9) vira Am79
        '11' => '11',
        '13' => '13',
        '46' => '6add11',   // ex: D4(6) vira D46 -> D6add11
        '69' => '6add9',    // ex: 6(9)
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

    // Substituir extensões conforme mapa
    foreach ($map as $pattern => $replacement) {
        if (stripos($suffix, $pattern) !== false) {
            $suffix = preg_replace('/' . preg_quote($pattern, '/') . '/i', $replacement, $suffix);
        }
    }

    return $root . $suffix;
}

function renderChordDiagramWithBass($chordFullName, $json) {
    // Se tem baixo (ex G/B), separar para normalizar o acorde base
    $parts = explode('/', $chordFullName);
    $baseChordRaw = $parts[0];
    $bassNote = $parts[1] ?? null;

    $normBaseChord = normalizeChordName($baseChordRaw);

    // Tenta buscar no JSON o acorde base
    if (isset($json[$normBaseChord]) && isset($json[$normBaseChord][0]['positions'])) {
        $positions = $json[$normBaseChord][0]['positions'];
        $diagram = implode('', $positions);
        // Exibir o nome completo com baixo na legenda
        $displayName = htmlspecialchars($chordFullName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return renderChordDiagram($displayName, $diagram);
    }

    // fallback: acorde não encontrado
    return "<div class='chord-diagram' style='margin:0 1em 1em 0; display:inline-block; font-family:monospace; color:#121214;'>
        <strong>" . htmlspecialchars($chordFullName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</strong><br>
        <span style='font-size:12px;'>Acorde não encontrado</span>
    </div>";
}

// No seu getChordDiagram.php, ao invés de procurar direto, use:

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
    $html .= renderChordDiagramWithBass($chord, $json);
}

$html .= '</div>';

echo json_encode(['html' => $html]);
