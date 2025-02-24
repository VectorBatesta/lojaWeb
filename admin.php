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

// ➕ Handle adding a category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
  $category_name = $conn->real_escape_string($_POST['category_name']);
  if (!$conn->query("INSERT INTO categories (name) VALUES ('$category_name')")) {
    die("Error: " . $conn->error);
  }
}

// ➕ Handle adding a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
  $name = $conn->real_escape_string($_POST['name']);
  $price = $conn->real_escape_string($_POST['price']);
  $category_id = $conn->real_escape_string($_POST['category_id']);
  $image_path = '';

  // Handle image upload
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $image_name = basename($_FILES['image']['name']);
    $target_dir = 'images/';
    $target_file = $target_dir . $image_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      $image_path = $target_file;
    } else {
      die("Error uploading image.");
    }
  }

  if (!$conn->query("INSERT INTO products (name, price, category, image_path) VALUES ('$name', $price, '$category_id', '$image_path')")) {
    die("Error: " . $conn->error);
  }
}

// ✏️ Handle updating a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id = $conn->real_escape_string($_POST['id']);
  $name = $conn->real_escape_string($_POST['name']);
  $price = $conn->real_escape_string($_POST['price']);
  $category_id = $conn->real_escape_string($_POST['category_id']);
  $image_path = '';

  // Handle image upload
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $image_name = basename($_FILES['image']['name']);
    $target_dir = 'images/';
    $target_file = $target_dir . $image_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      $image_path = $target_file;
    } else {
      die("Error uploading image.");
    }
  }

  $update_query = "UPDATE products SET name='$name', price=$price, category='$category_id'";
  if ($image_path) {
    $update_query .= ", image_path='$image_path'";
  }
  $update_query .= " WHERE id=$id";

  if (!$conn->query($update_query)) {
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
</head>
<body>
  <h1>Admin Panel</h1>

  <h2>Add Category</h2>
  <form method="POST">
    <input type="text" name="category_name" placeholder="Category name" required>
    <button type="submit" name="add_category">Add Category</button>
  </form>

  <h2>Add Product</h2>
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= isset($_GET['edit']) ? $_GET['edit'] : '' ?>">
    <input type="text" name="name" placeholder="Product name" value="<?= isset($_GET['edit']) ? $products[array_search($_GET['edit'], array_column($products, 'id'))]['name'] : '' ?>" required>
    <input type="number" name="price" step="0.01" placeholder="Price" value="<?= isset($_GET['edit']) ? $products[array_search($_GET['edit'], array_column($products, 'id'))]['price'] : '' ?>" required>
    <select name="category_id" required>
      <option value="">Select Category</option>
      <?php foreach ($categories as $category): ?>
        <option value="<?= $category['id'] ?>" <?= isset($_GET['edit']) && $products[array_search($_GET['edit'], array_column($products, 'id'))]['category'] == $category['id'] ? 'selected' : '' ?>><?= $category['name'] ?></option>
      <?php endforeach; ?>
    </select>
    <input type="file" name="image">
    <button type="submit" name="<?= isset($_GET['edit']) ? 'update' : 'add' ?>"><?= isset($_GET['edit']) ? 'Update Product' : 'Add Product' ?></button>
  </form>

  <h2>Products</h2>
  <ul>
    <?php foreach ($products as $product): ?>
      <li>
        <?= htmlspecialchars($product['name']) ?> ($<?= number_format($product['price'], 2, ',', '.') ?>)
        <?php if ($product['image_path']): ?>
          <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 50px; height: 50px;">
        <?php endif; ?>
        <a href="?edit=<?= $product['id'] ?>">Edit</a>
        <a href="?delete=<?= $product['id'] ?>">Delete</a>
      </li>
    <?php endforeach; ?>
  </ul>
</body>
</html>