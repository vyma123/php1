<?php
// Include the database configuration file 
include_once 'db.php';



if (isset($_POST['add_property'])) {
    $fileNames = array_filter($_FILES['gallery']['name']);
    $categories = $tags = $messempty = "";


    // File upload configuration 
    $targetDir = "uploads/";
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');

    $statusMsg = $errorMsg = $insertValuesSQL = $errorUpload = $errorUploadType = '';
    if (!empty($fileNames)) {
        foreach ($_FILES['gallery']['name'] as $key => $val) {
            // File upload path 
            $fileName = basename($_FILES['gallery']['name'][$key]);
            $targetFilePath = $targetDir . $fileName;

            // Check whether file type is valid 
            $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
            if (in_array($fileType, $allowTypes)) {
                // Upload file to server 
                if (move_uploaded_file($_FILES["gallery"]["tmp_name"][$key], $targetFilePath)) {
                    // Image db insert sql 
                    $insertValuesSQL .= "('" . $fileName . "', 'gallery'),";
                } else {
                    $errorUpload .= $_FILES['gallery']['name'][$key] . ' | ';
                }
            } else {
                $errorUploadType .= $_FILES['gallery']['name'][$key] . ' | ';
            }
        }

        // Error message 
        $errorUpload = !empty($errorUpload) ? 'Upload Error: ' . trim($errorUpload, ' | ') : '';
        $errorUploadType = !empty($errorUploadType) ? 'File Type Error: ' . trim($errorUploadType, ' | ') : '';
        $errorMsg = !empty($errorUpload) ? '<br/>' . $errorUpload . '<br/>' . $errorUploadType : '<br/>' . $errorUploadType;

        if (!empty($insertValuesSQL)) {
            $insertValuesSQL = trim($insertValuesSQL, ',');
            // Insert image file name into database 
            $insert = $conn->query("INSERT INTO property (name_, type_) VALUES $insertValuesSQL");
            if ($insert) {
                $statusMsg = "Files are uploaded successfully." . $errorMsg;
            } else {
                $statusMsg = "Sorry, there was an error uploading your file.";
            }
        } else {
            $statusMsg = "Upload failed! " . $errorMsg;
        }
    } else {
        $statusMsg = 'Please select a file to upload.';
    }

    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // add categories 

    if ($_POST['categories'] !== "") {
        $categories = test_input($_POST['categories']);

        $sql = "INSERT INTO property(type_, name_) values ('category','$categories')";

        if (!$conn->query($sql) === TRUE) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "";
    }

    // add tags 
    if (
        $_POST['tags'] !== ""
    ) {
        $tags = test_input($_POST['tags']);

        $sql = "INSERT INTO property(type_, name_) values ('tag','$tags')";


        if ($conn->query($sql) === TRUE) {
            $result_tag = "New tag created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "";
    }

    if (empty($fileNames) && empty($categories) && empty($tags)) {
        echo 'No properties have been added yet';
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property</title>

    <!-- semantic ui -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.5.0/semantic.min.css" integrity="sha512-KXol4x3sVoO+8ZsWPFI/r5KBVB/ssCGB5tsv2nVOKwLg33wTFP3fmnXa47FdSVIshVTgsYk/1734xSk9aFIa4A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- css -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>Add Property</h1>
    <form enctype="multipart/form-data" class="ui form" method="post">
        <div class="field">
            <label>Gallery</label>
            <input type="file" name="gallery[]" accept=".jpg, .png, .jpeg" multiple>
            <div>
                <?php
                $result_gallery = "New gallery created successfully";
                if (!empty($fileNames)) {
                    echo $result_gallery;
                }
                ?>
            </div>
        </div>
        <div class="field">
            <label>Categories</label>
            <input type="text" name="categories" placeholder="Categories">

            <div><?php
                    $result_cate = "New category created successfully";
                    if (isset($categories) && $categories !== "") {
                        echo $result_cate;
                    } else {
                        echo "";
                    }
                    ?></div>

        </div>
        <div class="field">
            <label>Tags</label>
            <input type="text" name="tags" placeholder="Tags">
            <div><?php
                    $result_tag = "New tag created successfully";
                    if (isset($tags) && $tags !== "") {
                        echo $result_tag;
                    } else {
                        echo "";
                    }
                    ?></div>
        </div>
        <div class="footer_property">
                <a class="ui button" href="index.php">Back</a>
            <button name="add_property" class="ui button" type="submit">Add</button>
        </div>

    </form>
    <script src="script.js"></script>
</body>

</html>