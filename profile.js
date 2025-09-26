import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

// Supabase credentials
const SUPABASE_URL = "https://icvfdwkiilnwjsrzxvos.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImljdmZkd2tpaWxud2pzcnp4dm9zIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1ODgyMTYzNSwiZXhwIjoyMDc0Mzk3NjM1fQ.EPeh9im3gt8zX0azAVU1Cu6IzVhFSFlNVNof4hbQC1U";
 // replace with your key
const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

// DOM elements
const usernameInput = document.getElementById('username');
const phoneInput = document.getElementById('phone');
const emailInput = document.getElementById('email');
const saveBtn = document.getElementById('saveChanges');
const imageInput = document.getElementById('imageUpload');
const userImage = document.getElementById('userImage');
const editButtons = document.querySelectorAll('.edit-btn');

let currentUser = null;
let selectedImageFile = null; // Temporarily store image before saving

// 1️⃣ Check logged-in user
async function checkUser() {
  const { data: { user }, error } = await supabase.auth.getUser();
  if (error) {
    console.error("Error getting logged-in user:", error);
    return;
  }
  if (!user) {
    window.location.href = 'index.html';
    return;
  }
  currentUser = user;
  await loadUserData();
}

checkUser();

// 2️⃣ Load user details from table
async function loadUserData() {
  const { data, error } = await supabase
    .from('users')
    .select('*')
    .eq('email', currentUser.email)
    .single();

  if (error) {
    console.error('Error fetching user:', error);
    return;
  }

  usernameInput.value = data.username || '';
  phoneInput.value = data.phone || '';
  emailInput.value = data.email || '';

  if (data.profile_picture) userImage.src = data.profile_picture;
}

// 3️⃣ Enable editing fields
editButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    const field = btn.dataset.field;
    const input = document.getElementById(field);
    input.disabled = false;
    input.focus();
    saveBtn.style.display = 'block';
  });
});

// 4️⃣ Handle image selection
imageInput.addEventListener('change', (event) => {
  const file = event.target.files[0];
  if (!file) return;
  selectedImageFile = file;

  // Preview locally
  const reader = new FileReader();
  reader.onload = e => userImage.src = e.target.result;
  reader.readAsDataURL(file);
});

// 5️⃣ Save changes (username, phone, and profile picture)
saveBtn.addEventListener('click', async () => {
  saveBtn.disabled = true;

  let profilePictureUrl = null;

  // 5a. Upload image if a new one was selected
  if (selectedImageFile) {
    const fileExt = selectedImageFile.name.split('.').pop();
    const fileName = `${currentUser.email}.${fileExt}`;
    const filePath = fileName;

    const { error: uploadError } = await supabase
      .storage
      .from('profile-pictures')
      .upload(filePath, selectedImageFile, { upsert: true });

    if (uploadError) {
      console.error('Image upload error:', uploadError);
      alert('Failed to upload profile picture.');
      saveBtn.disabled = false;
      return;
    }

    // Get public URL
    const { data: urlData } = supabase
      .storage
      .from('profile-pictures')
      .getPublicUrl(filePath);

    profilePictureUrl = urlData.publicUrl;
  }

  // 5b. Update the user record
  const updates = {
    username: usernameInput.value,
    phone: phoneInput.value
  };
  if (profilePictureUrl) updates.profile_picture = profilePictureUrl;

  const { error } = await supabase
    .from('users')
    .update(updates)
    .eq('email', currentUser.email);

  if (error) {
    console.error('Error updating profile:', error);
    alert('Failed to update profile');
  } else {
    usernameInput.disabled = true;
    phoneInput.disabled = true;
    saveBtn.style.display = 'none';
    selectedImageFile = null; // reset
    alert('Profile updated successfully!');
  }

  saveBtn.disabled = false;
});
