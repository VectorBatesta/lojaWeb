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
                <li><a href="home.php">Início</a></li>
                <li><a href="paginas.html">Páginas</a></li>
                <li><a href="contato.html">Contato</a></li>
            </ul>
        </nav>
    </header>

    <div class="store-image"></div> <!-- Imagem de Início -->

    <section class="categories">
        <h2>Categorias</h2>
        <ul>
            <li><a href="home.php" class="all-categories">Todas as Categorias</a></li>
            <li><a href="home.php?category=Equinos">Equinos</a></li>
            <li><a href="home.php?category=Suínos">Suínos</a></li>
            <li><a href="home.php?category=Remédios">Remédios</a></li>
            <li><a href="home.php?category=Pets">Pets</a></li>
            <li><a href="home.php?category=Equipamentos">Equipamentos</a></li>
        </ul>
    </section>

    <section class="products">
        <?php
        // 🗄️ Conectar ao banco de dados MySQL
        $conn = new mysqli('localhost', 'root', '', 'uni_db');
        if ($conn->connect_error) die("Falha na conexão: " . $conn->connect_error);

        // 📥 Buscar produtos com base na categoria selecionada
        $category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
        if ($category) {
            echo "<h2>Categoria: " . htmlspecialchars($category) . "</h2>";
        } else {
            echo "<h2>Todos os Produtos</h2>";
        }

        $query = "SELECT * FROM products";
        if ($category) {
            $query .= " WHERE category = (SELECT id FROM categories WHERE name = '$category')";
        }
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '
                <div class="produto">
                    <a href="product.php?id=' . $row['id'] . '">
                        <img src="' . htmlspecialchars($row['image_path']) . '" alt="' . htmlspecialchars($row['name']) . '">
                        <p>' . htmlspecialchars($row['name']) . '</p>
                        <p>Preço: R$' . number_format($row['price'], 2, ',', '.') . '</p>
                    </a>
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