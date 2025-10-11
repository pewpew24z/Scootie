// Check if logged in as employee
const userType = localStorage.getItem('userType');
if (!userType || userType !== 'employee') {
    alert('Access denied. Employees only.');
    window.location.href = 'login.html';
}

let currentTable = 'branch';
let allData = {};
let currentEditIndex = null;
let isAddMode = false;

// Load all data
async function loadData() {
    try {
        const response = await fetch('API/get_tables.php');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        allData = data;
        displayTable(currentTable);
    } catch (error) {
        console.error('Error loading data:', error);
        alert('Error loading data: ' + error.message);
    }
}

// Display table
function displayTable(tableName) {
    currentTable = tableName;
    
    // Update active tab
    document.querySelectorAll('.sidebar nav ul li').forEach(li => {
        li.classList.remove('active');
        if (li.dataset.table === tableName) {
            li.classList.add('active');
        }
    });
    
    // Update title
    const titleElement = document.getElementById('table-title');
    if (titleElement) {
        titleElement.textContent = tableName.charAt(0).toUpperCase() + tableName.slice(1) + ' Table';
    }
    
    const tableData = allData[tableName];
    if (!tableData) {
        const tableWrapper = document.querySelector('.table-wrapper');
        if (tableWrapper) {
            tableWrapper.innerHTML = '<p>No data available</p>';
        }
        return;
    }
    
    let html = '<table id="data-table"><thead><tr>';
    
    // Headers
    tableData.headers.forEach(header => {
        html += `<th>${header}</th>`;
    });
    html += '<th>Actions</th></tr></thead><tbody>';
    
    // Rows
    tableData.rows.forEach((row, index) => {
        html += '<tr>';
        row.forEach(cell => {
            html += `<td>${cell !== null ? cell : 'N/A'}</td>`;
        });
        html += `<td>
            <button class="btn small edit-btn-${index}" style="background: #f39c12; margin-right: 5px;">Edit</button>
            <button class="btn small delete-btn-${index}" style="background: #e74c3c;">Delete</button>
        </td></tr>`;
    });
    
    html += '</tbody></table>';
    
    const tableWrapper = document.querySelector('.table-wrapper');
    if (tableWrapper) {
        tableWrapper.innerHTML = html;
        
        // Add event listeners to buttons
        tableData.rows.forEach((row, index) => {
            const editBtn = document.querySelector(`.edit-btn-${index}`);
            const deleteBtn = document.querySelector(`.delete-btn-${index}`);
            
            if (editBtn) {
                editBtn.addEventListener('click', () => editRow(index));
            }
            if (deleteBtn) {
                deleteBtn.addEventListener('click', () => deleteRow(row[0]));
            }
        });
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Edit row
function editRow(index) {
    currentEditIndex = index;
    isAddMode = false;
    
    const tableData = allData[currentTable];
    const row = tableData.rows[index];
    
    let formHTML = '<div style="background: white; padding: 20px; border-radius: 8px; max-width: 600px; margin: 20px auto;">';
    formHTML += `<h3 style="margin-bottom: 20px;">Edit ${currentTable}</h3>`;
    formHTML += '<form id="edit-form" style="display: grid; gap: 15px;">';
    
    tableData.headers.forEach((header, i) => {
        const value = row[i] || '';
        const isFirstField = i === 0;
        const fieldId = `field_${i}`;
        
        formHTML += `
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">${header}:</label>
                <input type="text" 
                       id="${fieldId}" 
                       value="${escapeHtml(value)}" 
                       ${isFirstField ? 'readonly' : ''}
                       style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px; ${isFirstField ? 'background: #f5f5f5;' : ''}">
            </div>
        `;
    });
    
    formHTML += `
        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
            <button type="button" id="cancel-btn" class="btn" style="background: #95a5a6;">Cancel</button>
            <button type="submit" class="btn" style="background: #27ae60;">Save</button>
        </div>
    `;
    formHTML += '</form></div>';
    
    // Show form instead of table
    const container = document.querySelector('.table-wrapper');
    if (container) {
        container.innerHTML = formHTML;
        
        // Add form submit handler
        const form = document.getElementById('edit-form');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await saveEdit();
            });
        }
        
        // Add cancel button handler
        const cancelBtn = document.getElementById('cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', cancelEdit);
        }
    }
}

// Add new row
function addNewRow() {
    isAddMode = true;
    currentEditIndex = null;
    
    const tableData = allData[currentTable];
    
    let formHTML = '<div style="background: white; padding: 20px; border-radius: 8px; max-width: 600px; margin: 20px auto;">';
    formHTML += `<h3 style="margin-bottom: 20px;">Add New ${currentTable}</h3>`;
    formHTML += '<form id="edit-form" style="display: grid; gap: 15px;">';
    
    tableData.headers.forEach((header, i) => {
        // ไม่มี auto-increment เลย ให้กำหนดเองได้หมด
        const fieldId = `field_${i}`;
        
        formHTML += `
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">${header}:</label>
                <input type="text" 
                       id="${fieldId}" 
                       placeholder="Enter ${header}"
                       style="width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 5px;">
            </div>
        `;
    });
    
    formHTML += `
        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
            <button type="button" id="cancel-btn" class="btn" style="background: #95a5a6;">Cancel</button>
            <button type="submit" class="btn" style="background: #27ae60;">Add</button>
        </div>
    `;
    formHTML += '</form></div>';
    
    // Show form instead of table
    const container = document.querySelector('.table-wrapper');
    if (container) {
        container.innerHTML = formHTML;
        
        // Add form submit handler
        const form = document.getElementById('edit-form');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await saveEdit();
            });
        }
        
        // Add cancel button handler
        const cancelBtn = document.getElementById('cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', cancelEdit);
        }
    }
}

// Save edit
async function saveEdit() {
    const tableData = allData[currentTable];
    const data = {};
    
    // Collect data from form
    tableData.headers.forEach((header, i) => {
        const input = document.getElementById(`field_${i}`);
        if (input) {
            data[header] = input.value;
        }
    });
    
    console.log('Sending data:', data);
    
    try {
        let url, method;
        
        if (isAddMode) {
            url = `API/add_record.php?table=${currentTable}`;
            method = 'POST';
        } else {
            const primaryKey = tableData.rows[currentEditIndex][0];
            url = `API/update_record.php?table=${currentTable}&id=${encodeURIComponent(primaryKey)}`;
            method = 'POST';
        }
        
        console.log('Requesting:', url);
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        console.log('Response:', result);
        
        if (result.success) {
            alert(isAddMode ? 'Record added successfully!' : 'Record updated successfully!');
            await loadData(); // Reload data
        } else {
            throw new Error(result.error || 'Operation failed');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    }
}

// Cancel edit
function cancelEdit() {
    console.log('Cancel clicked');
    isAddMode = false;
    currentEditIndex = null;
    displayTable(currentTable);
}

// Delete row
async function deleteRow(id) {
    if (!confirm('Are you sure you want to delete this record?')) {
        return;
    }
    
    try {
        const response = await fetch(`API/delete_record.php?table=${currentTable}&id=${encodeURIComponent(id)}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Record deleted successfully!');
            await loadData();
        } else {
            throw new Error(result.error || 'Delete failed');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Sidebar navigation
    document.querySelectorAll('.sidebar nav ul li').forEach(li => {
        li.addEventListener('click', () => {
            displayTable(li.dataset.table);
        });
    });
    
    // Add button
    const addBtn = document.getElementById('add-row-btn');
    if (addBtn) {
        addBtn.addEventListener('click', addNewRow);
    }
    
    // Load data on start
    loadData();
});