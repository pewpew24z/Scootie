// ตรวจสอบว่า login แล้วและเป็น employee หรือไม่
function checkEmployeeAuth() {
    const userType = localStorage.getItem('userType');
    const userId = localStorage.getItem('userId');
    
    if (!userType || userType !== 'employee' || !userId) {
        alert('Please login as employee first');
        window.location.href = 'login.html';
        return false;
    }
    
    console.log('✅ Employee authenticated');
    return true;
}

// เรียกใช้ทันทีเมื่อโหลด script
checkEmployeeAuth();