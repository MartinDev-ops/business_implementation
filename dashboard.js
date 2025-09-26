import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

// Replace with your Supabase credentials
const SUPABASE_URL = "https://icvfdwkiilnwjsrzxvos.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImljdmZkd2tpaWxud2pzcnp4dm9zIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTg4MjE2MzUsImV4cCI6MjA3NDM5NzYzNX0.4bzKeLxudbRkKQp0rESQNkJzXuevaV4LP_KQOQBieik";

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

// Sidebar elements
const userNameEl = document.getElementById('userName');
const userImageEl = document.getElementById('userImage'); // Profile picture <img>
const logoutBtn = document.querySelector('.logout');

async function loadDashboard() {
  console.log("Dashboard JS loaded");

  // Get logged-in user
  const { data: { user }, error: userError } = await supabase.auth.getUser();

  if (userError) {
    console.error("Error getting logged-in user:", userError);
    return;
  }

  if (!user || !user.email) {
    console.error("No logged-in user detected!");
    window.location.href = 'index.html';
    return;
  }

  console.log("Logged-in user:", user);

  // Fetch user details from 'users' table (email, username, profile_picture)
  const { data, error } = await supabase
    .from('users')
    .select('email, username, profile_picture')
    .eq('email', user.email)
    .single();

  if (error) {
    console.error("Error fetching user from table:", error);
    return;
  }

  console.log("User data from table:", data);

  // Populate sidebar dynamically
  userNameEl.textContent = data.username || 'No Name';
  if (data.profile_picture) {
    userImageEl.src = data.profile_picture; // Set profile picture
  } else {
    userImageEl.src = 'default-avatar.png'; // fallback image
  }
}

// Logout
logoutBtn.addEventListener('click', async () => {
  const { error } = await supabase.auth.signOut();
  if (!error) window.location.href = 'index.html';
  else console.error("Logout error:", error);
});

loadDashboard();
