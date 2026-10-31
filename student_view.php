<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}
include("connection.php");

if (isset($_GET["message"])) {
    $message = $_GET["message"];
}

// Fitur pencarian (nama atau NIM)
$search = "";
if (isset($_GET["search"]) && trim($_GET["search"]) !== "") {
    $search = htmlentities(strip_tags(trim($_GET["search"])));
    $search_escaped = mysqli_real_escape_string($connection, $search);
    $query = "SELECT * FROM student
              WHERE name LIKE '%$search_escaped%'
                 OR nim LIKE '%$search_escaped%'
                 OR birth_city LIKE '%$search_escaped%'
                 OR faculty LIKE '%$search_escaped%'
                 OR department LIKE '%$search_escaped%'
              ORDER BY nim ASC";
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

    <!-- Form pencarian -->
    <form action="student_view.php" method="get" style="margin-bottom: 15px;">
        <input type="text" name="search" placeholder="Cari nama, NIM, kota, fakultas, jurusan" value="<?php echo $search; ?>" size="35">
        <input type="submit" value="Cari">
        <?php if ($search !== "") { ?>
            <a href="student_view.php">Reset</a>
        <?php } ?>
    </form>

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

        if (mysqli_num_rows($result) == 0) {
            echo "<tr><td colspan='8' style='text-align:center;'>Data tidak ditemukan</td></tr>";
        }

        while ($data = mysqli_fetch_assoc($result)) {
            $birth_date = strtotime($data["birth_date"]);
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
            echo "<form action='student_delete.php' method='post' style='display:inline;' onsubmit=\"return confirm('Yakin hapus data ini?');\">";
            echo "<input type='hidden' name='nim' value='$data[nim]'>";
            echo "<input type='submit' value='Hapus'>";
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