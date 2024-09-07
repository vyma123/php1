<?php
include 'db.php';
require_once 'functions.php';

if (isset($_GET['editid'])) {
    if (!is_numeric($_GET['editid'])) {
        header('Location: error.php');
        exit;
    }

    $product_id = $_GET['editid'];
    $sql_product = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $sql_product->bind_param("i", $product_id);
    $sql_product->execute();
    $result_product = $sql_product->get_result();

    if ($result_product->num_rows > 0) {
        $product_data = $result_product->fetch_assoc();
        $title = $product_data['title'];
        $sku = $product_data['sku'];
        $price = trim($product_data['price']);

        $featured_image = $product_data['featured_image'];
        $gallery_image = $product_data['gallery'];




        $name = "Edit Product";
        if ($name == "Edit Product") {
            $sql_product_properties = $conn->prepare("SELECT property_id FROM product_property WHERE product_id = ?");
            $sql_product_properties->bind_param("i", $product_id);
            $sql_product_properties->execute();
            $result_product_properties = $sql_product_properties->get_result();

            $product_properties = [];
            while ($row = $result_product_properties->fetch_assoc()) {
                $product_properties[] = $row['property_id'];
            }
        }
    } else {
        header('Location: error.php');
        exit;
    }
} else {
    $name = "Add Product";
}


