<?php
session_start();
require_once "../config.php";           // Assuming this sets up $conn (mysqli)
require_once "../auth_middleware.php";  // Assumes this checks user auth and redirects if not

$query = "SELECT division_name, division_code FROM division_definitions ORDER BY division_order ASC";
$result = $conn->query($query);

$divisions = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $divisions[] = [
            'name' => $row['division_name'],
            'code' => $row['division_code']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Data Management - Divisions</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
        }

        .division-sidebar {
            width: 300px;
            background-color: #f4f4f4;
            padding: 15px;
            height: 100vh;
            overflow-y: auto;
            border-right: 1px solid #ccc;
        }

        .division-sidebar h2 {
            margin-top: 0;
        }

        .division-item {
            cursor: pointer;
            padding: 8px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            background-color: #fff;
            border-radius: 4px;
        }

        .division-item:hover {
            background-color: #e0e0e0;
        }

        .content {
            padding: 20px;
            flex-grow: 1;
        }

        .file-display {
            margin-top: 20px;
            padding: 10px;
            background-color: #fefefe;
            border: 1px solid #ddd;
        }

        select {
            margin-top: 10px;
            padding: 5px;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="division-sidebar">
            <h2>Divisions</h2>
            <div id="divisionList"></div>
        </div>

        <div class="content">
            <h1>Data Viewer</h1>
            <label for="monthSelect">Select Month:</label>
            <select id="monthSelect">
                <option value="">-- Select Month --</option>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $monthName = date('F', mktime(0, 0, 0, $i, 10));
                    echo "<option value='$i'>$monthName</option>";
                }
                ?>
            </select>

            <div class="file-display" id="fileDisplay">
                Select a division and month to view data.
            </div>
        </div>
    </div>

    <script>
        const divisions = <?php echo json_encode($divisions, JSON_UNESCAPED_UNICODE); ?>;
        const divisionList = document.getElementById("divisionList");

        divisions.forEach(division => {
            const div = document.createElement("div");
            div.className = "division-item";
            div.textContent = division.name;
            div.addEventListener("click", () => {
                const selectedMonth = document.getElementById("monthSelect").value;
                if (!selectedMonth) {
                    alert("Please select a month.");
                    return;
                }
                document.getElementById("fileDisplay").innerHTML = `
                    <strong>Division:</strong> ${division.name} <br>
                    <strong>Code:</strong> ${division.code} <br>
                    <strong>Selected Month:</strong> ${new Date(0, selectedMonth - 1).toLocaleString('default', { month: 'long' })}
                    <br><br><em>Display file or data content here...</em>
                `;
            });
            divisionList.appendChild(div);
        });
        
    </script>
</body>

</html>