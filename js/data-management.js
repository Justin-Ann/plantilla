class DataManagement {
  constructor() {
    this.initializeTabs();
    this.initializeSearch();
    this.loadDivisions();

    // Initialize Handsontable if we're on spreadsheet tab
    if (
      document
        .querySelector('.tab[data-tab="spreadsheet"]')
        .classList.contains("active")
    ) {
      this.initializeSpreadsheet();
    }

    this.setupSpreadsheetControls();
  }

  initializeTabs() {
    document.querySelectorAll(".tab").forEach((tab) => {
      tab.addEventListener("click", () => {
        // Remove active class from all tabs
        document
          .querySelectorAll(".tab")
          .forEach((t) => t.classList.remove("active"));
        document
          .querySelectorAll(".tab-content")
          .forEach((c) => c.classList.remove("active"));

        // Add active class to clicked tab
        tab.classList.add("active");
        const tabId = tab.dataset.tab;
        document.getElementById(`${tabId}-content`).classList.add("active");

        // Initialize spreadsheet if switching to spreadsheet tab
        if (tabId === "spreadsheet" && !this.hot) {
          this.initializeSpreadsheet();
        }
      });
    });
  }

  initializeSearch() {
    const searchInput = document.getElementById("division-search");
    searchInput.addEventListener("input", (e) => {
      const searchTerm = e.target.value.toLowerCase();
      document.querySelectorAll(".division-item").forEach((item) => {
        const divisionName = item
          .querySelector(".division-name")
          .textContent.toLowerCase();
        const divisionCode = item
          .querySelector(".division-code")
          .textContent.toLowerCase();
        const isVisible =
          divisionName.includes(searchTerm) ||
          divisionCode.includes(searchTerm);
        item.style.display = isVisible ? "block" : "none";
      });
    });
  }

  async loadDivisions() {
    try {
      const response = await fetch(
        "http://localhost/plantilla/api/divisions.php"
      );
      const data = await response.json();
      if (data.success) {
        this.renderDivisions(data.divisions);
      } else {
        throw new Error(data.message || "Failed to load divisions");
      }
    } catch (error) {
      console.error("Error loading divisions:", error);
      const container = document.querySelector(".divisions-list");
      container.innerHTML =
        '<div class="error-message">Failed to load divisions. Please try again.</div>';
    }
  }

  renderDivisions(divisions) {
    const container = document.querySelector(".divisions-list");
    container.setAttribute("role", "list");
    container.innerHTML = divisions
      .map(
        (div) => `
          <div class="division-item" data-code="${div.division_code}" role="listitem">
            <div class="division-header">
              <span class="division-code" role="text">${div.division_code}</span>
              <span class="division-name" role="text">${div.division_name}</span>
            </div>
            <div class="division-actions">
              <button class="view-data-btn" 
                data-division-code="${div.division_code}" 
                title="View data for ${div.division_name}" 
                aria-label="View data for ${div.division_name}">
                View Data
              </button>
              <button class="open-files-btn"
              data-division-code="${div.division_code}" 
                title="Open files for ${div.division_name}"
                aria-label="Open files for ${div.division_name}">
                Open Files
              </button>
            </div>
          </div>
        `
      )
      .join("");

    container.querySelectorAll(".view-data-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const divisionCode = e.target.getAttribute("data-division-code");
        this.openFiles(divisionCode);
      });
    });

    container.querySelectorAll(".open-files-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const divisionCode = e.target.getAttribute("data-division-code");
        this.openFiles(divisionCode);
      });
    });
  }

  openFiles(divisionCode) {
    this.fetchFilesForDivision(divisionCode);
  }

  fetchFilesForDivision(divisionCode) {
    const xhr = new XMLHttpRequest();
    xhr.open(
      "GET",
      `../api/fetch_files.php?division_code=${divisionCode}`,
      true
    );

    xhr.onload = function () {
      if (xhr.status === 200) {
        const files = JSON.parse(xhr.responseText);
        console.log("Fetched files:", files);

        if (files.error) {
          console.error("Error from API:", files.error);
        } else {
          this.displayFilesInModal(files);
        }
      } else {
        console.error("Failed to fetch files");
      }
    }.bind(this);

    xhr.onerror = function () {
      console.error("Network error during fetch");
    };

    xhr.send();
  }

  displayFilesInModal(files) {
    const modal = document.createElement("div");
    modal.classList.add("modal");

    modal.innerHTML = `
        <div class="modal-content">
          <span class="close-btn" style="cursor:pointer;float:right;font-size:20px;">&times;</span>
          <h2>Available Files</h2>
          ${
            files.length === 0
              ? "<p>No files available.</p>"
              : `<ul style="list-style:none;padding-left:0;">
                  ${files
                    .map(
                      (file) => `
                        <li style="margin-bottom: 10px;">
                          <a href="${file.file_path}" target="_blank">${file.original_filename}</a>
                        </li>
                      `
                    )
                    .join("")}
                </ul>`
          }
        </div>
      `;

    document.body.appendChild(modal);

    modal.querySelector(".close-btn").addEventListener("click", () => {
      modal.remove();
    });

    modal.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #fff;
        padding: 20px;
        border: 2px solid #444;
        z-index: 9999;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
        max-width: 500px;
        width: 80%;
        border-radius: 8px;
        display: flex;
      `;
  }

  initializeSpreadsheet() {
    const container = document.getElementById("spreadsheet-container");

    // Generate column headers (A-Z)
    const colHeaders = Array.from({ length: 26 }, (_, i) =>
      String.fromCharCode("A".charCodeAt(0) + i)
    );

    // Generate initial data matrix
    const initialData = Array.from({ length: 100 }, (_, row) =>
      Array.from({ length: 26 }, () => "")
    );

    this.hot = new Handsontable(container, {
      data: initialData,
      rowHeaders: true,
      colHeaders: colHeaders,
      rowHeaderWidth: 50,
      width: "100%",
      height: "calc(100vh - 250px)",
      contextMenu: {
        /* ... context menu items ... */
      },
      // Other Handsontable options...
    });

    this.setupFormulaBar();
    this.updateRowHeaders();
    this.setupKeyboardShortcuts();
  }

  updateRowHeaders() {
    const rows = this.hot.countRows();
    const rowHeaders = Array.from({ length: rows }, (_, i) => i + 1);
    this.hot.updateSettings({ rowHeaders });
  }

  updateColumnHeaders() {
    const cols = this.hot.countCols();
    const colHeaders = Array.from({ length: cols }, (_, i) => {
      let header = "";
      let num = i;
      while (num >= 0) {
        header = String.fromCharCode("A".charCodeAt(0) + (num % 26)) + header;
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
    const formulaInput = document.querySelector(".formula-input");
    formulaInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
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
    document.addEventListener("keydown", (e) => {
      // Ctrl+S to save
      if (e.ctrlKey && e.key === "s") {
        e.preventDefault();
        console.log("Saving spreadsheet data...");
      }
    });
  }

  setupSpreadsheetControls() {
    const spreadsheetTab = document.querySelector('[data-tab="spreadsheet"]');
    if (!spreadsheetTab.classList.contains("active")) return;

    const spreadsheetContainer = document.getElementById(
      "spreadsheet-container"
    );

    spreadsheetTab.addEventListener("click", () => {
      spreadsheetContainer.style.display = "block";
    });
  }
}

document.addEventListener("DOMContentLoaded", () => new DataManagement());
