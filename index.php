<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

// 处理删除学生
if (isset($_GET['del_id'])) {
    $id = intval($_GET['del_id']);
    $stu = $conn->query("SELECT dorm_id, bed_no FROM student WHERE id=$id")->fetch_assoc();
    if ($stu && $stu['dorm_id']) {
        $conn->query("UPDATE dormitory SET available_beds = available_beds + 1 WHERE id=" . $stu['dorm_id']);
    }
    $conn->query("DELETE FROM student WHERE id=$id");
    header("Location: index.php");
    exit();
}

// 搜索逻辑
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchField = isset($_GET['field']) ? $_GET['field'] : 'all';

$where = '';
$params = [];
if ($search !== '') {
    $keyword = $conn->real_escape_string($search);
    if ($searchField === 'all') {
        $where = "WHERE s.student_no LIKE '%$keyword%' OR s.name LIKE '%$keyword%' OR s.class_name LIKE '%$keyword%'";
    } elseif ($searchField === 'student_no') {
        $where = "WHERE s.student_no LIKE '%$keyword%'";
    } elseif ($searchField === 'name') {
        $where = "WHERE s.name LIKE '%$keyword%'";
    } elseif ($searchField === 'class') {
        $where = "WHERE s.class_name LIKE '%$keyword%'";
    }
}

