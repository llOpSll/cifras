<?php
global $url_params;
$categoria = isset($url_params[0]) ? htmlspecialchars($url_params[0]) : 'Nenhuma';
$produto = isset($url_params[1]) ? htmlspecialchars($url_params[1]) : 'Nenhum';
$pageData = [
    'title' => "Produtos: $categoria - $produto | Meu Site",
    'description' => "Explore produtos na categoria $categoria, como $produto.",
    'keywords' => "produtos, $categoria, $produto, meu site, seo",
    'og_image' => BASE_URL . 'assets/images/produtos-og-image.jpg'
];
generateHeader($pageData);
?>
<main>
    <h1>Produtos</h1>
    <?php if ($categoria !== 'Nenhuma') { ?>
        <p>Categoria: <?php echo $categoria; ?></p>
    <?php } ?>
    <?php if ($produto !== 'Nenhum') { ?>
        <p>Produto: <?php echo $produto; ?></p>
    <?php } else { ?>
        <p>Lista de produtos na categoria <?php echo $categoria; ?>.</p>
    <?php } ?>
</main>
</body>
</html>