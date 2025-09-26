// login.js
import { createClient } from "https://esm.sh/@supabase/supabase-js@2";

const SUPABASE_URL = "https://icvfdwkiilnwjsrzxvos.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImljdmZkd2tpaWxud2pzcnp4dm9zIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1ODgyMTYzNSwiZXhwIjoyMDc0Mzk3NjM1fQ.EPeh9im3gt8zX0azAVU1Cu6IzVhFSFlNVNof4hbQC1U"; // safer to store in .env later

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm");

  form.addEventListener("submit", async (e) => {
    e.preventDefault(); // Prevent page refresh

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    try {
      const { data, error } = await supabase.auth.signInWithPassword({
        email,
        password
      });

      if (error) {
        // Wrong email or password
        alert("❌ Invalid email or password");
        console.error("Login error:", error.message);
        return;
      }

      // If login successful
      alert("✅ Login successful!...");
      window.location.href = "dashboard.html";
    } catch (err) {
      alert("❌ Unexpected error: " + err.message);
      console.error(err);
    }
  });
});
