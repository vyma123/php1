<?php
include 'db.php';

$product_id = $_GET['editid'];

// Fetch product details
$sql_product = "SELECT * FROM products WHERE id = $product_id";
$result_product = $conn->query($sql_product);
$product_data = $result_product->fetch_assoc();

$title = $product_data['title'];
$sku = $product_data['sku'];
$price = $product_data['price'];
$featured_image = $product_data['featured_image'];

// Fetch all gallery images
$sql_galleries = "SELECT id, name_ FROM property WHERE type_ = 'gallery'";
$result_galleries = $conn->query($sql_galleries);

// Fetch selected galleries for the product
$sql_selected_galleries = "SELECT property_id FROM product_property WHERE product_id = $product_id AND property_id IN (SELECT id FROM property WHERE type_ = 'gallery')";
$result_selected_galleries = $conn->query($sql_selected_galleries);
$selected_galleries = [];
while ($row = $result_selected_galleries->fetch_assoc()) {
    $selected_galleries[] = $row['property_id'];
}

// Fetch all categories
$sql_categories = "SELECT id, name_ FROM property WHERE type_ = 'category'";
$result_categories = $conn->query($sql_categories);

// Fetch selected categories for the product
$sql_selected_categories = "SELECT property_id FROM product_property WHERE product_id = $product_id AND property_id IN (SELECT id FROM property WHERE type_ = 'category')";
$result_selected_categories = $conn->query($sql_selected_categories);
$selected_categories = [];
while ($row = $result_selected_categories->fetch_assoc()) {
    $selected_categories[] = $row['property_id'];
}

// Fetch all tags
$sql_tags = "SELECT id, name_ FROM property WHERE type_ = 'tag'";
$result_tags = $conn->query($sql_tags);

// Fetch selected tags for the product
$sql_selected_tags = "SELECT property_id FROM product_property WHERE product_id = $product_id AND property_id IN (SELECT id FROM property WHERE type_ = 'tag')";
$result_selected_tags = $conn->query($sql_selected_tags);
$selected_tags = [];
while ($row = $result_selected_tags->fetch_assoc()) {
    $selected_tags[] = $row['property_id'];
}

if (isset($_POST['edit_product'])) {
    $product_id = $_GET['editid'];
    $title = test_input($_POST['title']);
    $sku = test_input($_POST['sku']);
    $price = test_input($_POST['price']);

    // Update product details
    $sql_update_product = "UPDATE products SET title = '$title', sku = '$sku', price = '$price' WHERE id = $product_id";
    if ($conn->query($sql_update_product) === TRUE) {

        // Update featured image if a new one is uploaded
        if ($_FILES['featured_image']['size'] > 0) {
            $target = "uploads/";
            $target = $target . basename($_FILES['featured_image']['name']);
            $Filename = basename($_FILES['featured_image']['name']);
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target)) {
                $sql_update_image = "UPDATE products SET featured_image = '$Filename' WHERE id = $product_id";
                $conn->query($sql_update_image);
            } else {
                echo "Sorry, there was a problem uploading your file.";
            }
        }

        // Update galleries
        $galleries = isset($_POST['galleries']) ? explode(',', $_POST['galleries']) : [];
        updateProductProperties($product_id, $galleries, 'gallery', $conn);

        // Update categories
        $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
        updateProductProperties($product_id, $categories, 'category', $conn);

        // Update tags
        $tags = isset($_POST['tags']) ? $_POST['tags'] : [];
        updateProductProperties($product_id, $tags, 'tag', $conn);

        echo "Product updated successfully";
    } else {
        echo "Error updating product: " . $conn->error;
    }
}

function test_input($data)
{
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function updateProductProperties($product_id, $properties, $type, $conn)
{
    // Validate properties
    $valid_properties = [];
    foreach ($properties as $property_id) {
        $property_id = (int) $property_id;
        $sql_validate_property = "SELECT id FROM property WHERE id = $property_id AND type_ = '$type'";
        $result = $conn->query($sql_validate_property);
        if ($result->num_rows > 0) {
            $valid_properties[] = $property_id;
        }
    }

    // Clear existing properties for the product
    $sql_delete_old = "DELETE FROM product_property WHERE product_id = $product_id AND property_id IN (SELECT id FROM property WHERE type_ = '$type')";
    $conn->query($sql_delete_old);

    // Insert new properties
    foreach ($valid_properties as $property_id) {
        $sql_insert_new = "INSERT INTO product_property (product_id, property_id) VALUES ('$product_id', '$property_id')";
        $conn->query($sql_insert_new);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
    </style>
</head>

<body>
    <h1>Edit Product</h1>
    <form enctype="multipart/form-data" class="ui form" method="post">
        <div class="field">
            <label for="title">Product name</label>
            <input name="title" value="<?php echo $title; ?>" required>
        </div>
        <div class="field">
            <label for="sku">SKU</label>
            <input type="text" name="sku" value="<?php echo $sku; ?>" required>
        </div>
        <div class="field">
            <label for="price">Price</label>
            <input step=".01" type="number" min="0" name="price" value="<?php echo $price; ?>">
        </div>
        <div class="field">
            <label for="featured_image">Featured Image</label>
            <input accept=".jpeg, .jpg, .png, .gif" type="file" name="featured_image">
            <img src="./uploads/<?php echo $featured_image; ?>" alt="Featured Image" width="80px">
        </div>

        <div class="field">
            <label for="gallery">Select Gallery</label>
            <div id="galleryPreview">
                <?php
                while ($gallery_data = $result_galleries->fetch_assoc()) {
                    $gallery_id = $gallery_data['id'];
                    $gallery_name = $gallery_data['name_'];
                    $selected = in_array($gallery_id, $selected_galleries) ? 'selected' : '';
                    echo '<img src="./uploads/' . $gallery_name . '" alt="" width="80px" class="gallery-image ' . $selected . '" data-id="' . $gallery_id . '">';
                }
                ?>
            </div>
            <input type="hidden" name="galleries" id="selectedGalleries" value="<?php echo implode(',', $selected_galleries); ?>">
        </div>

        <div class="field">
            <label for="categories">Categories</label>
            <select name="categories[]" multiple>
                <?php
                while ($category_data = $result_categories->fetch_assoc()) {
                    $category_id = $category_data['id'];
                    $category_name = $category_data['name_'];
                    $selected = in_array($category_id, $selected_categories) ? 'selected' : '';
                    echo '<option value="' . $category_id . '" ' . $selected . '>' . $category_name . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="field">
            <label for="tags">Tags</label>
            <select name="tags[]" multiple>
                <?php
                while ($tag_data = $result_tags->fetch_assoc()) {
                    $tag_id = $tag_data['id'];
                    $tag_name = $tag_data['name_'];
                    $selected = in_array($tag_id, $selected_tags) ? 'selected' : '';
                    echo '<option value="' . $tag_id . '" ' . $selected . '>' . $tag_name . '</option>';
                }
                ?>
            </select>
        </div>

        <div class="footer_property">
            <a class="ui button" href="index.php">Back</a>
            <button name="edit_product" class="ui button" type="submit">Save Changes</button>
        </div>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const galleryImages = document.querySelectorAll('.gallery-image');
            const selectedGalleriesInput = document.getElementById('selectedGalleries');
            let selectedGalleries = selectedGalleriesInput.value.split(',');

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