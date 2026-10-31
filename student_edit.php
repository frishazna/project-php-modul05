<?php
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    die();
}
include("connection.php");

$arr_month = [
    "1"=>"Januari", "2"=>"Februari", "3"=>"Maret", "4"=>"April",
    "5"=>"Mei", "6"=>"Juni", "7"=>"Juli", "8"=>"Agustus",
    "9"=>"September", "10"=>"Oktober", "11"=>"Nopember", "12"=>"Desember"
];

$error_message = "";

if (isset($_POST["submit"])) {
    // ===== PROSES UPDATE =====
    $nim         = htmlentities(strip_tags(trim($_POST["nim"])));
    $name        = htmlentities(strip_tags(trim($_POST["name"])));
    $birth_city  = htmlentities(strip_tags(trim($_POST["birth_city"])));
    $faculty     = htmlentities(strip_tags(trim($_POST["faculty"])));
    $department  = htmlentities(strip_tags(trim($_POST["department"])));
    $gpa         = htmlentities(strip_tags(trim($_POST["gpa"])));
    $birth_date  = htmlentities(strip_tags(trim($_POST["birth_date"])));
    $birth_month = htmlentities(strip_tags(trim($_POST["birth_month"])));
    $birth_year  = htmlentities(strip_tags(trim($_POST["birth_year"])));

    if (empty($name)) {
        $error_message .= "- Nama belum diisi <br>";
    }
    if (empty($birth_city)) {
        $error_message .= "- Tempat lahir belum diisi <br>";
    }
    if (empty($department)) {
        $error_message .= "- Jurusan belum diisi <br>";
    }
    if (!is_numeric($gpa) OR ($gpa <= 0)) {
        $error_message .= "- IPK harus diisi dengan angka <br>";
    }

    if ($error_message === "") {
        $nim_db        = mysqli_real_escape_string($connection, $nim);
        $name_db       = mysqli_real_escape_string($connection, $name);
        $birth_city_db = mysqli_real_escape_string($connection, $birth_city);
        $faculty_db    = mysqli_real_escape_string($connection, $faculty);
        $department_db = mysqli_real_escape_string($connection, $department);
        $gpa_db        = (float) $gpa;
        $birth_date_full = (int)$birth_year . "-" . (int)$birth_month . "-" . (int)$birth_date;

        $query  = "UPDATE student SET ";
        $query .= "name='$name_db', birth_city='$birth_city_db', ";
        $query .= "birth_date='$birth_date_full', faculty='$faculty_db', ";
        $query .= "department='$department_db', gpa=$gpa_db ";
        $query .= "WHERE nim='$nim_db'";

        $result = mysqli_query($connection, $query);
        if ($result) {
            $message = "Data mahasiswa dengan nama = \"<b>$name</b>\" berhasil diubah";
            header("Location: student_view.php?message=" . urlencode($message));
            die();
        } else {
            die("Query gagal dijalankan: " . mysqli_errno($connection) . " - " . mysqli_error($connection));
        }
    }
} else {
    // ===== AMBIL DATA LAMA UNTUK DITAMPILKAN DI FORM =====
    if (!isset($_GET["nim"])) {
        header("Location: student_view.php");
        die();
    }
    $nim_get = mysqli_real_escape_string($connection, $_GET["nim"]);
    $query   = "SELECT * FROM student WHERE nim='$nim_get'";
    $result  = mysqli_query($connection, $query);

    if (!$result) {
        die("Query Error: " . mysqli_errno($connection) . " - " . mysqli_error($connection));
    }
    if (mysqli_num_rows($result) == 0) {
        $message = "Data mahasiswa tidak ditemukan";
        header("Location: student_view.php?message=" . urlencode($message));
        die();
    }

    $data = mysqli_fetch_assoc($result);
    $nim        = $data["nim"];
    $name       = $data["name"];
    $birth_city = $data["birth_city"];
    $faculty    = $data["faculty"];
    $department = $data["department"];
    $gpa        = $data["gpa"];

    $birth_timestamp = strtotime($data["birth_date"]);
    $birth_date  = (int) date("j", $birth_timestamp);
    $birth_month = (int) date("n", $birth_timestamp);
    $birth_year  = (int) date("Y", $birth_timestamp);

    mysqli_free_result($result);
}

$select_ftib  = ($faculty === "FTIB")  ? "selected" : "";
$select_fteic = ($faculty === "FTEIC") ? "selected" : "";
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

    <h2>Edit Data Mahasiswa</h2>
    <?php
    if ($error_message !== "") {
        echo "<div class='error'>$error_message</div>";
    }
    ?>
    <form id="form_mahasiswa" action="student_edit.php" method="post">
        <fieldset>
            <legend>Edit Mahasiswa</legend>
            <p>
                <label for="nim_show">NIM : </label>
                <input type="text" id="nim_show" value="<?php echo $nim ?>" disabled>
                <!-- NIM adalah primary key, tidak boleh diubah, dikirim lewat hidden -->
                <input type="hidden" name="nim" value="<?php echo $nim ?>">
            </p>
            <p>
                <label for="name">Nama : </label>
                <input type="text" name="name" id="name" value="<?php echo $name ?>">
            </p>
            <p>
                <label for="birth_city">Tempat Lahir : </label>
                <input type="text" name="birth_city" id="birth_city" value="<?php echo $birth_city ?>">
            </p>
            <p>
                <label for="birth_date">Tanggal Lahir : </label>
                <select name="birth_date" id="birth_date">
                    <?php
                    for ($i = 1; $i <= 31; $i++) {
                        $sel = ($i == $birth_date) ? "selected" : "";
                        echo "<option value='$i' $sel>" . str_pad($i, 2, "0", STR_PAD_LEFT) . "</option>";
                    }
                    ?>
                </select>
                <select name="birth_month">
                    <?php
                    foreach ($arr_month as $key => $value) {
                        $sel = ($key == $birth_month) ? "selected" : "";
                        echo "<option value='$key' $sel>$value</option>";
                    }
                    ?>
                </select>
                <select name="birth_year">
                    <?php
                    for ($i = 1990; $i <= 2005; $i++) {
                        $sel = ($i == $birth_year) ? "selected" : "";
                        echo "<option value='$i' $sel>$i</option>";
                    }
                    ?>
                </select>
            </p>
            <p>
                <label for="faculty">Fakultas : </label>
                <select name="faculty" id="faculty">
                    <option value="FTIB" <?php echo $select_ftib ?>>FTIB</option>
                    <option value="FTEIC" <?php echo $select_fteic ?>>FTEIC</option>
                </select>
            </p>
            <p>
                <label for="department">Jurusan : </label>
                <input type="text" name="department" id="department" value="<?php echo $department ?>">
            </p>
            <p>
                <label for="gpa">IPK : </label>
                <input type="text" name="gpa" id="gpa" value="<?php echo $gpa ?>" placeholder="Contoh: 2.75">
                (angka desimal dipisah dengan karakter titik ".")
            </p>
        </fieldset>
        <br>
        <p>
            <input type="submit" name="submit" value="Simpan Perubahan">
        </p>
    </form>
</div>
</body>
</html>
<?php
mysqli_close($connection);
?>
