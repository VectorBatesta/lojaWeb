<?php
// 🔒 Basic authentication (username: admin, password: 123)
if (!isset($_SERVER['PHP_AUTH_USER']) || 
    $_SERVER['PHP_AUTH_USER'] !== 'admin' || 
    $_SERVER['PHP_AUTH_PW'] !== '123') {
  header('WWW-Authenticate: Basic realm="Admin Panel"');
  header('HTTP/1.0 401 Unauthorized');
  die('Unauthorized');
}

// 🗄️ Connect to MySQL
$conn = new mysqli('localhost', 'root', '', 'uni_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// ➕ Handle adding a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
  $name = $conn->real_escape_string($_POST['name']);
  $price = $conn->real_escape_string($_POST['price']);
  $category_id = $conn->real_escape_string($_POST['category_id']);
  $description = $conn->real_escape_string($_POST['description']);
  $image_path = '';

  // Handle image upload
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $image_name = basename($_FILES['image']['name']);
    $target_dir = 'products_images/';
    $target_file = $target_dir . $image_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      $image_path = $target_file;
    } else {
      die("Error uploading image.");
    }
  }

  if (!$conn->query("INSERT INTO products (name, price, category, description, image_path) VALUES ('$name', $price, '$category_id', '$description', '$image_path')")) {
    die("Error: " . $conn->error);
  }
  header("Location: admin.php");
  exit();
}

// ✏️ Handle updating a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = $conn->real_escape_string($_POST['id']);
  $name = $conn->real_escape_string($_POST['name']);
  $price = $conn->real_escape_string($_POST['price']);
  $category_id = $conn->real_escape_string($_POST['category_id']);
  $description = $conn->real_escape_string($_POST['description']);
  $image_path = '';

  // Handle image upload
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $image_name = basename($_FILES['image']['name']);
    $target_dir = 'products_images/';
    $target_file = $target_dir . $image_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      $image_path = $target_file;
    } else {
      die("Error uploading image.");
    }
  }

  $update_query = "UPDATE products SET name='$name', price=$price, category='$category_id', description='$description'";
  if ($image_path) {
    $update_query .= ", image_path='$image_path'";
  }
  $update_query .= " WHERE id=$id";

  if (!$conn->query($update_query)) {
    die("Error: " . $conn->error);
  }
  header("Location: admin.php");
  exit();
}

// ❌ Handle deleting a product
if (isset($_GET['delete'])) {
  $id = $conn->real_escape_string($_GET['delete']);
  
  // Fetch the image path before deleting the product
  $result = $conn->query("SELECT image_path FROM products WHERE id = $id");
  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $image_path = $row['image_path'];
    
    // Delete the product from the database
    if ($conn->query("DELETE FROM products WHERE id = $id")) {
      // Delete the image file if it exists
      if ($image_path && file_exists($image_path)) {
        unlink($image_path);
      }
    } else {
      die("Error: " . $conn->error);
    }
  }
  
  header("Location: admin.php");
  exit();
}

// 📝 Fetch all products
$result = $conn->query("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category = c.id");
if (!$result) {
  die("Error: " . $conn->error);
}
$products = $result->fetch_all(MYSQLI_ASSOC);

// 📝 Fetch all categories
$category_result = $conn->query("SELECT * FROM categories");
if (!$category_result) {
  die("Error: " . $conn->error);
}
$categories = $category_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel</title>
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
    }
    table, th, td {
      border: 1px solid black;
    }
    th, td {
      padding: 10px;
      text-align: left;
    }
    th {
      background-color: #f2f2f2;
    }
  </style>
</head>
<body>
  <h1>Admin Panel</h1>

  <h2>Adicionar Produto</h2>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= isset($_GET['edit']) ? $_GET['edit'] : '' ?>">
    <input type="text" name="name" placeholder="Nome do Produto" value="<?= isset($_GET['edit']) ? $products[array_search($_GET['edit'], array_column($products, 'id'))]['name'] : '' ?>" required>
    <input type="number" name="price" step="0.01" placeholder="Preço" value="<?= isset($_GET['edit']) ? $products[array_search($_GET['edit'], array_column($products, 'id'))]['price'] : '' ?>" required>
    <select name="category_id" required>
      <option value="">Selecione a Categoria</option>
      <?php foreach ($categories as $category): ?>
        <option value="<?= $category['id'] ?>" <?= isset($_GET['edit']) && $products[array_search($_GET['edit'], array_column($products, 'id'))]['category'] == $category['id'] ? 'selected' : '' ?>><?= $category['name'] ?></option>
      <?php endforeach; ?>
    </select>
    <textarea name="description" placeholder="Descrição do Produto" rows="4" cols="50"><?= isset($_GET['edit']) ? $products[array_search($_GET['edit'], array_column($products, 'id'))]['description'] : '' ?></textarea>
    <input type="file" name="image">
    <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : 'add' ?>"><?= isset($_GET['edit']) ? 'Atualizar Produto' : 'Adicionar Produto' ?></button>
  </form>

  <h2>Produtos</h2>
  <table>
    <thead>
      <tr>
        <th>Nome</th>
        <th>Preço</th>
        <th>Categoria</th>
        <th>Descrição</th>
        <th>Imagem</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $product): ?>
        <tr>
          <td><?= htmlspecialchars($product['name']) ?></td>
          <td>R$<?= number_format($product['price'], 2, ',', '.') ?></td>
          <td><?= htmlspecialchars($product['category_name']) ?></td>
          <td><?= htmlspecialchars($product['description']) ?></td>
          <td>
            <?php if ($product['image_path']): ?>
              <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 50px; height: 50px;">
            <?php endif; ?>
          </td>
          <td>
            <a href="?edit=<?= $product['id'] ?>">Editar</a>
            <a href="?delete=<?= $product['id'] ?>">Excluir</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>