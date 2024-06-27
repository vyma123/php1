<?php
include 'db.php';

if (isset($_GET['deleteid'])) {
    $id = $_GET['deleteid'];

    // Xóa sản phẩm từ bảng products
    $delete_product_query = "DELETE FROM products WHERE id = $id";
    $conn->query($delete_product_query);

    // Redirect về trang index.php sau khi xóa
    header("Location: index.php");
    
    exit();
}
