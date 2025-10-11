// ✅ Check login before booking
document.addEventListener("DOMContentLoaded", () => {
  const username = localStorage.getItem("username");
  const bookButtons = document.querySelectorAll(".book-btn");

  bookButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      if (!username) {
        alert("Please log in before booking a scooter.");
        window.location.href = "login.html";
        return;
      }

      // simulate "rented" update
      btn.textContent = "Rented";
      btn.disabled = true;
      btn.classList.add("disabled");
      btn.closest(".scooter-card").querySelector(".status").textContent = "rented";
      btn.closest(".scooter-card").querySelector(".status").classList.remove("available");
      btn.closest(".scooter-card").querySelector(".status").classList.add("rented");

      setTimeout(() => {
        window.location.href = "booking-success.html";
      }, 800);
    });
  });
});
