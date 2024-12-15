<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Xử lý tìm kiếm
$search_query = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_record'])) {
    $search_query = $_POST['search_query'];
    $sql_borrow = "SELECT * FROM borrow_records WHERE student_name LIKE ? OR student_id LIKE ? ORDER BY borrow_date DESC";
    $stmt = $conn->prepare($sql_borrow);
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("ss", $like_query, $like_query);
} else {
    $sql_borrow = "SELECT * FROM borrow_records ORDER BY borrow_date DESC";
    $stmt = $conn->prepare($sql_borrow);
}

$stmt->execute();
$result_borrow = $stmt->get_result();

// Xử lý thêm thông tin mượn sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $student_name = $_POST['student_name'];
    $student_id = $_POST['student_id'];
    $email = $_POST['email'];
    $book_title = $_POST['book_title'];
    $borrow_date = date('Y-m-d H:i:s');

    $sql = "INSERT INTO borrow_records (student_name, student_id, email, book_title, borrow_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $student_name, $student_id, $email, $book_title, $borrow_date);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Đã thêm thông tin mượn sách thành công!</p>";
    } else {
        $message = "<p style='color:red;'>Lỗi: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Xử lý xóa thông tin mượn sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_record'])) {
    $id = $_POST['record_id'];
    $sql = "DELETE FROM borrow_records WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "<p style='color:green;'>Đã xóa thông tin mượn sách thành công!</p>";
    } else {
        $message = "<p style='color:red;'>Lỗi: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Mượn Sách</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h2> Mượn Sách</h2>

    <!-- Hiển thị thông báo -->
    <?php echo $message; ?>

    <!-- Form Tìm kiếm -->
    <form method="POST">
        <label for="search_query">Tìm kiếm theo tên sinh viên hoặc mã số sinh viên:</label>
        <input type="text" id="search_query" name="search_query" value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" name="search_record">Tìm kiếm</button>
    </form>

    <h2>Danh Sách Mượn Sách</h2>
    <table>
        <thead>
            <tr>
                <th>Họ và Tên</th>
                <th>Mã Số Sinh Viên</th>
                <th>Email</th>
                <th>Tên Sách</th>
                <th>Ngày Giờ Mượn</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_borrow->num_rows > 0): ?>
                <?php while ($row = $result_borrow->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['book_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                        <td>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="record_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="delete_record" value="1">
                                <button type="submit" style="background-color:#dc3545;color:white;border:none;padding:5px 10px;border-radius:5px;">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Chưa có dữ liệu mượn sách nào phù hợp.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
