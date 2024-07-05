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
    $sku = test_input($_POST['sku']);
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

        $u = "SELECT sku from products where sku = '$sku'";
        $uu = mysqli_query($conn, $u);


        if (mysqli_num_rows($uu) > 0 && !isset($product_id)) {
            $check_sku = "<h5 class='warning'>this sku already exists</h5>";
        } else {

            for ($i = 0; $i < count($_FILES['galleries']['name']); $i++) {
                $galleryName[] = basename($_FILES['galleries']['name'][$i]);
                $uploadFile = $_FILES['galleries']['tmp_name'][$i];
                $targetpath = "uploads/" . $galleryName[$i];
                move_uploaded_file($uploadFile, $targetpath);
            };
            $images = implode(', ', $_FILES['galleries']['name']);
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
                $sql = $conn->prepare("INSERT INTO products (date, title, sku, price, featured_image, gallery) VALUES (NOW(), ?, ?, ?, ?, ?)");
                $sql->bind_param("sssss", $title, $sku, $price, $Filename, $images);
                $sql->execute();
            }
            if (!empty($title && $sku && $price) === TRUE) {
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
                echo "Error: " . $sql . "<br>" . $conn->error;
                echo "No products added yet";
            }
        }
    } else {
        $errorFile = 'Select Featured is required';
    }
}
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
                            if ($name == 'Add Product') {
                                echo '';
                            } else {
                                echo $title;
                            }

                            ?>" type="text" placeholder="required" name="title">
            <p><?php if (isset($status1)) {
                    echo "<h5 class='warning'>$status1</h5>";
                } ?></p>
        </div>
        <div class="field">
            <label for="sku">SKU</label>
            <input value="<?php
                            global $check_sku;
                            if ($name == 'Add Product') {
                                echo '';
                            } else {
                                echo $sku;
                            }
                            ?>" placeholder="required" type="text" name="sku">
            <p>
                <?php if (isset($status2)) {
                    echo "<h5 class='warning'>$status2</h5>";
                } else {
                    global $check_sku;
                    echo $check_sku;
                }
                ?>
            </p>
        </div>
        <div class="field">
            <label for="price">Price</label>
            <input onkeypress="return isNumberKey(event)" placeholder="required" value="<?php
                 if ($name == 'Add Product') {
                     echo '';
                 } else {
                     echo $price;
                 }
                 ?>" type="text" name="price">
            <p><?php if (isset($status3)) {
                    echo "<h5 class='warning'>$status3</h5>";
                } ?></p>
        </div>
        <div class="field">
            <label for="featured_image">Featured Image <span class="required">(Required)</span></label>
            <?php if (isset($product_data['featured_image']) && !empty($product_data['featured_image'])) : ?>
                <img src="uploads/<?php echo $product_data['featured_image']; ?>" alt="Featured Image" style="max-width: 200px;">
            <?php endif; ?>
            <input accept=".jpeg, .jpg, .png, .gif" type="file" name="featured_image">
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
                    // Check if editing and category is selected for the product
                    $selected = '';
                    if ($name == "Edit Product" && in_array($data['id'], $product_properties)) {
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
                    // Check if editing and tag is selected for the product
                    $selected = '';
                    if ($name == "Edit Product" && in_array($data['id'], $product_properties)) {
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
<script src="script.js"></script>
</body>

</html>