<?php
include('config.php');
$uploadDir = __DIR__ . '/thumbs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$files = array_diff(scandir($uploadDir), array('..', '.'));

rsort($files);
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
        <a href="upload.php"><img src="img/upload.svg" width="32" height="23" alt="Upload"></a>
        <h3><?php echo HEADER_NAME; ?> <span><a href="https://github.com/arajnoha/gally" target="_blank">/ Gally</a></span></h3>
    </header>
    <div class="gallery">
        <?php 
        foreach ($files as $file):
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): 
        ?>
            <div class="photo">
                <a href="detail.php?photo=<?= pathinfo($file, PATHINFO_FILENAME) ?>">
                    <img src="thumbs/<?= $file ?>" alt="photo">
                </a>
            </div>
        <?php endif; endforeach; ?>
    </div>
</body>
</html>
