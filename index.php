<?php
include 'db.php';

// Pagination settings
$per_page_record = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page - 1) * $per_page_record;


$query = "

SELECT 
    p.*, 
    GROUP_CONCAT( g.name_ SEPARATOR ',') as galleries,
    GROUP_CONCAT(DISTINCT c.name_ SEPARATOR ', ') as categories,
    GROUP_CONCAT(DISTINCT t.name_ SEPARATOR ', ') as tags
FROM 
    products p
LEFT JOIN 
    product_property pg ON p.id = pg.product_id AND pg.property_id IN (SELECT id FROM property WHERE type_ = 'gallery')
LEFT JOIN 
    property g ON pg.property_id = g.id
LEFT JOIN 
    product_property pc ON p.id = pc.product_id AND pc.property_id IN (SELECT id FROM property WHERE type_ = 'category')
LEFT JOIN 
    property c ON pc.property_id = c.id
LEFT JOIN 
    product_property pt ON p.id = pt.product_id AND pt.property_id IN (SELECT id FROM property WHERE type_ = 'tag')
LEFT JOIN 
    property t ON pt.property_id = t.id
GROUP BY 
    p.id
LIMIT 
    $start_from, $per_page_record";
$rs_result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- semantic ui -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- css -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>PHP1</h1>
    <header>
        <div class="ui secondary menu">
            <a href="add_product.php" class="ui button openModal">Add product</a>
            <a href="add_property.php" class="ui button">Add property</a>
            <a class="ui button">Sync from VillaTheme</a>
            <div class="right menu">
                <form action="" method="post">
                    <div class="item">
                        <div class="ui icon input">
                            <input name="search" class="search_input" type="text" placeholder="Search product...">
                            <button name="search_btn" class="search_btn">
                                <i class="search link icon"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <form action="" method="GET">
            <div class="filter_box">
                <div>
                    <select name="date_filter" class="ui dropdown">
                        <option value="date">Date</option>
                        <option value="product_name">Product name</option>
                        <option value="price">Price</option>
                    </select>
                </div>
                <div>
                    <select name="sort_alphabet" class="asc ui dropdown">
                        <option value="0">ASC</option>
                        <option value="1">DESC</option>

                    </select>
                </div>
                <div>
                    <select name="cat_filter" class="cate ui dropdown">
                        <option value="">Category</option>
                    </select>
                </div>
                <div>
                    <select name="tag_filter" class="tag ui dropdown">
                        <option value="">Select tag</option>
                    </select>
                </div>
                <div class="ui input">
                    <input type="date">
                </div>
                <div class="ui input">
                    <input type="date">
                </div>
                <div class="ui input">
                    <input type="text" name="price_start" placeholder="price from">
                </div>
                <div class="ui input">
                    <input type="text" name="price_end" placeholder="price to">
                </div>
                <button type="submit" name="e" class="filter ui button">Filter</button>
            </div>
        </form>
    </header>

    <section class="table_box">
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product name</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Feature Image</th>
                    <th>Gallery</th>
                    <th>Categories</th>
                    <th>Tags</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_array($rs_result)) {
                    $date = $row['date'];
                    $title = $row['title'];
                    $sku = $row['sku'];
                    $price = $row['price'];
                    $featured_image = $row['featured_image'];
                    $galleries = explode(',', $row['galleries']);
                    $categories = $row['categories'];
                    $tags = $row['tags'];
                ?>
                    <tr>
                        <td><?php echo $date ?></td>
                        <td><?php echo $title ?></td>
                        <td><?php echo $sku ?></td>
                        <td><?php echo $price ?></td>
                        <td><img src="./uploads/<?php echo $featured_image ?>" alt="" width="30px"></td>
                        <td>
                            <?php
                             foreach ($galleries as $gallery_image) : ?>
                                <img src="./uploads/<?php echo $gallery_image ?>" alt="" width="30px">
                            <?php endforeach; ?>
                        </td>
                        <td><?php echo $categories ?></td>
                        <td><?php echo $tags ?></td>
                        <td data-label="Job">
                            <a href="edit.php?editid=<?php echo $row['id']; ?>" class="btn_edit openModal_edit">
                                <!-- <i class="edit icon"></i> -->
                                Edit
                            </a>
                            
                            <a href="delete.php?deleteid=<?php echo $row['id']; ?>" class="btn_delete">
                                <!-- <i class="trash icon"></i> -->
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php
                }
                ?>
               
            </tbody>
        </table>
    </section>
    <div class="box_pagination">
        <div aria-label="Pagination Navigation" role="navigation" class="ui pagination menu">
            <?php
            $query = "SELECT COUNT(*) FROM products";
            $rs_result = $conn->query($query);
            $row = mysqli_fetch_row($rs_result);
            $total_records = $row[0];

            $total_pages = ceil($total_records / $per_page_record);
            $pagLink = "";

            if ($page >= 2) {
            ?>
                <a href="index.php?page=<?php echo $page - 1; ?>" aria-current="false" aria-disabled="false" tabindex="0" value="1" aria-label="Previous item" type="prevItem" class="item">
                    <i class="arrow left icon"></i>
                </a>
                <?php
            }
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                ?>
                    <a href="index.php?page=<?php echo $i ?>" aria-current="true" aria-disabled="false" tabindex="0" value="1" type="pageItem" class="active item"><?php echo $i; ?></a>
                <?php
                } else {
                ?>
                    <a href="index.php?page=<?php echo $i ?>" aria-current="true" aria-disabled="false" tabindex="0" value="1" type="pageItem" class="item"><?php echo $i; ?></a>
                <?php
                }
            }
            echo $pagLink;
            if ($page < $total_pages) {
                ?>
                <a href="index.php?page=<?php echo $page + 1 ?>" aria-current="false" aria-disabled="false" tabindex="0" aria-label="Next item" type="nextItem" class="item">
                    <i class="arrow right icon"></i>
                </a>
            <?php
            }
            ?>
        </div>
    </div>
</body>

</html>