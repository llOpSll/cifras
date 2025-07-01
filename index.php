<?php
// Definindo a BASE_URL
define('BASE_URL', 'http://192.168.0.70/cifras/');
define('ROOT_URL', __DIR__);

// Função para gerar o header dinâmico
function generateHeader($pageData = [])
{
  $defaults = [
    'title' => 'Cifras',
    'description' => 'Bem-vindo ao Cifras, um site para o ministério de Louvor',
    'keywords' => 'cifras, música, louvor, adoracao',
    'og_image' => BASE_URL . 'assets/images/default-og-image.jpg',
    'canonical' => BASE_URL . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')
  ];
  $pageData = array_merge($defaults, $pageData);
?>
  <!DOCTYPE html>
  <html lang="pt-BR">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageData['title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($pageData['description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($pageData['keywords']); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($pageData['canonical']); ?>">
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($pageData['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageData['description']); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($pageData['canonical']); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($pageData['og_image']); ?>">
    <meta property="og:type" content="website">
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageData['title']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageData['description']); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($pageData['og_image']); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
  </head>

  <body>
    <header class="container">
      <div class="logo">
        <a href="<?php echo BASE_URL; ?>"><img src="<?php echo BASE_URL; ?>/img/cifras-app.png"
            style="max-width: 250px;"></a>
      </div>

      <a href="" class="menuItem repo"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          class="lucide lucide-list sm:w-4 sm:h-4">
          <path d="M3 12h.01"></path>
          <path d="M3 18h.01"></path>
          <path d="M3 6h.01"></path>
          <path d="M8 12h13"></path>
          <path d="M8 18h13"></path>
          <path d="M8 6h13"></path>
        </svg> Repertórios</a>&nbsp;
      <a href="" class="menuItem cifra">+ Nova Cifra</a>
    </header>
  <?php
}

// Função de roteamento
function route($url)
{
  // Remove a BASE_URL da URL solicitada
  $path = parse_url($url, PHP_URL_PATH);
  $path = str_replace('/cifras/', '', $path);
  $path = trim($path, '/');

  // Divide a URL em segmentos
  $segments = $path ? explode('/', $path) : [];
  $params = [];

  // Define o caminho base para a pasta de páginas
  $base_path = 'pages/';
  $file_path = $base_path;

  // Se a URL for vazia, carrega a página inicial
  if (empty($segments) || $segments[0] === '') {
    $file_path = $base_path . 'home.php';
  } else {
    // Tenta encontrar o arquivo correspondente
    $current_path = $base_path;
    foreach ($segments as $index => $segment) {
      if (file_exists($current_path . $segment . '.php')) {
        $file_path = $current_path . $segment . '.php';
        $params = array_slice($segments, $index + 1);
        break;
      } elseif (is_dir($current_path . $segment)) {
        $current_path .= $segment . '/';
        // Verifica se há um index.php no diretório
        if (file_exists($current_path . 'index.php')) {
          $file_path = $current_path . 'index.php';
          $params = array_slice($segments, $index + 1);
          break;
        }
      } else {
        $params[] = $segment;
      }
    }
  }

  // Verifica se o arquivo existe
  if (file_exists($file_path)) {
    global $url_params;
    $url_params = $params;
    require $file_path;
  } else {
    http_response_code(404);
    require $base_path . '404.php';
  }
}

// Captura a URL atual
$current_url = $_SERVER['REQUEST_URI'];
route($current_url);
?>

<footer>© 2025 CifrasApp · Feito com amor 🎸</footer>