// signup.js (Auth version)
import { createClient } from "https://esm.sh/@supabase/supabase-js@2";

const SUPABASE_URL = "https://icvfdwkiilnwjsrzxvos.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImljdmZkd2tpaWxud2pzcnp4dm9zIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTg4MjE2MzUsImV4cCI6MjA3NDM5NzYzNX0.4bzKeLxudbRkKQp0rESQNkJzXuevaV4LP_KQOQBieik";

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const username = document.getElementById("username").value.trim();
    const phone = document.getElementById("phone").value.trim();

    try {
      // Sign up user
      const { data, error } = await supabase.auth.signUp({
        email,
        password,
        options: {
          data: { username, phone } // stores in user metadata
        }
      });

      if (error) throw error;

      alert("✅ Signup successful! Please check your email (if confirmation enabled).");
      console.log("User:", data);
      // Optional redirect
      window.location.href = "index.html";

    } catch (err) {
      alert("❌ Error: " + err.message);
      console.error(err);
    }
  });
});
