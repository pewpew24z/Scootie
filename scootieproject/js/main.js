// ✅ Navbar user state simulation
document.addEventListener("DOMContentLoaded", () => {
  const navLinks = document.getElementById("nav-links");
  const username = localStorage.getItem("username");

  if (username) {
    navLinks.innerHTML = `
      <a href="home.html">Home</a>
      <a href="book.html">Book a Scooter</a>
      <div class="user-menu">
        👤 <span>${username}</span>
        <button id="logout-btn" class="btn small">Logout</button>
      </div>
    `;

    document.getElementById("logout-btn").addEventListener("click", () => {
      localStorage.removeItem("username");
      window.location.href = "home.html";
    });
  }

  let tables = {};
  async function loadTables() {
    try {
        const response = await fetch('api/get_tables.php');
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        tables = await response.json();
        console.log('✅ Data loaded successfully:', tables);
        
    } catch (error) {
        console.error('❌ Error loading data:', error);
    }
  }

  function logout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.clear();
        window.location.href = 'home.html';
    }
}

loadTables();
});
