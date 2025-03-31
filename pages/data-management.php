<?php require_once '../includes/header.php'; ?>

<div class="data-management">
    <div class="tabs">
        <div class="tab" data-tab="divisions">Office and Organizational Code</div>
        <div class="tab" data-tab="spreadsheet">Spreadsheet</div>
    </div>

    <div id="divisions-content" class="tab-content">
        <div class="divisions-list">
            <?php
            $divisions = [
                'Office of the Administrator' => 1,
                'Administrative Division' => 2,
                // ... add all 43 divisions
            ];
            
            foreach($divisions as $name => $code) {
                echo "<div class='division-item' data-code='$code'>$name</div>";
            }
            ?>
        </div>
    </div>

    <div id="spreadsheet-content" class="tab-content">
        <div id="spreadsheet-container"></div>
    </div>
</div>

<script src="../js/data-management.js"></script>
<?php require_once '../includes/footer.php'; ?>
