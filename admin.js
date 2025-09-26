import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

// Supabase credentials
const SUPABASE_URL = "https://icvfdwkiilnwjsrzxvos.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImljdmZkd2tpaWxud2pzcnp4dm9zIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc1ODgyMTYzNSwiZXhwIjoyMDc0Mzk3NjM1fQ.EPeh9im3gt8zX0azAVU1Cu6IzVhFSFlNVNof4hbQC1U";

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

// DOM Elements
const guardForm = document.getElementById('guardForm');
const guardPhotoInput = document.getElementById('guardPhoto');
const uploadArea = document.getElementById('uploadArea');
const photoPreview = document.getElementById('photoPreview');

let selectedPhotoFile = null;

// Make the upload area clickable
uploadArea.addEventListener('click', () => {
    guardPhotoInput.click();
});

// Preview the uploaded photo
guardPhotoInput.addEventListener('change', (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        alert("Only image files are allowed!");
        guardPhotoInput.value = '';
        selectedPhotoFile = null;
        photoPreview.innerHTML = '';
        return;
    }

    selectedPhotoFile = file;

    const reader = new FileReader();
    reader.onload = e => {
        photoPreview.innerHTML = `<img src="${e.target.result}" alt="Guard Photo" style="max-width:200px; max-height:200px; border-radius:8px;">`;
    };
    reader.readAsDataURL(file);
});

// Handle form submission
guardForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!selectedPhotoFile) {
        alert("Please upload a photo of the guard.");
        return;
    }

    const name = document.getElementById('guardName').value.trim();
    const age = parseInt(document.getElementById('guardAge').value);
    const weight = parseInt(document.getElementById('guardWeight').value);
    const experience = parseInt(document.getElementById('guardExperience').value);
    const role = document.getElementById('guardRole').value;
    const location = document.getElementById('guardLocation').value.trim();
    const skills = document.getElementById('guardSkills').value.trim();

    // Upload photo to Supabase storage
    const fileExt = selectedPhotoFile.name.split('.').pop();
    const fileName = `${Date.now()}-${name.replace(/\s+/g, '-')}.${fileExt}`;
    const filePath = fileName;

    const { error: uploadError } = await supabase
        .storage
        .from('guard-photos')
        .upload(filePath, selectedPhotoFile, { upsert: true });

    if (uploadError) {
        console.error("Photo upload failed:", uploadError);
        alert("Failed to upload guard photo.");
        return;
    }

    // Get public URL of photo
    const { data: urlData } = supabase
        .storage
        .from('guard-photos')
        .getPublicUrl(filePath);

    const photo_url = urlData.publicUrl;

    // Insert guard details into security_guards table
    const { error: insertError } = await supabase
        .from('security_guards')
        .insert([{
            name, age, weight, experience, role, location, skills, photo_url
        }]);

    if (insertError) {
        console.error("Failed to save guard details:", insertError);
        alert("Error saving guard details.");
        return;
    }

    alert("Security guard added successfully!");
    guardForm.reset();
    photoPreview.innerHTML = '';
    selectedPhotoFile = null;
});
