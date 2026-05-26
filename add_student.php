<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: login.php");
require_once 'config.php';

$error = '';
$success = '';

$dorms = $conn->query("SELECT *, CONCAT(building_no,'号楼',floor_no,'层',room_no,'室') as full_name
                       FROM dormitory WHERE available_beds > 0 ORDER BY building_no, floor_no, room_no");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_no = $conn->real_escape_string($_POST['student_no']);
    $name = $conn->real_escape_string($_POST['name']);
    $class_name = $conn->real_escape_string($_POST['class_name']);
    $gender = $_POST['gender'];
    $dorm_id = intval($_POST['dorm_id']);

    $check = $conn->query("SELECT id FROM student WHERE student_no='$student_no'");
    if ($check->num_rows > 0) {
        $error = "该学号已存在，请检查后重试";
    } else {
        $dormInfo = $conn->query("SELECT * FROM dormitory WHERE id=$dorm_id")->fetch_assoc();
        if (!$dormInfo || $dormInfo['gender'] !== $gender) {
            $error = "该宿舍与所选性别不匹配，请重新选择";
        } else {
            $usedBeds = $conn->query("SELECT bed_no FROM student WHERE dorm_id=$dorm_id");
            $used = [];
            while($b = $usedBeds->fetch_assoc()) $used[] = $b['bed_no'];
            $freeBed = 0;
            for($i=1;$i<=4;$i++) if(!in_array($i,$used)) { $freeBed = $i; break; }

            if($freeBed) {
                $conn->query("INSERT INTO student (student_no, name, class_name, gender, dorm_id, bed_no)
                              VALUES ('$student_no','$name','$class_name','$gender',$dorm_id,$freeBed)");
                $conn->query("UPDATE dormitory SET available_beds = available_beds - 1 WHERE id=$dorm_id");
                $success = "添加成功！已分配至 {$dormInfo['building_no']}号楼 {$dormInfo['room_no']}室 · {$freeBed}号床";
                $dorms = $conn->query("SELECT *, CONCAT(building_no,'号楼',floor_no,'层',room_no,'室') as full_name
                                       FROM dormitory WHERE available_beds > 0 ORDER BY building_no, floor_no, room_no");
            } else {
                $error = "该宿舍已无空床位，请选择其他宿舍";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>添加学生 - 宿舍管理系统</title>
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
                <a href="add_student.php" class="active">
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
                    <span class="current">添加学生</span>
                </div>

                <div class="form-card">
                    <div class="form-card-header">
                        <div class="form-icon">🎓</div>
                        <h2>添加学生</h2>
                        <p class="form-desc">录入新生信息，系统将自动分配宿舍与床位</p>
                    </div>

                    <div class="form-card-body">
                        <?php if($error): ?>
                            <div class="alert alert-error"><?= $error ?></div>
                        <?php endif; ?>
                        <?php if($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <div class="form-field">
                                <label for="student_no">
                                    <span class="label-icon">🔢</span>
                                    学号 <span class="hint">(必填)</span>
                                </label>
                                <div class="input-wrap">
                                    <input type="text" id="student_no" name="student_no" class="form-control"
                                           placeholder="例如：2024001" required>
                                </div>
                            </div>

                            <div class="form-field-row">
                                <div class="form-field">
                                    <label for="name">
                                        <span class="label-icon">👤</span>
                                        姓名 <span class="hint">(必填)</span>
                                    </label>
                                    <div class="input-wrap">
                                        <input type="text" id="name" name="name" class="form-control"
                                               placeholder="请输入姓名" required>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label for="class_name">
                                        <span class="label-icon">📚</span>
                                        班级
                                    </label>
                                    <div class="input-wrap">
                                        <input type="text" id="class_name" name="class_name" class="form-control"
                                               placeholder="例如：计算机1班">
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
                                        <select id="gender" name="gender" class="form-control" required>
                                            <option value="男">男</option>
                                            <option value="女">女</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label for="dorm_id">
                                        <span class="label-icon">🏠</span>
                                        宿舍 <span class="hint">(有空位)</span>
                                    </label>
                                    <div class="input-wrap">
                                        <select id="dorm_id" name="dorm_id" class="form-control" required>
                                            <option value="">-- 请先选择性别 --</option>
                                            <?php while($d = $dorms->fetch_assoc()): ?>
                                            <option value="<?= $d['id'] ?>" data-gender="<?= $d['gender'] ?>">
                                                <?= $d['full_name'] ?>（空床 <?= $d['available_beds'] ?> 个）
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-submit green">
                                    ✦ 确认添加
                                </button>
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
        var defaultOption = dormEl.querySelector('option[value=""]');

        function filterDorms() {
            var gender = genderEl.value;
            defaultOption.textContent = '-- 请选择' + gender + '生宿舍 --';
            defaultOption.selected = true;

            var hasVisible = false;
            var options = dormEl.querySelectorAll('option[value!=""]');
            options.forEach(function(opt) {
                if (opt.getAttribute('data-gender') === gender) {
                    opt.style.display = '';
                    hasVisible = true;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (!hasVisible) {
                defaultOption.textContent = '-- 暂无可用' + gender + '生宿舍 --';
            }
        }

        genderEl.addEventListener('change', filterDorms);
        filterDorms();
    })();
    </script>
</body>
</html>
