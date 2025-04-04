class DataManagement {
    constructor() {
        this.initializeTabs();
        this.initializeSearch();
        this.loadDivisions();
        
        // Initialize Handsontable if we're on spreadsheet tab
        if (document.querySelector('.tab[data-tab="spreadsheet"]').classList.contains('active')) {
            this.initializeSpreadsheet();
        }

        this.setupSpreadsheetControls();
    }

    initializeTabs() {
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                tab.classList.add('active');
                const tabId = tab.dataset.tab;
                document.getElementById(`${tabId}-content`).classList.add('active');
                
                // Initialize spreadsheet if switching to spreadsheet tab
                if (tabId === 'spreadsheet' && !this.hot) {
                    this.initializeSpreadsheet();
                }
            });
        });
    }

    initializeSearch() {
        const searchInput = document.getElementById('division-search');
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.division-item').forEach(item => {
                const divisionName = item.querySelector('.division-name').textContent.toLowerCase();
                const divisionCode = item.querySelector('.division-code').textContent.toLowerCase();
                const isVisible = divisionName.includes(searchTerm) || divisionCode.includes(searchTerm);
                item.style.display = isVisible ? 'block' : 'none';
            });
        });
    }

    async loadDivisions() {
        try {
            const response = await fetch('http://localhost/HRIS/api/divisions.php');
            const data = await response.json();
            if (data.success) {
                this.renderDivisions(data.divisions);
            } else {
                throw new Error(data.message || 'Failed to load divisions');
            }
        } catch (error) {
            console.error('Error loading divisions:', error);
            // Add user-friendly error display
            const container = document.querySelector('.divisions-list');
            container.innerHTML = '<div class="error-message">Failed to load divisions. Please try again.</div>';
        }
    }

    renderDivisions(divisions) {
        const container = document.querySelector('.divisions-list');
        container.setAttribute('role', 'list');
        container.innerHTML = divisions.map(div => `
            <div class="division-item" data-code="${div.code}" role="listitem">
                <div class="division-header">
                    <span class="division-code" role="text">${div.code}</span>
                    <span class="division-name" role="text">${div.name}</span>
                </div>
                <div class="division-actions">
                    <button class="view-data-btn" 
                            title="View data for ${div.name}"
                            aria-label="View data for ${div.name}">
                        View Data
                    </button>
                    <button class="open-files-btn"
                            title="Open files for ${div.name}"
                            aria-label="Open files for ${div.name}">
                        Open Files
                    </button>
                </div>
            </div>
        `).join('');
    }

    initializeSpreadsheet() {
        const container = document.getElementById('spreadsheet-container');
        
        // Generate column headers (A-Z)
        const colHeaders = Array.from({length: 26}, (_, i) => 
            String.fromCharCode('A'.charCodeAt(0) + i)
        );

        // Generate initial data matrix
        const initialData = Array.from({length: 100}, (_, row) => 
            Array.from({length: 26}, () => '')
        );

        this.hot = new Handsontable(container, {
            data: initialData,
            rowHeaders: true,
            colHeaders: colHeaders,
            rowHeaderWidth: 50,
            width: '100%',
            height: 'calc(100vh - 250px)',
            contextMenu: {
                items: {
                    'row_above': {
                        name: 'Insert row above',
                        callback: (key, selection) => {
                            const row = selection[0].start.row;
                            this.hot.alter('insert_row', row);
                            this.updateRowHeaders();
                        }
                    },
                    'row_below': {
                        name: 'Insert row below',
                        callback: (key, selection) => {
                            const row = selection[0].end.row;
                            this.hot.alter('insert_row', row + 1);
                            this.updateRowHeaders();
                        }
                    },
                    'col_left': {
                        name: 'Insert column left',
                        callback: (key, selection) => {
                            const col = selection[0].start.col;
                            this.hot.alter('insert_col', col);
                            this.updateColumnHeaders();
                        }
                    },
                    'col_right': {
                        name: 'Insert column right',
                        callback: (key, selection) => {
                            const col = selection[0].end.col;
                            this.hot.alter('insert_col', col + 1);
                            this.updateColumnHeaders();
                        }
                    },
                    'separator1': Handsontable.plugins.ContextMenu.SEPARATOR,
                    'remove_row': {
                        name: 'Delete row(s)',
                        callback: (key, selection) => {
                            const startRow = selection[0].start.row;
                            const endRow = selection[0].end.row;
                            this.hot.alter('remove_row', startRow, endRow - startRow + 1);
                            this.updateRowHeaders();
                        }
                    },
                    'remove_col': {
                        name: 'Delete column(s)',
                        callback: (key, selection) => {
                            const startCol = selection[0].start.col;
                            const endCol = selection[0].end.col;
                            this.hot.alter('remove_col', startCol, endCol - startCol + 1);
                            this.updateColumnHeaders();
                        }
                    },
                    'separator2': Handsontable.plugins.ContextMenu.SEPARATOR,
                    'copy': {},
                    'cut': {},
                    'paste': {},
                    'separator3': Handsontable.plugins.ContextMenu.SEPARATOR,
                    'alignment': {
                        name: 'Alignment',
                        submenu: {
                            items: [
                                { key: 'alignment:left', name: 'Left' },
                                { key: 'alignment:center', name: 'Center' },
                                { key: 'alignment:right', name: 'Right' },
                                { key: 'alignment:justify', name: 'Justify' },
                                { key: 'alignment:top', name: 'Top' },
                                { key: 'alignment:middle', name: 'Middle' },
                                { key: 'alignment:bottom', name: 'Bottom' }
                            ]
                        }
                    }
                }
            },
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
            },
            beforeOnCellMouseDown: (event, coords) => {
                // Update formula bar with cell reference and value
                if (coords.row >= 0 && coords.col >= 0) {
                    const cellRef = this.getCellReference(coords.row, coords.col);
                    const cellValue = this.hot.getDataAtCell(coords.row, coords.col);
                    document.querySelector('.cell-address').textContent = cellRef;
                    document.querySelector('.formula-input').value = cellValue || '';
                }
            },
            afterChange: (changes) => {
                if (changes) {
                    // Update formula bar when cell content changes
                    const [row, col] = changes[0];
                    const cellRef = this.getCellReference(row, col);
                    if (document.querySelector('.cell-address').textContent === cellRef) {
                        document.querySelector('.formula-input').value = changes[0][3] || '';
                    }
                }
            },
            // Excel-like features
            fillHandle: true,
            manualColumnResize: true,
            manualRowResize: true,
            manualColumnMove: true,
            manualRowMove: true,
            mergeCells: true,
            comments: true,
            customBorders: true,
            search: true,
            stretchH: 'all',
            autoColumnSize: true,
            enterMoves: {row: 0, col: 1},
            selectionMode: 'multiple',
            licenseKey: 'non-commercial-and-evaluation'
        });

        this.setupFormulaBar();
        this.updateRowHeaders();
        this.setupKeyboardShortcuts();
    }

    updateRowHeaders() {
        const rows = this.hot.countRows();
        const rowHeaders = Array.from({length: rows}, (_, i) => i + 1);
        this.hot.updateSettings({ rowHeaders });
    }

    updateColumnHeaders() {
        const cols = this.hot.countCols();
        const colHeaders = Array.from({length: cols}, (_, i) => {
            let header = '';
            let num = i;
            while (num >= 0) {
                header = String.fromCharCode('A'.charCodeAt(0) + (num % 26)) + header;
                num = Math.floor(num / 26) - 1;
            }
            return header;
        });
        this.hot.updateSettings({ colHeaders });
    }

    getCellReference(row, col) {
        const colLetter = this.hot.getColHeader(col);
        const rowNumber = row + 1;
        return `${colLetter}${rowNumber}`;
    }

    setupFormulaBar() {
        const formulaInput = document.querySelector('.formula-input');
        formulaInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const selection = this.hot.getSelected()[0];
                if (selection) {
                    const [row, col] = selection;
                    this.hot.setDataAtCell(row, col, formulaInput.value);
                }
                e.preventDefault();
            }
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+S to save
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                this.saveChanges();
            }
            // Ctrl+Z to undo
            if (e.ctrlKey && e.key === 'z') {
                e.preventDefault();
                this.hot.undo();
            }
            // Ctrl+Y to redo
            if (e.ctrlKey && e.key === 'y') {
                e.preventDefault();
                this.hot.redo();
            }
        });
    }

    setupSpreadsheetControls() {
        const controls = document.createElement('div');
        controls.className = 'spreadsheet-controls';
        controls.innerHTML = `
            <div class="control-group">
                <button class="btn expand-btn" id="expandView" title="Toggle fullscreen">
                    <i class="fas fa-expand-arrows-alt"></i> Toggle Fullscreen
                </button>
                <button class="btn back-btn" id="backToDiv" title="Exit spreadsheet view">
                    <i class="fas fa-times"></i> Exit
                </button>
            </div>
            <button class="btn save-btn" id="saveChanges" title="Save all changes">
                <i class="fas fa-save"></i> Save Changes
            </button>
        `;

        const container = document.getElementById('spreadsheet-content');
        container.insertBefore(controls, container.firstChild);

        // Back button handler
        document.getElementById('backToDiv').addEventListener('click', () => {
            document.querySelector('.tab[data-tab="divisions"]').click();
        });

        // Save changes handler
        document.getElementById('saveChanges').addEventListener('click', () => {
            this.saveSpreadsheetChanges();
        });

        // Expand view handler with icon toggle and control group positioning
        document.getElementById('expandView').addEventListener('click', (e) => {
            const isExpanded = container.classList.toggle('expanded');
            e.currentTarget.innerHTML = isExpanded ? 
                '<i class="fas fa-compress-arrows-alt"></i>' : 
                '<i class="fas fa-expand-arrows-alt"></i>';
            
            // Move buttons to control group when expanded
            const backBtn = document.getElementById('backToDiv');
            const controlGroup = document.querySelector('.control-group');
            
            if (isExpanded) {
                controlGroup.prepend(backBtn);
            } else {
                controls.prepend(backBtn);
            }
        });
    }

    saveSpreadsheetChanges() {
        if (!this.hot) return;

        const changes = this.hot.getSourceData();
        const button = document.getElementById('saveChanges');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        fetch('api/save-spreadsheet.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                division: this.currentDivision,
                data: changes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Changes saved successfully!', 'success');
            } else {
                throw new Error(data.message || 'Failed to save changes');
            }
        })
        .catch(error => {
            this.showNotification(error.message, 'error');
            console.error('Save error:', error);
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-save"></i> Save Changes';
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    getDivisionsList() {
        // Return list of divisions for dropdown
        return [
            'Office of the Administrator',
            'Administrative Division',
            // ... add all divisions
        ];
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    new DataManagement();
});
