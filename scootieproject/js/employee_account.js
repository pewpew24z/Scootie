// ⚠️ NOTE: localStorage is used here and works in regular environments
// ดึงข้อมูลจาก localStorage
const userType = localStorage.getItem('userType');
const userId = localStorage.getItem('userId');
const accountId = localStorage.getItem('accountId');

// ตรวจสอบว่า login แล้วหรือยัง
if (!userType || userType !== 'employee' || !userId) {
    alert('Please login first');
    window.location.href = 'login.html';
}

let employeeData = null;
let accountData = null;
let branchData = null;

async function loadEmployeeData() {
    try {
        const response = await fetch('API/get_tables.php');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        // หาข้อมูล Employee โดยใช้ userId จาก localStorage
        const employeeRow = data.employee.rows.find(row => row[0] === userId);
        if (!employeeRow) {
            throw new Error('Employee not found');
        }
        
        employeeData = {
            id: employeeRow[0],
            accountId: employeeRow[1],
            branchId: employeeRow[2],
            name: employeeRow[3],
            email: employeeRow[4],
            phone: employeeRow[5],
            position: employeeRow[6],
            salary: employeeRow[7]
        };
        
        const accountRow = data.account.rows.find(row => row[0] === employeeData.accountId);
        if (accountRow) {
            accountData = {
                id: accountRow[0],
                username: accountRow[1],
                email: accountRow[3]
            };
        }
        
        const branchRow = data.branch.rows.find(row => row[0] === employeeData.branchId);
        if (branchRow) {
            branchData = {
                id: branchRow[0],
                name: branchRow[1],
                address: branchRow[2],
                phone: branchRow[3]
            };
        }
        
        const maintenanceCount = data.maintenance.rows.filter(
            row => row[5] === userId
        ).length;
        
        displayEmployeeData(maintenanceCount);
        
    } catch (error) {
        console.error('Error loading employee data:', error);
        alert('ไม่สามารถโหลดข้อมูลได้: ' + error.message);
    }
}

function displayEmployeeData(maintenanceCount) {
    document.getElementById('avatar-text').textContent = employeeData.name.charAt(0).toUpperCase();
    document.getElementById('employee-name').textContent = employeeData.name;
    document.getElementById('employee-position').textContent = employeeData.position;
    document.getElementById('employee-branch').textContent = branchData ? branchData.name : 'N/A';
    document.getElementById('employee-id').textContent = employeeData.id;
    document.getElementById('employee-email').textContent = employeeData.email;
    document.getElementById('employee-phone').textContent = employeeData.phone;
    document.getElementById('employee-position-detail').textContent = employeeData.position;
    document.getElementById('employee-salary').textContent = '฿' + parseFloat(employeeData.salary).toLocaleString();
    document.getElementById('employee-branch-detail').textContent = branchData ? branchData.name : 'N/A';
    document.getElementById('employee-username').textContent = accountData ? accountData.username : 'N/A';
    document.getElementById('account-id').textContent = accountData ? accountData.id : 'N/A';
    document.getElementById('maintenance-done').textContent = maintenanceCount;
    document.getElementById('tasks-completed').textContent = Math.floor(Math.random() * 50) + 20;
    document.getElementById('hours-worked').textContent = Math.floor(Math.random() * 100) + 120;
}

function changePassword() {
    const newPassword = prompt('Enter new password:');
    if (newPassword && newPassword.length >= 6) {
        alert('Password changed successfully! (Note: This is a demo)');
    } else if (newPassword) {
        alert('Password must be at least 6 characters');
    }
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.clear();
        window.location.href = 'login.html';
    }
}

// เพิ่มฟังก์ชันสำหรับไปหน้า Dashboard
function goToDashboard() {
    window.location.href = 'manager.html';
}

document.addEventListener('DOMContentLoaded', loadEmployeeData);