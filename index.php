<?php

$db_name = '';
$db_user = '';
$db_pass = '';


$pdo = new PDO('mysql:host=localhost;dbname=' . $db_name, $$db_user, $db_pass);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'];
    $shortCode = generateShortCode();


    $stmt = $pdo->prepare("INSERT INTO $db_name" . "urls (short_code, long_code) VALUES (?, ?)");
    $stmt->execute([
        $shortCode, $url
    ]);

    echo json_encode(['short_code' => $shortCode]);
} else  {

    $shortCode = $_GET['code'];

    $stmt = $pdo->prepare("SELECT long_code FROM $db_name" . ".urls WHERE short_code = ?");
    $stmt->execute([$shortCode]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        header("Location: " . $result['long_code']);
        exit();
    } else {
        http_response_code(404);
        echo "Short code not found";
    }

}




function generateShortCode() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 7; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}
