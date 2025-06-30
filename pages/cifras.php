<?php
global $url_params;
$produto = !empty($url_params) ? htmlspecialchars($url_params[0]) : 'Nenhum';
$pageData = [
    'title' => "Cifras | Louvor",
    'description' => "Cifras cadastradas no Louvor.",
    'keywords' => "cifras, louvor, adoracao",
    'og_image' => BASE_URL . 'assets/images/produto-og-image.jpg'
];
generateHeader($pageData);
?>
<main>

</main>
</body>

</html>