// 查询学生（带搜索过滤）
$students = $conn->query("SELECT s.*, d.building_no, d.floor_no, d.room_no, d.gender AS dorm_gender
                          FROM student s
                          LEFT JOIN dormitory d ON s.dorm_id = d.id
                          $where
                          ORDER BY s.id DESC");

// 获取筛选后的记录数（用于搜索结果提示）
$filtered_count = $students ? $students->num_rows : 0;

// 统计数据
$total_students = $conn->query("SELECT COUNT(*) as count FROM student")->fetch_assoc()['count'];
$total_vacant = $conn->query("SELECT SUM(available_beds) as vacant FROM dormitory")->fetch_assoc()['vacant'];
$total_dorms = $conn->query("SELECT COUNT(*) as count FROM dormitory")->fetch_assoc()['count'];
$full_dorms = $conn->query("SELECT COUNT(*) as count FROM dormitory WHERE available_beds = 0")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>学生宿舍管理系统</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
    <div class="app-layout">
        <!-- 侧边栏 -->
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
                <a href="index.php" class="active">
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

        <!-- 主内容 -->
        <main class="main-content">
            <div class="page-header">
                <div>
                    <h2>学生总览</h2>
                    <p class="header-sub">查看和管理所有住宿学生信息</p>
                </div>
                <div class="header-actions">
                    <a href="add_student.php" class="btn btn-primary">＋ 添加学生</a>
                    <a href="vacant_beds.php" class="btn btn-ghost">🛏️ 空缺床位</a>
                </div>
            </div>

            <!-- 统计卡片 -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon amber">👨‍🎓</div>
                    <div class="stat-info">
                        <h3><?= $total_students ?></h3>
                        <p>在宿学生</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">🛏️</div>
                    <div class="stat-info">
                        <h3><?= $total_vacant ?: 0 ?></h3>
                        <p>空闲床位</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">🏢</div>
                    <div class="stat-info">
                        <h3><?= $total_dorms ?></h3>
                        <p>宿舍总数</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon rose">📦</div>
                    <div class="stat-info">
                        <h3><?= $full_dorms ?></h3>
                        <p>已满宿舍</p>
                    </div>
                </div>
            </div>

            <!-- 🏢 楼栋分配 -->
            <div class="building-gender-info" style="margin-bottom:24px;">
                <span style="font-weight:600;color:var(--text-primary);">楼栋分配：</span>
                <span class="bldg-tag blue">1号楼 · 男生</span>
                <span class="bldg-tag blue">2号楼 · 男生</span>
                <span class="bldg-tag pink">3号楼 · 女生</span>
            </div>

            <!-- 🔍 搜索区域 -->
            <div class="search-section">
                <form class="search-form" method="get" action="index.php" id="searchForm">
                    <div class="search-field" id="searchFieldContainer">
                        <div class="search-icon-box">
                            <svg class="search-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               class="search-input"
                               placeholder="搜索学号、姓名或班级…"
                               value="<?= htmlspecialchars($search) ?>"
                               autocomplete="off"
                               id="searchInput">
                        <?php if ($search !== ''): ?>
                            <a href="index.php" class="search-clean" title="清除搜索">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </a>
                        <?php endif; ?>
                        <div class="search-haze"></div>
                        <div class="search-scope" id="scopeTabs">
                            <button type="button" class="scope-chip" data-field="all"><span class="chip-dot"></span>全部</button>
                            <button type="button" class="scope-chip" data-field="student_no"><span class="chip-dot"></span>学号</button>
                            <button type="button" class="scope-chip" data-field="name"><span class="chip-dot"></span>姓名</button>
                            <button type="button" class="scope-chip" data-field="class"><span class="chip-dot"></span>班级</button>
                        </div>
                        <input type="hidden" name="field" id="fieldInput" value="<?= $searchField ?>">
                        <button type="submit" class="search-go" title="搜索">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    <?php if ($search !== ''): ?>
                    <div class="search-badge">
                        <span class="badge-dot"></span>
                        <span class="badge-term">「<?= htmlspecialchars($search) ?>」</span>
                        <span class="badge-sep"></span>
                        <span class="badge-result">命中 <strong><?= $filtered_count ?></strong> 条</span>
                        <a href="index.php" class="badge-dismiss">清除</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- 学生列表 -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <?php if ($search !== ''): ?>
                            🔎 搜索结果
                        <?php else: ?>
                            🏠 所有学生住宿情况
                        <?php endif; ?>
                    </h3>
                    <span class="text-sm text-muted">
                        <?php if ($search !== ''): ?>
                            共 <?= $filtered_count ?> 条匹配结果
                        <?php else: ?>
                            共 <?= $total_students ?> 条记录
                        <?php endif; ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>学号</th>
                                    <th>姓名</th>
                                    <th>班级</th>
                                    <th>性别</th>
                                    <th>宿舍</th>
                                    <th>床位</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($students && $students->num_rows > 0): ?>
                                    <?php while($row = $students->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['student_no']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['class_name']) ?></td>
                                        <td>
                                            <span class="badge <?= $row['gender'] === '男' ? 'badge-info' : 'badge-success' ?>">
                                                <?= $row['gender'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($row['building_no']): ?>
                                                <?= $row['building_no'] ?>号楼 · <?= $row['room_no'] ?>室
                                                <span class="dorm-gender-badge <?= $row['dorm_gender'] === '男' ? 'male' : 'female' ?>" style="margin-left:6px"><?= $row['dorm_gender'] ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">未分配</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['bed_no']): ?>
                                                <span class="badge badge-accent"><?= $row['bed_no'] ?> 号床</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-group">
                                                <a href="edit_student.php?id=<?= $row['id'] ?>" class="btn btn-edit">✎ 修改</a>
                                                <a href="?del_id=<?= $row['id'] ?>" class="btn btn-del" onclick="return confirm('确定要删除该学生「<?= htmlspecialchars($row['name']) ?>」吗？此操作不可撤销。')">✕ 删除</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">
                                            <div class="empty-state">
                                                <div class="empty-icon">
                                                    <?php if ($search !== ''): ?>🔎<?php else: ?>📋<?php endif; ?>
                                                </div>
                                                <p>
                                                    <?php if ($search !== ''): ?>
                                                        未找到匹配「<?= htmlspecialchars($search) ?>」的学生
                                                    <?php else: ?>
                                                        暂无学生数据
                                                    <?php endif; ?>
                                                </p>
                                                <?php if ($search !== ''): ?>
                                                    <p class="text-xs text-muted mt-8">请尝试更换关键词或搜索字段</p>
                                                    <a href="index.php" class="btn btn-ghost mt-16">清除搜索</a>
                                                <?php else: ?>
                                                    <a href="add_student.php" class="btn btn-primary mt-16">添加第一位学生</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
<script>
(function() {
    'use strict';

    // 作用域芯片切换
    var activeField = '<?= $searchField ?>';
    var chips = document.querySelectorAll('.scope-chip');
    var fieldInput = document.getElementById('fieldInput');
    var searchForm = document.getElementById('searchForm');
    var searchInput = document.getElementById('searchInput');

    chips.forEach(function(chip) {
        if (chip.dataset.field === activeField) {
            chip.classList.add('is-active');
        }
        chip.addEventListener('click', function() {
            chips.forEach(function(c) { c.classList.remove('is-active'); });
            this.classList.add('is-active');
            fieldInput.value = this.dataset.field;
            if (searchInput && searchInput.value.trim() !== '') {
                searchForm.submit();
            }
        });
    });

    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.parentElement.classList.add('is-focused');
        });
        searchInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('is-focused');
        });
        searchInput.addEventListener('focus', function() { this.select(); });
    }
})();
</script>
</body>
</html>