if (isset($_POST['add_product'])) {

    $title = test_input($_POST['title']);
    $sku = $_POST['sku'];
    $price = trim(test_input($_POST['price']));

    $title == false ? $status1 = 'Required title' : '';
    $sku == false ? $status2 = 'Required sku' : '';
    $price == false ? $status3 = 'Required price' : '';


    // Get galleries, categories, tags
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];

    $target = "uploads/";
    $target = $target . basename($_FILES['featured_image']['name']);
    $Filename = basename($_FILES['featured_image']['name']);

    $gallery_selected = !empty(array_filter($_FILES['galleries']['name']));

    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target)) {

        if (isset($product_id)) {
            $u = "SELECT sku FROM products WHERE sku = ? AND id != ?";
            $stmt = $conn->prepare($u);
            $stmt->bind_param("si", $sku, $product_id);
        } else {
            $u = "SELECT sku FROM products WHERE sku = ?";
            $stmt = $conn->prepare($u);
            $stmt->bind_param("s", $sku);
        }
        $stmt->execute();
        $uu = $stmt->get_result();

        // try {
        //   print_r($uu);
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }


        if (mysqli_num_rows($uu) > 0) {
            echo  "<h5 class='warning'>this sku already exists</h5>";
        } else {

            for ($i = 0; $i < count($_FILES['galleries']['name']); $i++) {
                $galleryName[] = basename($_FILES['galleries']['name'][$i]);
                $uploadFile = $_FILES['galleries']['tmp_name'][$i];
                $targetpath = "uploads/" . $galleryName[$i];
                move_uploaded_file($uploadFile, $targetpath);
            };
            $images = implode(', ', $_FILES['galleries']['name']);
            $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
            $check_title = preg_match('/^[A-Za-z0-9 _\-–]*$/', $title);
            $check_sku = preg_match('/^[A-Za-z0-9_-]*$/', $sku);

            if (isset($product_id)) {
                if (is_numeric($_GET['editid'])) {
                    if (!$gallery_selected) {
                        $sql = $conn->prepare("UPDATE products SET title=?, sku=?, price=? WHERE id = ?");
                        $sql->bind_param("ssdi", $title, $sku, $price, $product_id);
                        $sql->execute();
                    } else {
                        $sql = $conn->prepare("UPDATE products SET title=?, sku=?, price=?, featured_image=?, gallery=? WHERE id = ?");
                        $sql->bind_param("ssdssi", $title, $sku, $price, $Filename, $images, $product_id);
                        $sql->execute();
                    }
                }
            } else {
                if (!$price && !$check_title && !$check_sku) {
                    $price_error = 'just number';
                    $title_error = "don't allow special char";
                    $sku_error = "don't allow special char";
                } else if (!$check_title && !$check_sku) {
                    $title_error = "don't allow special char";
                    $sku_error = "don't allow special char";
                } else if (!$check_title && !$price) {
                    $title_error = "don't allow special char";
                    $price_error = 'just number';
                } else if (!$check_sku && !$price) {
                    $sku_error = "don't allow special char";
                    $price_error = 'just number';
                } else if (!$check_title) {
                    $title_error = "don't allow special char";
                } else if (!$check_sku) {
                    $sku_error = "don't allow special char";
                } else if (!$price) {
                    $sku_error = "don't allow special char";
                } else {
                    $sku = htmlspecialchars($sku);
                    $title = htmlspecialchars($title);

                    $sql = $conn->prepare("INSERT INTO products (date, title, sku, price, featured_image, gallery) VALUES (NOW(), ?, ?, ?, ?, ?)");
                    $sql->bind_param("sssss", $title, $sku, $price, $Filename, $images);
                    $sql->execute();
                }
            }
            if (!empty($title && $sku && $price) === TRUE && $check_title && $check_sku) {
                if (!isset($product_id)) {
                    $product_id = $conn->insert_id;
                }

                $sql_clear = $conn->prepare("DELETE FROM product_property WHERE product_id = ?");
                $sql_clear->bind_param("i", $product_id);
                $sql_clear->execute();

                foreach ($categories as $category_id) {
                    if (isValidPropertyId($category_id, 'category', $conn)) {
                        $sql = $conn->prepare("INSERT INTO product_property (product_id, property_id) VALUES (?, ?)");
                        $sql->bind_param("ii", $product_id, $category_id);
                        $sql->execute();
                    }
                }

                foreach ($tags as $tag_id) {
                    if (isValidPropertyId($tag_id, 'tag', $conn)) {
                        $sql = $conn->prepare("INSERT INTO product_property (product_id, property_id) VALUES (?, ?)");
                        $sql->bind_param("ii", $product_id, $tag_id);
                        $sql->execute();
                    }
                }

                if ($name == 'Edit Product') {
                    echo '<script>
                    alert("Product updated successfully");
                    window.location.href="index.php";
                    </script>';
                } else {
                    echo "Product added successfully";
                }
            } else {
                if ($name == 'Edit Product') {
                    echo "No Product update yet";
                    if (!$price) {
                        $price_error = 'just number';
                    } else if (!$check_title) {
                        $title_error = "don't allow special char";
                    } else if (!$check_sku) {
                        $sku_error = "don't allow special char";
                    }
                } else {
                    echo "No Product added yet";
                }
            }
        }
    } else {
        $errorFile = 'Select Featured is required';
    }
}


