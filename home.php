<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroLoja</title>
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

    <div class="store-image"></div> <!-- Imagem do estabelecimento -->

    <section class="subprodutos">
        <!-- Your existing subprodutos links (equinos.html, suinos.html, etc.) -->
    </section>

    <section class="carrossel">
        <?php
        // 🗄️ Connect to MySQL database (same as admin.php)
        $conn = new mysqli('localhost', 'root', '', 'uni_db');
        if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

        // 📥 Fetch products from the database
        $result = $conn->query("SELECT * FROM products");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '
                <div class="produto">
                    <img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['name']) . '">
                    <p>' . htmlspecialchars($row['name']) . '</p>
                    <p>Preço: R$' . number_format($row['price'], 2, ',', '.') . '</p>
                    <p>Descrição: ' . htmlspecialchars($row['description']) . '</p>
                </div>
                ';
            }
        } else {
            echo "<p>Nenhum produto cadastrado ainda.</p>";
        }
        $conn->close();
        ?>
    </section>

    <footer>
        <p>&copy; 2025 AgroLoja - Todos os direitos reservados</p>
    </footer>
</body>
</html>