<?php
session_start();
if (!isset($_SESSION['admin'])) header("Location: login.php");
require_once 'config.php';

// 搜索与筛选
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

$where = '';
if ($search !== '') {
    $keyword = $conn->real_escape_string($search);
    $where = "WHERE CONCAT(building_no, floor_no, room_no) LIKE '%$keyword%' OR building_no LIKE '%$keyword%' OR room_no LIKE '%$keyword%'";
} else {
    $where = "WHERE 1=1";
}

if ($status === 'vacant') {
    $where .= " AND available_beds > 0";
} elseif ($status === 'full') {
    $where .= " AND available_beds = 0";
}

$sql = "SELECT *, CONCAT(building_no,'号楼',floor_no,'层',room_no,'室') as room_name
        FROM dormitory $where
        ORDER BY building_no, floor_no, room_no";
$result = $conn->query($sql);

// 统计
$total_vacant = $conn->query("SELECT SUM(available_beds) as total FROM dormitory")->fetch_assoc()['total'];
$total_rooms = $conn->query("SELECT COUNT(*) as total FROM dormitory")->fetch_assoc()['total'];
$total_capacity = $conn->query("SELECT SUM(capacity) as total FROM dormitory")->fetch_assoc()['total'];
$full_count = $conn->query("SELECT COUNT(*) as total FROM dormitory WHERE available_beds = 0")->fetch_assoc()['total'];
$filtered_count = $result ? $result->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>空缺床位 - 宿舍管理系统</title>
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
                <a href="vacant_beds.php" class="active">
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

        <main class="main-content">
            <div class="breadcrumb">
                <a href="index.php">首页</a>
                <span class="sep">/</span>
                <span class="current">空缺床位</span>
            </div>

            <div class="page-header">
                <div>
                    <h2>空缺床位</h2>
                    <p class="header-sub">查看所有宿舍的床位占用与空余情况</p>
                </div>
                <div class="header-actions">
                    <a href="add_student.php" class="btn btn-primary">＋ 添加学生</a>
                    <a href="index.php" class="btn btn-ghost">← 返回首页</a>
                </div>
            </div>

            <!-- 统计卡片 -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon green">🛏️</div>
                    <div class="stat-info">
                        <h3><?= $total_vacant ?: 0 ?></h3>
                        <p>空闲床位总数</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">🏠</div>
                    <div class="stat-info">
                        <h3><?= $total_rooms ?: 0 ?></h3>
                        <p>宿舍总数</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber">📐</div>
                    <div class="stat-info">
                        <h3><?= $total_capacity ?: 0 ?></h3>
                        <p>总床位容量</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon rose">📊</div>
                    <div class="stat-info">
                        <h3><?= $total_capacity ? round(($total_capacity - $total_vacant) / $total_capacity * 100) : 0 ?>%</h3>
                        <p>总体入住率</p>
                        <?php if($total_capacity): ?>
                            <p class="stat-trend"><?= $total_capacity - $total_vacant ?> / <?= $total_capacity ?> 床位</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="building-gender-info" style="margin-bottom:24px;">
                <span style="font-weight:600;color:var(--text-primary);">楼栋分配：</span>
                <span class="bldg-tag blue">1号楼 · 男生</span>
                <span class="bldg-tag blue">2号楼 · 男生</span>
                <span class="bldg-tag pink">3号楼 · 女生</span>
            </div>

            <!-- 🔍 搜索区域 -->
            <div class="search-section">
                <form class="search-form" method="get" action="vacant_beds.php" id="searchForm">
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
                               placeholder="搜索楼号或房间号…"
                               value="<?= htmlspecialchars($search) ?>"
                               autocomplete="off"
                               id="searchInput">
                        <?php if ($search !== '' || $status !== 'all'): ?>
                            <a href="vacant_beds.php" class="search-clean" title="清除">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </a>
                        <?php endif; ?>
                        <div class="search-haze"></div>
                        <div class="search-filter-bar">
                            <input type="hidden" name="status" id="statusInput" value="<?= $status ?>">
                            <button type="button" class="filter-chip <?= $status === 'all' ? 'is-active' : '' ?>" data-status="all">
                                <span class="chip-dot"></span>全部
                            </button>
                            <button type="button" class="filter-chip <?= $status === 'vacant' ? 'is-active' : '' ?>" data-status="vacant">
                                <span class="chip-dot"></span>有空床
                            </button>
                            <button type="button" class="filter-chip <?= $status === 'full' ? 'is-active' : '' ?>" data-status="full">
                                <span class="chip-dot"></span>已住满
                            </button>
                        </div>
                        <button type="submit" class="search-go" title="搜索">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    <?php if ($search !== '' || $status !== 'all'): ?>
                    <div class="search-badge">
                        <span class="badge-dot"></span>
                        <?php if ($search !== ''): ?>
                            <span class="badge-term">「<?= htmlspecialchars($search) ?>」</span>
                            <span class="badge-sep"></span>
                        <?php endif; ?>
                        <?php if ($status !== 'all'): ?>
                            <span><?= $status === 'vacant' ? '有空床' : '已住满' ?></span>
                            <span class="badge-sep"></span>
                        <?php endif; ?>
                        <span class="badge-result">命中 <strong><?= $filtered_count ?></strong> 间</span>
                        <a href="vacant_beds.php" class="badge-dismiss">清除</a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- 宿舍列表 -->
            <div class="card">
                <div class="card-header">
                    <h3>🛏️ 宿舍入住详情</h3>
                    <span class="text-sm text-muted">共 <?= $filtered_count ?> 间</span>
                </div>
                <div class="card-body">
                    <?php if($result && $result->num_rows > 0): ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>宿舍</th>
                                    <th>类型</th>
                                    <th>总床位</th>
                                    <th>已入住</th>
                                    <th>空闲床位</th>
                                    <th>入住率</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result->data_seek(0);
                                while($row = $result->fetch_assoc()):
                                    $occupied = $row['capacity'] - $row['available_beds'];
                                    $rate = $row['capacity'] > 0 ? round(($occupied / $row['capacity']) * 100) : 0;
                                    $isFull = $row['available_beds'] == 0;
                                    $fillClass = $rate >= 90 ? 'high' : ($rate >= 70 ? 'mid' : 'low');
                                ?>
                                <tr>
                                    <td><strong><?= $row['room_name'] ?></strong></td>
                                    <td><span class="dorm-gender-badge <?= $row['gender'] === '男' ? 'male' : 'female' ?>"><?= $row['gender'] ?></span></td>
                                    <td><?= $row['capacity'] ?> 个</td>
                                    <td><?= $occupied ?> 人</td>
                                    <td>
                                        <?php if ($isFull): ?>
                                            <span style="color:var(--danger);font-weight:500;">已满</span>
                                        <?php else: ?>
                                            <span style="color:var(--success);font-weight:600;"><?= $row['available_beds'] ?> 个</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="dorm-progress">
                                            <div class="track">
                                                <div class="fill <?= $fillClass ?>" style="width:<?= $rate ?>%"></div>
                                            </div>
                                            <span class="label" style="color:<?= $rate >= 90 ? 'var(--danger)' : ($rate >= 70 ? 'var(--warning)' : 'var(--text-muted)') ?>"><?= $rate ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="dorm-status <?= $isFull ? 'full' : 'available' ?>">
                                            <span class="status-dot"></span>
                                            <?= $isFull ? '已住满' : '有空床' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"><?= $search !== '' || $status !== 'all' ? '🔎' : '🎉' ?></div>
                        <p>
                            <?php if ($search !== '' || $status !== 'all'): ?>
                                没有匹配的宿舍
                            <?php else: ?>
                                暂无空缺床位，所有宿舍已住满
                            <?php endif; ?>
                        </p>
                        <?php if ($search !== '' || $status !== 'all'): ?>
                            <p class="text-xs text-muted mt-8">试试其他关键词或筛选条件</p>
                            <a href="vacant_beds.php" class="btn btn-ghost mt-16">清除筛选</a>
                        <?php else: ?>
                            <p class="text-xs text-muted mt-8">可以考虑扩建宿舍了！</p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
    (function() {
        'use strict';

        // 筛选芯片切换
        var chips = document.querySelectorAll('.filter-chip');
        var statusInput = document.getElementById('statusInput');
        var searchForm = document.getElementById('searchForm');

        chips.forEach(function(chip) {
            chip.addEventListener('click', function() {
                chips.forEach(function(c) { c.classList.remove('is-active'); });
                this.classList.add('is-active');
                statusInput.value = this.dataset.status;
                searchForm.submit();
            });
        });

        // 搜索框聚焦态
        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                this.parentElement.classList.add('is-focused');
            });
            searchInput.addEventListener('blur', function() {
                this.parentElement.classList.remove('is-focused');
            });
        }
    })();
    </script>
</body>
</html>
