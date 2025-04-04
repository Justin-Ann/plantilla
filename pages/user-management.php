<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Applicant Record - PAGASA Plantilla System</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <?php include '../sidebar.php'; ?>

        <div class="main-content">
            <section id="applicant-records" class="page">
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

            <!-- Applicant Modal -->
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
    <script src="../js/applicant-records.js"></script>
</body>

</html>