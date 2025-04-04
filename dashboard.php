<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Plantilla Management System</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>
            <div id="dashboard">
                <h2>Dashboard Overview</h2>
                <div class="status-counts">
                    <div class="card" id="on-process">
                        <h3>On Process</h3>
                        <div class="count" id="onProcess">0</div>
                    </div>
                    <div class="card" id="on-hold">
                        <h3>On Hold</h3>
                        <div class="count" id="onHold">0</div>
                    </div>
                    <div class="card" id="not-yet-filling">
                        <h3>Not Yet for Filling</h3>
                        <div class="count" id="notYetFilling">0</div>
                    </div>
                </div>

                <!-- Monthly Files Section -->
                <div class="section-header">
                    <h3>Monthly Files</h3>
                    <div class="controls">
                        <input type="text" id="file-search" placeholder="Search files...">
                        <button class="btn" id="upload-btn">Upload New File</button>
                    </div>
                </div>
                <div class="monthly-files" id="monthly-files"></div>

                <!-- Recent Files Section -->
                <div class="recent-files">
                    <h3>Recent Activities</h3>
                    <div id="recent-files"></div>
                </div>
            </div>
            <section id="data-management" class="page">
                <h2>Data Management</h2>
                <div class="tabs">
                    <div class="tab active" data-tab="divisions">Office and Organizational Code</div>
                    <div class="tab" data-tab="spreadsheet">Spreadsheet</div>
                </div>

                <div id="divisions-content" class="tab-content active">
                    <div class="division-filter">
                        <input type="text" id="division-search" placeholder="Search divisions...">
                    </div>
                    <div class="divisions-list" id="divisions-list"></div>
                </div>

                <div id="spreadsheet-content" class="tab-content">
                    <div class="excel-toolbar">
                        <div class="toolbar-group">
                            <button class="toolbar-btn" data-action="copy"><i class="fas fa-copy"></i></button>
                            <button class="toolbar-btn" data-action="cut"><i class="fas fa-cut"></i></button>
                            <button class="toolbar-btn" data-action="paste"><i class="fas fa-paste"></i></button>
                        </div>
                        <div class="toolbar-group">
                            <button class="toolbar-btn" data-action="bold"><i class="fas fa-bold"></i></button>
                            <button class="toolbar-btn" data-action="italic"><i class="fas fa-italic"></i></button>
                            <button class="toolbar-btn" data-action="underline"><i class="fas fa-underline"></i></button>
                        </div>
                        <div class="toolbar-group">
                            <button class="toolbar-btn" data-action="align-left"><i class="fas fa-align-left"></i></button>
                            <button class="toolbar-btn" data-action="align-center"><i class="fas fa-align-center"></i></button>
                            <button class="toolbar-btn" data-action="align-right"><i class="fas fa-align-right"></i></button>
                        </div>
                        <div class="toolbar-group">
                            <select class="font-family">
                                <option value="Arial">Arial</option>
                                <option value="Times New Roman">Times New Roman</option>
                                <option value="Calibri">Calibri</option>
                            </select>
                            <select class="font-size">
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                                <option value="14">14</option>
                                <option value="16">16</option>
                                <option value="18">18</option>
                                <option value="20">20</option>
                            </select>
                        </div>
                        <div class="toolbar-group">
                            <button class="toolbar-btn color-picker" data-action="font-color">
                                <i class="fas fa-font"></i>
                                <input type="color" class="hidden-color-input">
                            </button>
                            <button class="toolbar-btn color-picker" data-action="fill-color">
                                <i class="fas fa-fill-drip"></i>
                                <input type="color" class="hidden-color-input">
                            </button>
                        </div>
                        <div class="toolbar-group">
                            <button class="toolbar-btn" data-action="merge"><i class="fas fa-compress-alt"></i></button>
                            <button class="toolbar-btn" data-action="autofit"><i class="fas fa-arrows-alt-h"></i></button>
                        </div>
                    </div>
                    <div class="formula-bar">
                        <span class="cell-address">A1</span>
                        <input type="text" class="formula-input" placeholder="Enter formula">
                    </div>
                    <div id="spreadsheet-container"></div>
                </div>
            </section>
            <section id="applicants" class="page">
                <h2>Applicant Records</h2>
                <div class="filter-section">
                    <select id="month-filter">
                        <option value="">Select Month</option>
                    </select>
                    <select id="division-filter-records">
                        <option value="">Select Division</option>
                    </select>
                    <input type="text" id="search-records" placeholder="Search applicants...">
                    <button class="btn btn-primary" id="add-applicant">Add New Applicant</button>
                </div>
                <div class="data-table">
                    <table id="applicants-table">
                        <thead>
                            <tr role="row">
                                <th role="columnheader">ID No.</th>
                                <th role="columnheader">Full Name</th>
                                <th role="columnheader">Position Title</th>
                                <th role="columnheader">Division</th>
                                <th role="columnheader">Status</th>
                                <th role="columnheader">Actions</th>
                            </tr>
                        </thead>
                        <tbody role="rowgroup"></tbody>
                    </table>
                </div>
                <form id="applicant-form">
                    <div class="form-group">
                        <label for="fullname">Full Name</label>
                        <input type="text" id="fullname" name="fullname" required>
                    </div>
                    <div class="form-group">
                        <label for="sex">Sex</label>
                        <select id="sex" name="sex" required title="Select sex">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable@10.0.0/dist/handsontable.full.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/handsontable@10.0.0/dist/handsontable.full.min.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/data-management.js"></script>
    <script src="js/spreadsheet.js"></script>
    <script src="js/script.js"></script>
</body>

</html>