<?php
$pageData = [
  'title' => 'Home | Cifras',
  'description' => 'Página inicial do Cifras, com conteúdo otimizado para SEO.',
  'keywords' => 'home, cifras, música, seo',
  'og_image' => 'http://192.168.0.70/cifras/assets/images/home-og-image.jpg'
];
generateHeader($pageData);
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
  rel="stylesheet">
<link
  href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
  rel="stylesheet">

<style>
</style>

<main class="container" style="background: transparent; box-shadow: 0 0 0 0 rgba(0,0,0,0); text-align: center;">
  <h1>Lista de Cifras</h1>
  <?php
  $arrFiles = array();
  $handle = opendir(__DIR__ . '/../cifras/');
  if ($handle) {
    while (($entry = readdir($handle)) !== FALSE) {
      $arrFiles[] = $entry;
    }
  }
  closedir($handle);

  $arrFilesNews = array();
  foreach ($arrFiles as $files) {
    if (is_file(__DIR__ . '/../cifras/' . $files)) {
      $arrFilesNews[] = $files;
    }
  }

  sort($arrFilesNews);
  echo '<div class="cifras">';
    foreach ($arrFilesNews as $file) {
      echo '<a href="'.BASE_URL.'ver/'.$file.'">';
        echo '<div class="cifraFile">';
          echo '<h4 style="font-weight: 500">'.substr($file, 0, -4).'</h4>';
        echo '</div>';
      echo '</a>';
    }
  echo '</div>';
  ?>
</main>
</body>

</html>