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

  :root {
    --primary-color: rgb(184, 38, 94);
    --primary-color-pestana: rgba(184, 38, 94, .8);
    --meta: rgb(127, 140, 170);
  }

  html,
  body {
    font-family: "Poppins", sans-serif;
    padding: 10px;
    font-size: 14px;
    width: 100%;
    height: auto;
    background: #f1f2f5;
    color: var(--meta);
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
    color: var(--primary-color);
    font-weight: bolder;
    position: relative;
    background: #f9f9f9;
    padding: 2px 4px;
    border-radius: 8px;
    line-height: 1;
  }

  .fret,
  #meta-tom {
    color: var(--primary-color);
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
    font-size: 12px;
    color: var(--meta);
    text-transform: uppercase;
    letter-spacing: 1.1px;
  }

  .meta-info {
    background: rgba(241, 242, 245, 0.3);
    padding: 2px 5px;
    border-radius: 6px;
    margin: 2px;
  }

  .meta-info:first-child {
    margin-left: 0px;
  }

  .musicTitle {
    font-size: 28px;
    margin-top: 10px;
    margin-bottom: -10px;
  }

  .artistaSubTitle {
    font-size: 18px;
  }

  .chord-diagram {
    border: 0 !important;
  }

  .chord-diagram svg {
    width: 70px;
    height: 80px;
    border: 0 !important;
  }

  .chord-diagram svg line {
    stroke: #121214;
  }

  .chord-diagram svg text {
    fill: #121214;
  }

  .chord-diagram svg circle {
    fill: var(--primary-color);
  }

  .svg {
    width: 100%;
    height: auto;
    max-width: 80px;
    float: left;
  }

  .title {
    font: 600 16px sans-serif;
    fill: var(--meta);
    text-anchor: middle;
  }

  .dot {
    fill: var(--primary-color);
  }

  .barre {
    fill: var(--primary-color-pestana);
  }

  .ox {
    font: 500 9px sans-serif;
    fill: var(--meta);
    text-anchor: middle;
  }

  .base {
    font: 400 12px sans-serif;
    fill: var(--meta);
    text-anchor: end;
  }
</style>

<main class="container">
  <div class="metadata">
    <span class="meta-info"><strong>Tom:</strong> <span id="meta-tom"><?= $info['tom'] ?></span></span>
    <span class="meta-info"><strong>BPM:</strong> <?= $info['bpm'] ?></span>
    <span class="meta-info"><strong>Afinação:</strong> <?= $info['afinacao'] ?></span>
  </div>

  <h1 class="musicTitle"><?= str_replace(": ", "", $info['titulo']) ?></h1>
  <p class="artistaSubTitle"><?= $info['artista'] ?></p>

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
      <?php
      for ($i = 1; $i < 13; $i++) {
        if ($info['capo'] == $i) {
          $sel = 'selected';
        } else {
          $sel = '';
        }
        echo '<option value="' . $i . '" ' . $sel . '>' . $i . 'ª Casa</option>';
      }
      ?>
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

  <div id="chord-dictionary" style="margin-top: 2em; padding-top: 1em; border-top: 1px solid #ccc;">
    <!-- <h2>Dicionário de Acordes</h2> -->
    <div id="dictionary-content" style="display: flex; flex-wrap: wrap; gap: 12px;"></div>
  </div>

</main>

<script src="<?= BASE_URL ?>/js/script.js"></script>
</body>

</html>