<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: login.php");
require_once 'config.php';

$id = intval($_GET['id']);
$student = $conn->query("SELECT * FROM student WHERE id=$id")->fetch_assoc();
if (!$student) die("学生不存在");

$error = '';

$allDorms = $conn->query("SELECT *, CONCAT(building_no,'号楼',floor_no,'层',room_no,'室') as full_name
                          FROM dormitory ORDER BY building_no, floor_no, room_no");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_no = $conn->real_escape_string($_POST['student_no']);
    $name = $conn->real_escape_string($_POST['name']);
    $class_name = $conn->real_escape_string($_POST['class_name']);
    $gender = $_POST['gender'];
    $new_dorm_id = intval($_POST['dorm_id']);
    $old_dorm_id = $student['dorm_id'];

    $newDormInfo = $conn->query("SELECT gender FROM dormitory WHERE id=$new_dorm_id")->fetch_assoc();
    if ($newDormInfo && $newDormInfo['gender'] !== $gender) {
        $error = "性别与所选宿舍不匹配";
    } elseif ($new_dorm_id != $old_dorm_id && $old_dorm_id) {
        $conn->query("UPDATE dormitory SET available_beds = available_beds + 1 WHERE id=$old_dorm_id");
        $usedBeds = $conn->query("SELECT bed_no FROM student WHERE dorm_id=$new_dorm_id");
        $used = [];
        while($b = $usedBeds->fetch_assoc()) $used[] = $b['bed_no'];
        $freeBed = 0;
        for($i=1;$i<=4;$i++) if(!in_array($i,$used)) { $freeBed = $i; break; }
        if($freeBed) {
            $conn->query("UPDATE student SET student_no='$student_no', name='$name', class_name='$class_name', gender='$gender', dorm_id=$new_dorm_id, bed_no=$freeBed WHERE id=$id");
            $conn->query("UPDATE dormitory SET available_beds = available_beds - 1 WHERE id=$new_dorm_id");
            header("Location: index.php");
            exit();
        } else {
            $error = "新宿舍无空床位";
        }
    } else {
        $conn->query("UPDATE student SET student_no='$student_no', name='$name', class_name='$class_name', gender='$gender' WHERE id=$id");
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改学生 - 宿舍管理系统</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="index.php" class="sidebar-brand">
                    <div class="sidebar-logo">🏠</div>
                    <div class="sidebar-brand-text">
                        <h1>宿舍管理系统</h1>
                        <div class="subtitle">Dormitory Management</div>
                    </div>
                </a>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section-label">概览</div>
                <a href="index.php">
                    <span class="nav-icon">📊</span>
                    <span>学生总览</span>
                </a>
                <a href="add_student.php">
                    <span class="nav-icon">➕</span>
                    <span>添加学生</span>
                </a>
                <a href="vacant_beds.php">
                    <span class="nav-icon">🛏️</span>
                    <span>空缺床位</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php">
                    <span class="nav-icon">🚪</span>
                    <span>退出登录</span>
                </a>
            </div>
        </aside>

        <main class="main-content form-page">
            <div class="form-page-glow"></div>
            <div class="main-inner">
                <div class="breadcrumb">
                    <a href="index.php">首页</a>
                    <span class="sep">/</span>
                    <span class="current">修改学生</span>
                </div>

                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-icon">✎</div>
                        <h2>修改学生信息</h2>
                        <p class="form-desc">编辑 <?= htmlspecialchars($student['name']) ?> 的基本信息与宿舍分配</p>
                    </div>

                    <div class="form-card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-error"><?= $error ?></div>
                        <?php endif; ?>
                        <?php
                        $curDorm = $conn->query("SELECT building_no, floor_no, room_no FROM dormitory WHERE id=" . $student['dorm_id'])->fetch_assoc();
                        ?>
                        <div class="form-info-banner">
                            <div class="banner-icon">📍</div>
                            <div class="banner-content">
                                <div class="banner-label">当前宿舍信息</div>
                                <div class="banner-value">
                                    <?php if($curDorm): ?>
                                        <?= $curDorm['building_no'] ?>号楼 · <?= $curDorm['room_no'] ?>室 · <?= $student['bed_no'] ?>号床
                                    <?php else: ?>
                                        未分配宿舍
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <form method="post">
                            <div class="form-field">
                                <label for="student_no">
                                    <span class="label-icon">🔢</span>
                                    学号
                                </label>
                                <div class="input-wrap">
                                    <input type="text" id="student_no" name="student_no" class="form-control"
                                           value="<?= htmlspecialchars($student['student_no']) ?>" required>
                                </div>
                            </div>

                            <div class="form-field-row">
                                <div class="form-field">
                                    <label for="name">
                                        <span class="label-icon">👤</span>
                                        姓名
                                    </label>
                                    <div class="input-wrap">
                                        <input type="text" id="name" name="name" class="form-control"
                                               value="<?= htmlspecialchars($student['name']) ?>" required>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label for="class_name">
                                        <span class="label-icon">📚</span>
                                        班级
                                    </label>
                                    <div class="input-wrap">
                                        <input type="text" id="class_name" name="class_name" class="form-control"
                                               value="<?= htmlspecialchars($student['class_name']) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-field-row">
                                <div class="form-field">
                                    <label for="gender">
                                        <span class="label-icon">⚤</span>
                                        性别
                                    </label>
                                    <div class="input-wrap">
                                        <select id="gender" name="gender" class="form-control">
                                            <option value="男" <?= $student['gender']=='男'?'selected':'' ?>>男</option>
                                            <option value="女" <?= $student['gender']=='女'?'selected':'' ?>>女</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label for="dorm_id">
                                        <span class="label-icon">🏠</span>
                                        调换宿舍
                                    </label>
                                    <div class="input-wrap">
                                        <select id="dorm_id" name="dorm_id" class="form-control">
                                            <?php while($d = $allDorms->fetch_assoc()): ?>
                                            <option value="<?= $d['id'] ?>" data-gender="<?= $d['gender'] ?>" <?= ($d['id']==$student['dorm_id'])?'selected':'' ?>>
                                                <?= $d['full_name'] ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <div class="form-actions-row">
                                    <button type="submit" class="btn-submit primary">
                                        ✦ 保存修改
                                    </button>
                                    <a href="index.php" class="btn-cancel">取消</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
    (function() {
        var genderEl = document.getElementById('gender');
        var dormEl = document.getElementById('dorm_id');

        function filterDorms() {
            var gender = genderEl.value;
            var options = dormEl.querySelectorAll('option[value!=""]');
            var firstVisible = null;
            options.forEach(function(opt) {
                if (opt.getAttribute('data-gender') === gender) {
                    opt.style.display = '';
                    if (!firstVisible) firstVisible = opt;
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) opt.selected = false;
                }
            });
            if (!dormEl.querySelector('option:checked') && firstVisible) {
                firstVisible.selected = true;
            }
        }

        genderEl.addEventListener('change', filterDorms);
        filterDorms();
    })();
    </script>
</body>
</html>
