<?php
// 🗄️ Connect to MySQL
$conn = new mysqli('localhost', 'root', '', 'uni_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// 📝 Fetch product details
$id = $conn->real_escape_string($_GET['id']);
$result = $conn->query("SELECT * FROM products WHERE id = $id");
if (!$result) {
  die("Error: " . $conn->error);
}
$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - AgroLoja</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/logo.jpg" alt="Logo AgroLoja">
        </div>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="paginas.html">Páginas</a></li>
                <li><a href="contato.html">Contato</a></li>
            </ul>
        </nav>
    </header>

    <div class="store-image"></div> <!-- Imagem de Produto -->

    <section class="product-detail">
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 300px; height: 300px;">
        <p>Preço: R$<?= number_format($product['price'], 2, ',', '.') ?></p>
        <p>Descrição: <?= htmlspecialchars($product['description']) ?></p>
    </section>

    <footer>
        <p>&copy; 2025 AgroLoja - Todos os direitos reservados</p>
    </footer>
</body>
</html>