<?php
function listTree($dir, $level = 0)
{
  if (!is_dir($dir)) {
    echo "O caminho fornecido não é um diretório válido.";
    return;
  }

  $files = scandir($dir);
  foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;

    echo str_repeat('  ', $level) . '|-- ' . $file . PHP_EOL;

    $path = $dir . DIRECTORY_SEPARATOR . $file;
    if (is_dir($path)) {
      listTree($path, $level + 1);
    }
  }
}

// Defina aqui o caminho raiz do seu projeto
$rootDir = __DIR__; // pasta atual, pode alterar para o caminho que preferir

header('Content-Type: text/plain; charset=utf-8');
listTree($rootDir);
