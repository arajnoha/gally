<?php
include('config.php');
$photoId = $_GET['photo'] ?? '';

$uploadDir = __DIR__ . '/upload/';

if (!ctype_digit($photoId)) {
    echo "Bad photo ID!";
    exit;
}

$imageFile = null;
$files = array_diff(scandir($uploadDir), array('..', '.'));

foreach ($files as $file) {
    if (preg_match('/^' . preg_quote($photoId, '/') . '\.(jpg|jpeg|png|gif)$/i', $file)) {
        $imageFile = $file;
        break;
    }
}

if ($imageFile && file_exists($uploadDir . $imageFile)) {
    $metadataFilePath = $uploadDir . $photoId . '.json';

    if (file_exists($metadataFilePath)) {
        $metadata = json_decode(file_get_contents($metadataFilePath), true);
        $description = $metadata['description'];
        $viewCount = $metadata['view_count'];
        $timestamp = $metadata['timestamp'];

        $metadata['view_count'] = $viewCount + 1;
        file_put_contents($metadataFilePath, json_encode($metadata));

        $formattedDate = date('d.m.Y', $timestamp);
    } else {
        echo "Metadata not found!";
        exit;
    }
} else {
    echo "Photo not found!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gally</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/png" href="img/favicon.png">
</head>
<body>
    <header>
        <a href="./"><img src="img/arrow.svg" width="20" height="40" alt="Go back"></a>
        <h3><?php echo HEADER_NAME; ?> <span><a href="https://github.com/arajnoha/gally" target="_blank">/ Gally</a></span></h3>
    </header>
    <div class="preview">
        <img src="upload/<?= htmlspecialchars($imageFile) ?>" alt="photo">
        <div class="meta">
        <span><img src="img/time.svg" width="24" height="24" alt="date"><?= $formattedDate ?></span>
        <span><?= $viewCount ?>x<img src="img/eye.svg" width="24" height="24" alt="viw count"></span>
        </div>
        <p><?= htmlspecialchars($description) ?></p>
    </div>
</body>
</html>
