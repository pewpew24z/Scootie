document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('form');
    
    if (!registerForm) {
        console.error('Register form not found');
        return;
    }
    
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        console.log('Form submitted');
        
        const name = document.getElementById('name').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const email = document.getElementById('email').value.trim();
        const license = document.getElementById('license').value.trim();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        
        if (!name || !phone || !email || !license || !username || !password) {
            alert('Please fill in all fields');
            return;
        }
        
        // check password length
        if (password.length < 6) {
            alert('Password must be at least 6 characters');
            return;
        }
        
        // check email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return;
        }
        
        const data = {
            name: name,
            phone: phone,
            email: email,
            license: license,
            username: username,
            password: password
        };
        
        console.log('Sending data:', data);
        
        // Disable submit button
        const submitBtn = registerForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Registering...';
        }
        
        try {
            const response = await fetch('api/register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            console.log('Response status:', response.status);
            
            const result = await response.json();
            console.log('Response:', result);
            
            if (result.success) {
                alert('Registration successful! Please login.');
                // Redirect to login
                window.location.href = 'login.html';
            } else {
                throw new Error(result.error || 'Registration failed');
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert('Error: ' + error.message);
            
            // Re-enable submit button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Register';
            }
        }
    });
});