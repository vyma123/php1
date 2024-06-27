<?php
include 'db.php';

// Pagination settings
$per_page_record = 3;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page - 1) * $per_page_record;

// Initialize search term
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// If search button is clicked, store search term in session
if (isset($_GET['search_btn'])) {
    session_start();
    $_SESSION['search_term'] = $search_term;
}

// Retrieve search term from session if available
if (isset($_SESSION['search_term'])) {
    $search_term = $_SESSION['search_term'];
}

// Construct the SQL query
$query = "
SELECT 
    p.*, 
    GROUP_CONCAT(g.name_ SEPARATOR ',') as galleries,
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
WHERE 
    p.title LIKE '%$search_term%'
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
        <form action="" method="get">
        <div class="ui secondary menu">
            <a href="add_product.php" class="ui button openModal">Add product</a>
            <a href="add_property.php" class="ui button">Add property</a>
            <a class="ui button">Sync from VillaTheme</a>
            <div class="right menu">
                    <div class="item">
                        <div class="ui icon input">
                            <input name="search" class="search_input" type="text" placeholder="Search product..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button name="search_btn" class="search_btn">
                                Search
                            </button>
                        </div>
                    </div>
            </div>
        </div>
            <div class="filter_box">
                <div>
                    <select name="date_filter" class="ui dropdown">
                        <option value="date" <?php if (isset($_GET['date_filter']) && $_GET['date_filter'] == 'date') echo 'selected'; ?>>Date</option>
                        <option value="product_name" <?php if (isset($_GET['date_filter']) && $_GET['date_filter'] == 'product_name') echo 'selected'; ?>>Product name</option>
                        <option value="price" <?php if (isset($_GET['date_filter']) && $_GET['date_filter'] == 'price') echo 'selected'; ?>>Price</option>
                    </select>
                </div>
                <div>
                    <select name="sort_order" class="asc ui dropdown">
                        <option value="ASC" <?php if (isset($_GET['sort_order']) && $_GET['sort_order'] == 'ASC') echo 'selected'; ?>>ASC</option>
                        <option value="DESC" <?php if (isset($_GET['sort_order']) && $_GET['sort_order'] == 'DESC') echo 'selected'; ?>>DESC</option>
                    </select>
                </div>
                <div>
                    <select name="cat_filter" class="cate ui dropdown">
                        <option value="" <?php if (!isset($_GET['cat_filter'])) echo 'selected'; ?>>Category</option>
                        <!-- Các tùy chọn danh mục -->
                    </select>
                </div>
                <div>
                    <select name="tag_filter" class="tag ui dropdown">
                        <option value="" <?php if (!isset($_GET['tag_filter'])) echo 'selected'; ?>>Select tag</option>
                        <!-- Các tùy chọn tag -->
                    </select>
                </div>
                <div class="ui input">
                    <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                </div>
                <div class="ui input">
                    <input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                </div>
                <div class="ui input">
                    <input type="text" name="price_start" placeholder="price from" value="<?php echo isset($_GET['price_start']) ? htmlspecialchars($_GET['price_start']) : ''; ?>">
                </div>
                <div class="ui input">
                    <input type="text" name="price_end" placeholder="price to" value="<?php echo isset($_GET['price_end']) ? htmlspecialchars($_GET['price_end']) : ''; ?>">
                </div>

                <button  name="search_btn" class="filter ui button">Filter</button>
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
                            <?php foreach ($galleries as $gallery_image) : ?>
                                <img src="./uploads/<?php echo $gallery_image ?>" alt="" width="30px">
                            <?php endforeach; ?>
                        </td>
                        <td><?php echo $categories ?></td>
                        <td><?php echo $tags ?></td>
                        <td data-label="Job">
                            <a href="edit.php?editid=<?php echo $row['id']; ?>" class="btn_edit openModal_edit">
                                Edit
                            </a>
                            <a href="delete.php?deleteid=<?php echo $row['id']; ?>" class="btn_delete">
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
            $query = "SELECT COUNT(*) FROM products WHERE title LIKE '%$search_term%'";
            $rs_result = $conn->query($query);
            $row = mysqli_fetch_row($rs_result);
            $total_records = $row[0];

            $total_pages = ceil($total_records / $per_page_record);
            $pagLink = "";

            if ($page >= 2) {
            ?>
                <a href="index.php?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search_term); ?>" class="item">
                    <i class="arrow left icon"></i>
                </a>
                <?php
            }
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                ?>
                    <a href="index.php?page=<?php echo $i ?>&search=<?php echo htmlspecialchars($search_term); ?>" class="active item"><?php echo $i; ?></a>
                <?php
                } else {
                ?>
                    <a href="index.php?page=<?php echo $i ?>&search=<?php echo htmlspecialchars($search_term); ?>" class="item"><?php echo $i; ?></a>
                <?php
                }
            }
            if ($page < $total_pages) {
                ?>
                <a href="index.php?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search_term); ?>" class="item">
                    <i class="arrow right icon"></i>
                </a>
            <?php
            }
            ?>
        </div>
    </div>
</body>

</html>