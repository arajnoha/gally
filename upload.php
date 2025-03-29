<?php
define('UPLOAD_PASSWORD', 'password');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['password']) || $_POST['password'] !== UPLOAD_PASSWORD) {
        echo json_encode(['error' => 'Wrong password.']);
        exit;
    }

    if (!isset($_FILES['image'])) {
        echo json_encode(['error' => 'No photo chosen.']);
        exit;
    }

    $file = $_FILES['image'];
    $uploadDir = __DIR__ . '/upload/';
    $thumbsDir = __DIR__ . '/thumbs/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    if (!is_dir($thumbsDir)) {
        mkdir($thumbsDir, 0755, true);
    }
    
    if ($file['error'] !== 0) {
        echo json_encode([
            'error' => 'Error while uploading the photo.',
            'file_error' => $file['error']
        ]);
        exit;
    }
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'heic'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExtensions)) {
        echo json_encode(['error' => 'Unsupported image format.']);
        exit;
    }

    $timestamp = time();
    $newFilename = "$timestamp.jpg"; 
    $filePath = $uploadDir . $newFilename;
    $thumbFilePath = $thumbsDir . $newFilename;

    if ($ext === 'heic') {
        if (!class_exists('Imagick')) {
            echo json_encode(['error' => 'HEIC photo format is not supported on this specific server.']);
            exit;
        }
        $image = new Imagick($file['tmp_name']);
        $image->setImageFormat('jpeg');
        $image->writeImage($filePath);
    } else {
        move_uploaded_file($file['tmp_name'], $filePath);
    }

    createThumbnail($filePath, $thumbFilePath, 600);

    $jsonPath = $uploadDir . "$timestamp.json";
    $metadata = [
        'description' => $_POST['description'] ?? '',
        'timestamp' => $timestamp,
        'view_count' => 0
    ];
    file_put_contents($jsonPath, json_encode($metadata, JSON_PRETTY_PRINT));

    echo json_encode(['success' => 'Photo was successfully uploaded!']);
    exit;
}

function createThumbnail($filePath, $thumbFilePath, $maxSize) {
    list($width, $height, $type) = getimagesize($filePath);

    if ($width < $height) {
        $newWidth = $maxSize;
        $newHeight = ($height / $width) * $newWidth;
    } else {
        $newHeight = $maxSize;
        $newWidth = ($width / $height) * $newHeight;
    }

    $newWidth = intval($newWidth);
    $newHeight = intval($newHeight);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $srcImage = imagecreatefromjpeg($filePath);
            break;
        case IMAGETYPE_PNG:
            $srcImage = imagecreatefrompng($filePath);
            break;
        case IMAGETYPE_GIF:
            $srcImage = imagecreatefromgif($filePath);
            break;
        default:
            echo json_encode(['error' => 'Unsupported format.']);
            exit;
    }

    $thumbImage = imagecreatetruecolor($newWidth, $newHeight);

    if ($type == IMAGETYPE_PNG) {
        imagealphablending($thumbImage, false);
        imagesavealpha($thumbImage, true);
    }

    imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if ($type == IMAGETYPE_PNG) {
        imagepng($thumbImage, $thumbFilePath, 9);
    } else {
        imagejpeg($thumbImage, $thumbFilePath, 90);
    }

    imagedestroy($srcImage);
    imagedestroy($thumbImage);
}

?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload fotky</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <a href="./"><img src="img/arrow.svg" width="20" height="40" alt="Go back"></a>
        <h3>Adam Rajnoha <span>/ Gally</span></h3>
    </header>
    <h1>Upload a new photo</h1>

    <form id="uploadForm" enctype="multipart/form-data">
        <br>

        <label for="description">Description (optional):</label>
        <textarea name="description" id="description" rows="4" cols="50"></textarea>
        <br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <br>

        <div class="buttons">
        <label for="image">
            Choose a photo
            <input type="file" name="image" id="image">
        </label>

        <input type="submit" id="upload-button" value="Upload">
        </div>
    </form>

    <div id="responseMessage"></div>

    <script>
    const form = document.getElementById('uploadForm');
    const responseMessage = document.getElementById('responseMessage');
    const button = document.getElementById('upload-button');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        button.classList.add("loading");
        button.value = "Uploading...";
        const formData = new FormData(form);
        
        try {
            const response = await fetch('upload.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                setTimeout(() => {
                    window.location.href = 'index.php'; 
                }, 1000);
            } else if (result.error) {
                responseMessage.innerHTML = `<p class="error">${result.error}</p>`;
            }
        } catch (error) {
            responseMessage.innerHTML = `<p class="error">Error with photo submission.</p>`;
        }  finally {
            button.classList.remove('loading');
            button.value = "Success!";
        }
    });
    </script>

</body>
</html>