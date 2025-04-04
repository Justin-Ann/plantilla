class ExcelLikeSpreadsheet {
    constructor() {
        this.hot = null;
        this.initializeHandsontable();
    }

    generateInitialData() {
        return Array.from({ length: 100 }, () => 
            Array.from({ length: 26 }, () => '')
        );
    }

    initializeHandsontable() {
        const container = document.getElementById('spreadsheet-container');
        
        // Generate column headers (A-Z)
        const colHeaders = Array.from({ length: 26 }, (_, i) => 
            String.fromCharCode('A'.charCodeAt(0) + i)
        );

        // Initialize with empty data
        const initialData = this.generateInitialData();

        this.hot = new Handsontable(container, {
            data: initialData,
            rowHeaders: true,
            colHeaders: colHeaders,
            width: '100%',
            height: 'calc(100vh - 200px)',
            rowHeaderWidth: 50,
            colWidths: 100,
            licenseKey: 'non-commercial-and-evaluation',
            contextMenu: true,
            manualColumnResize: true,
            manualRowResize: true,
            fillHandle: true,
            // Additional Handsontable configuration
            cells(row, col) {
                const cellProperties = {};
                if (col === 'monthly_salary') {
                    cellProperties.type = 'numeric';
                    cellProperties.numericFormat = {
                        pattern: '₱0,0.00',
                        culture: 'en-US'
                    };
                }
                return cellProperties;
            }
        });

        // Initialize cell selection and formula bar
        this.setupFormulaBar();
        this.setupKeyboardShortcuts();
    }

    setupFormulaBar() {
        const formulaInput = document.querySelector('.formula-input');
        formulaInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                this.applyFormula(formulaInput.value);
            }
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey) {
                switch(e.key) {
                    case 'b': this.toggleBold(); break;
                    case 'i': this.toggleItalic(); break;
                    case 'u': this.toggleUnderline(); break;
                    // Add more shortcuts
                }
            }
        });
    }

    // Add implementation methods for all Excel features
    handleToolbarAction(action) {
        const selected = this.hot.getSelected();
        if (!selected) return;

        switch(action) {
            case 'bold': this.toggleBold(); break;
            case 'italic': this.toggleItalic(); break;
            case 'underline': this.toggleUnderline(); break;
            case 'merge': this.mergeCells(); break;
            case 'autofit': this.autofitColumns(); break;
            // Add more actions
        }
    }

    // Add more spreadsheet functionality methods
    // ...
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    new ExcelLikeSpreadsheet();
});
