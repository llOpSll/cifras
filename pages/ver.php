<?php
$pageData = [
  'title' => 'Home | Cifras',
  'description' => 'Página inicial do Cifras, com conteúdo otimizado para SEO.',
  'keywords' => 'home, cifras, música, seo',
  'og_image' => 'http://192.168.0.70/cifras/assets/images/home-og-image.jpg'
];
generateHeader($pageData);

global $url_params;
$url_params = $params;
$param1 = urldecode($url_params[0]);

include(ROOT_URL . '/includes/functions.php');

$cifra = file_get_contents(ROOT_URL . "/cifras/" . $param1);
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
</style>

<main class="container">
  <div class="metadata">
    <span class="meta-info"><strong>TOM:</strong> <span id="meta-tom"><?= $info['tom'] ?></span></span> •
    <span class="meta-info"><strong>BPM:</strong> <?= $info['bpm'] ?></span> •
    <span class="meta-info"><strong>AFINAÇÃO:</strong> <?= $info['afinacao'] ?></span>
  </div>

  <h1 class="musicTitle"><?= str_replace(": ", "", $info['titulo']) ?></h1>
  <p class="artistaSubTitle"><?= $info['artista'] ?></p>

  <div class="transpose-controls">
    <div class="transpose-buttons">
      <span id="current-transpose_">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          class="lucide lucide-guitar text-blue-600" style="color: rgb(127, 140, 170);">
          <path d="m11.9 12.1 4.514-4.514"></path>
          <path
            d="M20.1 2.3a1 1 0 0 0-1.4 0l-1.114 1.114A2 2 0 0 0 17 4.828v1.344a2 2 0 0 1-.586 1.414A2 2 0 0 1 17.828 7h1.344a2 2 0 0 0 1.414-.586L21.7 5.3a1 1 0 0 0 0-1.4z">
          </path>
          <path d="m6 16 2 2"></path>
          <path
            d="M8.2 9.9C8.7 8.8 9.8 8 11 8c2.8 0 5 2.2 5 5 0 1.2-.8 2.3-1.9 2.8l-.9.4A2 2 0 0 0 12 18a4 4 0 0 1-4 4c-3.3 0-6-2.7-6-6a4 4 0 0 1 4-4 2 2 0 0 0 1.8-1.2z">
          </path>
          <circle cx="11.5" cy="12.5" r=".5" fill="currentColor"></circle>
        </svg>
        <span class="label">TOM</span>:
        <button id="transpose-down"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-arrow-left">
            <path d="m12 19-7-7 7-7"></path>
            <path d="M19 12H5"></path>
          </svg></button>
        <span class="chord"><strong data-original-chord="<?= $info['tom'] ?>"><?= $info['tom'] ?></strong></span>
        <button id="transpose-up"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-arrow-right">
            <path d="M5 12h14"></path>
            <path d="m12 5 7 7-7 7"></path>
          </svg></button>
      </span>
      <span id="current-transpose" hidden>0 semitons</span>
      <button id="transpose-reset" title="Reset">Reset</button>
    </div>

    <div style="display: inline-block; width: 100%;">
      <label for="capo-select" class="capo-select"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
          viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" class="lucide lucide-hash text-blue-500" style="color: rgb(127, 140, 170);">
          <line x1="4" x2="20" y1="9" y2="9"></line>
          <line x1="4" x2="20" y1="15" y2="15"></line>
          <line x1="10" x2="8" y1="3" y2="21"></line>
          <line x1="16" x2="14" y1="3" y2="21"></line>
        </svg>
        <span class="label">CAPOTRASTE</span>:
      </label>
      <button id="capo-down"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          class="lucide lucide-arrow-left">
          <path d="m12 19-7-7 7-7"></path>
          <path d="M19 12H5"></path>
        </svg></button>
      <select id="capo-select" disabled>
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
      <button id="capo-up"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
          class="lucide lucide-arrow-right">
          <path d="M5 12h14"></path>
          <path d="m12 5 7 7-7 7"></path>
        </svg></button>

      <div id="font-controls">
        <button id="font-increase">A+</button>
        <button id="font-decrease">A-</button>
        <button id="fullscreen-toggle">Tela Cheia</button>
      </div>
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