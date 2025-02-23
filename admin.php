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
  if (!$conn->query("INSERT INTO products (name, price) VALUES ('$name', $price)")) {
    die("Error: " . $conn->error);
  }
}

// ✏️ Handle updating a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = $conn->real_escape_string($_POST['id']);
  $name = $conn->real_escape_string($_POST['name']);
  $price = $conn->real_escape_string($_POST['price']);
  if (!$conn->query("UPDATE products SET name='$name', price=$price WHERE id=$id")) {
    die("Error: " . $conn->error);
  }
}

// ❌ Handle deleting a product
if (isset($_GET['delete'])) {
  $id = $conn->real_escape_string($_GET['delete']);
  if (!$conn->query("DELETE FROM products WHERE id = $id")) {
    die("Error: " . $conn->error);
  }
}

// 📝 Fetch all products
$result = $conn->query("SELECT * FROM products");
if (!$result) {
  die("Error: " . $conn->error);
}
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel</title>
</head>
<body>
  <h1>Admin Panel</h1>
  <form method="POST">
    <input type="hidden" name="id" value="<?= isset($_GET['edit']) ? $_GET['edit'] : '' ?>">
    <input type="text" name="name" placeholder="Product name" value="<?= isset($_GET['edit']) ? $products[array_search($_GET['edit'], array_column($products, 'id'))]['name'] : '' ?>" required>
    <input type="number" name="price" step="0.01" placeholder="Price" value="<?= isset($_GET['edit']) ? $products[array_search($_GET['edit'], array_column($products, 'id'))]['price'] : '' ?>" required>
    <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : 'add' ?>"><?= isset($_GET['edit']) ? 'Update Product' : 'Add Product' ?></button>
  </form>
  <ul>
    <?php foreach ($products as $product): ?>
      <li>
        <?= htmlspecialchars($product['name']) ?> ($<?= number_format($product['price'], 2, ',', '.') ?>)
        <a href="?edit=<?= $product['id'] ?>">Edit</a>
        <a href="?delete=<?= $product['id'] ?>">Delete</a>
      </li>
    <?php endforeach; ?>
  </ul>
</body>
</html>