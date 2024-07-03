<?php
include 'db.php';
require_once 'functions.php';


if (isset($_GET['editid'])) {

    $product_id = $_GET['editid'];
    $sql_product = "SELECT * FROM products WHERE id = $product_id";
    $result_product = $conn->query($sql_product);
    $product_data = $result_product->fetch_assoc();
}
if (isset($product_id)) {
    $name = "Edit Product";
} else {
    $name = "Add Product";
}





if (isset($_POST['add_product'])) {

    $title = test_input($_POST['title']);
    $sku = test_input($_POST['sku']);
    $price = test_input($_POST['price']);

    $title == false ? $status1 = 'Required title' : '';
    $sku == false ? $status2 = 'Required sku' : '';
    $price == false ? $status3 = 'Required price' : '';


    // Get galleries, categories, tags
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];

    $target = "uploads/";
    $target = $target . basename($_FILES['featured_image']['name']);
    $Filename = basename($_FILES['featured_image']['name']);

    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target)) {

        $u = "SELECT sku from products where sku = '$sku'";
        $uu = mysqli_query($conn, $u);

        if (mysqli_num_rows($uu) > 0) {
            $check_sku = "<h5 class='warning'>this sku already exists</h5>";
        } else {
         

                for ($i = 0; $i < count($_FILES['galleries']['name']); $i++) {
                    $galleryName[] = basename($_FILES['galleries']['name'][$i]);
                    $uploadFile = $_FILES['galleries']['tmp_name'][$i];
                    $targetpath = "uploads/" . $galleryName[$i];
                    move_uploaded_file($uploadFile, $targetpath);
                };
                $product_id = $conn->insert_id;
                $images = implode(', ', $_FILES['galleries']['name']);

            $sql = $conn->prepare("INSERT INTO products (date, title, sku, price, featured_image,gallery) VALUES (NOW(), ?, ?, ?, ?, ?)");

            $sql->execute(array(
                 $title,
                 $sku,
                 $price,
                 $Filename,
                 $images,

            ));

         


            if (!empty($title && $sku && $price)  === TRUE) {
                $product_id = $conn->insert_id;
                // Insert galleries if valid


                // Insert categories if valid
                foreach ($categories as $category_id) {
                    if (isValidPropertyId($category_id, 'category', $conn)) {
                        $sql = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$category_id')";
                        $conn->query($sql);
                    }
                }

                // Insert tags if valid
                foreach ($tags as $tag_id) {
                    if (isValidPropertyId($tag_id, 'tag', $conn)) {
                        $sql = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$tag_id')";
                        $conn->query($sql);
                    }
                }

                echo "Product added successfully";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
                echo "No products added yet";
            }
        }
    } else {
        $errorFile =  "Sorry, there was a problem uploading your file.";
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
            <input value="<?php if (isset($_POST['title'])) echo $_POST['title']; ?>" type="text" placeholder="required" name="title">
            <p><?php if (isset($status1)) {
                    echo "<h5 class='warning'>$status1</h5>";
                } ?></p>
        </div>
        <div class="field">
            <label for="sku">SKU</label>
            <input value="<?php
                            global $check_sku;
                            if (isset($_POST['sku'])) echo $_POST['sku']; ?>" placeholder="required" type="text" name="sku">
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
            <input placeholder="required" value="<?php if (isset($_POST['price'])) echo $_POST['price']; ?>" step=".01" min="0" type="number" name="price">
            <p><?php if (isset($status3)) {
                    echo "<h5 class='warning'>$status3</h5>";
                } ?></p>

        </div>
        <div class="field">
            <label for="featured_image">Featured Image</label>
            <input placeholder="required" accept=".jpeg, .jpg, .png, .gif" type="file" name="featured_image">
            <p><?php if (isset($errorFile)) {
                    echo "<h5 class='warning'>$errorFile</h5>";
                } ?></p>
        </div>

        <div class="field">
            <label for="gallery">Select Gallery</label>
            <input accept="image/png, image/jpg, image/jpeg, image/gif" type="file" name="galleries[]" multiple>
            <input type="hidden" name="galleries" id="selectedGalleries">
        </div>

        <div class="field">
            <label for="categories">Categories</label>
            <?php
            
            $sql = "SELECT id, name_ FROM property WHERE type_ = 'category'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo '<select name="categories[]" multiple>';
                while ($data = mysqli_fetch_array($result)) {
                    echo '<option value="' . $data['id'] . '">' . $data['name_'] . '</option>';
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
                    echo '<option value="' . $data['id'] . '">' . $data['name_'] . '</option>';
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