import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

// Supabase credentials
const SUPABASE_URL = "https://icvfdwkiilnwjsrzxvos.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImljdmZkd2tpaWxud2pzcnp4dm9zIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1ODgyMTYzNSwiZXhwIjoyMDc0Mzk3NjM1fQ.EPeh9im3gt8zX0azAVU1Cu6IzVhFSFlNVNof4hbQC1U";

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
let selectedImageFile = null;
let currentProfilePicturePath = null;

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

// 2️⃣ Load user details
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

  if (data.profile_picture) {
    userImage.src = data.profile_picture;
    const url = new URL(data.profile_picture);
    currentProfilePicturePath = decodeURIComponent(url.pathname.replace('/storage/v1/object/public/profile-pictures/', ''));
  } else {
    userImage.src = 'default-avatar.png';
    currentProfilePicturePath = null;
  }
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

// 4️⃣ Handle image selection with validation
imageInput.addEventListener('change', (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    alert("Only image files are allowed!");
    imageInput.value = '';
    selectedImageFile = null;
    return;
  }

  selectedImageFile = file;
  saveBtn.style.display = 'block';

  const reader = new FileReader();
  reader.onload = e => userImage.src = e.target.result;
  reader.readAsDataURL(file);
});

// 5️⃣ Save changes
saveBtn.addEventListener('click', async () => {
  saveBtn.disabled = true;
  let profilePictureUrl = null;

   // ✅ South African phone number validation
  const phoneValue = phoneInput.value.trim();
  const phoneRegex = /^0[6781]\d{8}$/;
  if (!phoneRegex.test(phoneValue)) {
    alert("Enter a valid South African phone number ");
    saveBtn.disabled = false;
    return;
  }

  // Delete old profile picture if exists
  if (selectedImageFile && currentProfilePicturePath) {
    const { error: deleteError } = await supabase
      .storage
      .from('profile-pictures')
      .remove([currentProfilePicturePath]);

    if (deleteError) console.warn("Failed to delete old profile picture:", deleteError);
  }

  // Upload new profile picture
  if (selectedImageFile) {
    const fileExt = selectedImageFile.name.split('.').pop();
    const fileName = `${currentUser.id}.${fileExt}`;
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

    const { data: urlData } = supabase
      .storage
      .from('profile-pictures')
      .getPublicUrl(filePath);

    profilePictureUrl = `${urlData.publicUrl}?t=${new Date().getTime()}`; // cache-busting
    currentProfilePicturePath = filePath;
    userImage.src = profilePictureUrl;
  }

  // Update users table
  const updates = {
    username: usernameInput.value,
    phone: phoneInput.value,
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
    selectedImageFile = null;
    alert('Profile updated successfully!');
  }

  saveBtn.disabled = false;
});
