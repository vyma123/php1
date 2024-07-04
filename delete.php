
<?php
include 'db.php';


if (isset($_GET['deleteid'])) {
    $id = $_GET['deleteid'];

    // delete from product
    $delete_product_query = "DELETE FROM products WHERE id = $id";
    $conn->query($delete_product_query);

    // Redirect to index.php
    header("Location: index.php");
    
    exit();
}



// // Function to get the current page number
// function getCurrentPageNumber($total_data, $data_per_page, $current_data)
// {
//     return ceil(($total_data - $current_data) / $data_per_page);
// }

// if (isset($_GET['deleteid'])) {
//     $id = $_GET['deleteid'];

//     // Retrieve current page number and current data index
//     $data_per_page = 5; // Số lượng bản ghi mỗi trang
//     $current_data = isset($_GET['current_data']) ? $_GET['current_data'] : 0; // Chỉ mục dữ liệu hiện tại
//     $total_data_query = "SELECT COUNT(*) as total FROM products";
//     $total_data_result = $conn->query($total_data_query);
//     $total_data = $total_data_result->fetch_assoc()['total'];
//     $current_page = getCurrentPageNumber($total_data, $data_per_page, $current_data);

//     // Xóa sản phẩm từ cơ sở dữ liệu
//     $delete_product_query = "DELETE FROM products WHERE id = $id";
//     if ($conn->query($delete_product_query) === TRUE) {
//         // Tính toán lại số lượng trang sau khi xóa
//         $total_page = ceil($total_data / $data_per_page);

//         // Nếu trang hiện tại vượt quá số trang sau khi xóa và là trang cuối cùng không có dữ liệu, điều chỉnh về trang trước đó
//         if ($current_page > $total_page && $total_data % $data_per_page == 0) {
//             $current_page = $total_page;
//         }

//         // Chuyển hướng về trang hiển thị chính xác sau khi xóa
//         $redirect_url = "index.php?page=" . $current_page;
//         header("Location: $redirect_url");
//         exit();
//     } else {
//         // Xử lý lỗi nếu cần thiết
//         echo "Error deleting record: " . $conn->error;
//     }
// }
