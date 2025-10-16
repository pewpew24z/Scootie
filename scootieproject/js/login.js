console.log('Login script loaded!');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded!');
    
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-message');
    const btnText = document.getElementById('btn-text');
    const btnLoading = document.getElementById('btn-loading');

    if (!loginForm) {
        console.error('Login form not found!');
        return;
    }

    console.log('Login form found!');

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        console.log('Form submitted!');
        
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        
        console.log('Username:', username);
        
        if (!username || !password) {
            showError('Please fill in all fields');
            return;
        }

        if (btnText) btnText.style.display = 'none';
        if (btnLoading) btnLoading.style.display = 'inline';
        if (errorMessage) errorMessage.style.display = 'none';
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        try {
            console.log('Attempting to login...');
            
            // retrieve information from API -  use relative path
            const apiUrl = 'API/get_tables.php';
            const fullUrl = window.location.origin + window.location.pathname.replace('login.html', '') + apiUrl;
            
            console.log('Fetching from:', fullUrl);
            
            const response = await fetch(apiUrl);
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const responseText = await response.text();
            console.log('Response length:', responseText.length);
            console.log('Response preview:', responseText.substring(0, 100));
            
            // check JSON 
            if (responseText.trim().startsWith('<?php') || responseText.trim().startsWith('<')) {
                console.error(' Server returned PHP/HTML instead of JSON');
                throw new Error('Server configuration error. PHP is not running properly.');
            }
            
            // convert to JSON
            let data;
            try {
                data = JSON.parse(responseText);
                console.log('JSON parsed successfully');
            } catch (parseError) {
                console.error('JSON Parse Error:', parseError);
                console.error('Response was:', responseText.substring(0, 500));
                throw new Error('Server returned invalid JSON. Response: ' + responseText.substring(0, 100));
            }
            
            console.log('Data loaded:', Object.keys(data));
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            // find Account that match username
            const accountRow = data.account.rows.find(row => row[1] === username);
            
            console.log('🔍 Found account:', accountRow ? 'Yes' : 'No');
            
            if (!accountRow) {
                throw new Error('Username not found');
            }
            
            console.log('Username found');
            console.log('Account data:', accountRow);
            
            // check password
            console.log('Checking password...');
            console.log('Stored password:', accountRow[2]);
            
            if (accountRow[2] !== password) {
                throw new Error('Incorrect password');
            }
            
            console.log('Password correct');

            // Login success
            const accountId = accountRow[0];
            const accountUsername = accountRow[1];
            const accountEmail = accountRow[3];
            
            console.log('Account ID:', accountId);
            
            // check user (customer/employee)
            const employeeRow = data.employee.rows.find(row => row[1] == accountId);
            const customerRow = data.customer.rows.find(row => row[1] == accountId);
            
            console.log('Employee found:', employeeRow ? 'Yes' : 'No');
            console.log('Customer found:', customerRow ? 'Yes' : 'No');
            
            if (employeeRow) {
                //Employee
                console.log('Login as Employee');
                console.log('Employee data:', employeeRow);

                try {

                    localStorage.setItem('userType', 'employee');
                    localStorage.setItem('userId', employeeRow[0]);
                    localStorage.setItem('accountId', accountId);
                    localStorage.setItem('username', accountUsername);
                    localStorage.setItem('email', accountEmail);
                    localStorage.setItem('name', employeeRow[3]);
                    
                    console.log('Data saved to localStorage');
                } catch (storageError) {
                    console.error('localStorage error:', storageError);
                }
                
                console.log('Redirecting to employee_account.html...');
                
                // success message
                alert('Welcome back, ' + employeeRow[3] + '!');
                
                // Redirect to Employee Account
                window.location.href = 'employee_account.html';
                
            } else if (customerRow) {
                // Customer
                console.log('Login as Customer');
                console.log('Customer data:', customerRow);
                
                try {
        
                    localStorage.setItem('userType', 'customer');
                    localStorage.setItem('userId', customerRow[0]);
                    localStorage.setItem('accountId', accountId);
                    localStorage.setItem('username', accountUsername);
                    localStorage.setItem('email', accountEmail);
                    localStorage.setItem('name', customerRow[2]);

                    localStorage.setItem('customerName', customerRow[2]);
                    
                    console.log('Data saved to localStorage');
                } catch (storageError) {
                    console.error('localStorage error:', storageError);
                }
                
                console.log('Redirecting to customer_account.html...');
                
                // show success message
                alert('Welcome back, ' + customerRow[2] + '!');
                
                // Redirect to Customer Account
                window.location.href = 'customer_account.html';
                
            } else {
                throw new Error('Account not associated with any user');
            }
            
        } catch (error) {
            console.error('❌ Login error:', error);
            console.error('❌ Error name:', error.name);
            console.error('❌ Error message:', error.message);
            console.error('❌ Error stack:', error.stack);
            
            showError(error.message);

            // reset button
            if (btnText) btnText.style.display = 'inline';
            if (btnLoading) btnLoading.style.display = 'none';
            if (submitBtn) submitBtn.disabled = false;
        }
    });
    
    function showError(message) {
        if (errorMessage) {
            errorMessage.textContent = '❌ ' + message;
            errorMessage.style.display = 'block';
        }
        console.error('❌ Error shown:', message);
    }
});