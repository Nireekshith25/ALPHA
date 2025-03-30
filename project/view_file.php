<?php 
if (isset($_GET['file'])) {
    $file = urldecode($_GET['file']);
    if (file_exists($file)) {
        $file_type = mime_content_type($file);
        header('Content-Type: ' . $file_type);
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit();
    } else {
        echo "File not found.";
    }
} else {
    echo "No file specified.";
}
?>
