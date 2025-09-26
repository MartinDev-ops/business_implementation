import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

// Supabase credentials
const SUPABASE_URL = "https://icvfdwkiilnwjsrzxvos.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImljdmZkd2tpaWxud2pzcnp4dm9zIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTg4MjE2MzUsImV4cCI6MjA3NDM5NzYzNX0.4bzKeLxudbRkKQp0rESQNkJzXuevaV4LP_KQOQBieik";

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

const guardContainer = document.getElementById('selectedGuard');
const bookingForm = document.getElementById('bookingForm');
const totalEl = document.getElementById('totalAmount');

// Pricing rates
const rates = {
    "Onsite Security": 50,
    "Private Security": 150,
    "Event Security": 100
};

// Load selected guard from localStorage
const selectedGuard = JSON.parse(localStorage.getItem('selectedGuard'));
if (!selectedGuard) {
    guardContainer.innerHTML = '<p>No guard selected.</p>';
} else {
    guardContainer.innerHTML = `
        <div class="guard-card">
            <div class="guard-image">
                <img src="${selectedGuard.photo_url}" alt="${selectedGuard.name}">
            </div>
            <div class="guard-info">
                <h3 class="guard-name">${selectedGuard.name}</h3>
                <p class="guard-role">${selectedGuard.role}</p>
                <div class="guard-details">
                    <p><strong>Age:</strong> ${selectedGuard.age}</p>
                    <p><strong>Weight:</strong> ${selectedGuard.weight} lbs</p>
                    <p><strong>Experience:</strong> ${selectedGuard.experience} years</p>
                    <p><strong>Location:</strong> ${selectedGuard.location}</p>
                    <p><strong>Skills:</strong> ${selectedGuard.skills || 'N/A'}</p>
                </div>
            </div>
        </div>
    `;
}

// Calculate total dynamically
function calculateTotal() {
    const hours = parseFloat(document.getElementById('hours').value) || 0;
    const rate = rates[selectedGuard.role] || 0;
    totalEl.textContent = `$${hours * rate}`;
}

document.getElementById('hours').addEventListener('input', calculateTotal);

// Handle booking confirmation
bookingForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const location = document.getElementById('bookingLocation').value.trim();
    const date = document.getElementById('bookingDate').value;
    const hours = parseFloat(document.getElementById('hours').value);

    if (!location || !date || !hours) {
        alert("Please fill all booking details.");
        return;
    }

    // Insert booking into Supabase
    const { error } = await supabase.from('bookings').insert([{
        guard_id: selectedGuard.id,
        guard_name: selectedGuard.name,
        role: selectedGuard.role,
        photo_url: selectedGuard.photo_url,
        location,
        booking_date: date,
        hours,
        total: hours * rates[selectedGuard.role]
    }]);

    if (error) {
         console.error("Supabase insert error:", error);
    console.error("Error details:", JSON.stringify(error, null, 2));
    alert("Failed to confirm booking: " + (error.message || "Unknown error"));
    return;
    }

    alert("Booking confirmed!");
    localStorage.removeItem('selectedGuard');
    window.location.href = 'Booking.html';
});