$selected_categories = isset($_POST['categories']) ? $_POST['categories'] : [];
$selected_tags = isset($_POST['tags']) ? $_POST['tags'] : [];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1><?php echo $name; ?></h1>
    <form enctype="multipart/form-data" class="ui form" method="post">
        <div class="field">
            <label for="title">Product name</label>
            <input value="<?php
                            if ($name == 'Add Product' && !isset($_POST['title'])) {
                                echo '';
                            } else {
                                echo $title;
                            }

                            ?>" type="text" placeholder="required" name="title">
            <p><?php if (isset($status1)) {
                    echo "<h5 class='warning'>$status1</h5>";
                } else if (isset($title_error)) {
                    echo "<h5 class='warning'>$title_error</h5>";
                }
                ?></p>
        </div>
        <div class="field">
            <label for="sku">SKU</label>
            <input value="<?php
                            if ($name == 'Add Product' && !isset($_POST['sku'])) {
                                echo '';
                            } else {
                                echo $sku;
                            }
                            ?>" placeholder="required" type="text" name="sku">
            <p>
                <?php if (isset($status2)) {
                    echo "<h5 class='warning'>$status2</h5>";
                } else if (isset($sku_error)) {
                    echo "<h5 class='warning'>$sku_error</h5>";
                }
                ?>
            </p>
        </div>
        <div class="field">
            <label for="price">Price</label>
            <input onkeypress="return isNumberKey(event)" placeholder="required" value="<?php
              if ($name == 'Add Product' && !isset($_POST['price'])) {
                 echo '';
               } else {
               echo $price;
                }
            ?>" type="text" name="price">
            <p><?php if (isset($status3)) {
                    echo "<h5 class='warning'>$status3</h5>";
                } else if (isset($price_error)) {
                    echo "<h5 class='warning'>$price_error</h5>";
                }
                ?></p>
        </div>
        <div class="field">
            <label for="featured_image">Featured Image <span class="required">(Required)</span></label>
            <?php if (isset($product_data['featured_image']) && !empty($product_data['featured_image'])) : ?>
                <img src="uploads/<?php echo $product_data['featured_image']; ?>" alt="Featured Image" style="max-width: 200px;">
            <?php endif; ?>
            <input id="myFile" accept=".jpeg, .jpg, .png, .gif" type="file" name="featured_image">
            <p><?php if (isset($errorFile)) {
                    echo "<h5 class='warning'>$errorFile</h5>";
                } ?></p>
        </div>
        <div class="field">
            <label for="gallery">Select Gallery</label>
            <div class="gallery-images">
                <?php
                // Display gallery images with edit capability
                if (isset($product_data['gallery']) && !empty($product_data['gallery'])) {
                    $gallery_images = explode(', ', $product_data['gallery']);
                    foreach ($gallery_images as $image) {
                        echo '<div class="gallery-image">';
                        echo '<img src="uploads/' . $image . '" alt="' . $image . '" style="max-width: 150px;">';
                        echo '</div>';
                    }
                }
                ?>
            </div>
            <input accept="image/png, image/png, image/jpg, image/jpeg, image/gif" type="file" name="galleries[]" multiple>
            <p><?php if (isset($errorGallery)) {
                    echo "<h5 class='warning'>$errorGallery</h5>";
                } ?></p>
        </div>
        <div class="field">
            <label for="categories">Categories</label>
            <?php
            $sql = "SELECT id, name_ FROM property WHERE type_ = 'category'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo '<select name="categories[]" multiple>';
                while ($data = mysqli_fetch_array($result)) {
                    // Kiểm tra nếu đang trong chế độ chỉnh sửa và category đã được chọn cho sản phẩm
                    $selected = '';
                    if (($name == "Edit Product" && in_array($data['id'], $product_properties)) || in_array($data['id'], $selected_categories)) {
                        $selected = 'selected';
                    }
                    echo '<option value="' . $data['id'] . '" ' . $selected . '>' . $data['name_'] . '</option>';
                }
                echo '</select>';
            } else {
                echo '<h5>No categories available</h5>';
            }
            ?>
        </div>

        <div class="field">
            <label for="tags">Tags</label>
            <?php
            $sql = "SELECT id, name_ FROM property WHERE type_ = 'tag'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo '<select name="tags[]" multiple>';
                while ($data = mysqli_fetch_array($result)) {
                    // Kiểm tra nếu đang trong chế độ chỉnh sửa và tag đã được chọn cho sản phẩm
                    $selected = '';
                    if (($name == "Edit Product" && in_array($data['id'], $product_properties)) || in_array($data['id'], $selected_tags)) {
                        $selected = 'selected';
                    }
                    echo '<option value="' . $data['id'] . '" ' . $selected . '>' . $data['name_'] . '</option>';
                }
                echo '</select>';
            } else {
                echo '<h5>No tags available</h5>';
            }
            ?>
        </div>

        <div class="footer_property">
            <a class="ui button" href="index.php">Back</a>
            <button id="img-upload" name="add_product" class="ui button" type="submit">Add</button>
        </div>
    </form>
    <script src="script.js">

    </script>
</body>

</html>