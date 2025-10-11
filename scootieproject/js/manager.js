// ===== Initialize Variables =====
let tables = {}; // à¹€à¸£à¸´à¹ˆà¸¡à¸•à¹‰à¸™à¹€à¸›à¹‡à¸™ empty
let currentTableKey = "branch";

// ===== Elements (à¸ˆà¸° assign à¹ƒà¸™ DOMContentLoaded) =====
let sidebarItems;
let tableTitle;
let dataTable;
let addRowBtn;


// ===== API Functions =====
async function loadTablesFromAPI() {
    try {
        console.log('ðŸ”„ Loading data from API...');
        
        const response = await fetch('API/get_tables.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        tables = data;
        console.log('âœ… Data loaded successfully!');
        console.log('ðŸ“Š Tables available:', Object.keys(tables));
        
        // à¹€à¸£à¸µà¸¢à¸ initialize à¸«à¸¥à¸±à¸‡à¹‚à¸«à¸¥à¸”à¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¹€à¸ªà¸£à¹‡à¸ˆ
        initializeApp();
        
    } catch (error) {
        console.error('âŒ Error loading data:', error);
        alert('à¹„à¸¡à¹ˆà¸ªà¸²à¸¡à¸²à¸£à¸–à¹‚à¸«à¸¥à¸”à¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¹„à¸”à¹‰: ' + error.message + '\nà¸à¸£à¸¸à¸“à¸²à¸•à¸£à¸§à¸ˆà¸ªà¸­à¸š Console (F12)');
    }
}

// ===== Initialize App =====
function initializeApp() {
    console.log('ðŸŽ‰ App initialized with real data!');
    
    // Setup event listeners
    setupEventListeners();
    
    // à¹‚à¸«à¸¥à¸”à¸•à¸²à¸£à¸²à¸‡à¹à¸£à¸
    loadTable("branch");
}

// ===== Setup Event Listeners =====
function setupEventListeners() {
    // Add Row Button
    addRowBtn.addEventListener("click", () => {
        const t = tables[currentTableKey];
        const newRow = Array(t.headers.length).fill("");
        t.rows.push(newRow);
        loadTable(currentTableKey);
    });

    // Sidebar Navigation
    sidebarItems.forEach(item => {
        item.addEventListener("click", () => {
            sidebarItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");
            loadTable(item.dataset.table);
        });
    });
}

// ===== Load Table =====
function loadTable(key) {
    currentTableKey = key;
    const t = tables[key];
    
    if (!t) {
        console.error(`âŒ Table "${key}" not found!`);
        return;
    }
    
    tableTitle.textContent = t.title;

    let html = `
        <thead>
            <tr>
                ${t.headers.map(h => `<th>${h}</th>`).join("")}
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            ${t.rows.map((row, rIndex) => `
                <tr data-index="${rIndex}">
                    ${row.map((cell, cIndex) => `<td data-col="${cIndex}">${cell || ''}</td>`).join("")}
                    <td>
                        <button class="edit-btn">Edit</button>
                        <button class="save-btn" style="display:none;">Save</button>
                        <button class="delete-btn">Delete</button>
                    </td>
                </tr>
            `).join("")}
        </tbody>
    `;
    dataTable.innerHTML = html;
    attachRowEvents();
}

// ===== Edit/Save/Delete Handlers =====
function attachRowEvents() {
    // Edit Button
    dataTable.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const row = e.target.closest("tr");
            const cells = row.querySelectorAll("td[data-col]");
            cells.forEach(td => {
                const val = td.textContent;
                td.innerHTML = `<input type="text" value="${val}">`;
            });
            row.querySelector(".edit-btn").style.display = "none";
            row.querySelector(".save-btn").style.display = "inline";
        });
    });

    // Save Button
    dataTable.querySelectorAll(".save-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const row = e.target.closest("tr");
            const index = row.dataset.index;
            const inputs = row.querySelectorAll("input");
            const newData = Array.from(inputs).map(i => i.value);
            tables[currentTableKey].rows[index] = newData;
            loadTable(currentTableKey);
            console.log("âœ… Updated:", tables[currentTableKey].rows[index]);
        });
    });

    // Delete Button
    dataTable.querySelectorAll(".delete-btn").forEach(btn => {
        btn.addEventListener("click", (e) => {
            if (confirm('à¸„à¸¸à¸“à¹à¸™à¹ˆà¹ƒà¸ˆà¸§à¹ˆà¸²à¸•à¹‰à¸­à¸‡à¸à¸²à¸£à¸¥à¸šà¸‚à¹‰à¸­à¸¡à¸¹à¸¥à¸™à¸µà¹‰?')) {
                const row = e.target.closest("tr");
                const index = row.dataset.index;
                tables[currentTableKey].rows.splice(index, 1);
                loadTable(currentTableKey);
                console.log("ðŸ—‘ï¸ Deleted row at index:", index);
            }
        });
    });
}

// ===== DOM Content Loaded =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('ðŸ“„ DOM loaded');
    
    // Assign elements
    sidebarItems = document.querySelectorAll(".sidebar li");
    tableTitle = document.getElementById("table-title");
    dataTable = document.getElementById("data-table");
    addRowBtn = document.getElementById("add-row-btn");
    
    // Check if elements exist
    if (!tableTitle || !dataTable || !addRowBtn) {
        console.error('âŒ Required elements not found!');
        return;
    }
    
    // Load data from API
    loadTablesFromAPI();
});