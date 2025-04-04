<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Data Management - PAGASA Plantilla System</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <?php include '../sidebar.php'; ?>

        <div class="main-content">
            <div class="tabs">
                <div class="tab active" data-tab="divisions">Office and Organizational Code</div>
                <div class="tab" data-tab="spreadsheet">Spreadsheet</div>
            </div>
            <div id="divisions-content" class="tab-content active">
                <div class="division-filter">
                    <input type="month" id="month-picker" name="month-picker" title="Select month">
                    <input type="text" id="division-search" name="division-search"
                        placeholder="Search divisions..."
                        title="Search divisions">
                </div>
                <div class="divisions-list" role="list">
                    <?php
                    $divisions = [
                        'Office of the Administrator' => 1,
                        'Administrative Division' => 2,
                    ];

                    foreach ($divisions as $name => $code): ?>
                        <div class="division-item" data-code="<?= $code ?>" role="listitem">
                            <div class="division-header">
                                <span class="division-code" role="text"><?= $code ?></span>
                                <span class="division-name" role="text"><?= $name ?></span>
                            </div>
                            <div class="division-actions">
                                <button class="view-data-btn"
                                    title="View data for <?= $name ?>"
                                    aria-label="View data for <?= $name ?>">
                                    View Data
                                </button>
                                <button class="open-files-btn"
                                    title="Open files for <?= $name ?>"
                                    aria-label="Open files for <?= $name ?>">
                                    Open Files
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="spreadsheet-content" class="tab-content">
                <div class="spreadsheet-toolbar" role="toolbar" aria-label="Spreadsheet controls">
                    <select id="division-filter" name="division-filter"
                        title="Filter by division" aria-label="Filter by division">
                        <option value="">All Divisions</option>
                    </select>
                    <button id="save-changes" title="Save changes" aria-label="Save changes">
                        <i class="fas fa-save" aria-hidden="true"></i>
                        <span>Save Changes</span>
                    </button>
                    <button id="export-data" title="Export data" aria-label="Export data">
                        <i class="fas fa-download" aria-hidden="true"></i>
                        <span>Export</span>
                    </button>
                </div>
                <div id="spreadsheet" role="grid">
                    <table role="presentation">
                        <thead role="rowgroup">
                            <tr role="row">
                            </tr>
                        </thead>
                        <tbody role="rowgroup">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="applicant-records-container">
        <div class="applicant-header">
            <h2>Applicant Records</h2>
            <div class="filter-controls">
                <select id="month-filter">
                    <option value="">Select Month</option>
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        $month = date('Y-m', strtotime("2024-$i-01"));
                        echo "<option value='$month'>" . date('F Y', strtotime($month)) . "</option>";
                    }
                    ?>
                </select>
                <select id="division-filter">
                    <option value="">Select Division</option>
                </select>
                <button id="apply-filters" class="btn btn-primary">Apply Filters</button>
            </div>
        </div>
        <div class="data-table">
            <table id="applicants-table">
                <thead>
                    <tr>
                        <th>ID NO.</th>
                        <th>FULLNAME</th>
                        <th>LAST NAME</th>
                        <th>FIRST NAME</th>
                        <th>MIDDLE NAME</th>
                        <th>EXTNAME</th>
                        <th>MI</th>
                        <th>SEX</th>
                        <th>POSITION TITLE</th>
                        <th>ITEM NUMBER</th>
                        <th>TECHCODE</th>
                        <th>LEVEL</th>
                        <th>APPOINTMENTSTATUS</th>
                        <th>SG</th>
                        <th>STEP</th>
                        <th>MONTHLYSALARY</th>
                        <th>DATE OFBIRTH</th>
                        <th>DATEORIG. APPT.</th>
                        <th>DATEGOVT SRVC</th>
                        <th>DATELASTPROMOTION</th>
                        <th>DATELAST INCREMENT</th>
                        <th>DATE OFLONGEVITY</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div id="applicant-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Applicant</h2>
                <span class="close">&times;</span>
            </div>
            <form id="applicant-form">
                <div class="form-group">
                    <label for="position-title">Position Title</label>
                    <input type="text" id="position-title" name="position_title" required>
                </div>
                <div class="form-group">
                    <label for="division">Division</label>
                    <select id="division" name="division" required>
                        <option value="">Select Division</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Applicant</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
    <script src="../js/data-management.js"></script>
</body>

</html>