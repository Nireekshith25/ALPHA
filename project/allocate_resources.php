<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allocate Resources</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #4a148c;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group select,
        .form-group button {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        .form-group button {
            background-color: #4a148c;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #6a1b9a;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .message.success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .message.error {
            background-color: #f2dede;
            color: #a94442;
        }
        .view-resources-button {
            margin-top: 20px;
        }
        .view-resources-button a {
            background-color: #4a148c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Allocate Resources</h1>
    </div>
    <div class="container">
        <div class="back-button">
            <a href="index.html">Back to Main Page</a>
        </div>
        
        <?php
        session_start();

        if (!isset($_SESSION['username'])) {
            header("Location: index.html");
            exit();
        }

        // Database connection
        $conn = new mysqli('localhost', 'root', '', 'anganwadi2');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_resource'])) {
            $resource_type = $_POST['resource_type'];
            $file = $_FILES['resource_file'];
            $message = "";
            $status = "";

            // Check file size
            if ($file['size'] > 600 * 1024 * 1024) {
                $message = "File size exceeds 600MB.";
                $status = "error";
            } else {
                $target_dir = __DIR__ . "/uploads/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $target_file = $target_dir . basename($file["name"]);

                if (move_uploaded_file($file["tmp_name"], $target_file)) {
                    $stmt = $conn->prepare("INSERT INTO resources (resource_type, file_path) VALUES (?, ?)");
                    $stmt->bind_param("ss", $resource_type, $target_file);
                    if ($stmt->execute()) {
                        $message = "File uploaded successfully: " . basename($file["name"]);
                        $status = "success";
                    } else {
                        $message = "Error uploading file.";
                        $status = "error";
                    }
                    $stmt->close();
                } else {
                    $message = "Error moving uploaded file.";
                    $status = "error";
                }
            }
        }
        ?>

        <?php if (isset($message)): ?>
            <div class="message <?php echo $status; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="resource_type">Resource Type</label>
                <select id="resource_type" name="resource_type" required>
                    <option value="Books">Books</option>
                    <option value="Rhymes">Rhymes</option>
                    <option value="Tables">Tables</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group">
                <label for="resource_file">Upload Resource (max 600MB)</label>
                <input type="file" id="resource_file" name="resource_file" accept=".pdf,.doc,.docx,.txt,.epub,.mobi" required>
            </div>
            <div class="form-group">
                <button type="submit" name="upload_resource">Upload Resource</button>
            </div>
        </form>

        <div class="view-resources-button">
            <a href="view_resources.php">View Allocated Resources</a>
        </div>
    </div>
</body>
</html>
