<?php
include 'db.php';

if (isset($_POST['add_product'])) {
    $title = test_input($_POST['title']);
    $sku = test_input($_POST['sku']);
    $price = test_input($_POST['price']);
    $categories = $_POST['categories'];
    $tags = $_POST['tags'];
    $galleries = $_POST['galleries'];

    $target = "uploads/";
    $target = $target . basename($_FILES['featured_image']['name']);

    $Filename = basename($_FILES['featured_image']['name']);

    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target)) {
        $sql = "INSERT INTO products (date, title, sku, price, featured_image) VALUES (NOW(), '$title', '$sku', '$price', '$Filename')";
        if ($conn->query($sql) === TRUE) {
            $product_id = $conn->insert_id;

            foreach ($galleries as $gallery_id) {
                $sql = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$gallery_id')";
                $conn->query($sql);
            }

            foreach ($categories as $category_id) {
                $sql = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$category_id')";
                $conn->query($sql);
            }
            foreach ($tags as $tag_id) {
                $sql = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$tag_id')";
                $conn->query($sql);
            }

            echo "Product added successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Sorry, there was a problem uploading your file.";
    }
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Add Product</h1>
    <form enctype="multipart/form-data" class="ui form" method="post">
        <div class="field">
            <label for="title">Product name</label>
            <input name="title" required>
        </div>
        <div class="field">
            <label for="sku">SKU</label>
            <input type="text" name="sku" required>
        </div>
        <div class="field">
            <label for="price">Price</label>
            <input type="number" min="0" name="price">
        </div>
        <div class="field">
            <label for="featured_image">Featured Image</label>
            <input type="file" name="featured_image" required>
        </div>

        <div class="field">
            <label for="gallery">Select Gallery</label>
            <select name="galleries[]" multiple>
                <?php
                include 'db.php';
                $sql = "SELECT id, name_ FROM property WHERE type_ = 'gallery'";
                $result = $conn->query($sql);
                while ($data = mysqli_fetch_array($result)) {
                    echo '<option value="' . $data['id'] . '">' . $data['name_'] . '</option>';
                }
                ?>
            </select>
            <div id="galleryPreview">
                <?php
                $result = $conn->query($sql);
                while ($data = mysqli_fetch_array($result)) {
                    echo '<img src="./uploads/' . $data['name_'] . '" alt="" width="80px" height="80px" id="gallery_' . $data['id'] . '">';
                }
                ?>
            </div>
        </div>

        <div class="field">
            <label for="category">Categories</label>
            <select name="categories[]" multiple>
                <?php
                $sql = "SELECT id, name_ FROM property WHERE type_ = 'category'";
                $result = $conn->query($sql);
                while ($data = mysqli_fetch_array($result)) {
                    echo '<option value="' . $data['id'] . '">' . $data['name_'] . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="field">
            <label for="tag">Tags</label>
            <select name="tags[]" multiple>
                <?php
                $sql = "SELECT id, name_ FROM property WHERE type_ = 'tag'";
                $result = $conn->query($sql);
                while ($data = mysqli_fetch_array($result)) {
                    echo '<option value="' . $data['id'] . '">' . $data['name_'] . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="footer_property">
            <a class="ui button" href="index.php">Back</a>
            <button name="add_product" class="ui button" type="submit">Add</button>
        </div>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const selectGalleries = document.querySelector('select[name="galleries[]"]');
            const galleryImages = document.querySelectorAll('#galleryPreview img');

            // Show all images initially
            galleryImages.forEach(img => {
                img.style.display = 'inline';
            });

            selectGalleries.addEventListener('change', function() {
                const selectedOptions = Array.from(this.selectedOptions).map(option => option.value);
                galleryImages.forEach(img => {
                    if (selectedOptions.includes(img.id.replace('gallery_', ''))) {
                        img.style.border = '3px solid green'; // Highlight selected images
                    } else {
                        img.style.border = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>