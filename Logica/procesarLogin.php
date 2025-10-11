<?php
include('sql.php');
session_start();

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: welcome.php");
        exit();
    } else {
        $_SESSION['error'] = "Contraseña incorrecta.";
        header("Location: login.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Correo electrónico no encontrado.";
    header("Location: login.php");
    exit();
}
?>