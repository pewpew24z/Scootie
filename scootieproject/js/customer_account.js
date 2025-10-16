
const userType = localStorage.getItem('userType');
const userId = localStorage.getItem('userId');
const accountId = localStorage.getItem('accountId');

if (!userType || userType !== 'customer' || !userId) {
    alert('Please login first');
    window.location.href = 'login.html';
}

let customerData = null;
let accountData = null;
let rentalHistory = [];

async function loadCustomerData() {
    try {
        const response = await fetch('API/get_tables.php');
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        const customerRow = data.customer.rows.find(row => row[0] === userId);
        if (!customerRow) {
            throw new Error('Customer not found');
        }
        
        customerData = {
            id: customerRow[0],
            accountId: customerRow[1],
            name: customerRow[2],
            phone: customerRow[3],
            email: customerRow[4],
            driverLicense: customerRow[5],
            membershipLevel: customerRow[6]
        };
        
        const accountRow = data.account.rows.find(row => row[0] === customerData.accountId);
        if (accountRow) {
            accountData = {
                id: accountRow[0],
                username: accountRow[1],
                email: accountRow[3]
            };
        }
        
        rentalHistory = data.rental.rows.filter(row => row[1] === userId);
        
        const activeRentals = rentalHistory.filter(row => !row[7]).length;
        const totalSpent = rentalHistory.reduce((sum, rental) => {
            return sum + (parseFloat(rental[10]) || 0);
        }, 0);
        
        displayCustomerData(activeRentals, totalSpent);
        displayRecentRentals(data);
        
    } catch (error) {
        console.error('Error loading customer data:', error);
        alert('cannot loading: ' + error.message);
    }
}

function displayCustomerData(activeRentals, totalSpent) {
    document.getElementById('avatar-text').textContent = customerData.name.charAt(0).toUpperCase();
    document.getElementById('customer-name').textContent = customerData.name;
    document.getElementById('customer-email-header').textContent = customerData.email;
    
    const membershipBadge = document.getElementById('membership-badge');
    membershipBadge.textContent = customerData.membershipLevel;
    membershipBadge.style.background = customerData.membershipLevel === 'VIP' ? '#f39c12' : '#95a5a6';
    
    document.getElementById('customer-id').textContent = customerData.id;
    document.getElementById('customer-name-detail').textContent = customerData.name;
    document.getElementById('customer-email').textContent = customerData.email;
    document.getElementById('customer-phone').textContent = customerData.phone;
    document.getElementById('driver-license').textContent = customerData.driverLicense;
    document.getElementById('membership-level').textContent = customerData.membershipLevel;
    document.getElementById('customer-username').textContent = accountData ? accountData.username : 'N/A';
    document.getElementById('account-id').textContent = accountData ? accountData.id : 'N/A';
    document.getElementById('total-rentals').textContent = rentalHistory.length;
    document.getElementById('active-rentals').textContent = activeRentals;
    document.getElementById('total-spent').textContent = '฿' + totalSpent.toLocaleString();
}

function displayRecentRentals(data) {
    const container = document.getElementById('recent-rentals-list');
    
    if (rentalHistory.length === 0) {
        container.innerHTML = '<p style="text-align:center; color:#999; padding:20px;">No rental history</p>';
        return;
    }
    
    const recentRentals = rentalHistory.slice(0, 5);
    
    let html = '';
    recentRentals.forEach(rental => {
        const isActive = !rental[7];
        const status = isActive ? 'active' : 'completed';
        const statusText = isActive ? 'Active' : 'Completed';
        
        html += `
            <div class="rental-item">
                <div class="rental-info">
                    <h4>🛴 ${rental[2]}</h4>
                    <p>Pickup: ${rental[5]} | Return: ${rental[6] || 'Pending'}</p>
                </div>
                <span class="rental-status ${status}">${statusText}</span>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function editProfile() {
    alert('Edit profile feature coming soon! (This is a demo)');
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.clear();
        window.location.href = 'login.html';
    }
}

document.addEventListener('DOMContentLoaded', loadCustomerData);