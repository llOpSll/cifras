<?php
$pageData = [
  'title' => 'Home | Cifras',
  'description' => 'Página inicial do Cifras, com conteúdo otimizado para SEO.',
  'keywords' => 'home, cifras, música, seo',
  'og_image' => 'http://192.168.0.70/cifras/assets/images/home-og-image.jpg'
];
generateHeader($pageData);

include(ROOT_URL . '/includes/functions.php');

$cifra = file_get_contents(ROOT_URL . "/cifras/É Tudo Sobre Você - Morada.txt");
$data = extractCifraMetadata($cifra);
$info = $data['metadata'];
$html = parseCifraText($data['body']);


?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap"
  rel="stylesheet">
<link
  href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
  rel="stylesheet">

<style>
  * {
    margin: 0;
    padding: 0;
    outline: none;
    box-sizing: border-box;
  }

  html,
  body {
    font-family: "Poppins", sans-serif;
    padding: 10px;
    font-size: 14px;
    width: 100%;
    height: auto;
    background: #f1f2f5;
    color: #121214;
  }

  main {
    position: relative;
    width: 100%;
    height: auto;
    overflow: auto;
  }

  pre {
    font-family: "Roboto Mono", monospace;
    line-height: 1.5;
    background: #FFF;
  }

  :-webkit-full-screen pre {
    background: #fff;
  }

  :fullscreen pre {
    background: #fff;
  }

  .container {
    display: block;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    background: #FFF;
    border-radius: 16px;
    padding: 2em;
    box-shadow: 0 2px 10px 0 rgba(0, 0, 0, .05);
  }

  .chord {
    color: rgb(184, 38, 94);
    font-weight: bolder;
    position: relative;
    background: #f9f9f9;
    padding: 2px 4px;
    border-radius: 8px;
    line-height: 1;
  }

  .fret,
  #meta-tom {
    color: rgb(184, 38, 94);
    font-weight: bolder;
    position: relative;
  }

  #transpose-down,
  #transpose-up {
    padding: 10px;
    border-radius: 8px;
    border: none;
    background: #121214;
    color: #FFF;
    cursor: pointer;
  }

  #transpose-reset {
    padding: 10px;
    border-radius: 8px;
    border: none;
    background: #f9f9f9;
    color: #121214;
    font-size: 12px;
    cursor: pointer;
  }

  #capo-select {
    position: relative;
    padding: 10px;
    border-radius: 8px;
    border: none;
    background: #f1f2f5;
    color: #121214;
  }

  .metadata {
    font-size: 13px;
    color: #333;
  }
</style>

<main class="container">
  <h1><?= str_replace(": ", "", $info['titulo']) ?></h1>

  <div class="metadata">
    <strong>Tom:</strong> <span id="meta-tom">D</span> | <strong>Artista:</strong> <?= $info['artista'] ?> | <strong>BPM:</strong> <?= $info['bpm'] ?> | <strong>Afinação:</strong> <?= $info['afinacao'] ?>
  </div>

  <br>

  <div class="transpose-controls">
    <div class="transpose-buttons">
      <span id="current-transpose_">½ Tom</span>
      <button id="transpose-down">-</button>
      <span id="current-transpose" hidden>0 semitons</span>
      <button id="transpose-up">+</button>
      <button id="transpose-reset" title="Reset">Reset</button>
    </div>
    <br>
    <label for="capo-select" class="capo-select">Capotraste:</label>
    <select id="capo-select">
      <option value="0">Sem Capotraste</option>
      <option value="1" <?php if ($info['capo'] == '1') {
                          echo 'selected';
                        } ?>>1ª Casa</option>
      <option value="2" <?php if ($info['capo'] == '2') {
                          echo 'selected';
                        } ?>>2ª Casa</option>
      <option value="3" <?php if ($info['capo'] == '3') {
                          echo 'selected';
                        } ?>>3ª Casa</option>
      <option value="4" <?php if ($info['capo'] == '4') {
                          echo 'selected';
                        } ?>>4ª Casa</option>
      <option value="5" <?php if ($info['capo'] == '5') {
                          echo 'selected';
                        } ?>>5ª Casa</option>
      <option value="6" <?php if ($info['capo'] == '6') {
                          echo 'selected';
                        } ?>>6ª Casa</option>
      <option value="7" <?php if ($info['capo'] == '7') {
                          echo 'selected';
                        } ?>>7ª Casa</option>
      <option value="8" <?php if ($info['capo'] == '8') {
                          echo 'selected';
                        } ?>>8ª Casa</option>
      <option value="9" <?php if ($info['capo'] == '9') {
                          echo 'selected';
                        } ?>>9ª Casa</option>
      <option value="10" <?php if ($info['capo'] == '10') {
                            echo 'selected';
                          } ?>>10ª Casa</option>
      <option value="11" <?php if ($info['capo'] == '11') {
                            echo 'selected';
                          } ?>>11ª Casa</option>
      <option value="12" <?php if ($info['capo'] == '12') {
                            echo 'selected';
                          } ?>>12ª Casa</option>
    </select>

    <div id="font-controls">
      <button id="font-decrease">A-</button>
      <button id="font-increase">A+</button>
      <button id="fullscreen-toggle">⛶</button>
    </div>
  </div>

  <br>

  <pre>
    <?php
    echo ($html);
    ?>
  </pre>
</main>

<script src="<?= BASE_URL ?>/js/script.js"></script>
</body>

</html>