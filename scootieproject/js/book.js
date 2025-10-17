document.addEventListener("DOMContentLoaded", async () => {
  // check login status and show profile icon
  const userId = localStorage.getItem("userId");
  const customerName = localStorage.getItem("customerName") || localStorage.getItem("name");
  const loginLink = document.getElementById("login-link");
  const profileIcon = document.getElementById("profile-icon");

  console.log("User ID:", userId);
  console.log("Customer Name:", customerName);

  if (userId && customerName) {
    loginLink.style.display = "none";
    profileIcon.style.display = "flex";
    profileIcon.textContent = customerName.charAt(0).toUpperCase();
    console.log("Profile icon displayed!");
  } else {
    console.log("Not logged in or missing data");
  }

  // branches, promotions, scooters
  let branches = [];
  let promotions = [];
  let scooters = [];
  let currentScooterData = null;

  try {
    const response = await fetch('api/get_tables.php');
    const data = await response.json();
    
    if (data.branch) {
      branches = data.branch.rows;
      populateBranches();
    }
    
    if (data.promotion) {
      promotions = data.promotion.rows;
      populatePromotions();
    }

    if (data.scooter) {
      scooters = data.scooter.rows;
      console.log("Scooters loaded:", scooters);
      updateScooterButtons();
    }
    
  } catch (error) {
    console.error('Error loading data:', error);
  }

  function updateScooterButtons() {
    const bookButtons = document.querySelectorAll(".book-btn");
    
    bookButtons.forEach((btn, index) => {
      if (scooters[index]) {
        const scooter = scooters[index];
        // scooter[0] = License_Plate
        // scooter[1] = Model_Name
        // scooter[2] = Current_Branch_ID
        // scooter[3] = Status
        // scooter[4] = Daily_Rate
        
        btn.dataset.plate = scooter[0];
        btn.dataset.model = scooter[1];
        btn.dataset.rate = scooter[4];
        btn.dataset.branch = scooter[2];
        
        console.log(`Button ${index} updated:`, btn.dataset);
      }
    });
  }

  function populateBranches() {
    const pickupBranch = document.getElementById("pickupBranch");
    const returnBranch = document.getElementById("returnBranch");
    
    branches.forEach(branch => {
      const option1 = document.createElement("option");
      option1.value = branch[0]; // Branch_ID
      option1.textContent = branch[1]; // Location
      pickupBranch.appendChild(option1);
      
      const option2 = document.createElement("option");
      option2.value = branch[0];
      option2.textContent = branch[1];
      returnBranch.appendChild(option2);
    });
  }

  function populatePromotions() {
    const promotionSelect = document.getElementById("promotion");
    
    promotions.forEach(promo => {
      const option = document.createElement("option");
      option.value = promo[0]; // Promotion_ID
      option.textContent = `${promo[1]} (${promo[3]}% off)`;
      promotionSelect.appendChild(option);
    });
  }

  // Modal functionality
  const modal = document.getElementById("bookingModal");
  const closeBtn = document.querySelector(".close");
  const cancelBtn = document.getElementById("cancelBtn");
  const bookButtons = document.querySelectorAll(".book-btn");

  bookButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      // check if user login or not
      if (!userId) {
        alert("Please log in before booking a scooter.");
        window.location.href = "login.html";
        return;
      }

      currentScooterData = {
        licensePlate: btn.dataset.plate,
        model: btn.dataset.model,
        rate: btn.dataset.rate,
        branch: btn.dataset.branch
      };

      document.getElementById("modal-scooter-model").textContent = currentScooterData.model;
      document.getElementById("modal-license-plate").textContent = currentScooterData.licensePlate;
      document.getElementById("modal-daily-rate").textContent = currentScooterData.rate;

      // set default pickup branch
      document.getElementById("pickupBranch").value = currentScooterData.branch;

      // set default pickup date เป็นวันนี้
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
      document.getElementById("pickupDate").value = now.toISOString().slice(0, 16);

      modal.style.display = "block";
    });
  });

  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });

  cancelBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });

  // Handle form submission
  const bookingForm = document.getElementById("bookingForm");
  bookingForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const pickupDate = document.getElementById("pickupDate").value;
    const returnDate = document.getElementById("returnDate").value;
    const pickupBranchId = document.getElementById("pickupBranch").value;
    const returnBranchId = document.getElementById("returnBranch").value;
    const promotionId = document.getElementById("promotion").value || null;

    // Validate dates
    if (new Date(returnDate) <= new Date(pickupDate)) {
      alert("Return date must be after pickup date!");
      return;
    }

    // คcalculate total cost
    const days = Math.ceil((new Date(returnDate) - new Date(pickupDate)) / (1000 * 60 * 60 * 24));
    const initialCost = days * parseFloat(currentScooterData.rate);
    
    let discountAmount = 0;
    let finalCost = initialCost;
    
    if (promotionId) {
      const selectedPromo = promotions.find(p => p[0] === promotionId);
      if (selectedPromo) {
        discountAmount = initialCost * (parseFloat(selectedPromo[3]) / 100);
        finalCost = initialCost - discountAmount;
      }
    }

    // create rental object
    const rentalData = {
      customerId: userId,
      licensePlate: currentScooterData.licensePlate,
      pickupBranchId: pickupBranchId,
      returnBranchId: returnBranchId,
      pickupDateTime: pickupDate.replace('T', ' ') + ':00',
      returnDateTime: returnDate.replace('T', ' ') + ':00',
      initialTotalCost: initialCost.toFixed(2),
      discountAmount: discountAmount.toFixed(2),
      finalTotalCost: finalCost.toFixed(2),
      promotionId: promotionId
    };

    try {
      // send data to API
      console.log('Sending rental data:', rentalData);
      
      const response = await fetch('api/create_rental.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(rentalData)
      });

      console.log('Response status:', response.status);
      console.log('Response ok:', response.ok);

      const result = await response.json();
      console.log('Response data:', result);

      if (result.success) {
        alert(`Booking Successful!\n\nTotal: ${finalCost.toFixed(2)} Baht\nDuration: ${days} days`);
        modal.style.display = "none";
        
        // update UI
        const btn = Array.from(bookButtons).find(b => b.dataset.plate === currentScooterData.licensePlate);
        if (btn) {
          btn.textContent = "Rented";
          btn.disabled = true;
          btn.classList.add("disabled");
          const statusSpan = btn.closest(".scooter-card").querySelector(".status");
          statusSpan.textContent = "Rented";
          statusSpan.classList.remove("available");
          statusSpan.classList.add("rented");
        }
        
        // Redirect to account page
        setTimeout(() => {
          window.location.href = "customer_account.html";
        }, 1500);
      } else {
        alert("Booking failed: " + (result.error || "Unknown error"));
      }
    } catch (error) {
      console.error("Error creating rental:", error);
      console.error("Error details:", error.message);
      alert("An error occurred while booking: " + error.message + "\n\nPlease check the console for more details.");
    }
  });
});