<?php
include 'db.php';

if (isset($_POST['add_product'])) {
    $title = test_input($_POST['title']);
    $sku = test_input($_POST['sku']);
    $price = test_input($_POST['price']);

    // Get selected galleries, categories, tags
    $galleries = isset($_POST['galleries']) ? explode(',', $_POST['galleries']) : [];
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : [];

    $target = "uploads/";
    $target = $target . basename($_FILES['featured_image']['name']);
    $Filename = basename($_FILES['featured_image']['name']);

    if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target)) {
        $sql = "INSERT INTO products (date, title, sku, price, featured_image) VALUES (NOW(), '$title', '$sku', '$price', '$Filename')";

        if ($conn->query($sql) === TRUE) {
            $product_id = $conn->insert_id;

            // Function to check if property_id exists and is valid
            function isValidPropertyId($property_id, $type, $conn)
            {
                $property_id = (int) $property_id;
                $type = $conn->real_escape_string($type);
                $check_sql = "SELECT COUNT(*) as count FROM property WHERE id = $property_id AND type_ = '$type'";
                $result = $conn->query($check_sql);
                $row = $result->fetch_assoc();
                return $row['count'] > 0;
            }

            // Insert galleries into product_property if valid
            foreach ($galleries as $gallery_id) {
                if (isValidPropertyId($gallery_id, 'gallery', $conn)) {
                    $sql = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$gallery_id')";
                    $conn->query($sql);
                }
            }

            // Insert categories into product_property if valid
            foreach ($categories as $category_id) {
                if (isValidPropertyId($category_id, 'category', $conn)) {
                    $sql = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$category_id')";
                    $conn->query($sql);
                }
            }

            // Insert tags into product_property if valid
            foreach ($tags as $tag_id) {
                if (isValidPropertyId($tag_id, 'tag', $conn)) {
                    $sql = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$tag_id')";
                    $conn->query($sql);
                }
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
    <style>
        .gallery-image {
            display: inline-block;
            margin: 5px;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .gallery-image.selected {
            border: 2px solid blue;
        }

        label {
            font-size: 1.2rem !important;
        }

        .field {
            margin-bottom: 1.6rem !important;
        }
    </style>
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
            <input accept=".jpeg, .jpg, .png, .gif" type="file" name="featured_image" required>
        </div>

        <div class="field">
            <label for="gallery">Select Gallery</label>
            <div id="galleryPreview">
                <?php
                $sql = "SELECT id, name_ FROM property WHERE type_ = 'gallery'";
                $result = $conn->query($sql);

                while ($data = mysqli_fetch_array($result)) {
                    echo '<img src="./uploads/' . $data['name_'] . '" alt="" width="80px" height="80px" class="gallery-image" data-id="' . $data['id'] . '">';
                }
                ?>
            </div>
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
            <button name="add_product" class="ui button" type="submit">Add</button>

        </div>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const galleryImages = document.querySelectorAll('.gallery-image');
            const selectedGalleriesInput = document.getElementById('selectedGalleries');
            let selectedGalleries = [];

            galleryImages.forEach(img => {
                img.addEventListener('click', function() {
                    const id = this.dataset.id;
                    if (selectedGalleries.includes(id)) {
                        selectedGalleries = selectedGalleries.filter(galleryId => galleryId !== id);
                        this.classList.remove('selected');
                    } else {
                        selectedGalleries.push(id);
                        this.classList.add('selected');
                    }
                    selectedGalleriesInput.value = selectedGalleries.join(',');
                });
            });
        });
    </script>
</body>
</html>