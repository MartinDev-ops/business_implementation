import { createClient } from "https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm";

// Supabase credentials
const SUPABASE_URL = "https://icvfdwkiilnwjsrzxvos.supabase.co";
const SUPABASE_KEY = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImljdmZkd2tpaWxud2pzcnp4dm9zIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTg4MjE2MzUsImV4cCI6MjA3NDM5NzYzNX0.4bzKeLxudbRkKQp0rESQNkJzXuevaV4LP_KQOQBieik";

const supabase = createClient(SUPABASE_URL, SUPABASE_KEY);

const guardsContainer = document.getElementById('guardsContainer');

async function loadGuards() {
    const { data: guards, error } = await supabase
        .from('security_guards')
        .select('*')
        .order('created_at', { ascending: false });

    if (error) {
        console.error("Error fetching guards:", error);
        guardsContainer.innerHTML = '<p style="color:red;">Failed to load guards.</p>';
        return;
    }

    guardsContainer.innerHTML = '';

    guards.forEach(guard => {
        const card = document.createElement('div');
        card.className = 'guard-card';

        const imgDiv = document.createElement('div');
        imgDiv.className = 'guard-image';
        const img = document.createElement('img');
        img.src = guard.photo_url;
        img.alt = `${guard.name} Photo`;
        imgDiv.appendChild(img);

        const infoDiv = document.createElement('div');
        infoDiv.className = 'guard-info';

        const nameEl = document.createElement('h3');
        nameEl.className = 'guard-name';
        nameEl.textContent = guard.name;

        const roleEl = document.createElement('p');
        roleEl.className = 'guard-role';
        roleEl.textContent = guard.role;

        const detailsDiv = document.createElement('div');
        detailsDiv.className = 'guard-details';
        detailsDiv.innerHTML = `
            <p><strong>Age:</strong> ${guard.age}</p>
            <p><strong>Weight:</strong> ${guard.weight} lbs</p>
            <p><strong>Experience:</strong> ${guard.experience} years</p>
            <p><strong>Location:</strong> ${guard.location}</p>
            <p><strong>Skills:</strong> ${guard.skills || 'N/A'}</p>
        `;

        const bookBtn = document.createElement('button');
        bookBtn.className = 'book-btn';
        bookBtn.textContent = 'Book Now';
        bookBtn.addEventListener('click', () => {
            // Store the selected guard temporarily in localStorage
            localStorage.setItem('selectedGuard', JSON.stringify(guard));
            window.location.href = 'confirm.html';
        });

        infoDiv.appendChild(nameEl);
        infoDiv.appendChild(roleEl);
        infoDiv.appendChild(detailsDiv);
        infoDiv.appendChild(bookBtn);

        card.appendChild(imgDiv);
        card.appendChild(infoDiv);

        guardsContainer.appendChild(card);
    });
}

loadGuards();
