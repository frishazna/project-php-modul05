<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    die();
}
include("connection.php");

// Hapus hanya boleh lewat method POST (lebih aman daripada link GET)
if (!isset($_POST["delete"]) || !isset($_POST["nim"])) {
    header("Location: student_view.php");
    die();
}

$nim = mysqli_real_escape_string($connection, trim($_POST["nim"]));

// Ambil nama dulu untuk pesan konfirmasi
$query  = "SELECT name FROM student WHERE nim='$nim'";
$result = mysqli_query($connection, $query);

if (!$result) {
    die("Query Error: " . mysqli_errno($connection) . " - " . mysqli_error($connection));
}

if (mysqli_num_rows($result) == 0) {
    $message = "Data mahasiswa tidak ditemukan";
} else {
    $data = mysqli_fetch_assoc($result);
    $name = $data["name"];

    $query  = "DELETE FROM student WHERE nim='$nim'";
    $delete = mysqli_query($connection, $query);

    if ($delete) {
        $message = "Mahasiswa dengan nama = \"<b>$name</b>\" berhasil dihapus";
    } else {
        die("Query gagal dijalankan: " . mysqli_errno($connection) . " - " . mysqli_error($connection));
    }
}

mysqli_free_result($result);
mysqli_close($connection);

header("Location: student_view.php?message=" . urlencode($message));
die();
?>
