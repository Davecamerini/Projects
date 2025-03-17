<?php
// ...

require('database.php');
$db = $conn; // Assign your database connection variable

// Get video title, category ID, and subcategory ID from HTML form
$videoTitle = isset($_POST['video_title']) ? $_POST['video_title'] : '';
$categoryID = isset($_POST['video_category']) ? $_POST['video_category'] : '';
$subCategoryID = isset($_POST['video_subcategory']) ? $_POST['video_subcategory'] : '';

if ($_FILES["uploadingfile"]["error"] == 0 && is_uploaded_file($_FILES["uploadingfile"]["tmp_name"])) {
    $allowed_mime_types = array("video/mp4");
    $max_file_size = 104857600;  // 100 MB
    $baseFolderPath = "/srv/www/vhosts/siti_dinamici/www.luciailariaseglie.it/website/area-corsi-online/corsi/";

    // Check if file type and extension are valid
    $extension = pathinfo($_FILES["uploadingfile"]["name"], PATHINFO_EXTENSION);
    if (in_array($_FILES["uploadingfile"]["type"], $allowed_mime_types) && $extension == 'mp4') {

        // Check file size
        if ($_FILES["uploadingfile"]["size"] <= $max_file_size) {

            // Database connection
            // Check connection
            if ($db->connect_error) {
                die("Connection failed: " . $db->connect_error);
            }

            // Fetch category name based on category ID
            $categoryNameQuery = "SELECT category_name FROM video_categories WHERE id = ?";
            $categoryNameStmt = $db->prepare($categoryNameQuery);
            $categoryNameStmt->bind_param("i", $categoryID);
            $categoryNameStmt->execute();
            $categoryNameResult = $categoryNameStmt->get_result();

            if ($categoryNameResult->num_rows > 0) {
                $categoryRow = $categoryNameResult->fetch_assoc();
                $category = $categoryRow['category_name'];

                // Fetch subcategory name based on subcategory ID
                $subCategoryNameQuery = "SELECT subcategory_name FROM video_subcategories WHERE id = ?";
                $subCategoryNameStmt = $db->prepare($subCategoryNameQuery);
                $subCategoryNameStmt->bind_param("i", $subCategoryID);
                $subCategoryNameStmt->execute();
                $subCategoryNameResult = $subCategoryNameStmt->get_result();

                if ($subCategoryNameResult->num_rows > 0) {
                    $subCategoryRow = $subCategoryNameResult->fetch_assoc();
                    $subCategory = $subCategoryRow['subcategory_name'];

                    // Create category folder if it doesn't exist
                    $categoryFolderPath = $baseFolderPath . $category . "/";
                    if (!file_exists($categoryFolderPath)) {
                        mkdir($categoryFolderPath, 0755, true);
                    }

                    // Create subcategory folder if it doesn't exist
                    $subCategoryFolderPath = $categoryFolderPath . $subCategory . "/";
                    if (!file_exists($subCategoryFolderPath)) {
                        mkdir($subCategoryFolderPath, 0755, true);
                    }

                    // Handle file name conflicts
                    $uploadFilePath = $subCategoryFolderPath . $_FILES["uploadingfile"]["name"];
                    if (file_exists($uploadFilePath)) {
                        $filename_parts = pathinfo($uploadFilePath);
                        $counter = 1;
                        while (file_exists($subCategoryFolderPath . $filename_parts['filename'] . "_" . $counter . "." . $filename_parts['extension'])) {
                            $counter++;
                        }
                        $uploadFilePath = $subCategoryFolderPath . $filename_parts['filename'] . "_" . $counter . "." . $filename_parts['extension'];
                    }

                    // Perform the upload of the renamed file
                    if (move_uploaded_file($_FILES["uploadingfile"]["tmp_name"], $uploadFilePath)) {
                        // Insert data into the database
                        $uploadFilePath = str_replace('/srv/www/vhosts/siti_dinamici/www.luciailariaseglie.it/website', '', $uploadFilePath);

                        // Prepare and execute the query to insert data
                        $insertQuery = "INSERT INTO videos (id, title, category, subcategory, file_path) VALUES (NULL, ?, ?, ?, ?)";
                        $insertStmt = $db->prepare($insertQuery);
                        $insertStmt->bind_param("ssss", $videoTitle, $categoryID, $subCategory, $uploadFilePath);
                        $insertStmt->execute();

                        // Get the newly inserted ID
                        $lastInsertedId = $insertStmt->insert_id;

                        echo "Upload completed: $original_file_name and data inserted into the database with ID: $lastInsertedId";
                    } else {
                        echo "Error uploading the file.";
                    }
                } else {
                    echo "Error: Subcategory not found for the given subcategory ID.";
                }
            } else {
                echo "Error: Category not found for the given category ID.";
            }
        } else {
            echo "Error: File size exceeds the allowed limit.";
        }
    } else {
        echo "Error: The file is not an MP4 type or has a different extension.";
    }
} else {
    echo "Error: No file selected or error during upload.";
}
?>
