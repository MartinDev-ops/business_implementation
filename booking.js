import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

// Supabase credentials
const SUPABASE_URL = "https://icvfdwkiilnwjsrzxvos.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImljdmZkd2tpaWxud2pzcnp4dm9zIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTg4MjE2MzUsImV4cCI6MjA3NDM5NzYzNX0.4bzKeLxudbRkKQp0rESQNkJzXuevaV4LP_KQOQBieik";
 // Replace with your key

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

const bookingsList = document.getElementById('bookingsList');

async function loadBookings() {
    const { data: bookings, error } = await supabase
        .from('bookings')
        .select('*')
        .order('booking_date', { ascending: false });

    if (error) {
        console.error("Error fetching bookings:", error);
        bookingsList.innerHTML = '<p style="color:red;">Failed to load bookings.</p>';
        return;
    }

    if (!bookings || bookings.length === 0) {
        bookingsList.innerHTML = '<p>No bookings yet.</p>';
        return;
    }

    bookingsList.innerHTML = '';

    bookings.forEach(booking => {
        const card = document.createElement('div');
        card.className = 'booking-card';

        const img = document.createElement('img');
        img.src = booking.photo_url;
        img.alt = booking.guard_name;

        const infoDiv = document.createElement('div');
        infoDiv.className = 'booking-info';
        infoDiv.innerHTML = `
            <h3>${booking.guard_name} (${booking.role})</h3>
            <p><strong>Date:</strong> ${new Date(booking.start_time).toLocaleDateString()}</p>
            <p><strong>Time:</strong> ${new Date(booking.start_time).toLocaleTimeString()} - ${new Date(booking.end_time).toLocaleTimeString()}</p>
            <p><strong>Hours:</strong> ${booking.hours}</p>
            <p class="booking-total">R${booking.total}</p>
        `;

        card.appendChild(img);
        card.appendChild(infoDiv);

        bookingsList.appendChild(card);
    });
}

loadBookings();
