<?php
http_response_code(404);
$pageData = [
    'title' => '404 - Página Não Encontrada | Meu Site',
    'description' => 'A página que você está procurando não foi encontrada.',
    'keywords' => '404, erro, página não encontrada, meu site',
    'og_image' => BASE_URL . 'assets/images/404-og-image.jpg'
];
generateHeader($pageData);
?>
<main>
    <h1>404 - Página Não Encontrada</h1>
    <p>Desculpe, mas a página que você está procurando não existe.</p>
</main>
</body>
</html>