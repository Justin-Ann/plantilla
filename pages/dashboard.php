<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - PAGASA Plantilla System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>
    <div class="container">
        <?php include '../sidebar.php'; ?>

        <div class="main-content">
            <h2>Dashboard Overview</h2>
            <div class="status-counts">
                <div class="card status-card" data-status="On-process" id="on-process">
                    <h3>On Process</h3>
                    <div class="count">0</div>
                </div>
                <div class="card status-card" data-status="On-hold" id="on-hold">
                    <h3>On Hold</h3>
                    <div class="count">0</div>
                </div>
                <div class="card status-card" data-status="Not-yet-for-filling" id="not-yet-for-filling">
                    <h3>Not Yet for Filing</h3>
                    <div class="count">0</div>
                </div>
            </div>
            <div class="section">
                <div class="section-header">
                    <h3>Monthly Files</h3>
                    <div class="controls">
                        <input type="month" id="month-picker">
                        <button id="upload-btn" class="btn">Upload New File</button>
                    </div>
                </div>
                <div class="monthly-files" id="monthly-files-list"></div>
            </div>
            <div class="section">
                <h3>Recently Edited Files</h3>
                <div class="recent-files" id="recent-files-list"></div>
            </div>
        </div>
    </div>
    <div id="upload-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Upload Monthly File</h3>
            <form id="upload-form" enctype="multipart/form-data" method="POST">
                <div class="form-group">
                    <label for="division">Select Division</label>
                    <select id="division" name="division_code" required>
                        <option value="">-- Select Division --</option>
                        <?php
                        require_once "../config.php";
                        $query = "SELECT division_code, division_name FROM division_definitions ORDER BY division_order";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$row['division_code']}'>{$row['division_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="file-month">Month</label>
                    <input type="month" id="file-month" name="month_year" required>
                </div>
                <div class="form-group">
                    <label for="file-upload">Select File</label>
                    <input type="file" id="file-upload" name="file-upload" accept=".xlsx,.xls,.csv" required>
                </div>
                <button type="submit" class="btn">Upload</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="../js/dashboard.js"></script>
</body>

</html>