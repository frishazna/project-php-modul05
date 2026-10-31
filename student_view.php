<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    die();
}
include("connection.php");

if (isset($_GET["message"])) {
    $message = $_GET["message"];
}

$search = "";
if (isset($_GET["search"]) && $_GET["search"] != "") {
    $search = htmlentities(strip_tags(trim($_GET["search"])));
    $search = mysqli_real_escape_string($connection, $search);
    $query = "SELECT * FROM student WHERE name LIKE '%$search%' OR nim LIKE '%$search%' ORDER BY nim ASC";
} else {
    $query = "SELECT * FROM student ORDER BY nim ASC";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Mahasiswa</title>
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div id="header">
        <h1 id="logo">Data Mahasiswa</h1>
    </div>
    <hr>
    <nav>
        <ul>
            <li><a href="student_view.php">Tampil</a></li>
            <li><a href="student_add.php">Tambah</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <?php
    if (isset($message)) {
        echo "<div class='pesan'>$message</div>";
    }
    ?>

    <form action="student_view.php" method="get">
        <input type="text" name="search" placeholder="Cari nama atau NIM" value="<?php echo $search ?>">
        <input type="submit" value="Cari">
        <a href="student_view.php">Reset</a>
    </form>
    <br>

    <table border="1">
        <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Tempat Lahir</th>
            <th>Tanggal Lahir</th>
            <th>Fakultas</th>
            <th>Jurusan</th>
            <th>IPK</th>
            <th>Aksi</th>
        </tr>
        <?php
        $result = mysqli_query($connection, $query);
        if (!$result) {
            die("Query Error: " . mysqli_errno($connection) . " - " . mysqli_error($connection));
        }
        while ($data = mysqli_fetch_assoc($result)) {
            $birth_date     = strtotime($data["birth_date"]);
            $formatted_date = date("d-m-Y", $birth_date);

            echo "<tr>";
            echo "<td>$data[nim]</td>";
            echo "<td>$data[name]</td>";
            echo "<td>$data[birth_city]</td>";
            echo "<td>$formatted_date</td>";
            echo "<td>$data[faculty]</td>";
            echo "<td>$data[department]</td>";
            echo "<td>$data[gpa]</td>";
            echo "<td>";
            echo "<a href='student_edit.php?nim=$data[nim]'>Edit</a> ";
            echo "<form action='student_delete.php' method='post' style='display:inline;' ";
            echo "onsubmit=\"return confirm('Yakin ingin menghapus data $data[name]?');\">";
            echo "<input type='hidden' name='nim' value='$data[nim]'>";
            echo "<input type='submit' name='delete' value='Hapus'>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        mysqli_free_result($result);
        mysqli_close($connection);
        ?>
    </table>
</div>
</body>
</html>