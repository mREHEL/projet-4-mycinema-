const API_URL = 'http://localhost:8080/index.php';

// NAVIGATION & TABS

function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
    
    // Load data
    if (tabName === 'movies') loadMovies();
    else if (tabName === 'rooms') loadRooms();
    else if (tabName === 'screenings') loadScreenings();
}

// MOVIES

function addMovie(event) {
    event.preventDefault();
    const msg = document.getElementById('movie-message');
    
    const data = {
        title: document.getElementById('movie-title').value,
        description: document.getElementById('movie-description').value,
        duration: parseInt(document.getElementById('movie-duration').value),
        release_year: parseInt(document.getElementById('movie-year').value) || null,
        genre: document.getElementById('movie-genre').value,
        director: document.getElementById('movie-director').value
    };
    
    if (!data.title || !data.duration) {
        showMessage(msg, 'Titre et durée requis', 'error');
        return;
    }
    
    fetch(`${API_URL}?route=movies`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        showMessage(msg, res.message || 'Film ajouté', res.message ? 'success' : 'error');
        if (res.message && res.message.includes('créé')) {
            document.getElementById('movie-form').reset();
            loadMovies();
            loadScreeningMovies();
        }
    })
    .catch(e => showMessage(msg, 'Erreur: ' + e.message, 'error'));
}

function loadMovies(page = 1) {
    fetch(`${API_URL}?route=movies&page=${page}&per_page=5`)
    .then(r => r.json())
    .then(movies => {
        const list = document.getElementById('movies-list');
        if (!Array.isArray(movies) || movies.length === 0) {
            list.innerHTML = '<p>Aucun film trouvé</p>';
            return;
        }
        list.innerHTML = movies.map(m => `
            <div class="list-item">
                <div class="list-item-content">
                    <strong>${m.title}</strong> (${m.duration} min)
                    <p>${m.description || ''}</p>
                    <small>${m.genre || ''} • ${m.director || ''} (${m.release_year || '?'})</small>
                </div>
                <div class="list-item-actions">
                    <button class="btn-edit" onclick="editMovie(${m.id})">Modifier</button>
                    <button class="btn-delete" onclick="deleteMovie(${m.id})">Supprimer</button>
                </div>
            </div>
        `).join('');
    })
    .catch(e => console.error('Erreur chargement films:', e));
}

function editMovie(id) {
    fetch(`${API_URL}?route=movies&id=${id}`)
    .then(r => r.json())
    .then(m => {
        document.getElementById('movie-title').value = m.title;
        document.getElementById('movie-description').value = m.description || '';
        document.getElementById('movie-duration').value = m.duration;
        document.getElementById('movie-year').value = m.release_year || '';
        document.getElementById('movie-genre').value = m.genre || '';
        document.getElementById('movie-director').value = m.director || '';
        
        // Change form behavior for update
        const form = document.getElementById('movie-form');
        form.onsubmit = (e) => updateMovie(e, id);
        form.querySelector('button').textContent = 'Mettre à jour le film';
    })
    .catch(e => console.error('Erreur:', e));
}

function updateMovie(event, id) {
    event.preventDefault();
    const msg = document.getElementById('movie-message');
    
    const data = {
        title: document.getElementById('movie-title').value,
        description: document.getElementById('movie-description').value,
        duration: parseInt(document.getElementById('movie-duration').value),
        release_year: parseInt(document.getElementById('movie-year').value) || null,
        genre: document.getElementById('movie-genre').value,
        director: document.getElementById('movie-director').value
    };
    
    fetch(`${API_URL}?route=movies&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        showMessage(msg, res.message || 'Film mis à jour', 'success');
        document.getElementById('movie-form').reset();
        document.getElementById('movie-form').onsubmit = addMovie;
        document.getElementById('movie-form').querySelector('button').textContent = 'Ajouter un film';
        loadMovies();
    })
    .catch(e => showMessage(msg, 'Erreur: ' + e.message, 'error'));
}

function deleteMovie(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce film?')) return;
    
    fetch(`${API_URL}?route=movies&id=${id}`, { method: 'DELETE' })
    .then(r => r.json())
    .then(res => {
        alert(res.message || 'Film supprimé');
        loadMovies();
    })
    .catch(e => alert('Erreur: ' + e.message));
}

function loadScreeningMovies() {
    fetch(`${API_URL}?route=movies`)
    .then(r => r.json())
    .then(movies => {
        const select = document.getElementById('screening-movie');
        select.innerHTML = '<option value="">-- Sélectionner un film --</option>' + 
            movies.map(m => `<option value="${m.id}">${m.title} (${m.duration}min)</option>`).join('');
    });
}

// ROOMS

function addRoom(event) {
    event.preventDefault();
    const msg = document.getElementById('room-message');
    
    const data = {
        name: document.getElementById('room-name').value,
        capacity: parseInt(document.getElementById('room-capacity').value),
        type: document.getElementById('room-type').value
    };
    
    if (!data.name || !data.capacity) {
        showMessage(msg, 'Nom et capacité requis', 'error');
        return;
    }
    
    fetch(`${API_URL}?route=rooms`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        showMessage(msg, res.message || 'Salle ajoutée', res.message ? 'success' : 'error');
        if (res.message && res.message.includes('créé')) {
            document.getElementById('room-form').reset();
            loadRooms();
            loadScreeningRooms();
        }
    })
    .catch(e => showMessage(msg, 'Erreur: ' + e.message, 'error'));
}

function loadRooms() {
    fetch(`${API_URL}?route=rooms`)
    .then(r => r.json())
    .then(rooms => {
        const list = document.getElementById('rooms-list');
        if (!Array.isArray(rooms) || rooms.length === 0) {
            list.innerHTML = '<p>Aucune salle trouvée</p>';
            return;
        }
        list.innerHTML = rooms.map(r => `
            <div class="list-item">
                <div class="list-item-content">
                    <strong>${r.name}</strong> - ${r.capacity} places (${r.type})
                </div>
                <div class="list-item-actions">
                    <button class="btn-edit" onclick="editRoom(${r.id})">Modifier</button>
                    <button class="btn-delete" onclick="deleteRoom(${r.id})">Supprimer</button>
                </div>
            </div>
        `).join('');
    })
    .catch(e => console.error('Erreur chargement salles:', e));
}

function editRoom(id) {
    fetch(`${API_URL}?route=rooms&id=${id}`)
    .then(r => r.json())
    .then(room => {
        document.getElementById('room-name').value = room.name;
        document.getElementById('room-capacity').value = room.capacity;
        document.getElementById('room-type').value = room.type;
        
        const form = document.getElementById('room-form');
        form.onsubmit = (e) => updateRoom(e, id);
        form.querySelector('button').textContent = 'Mettre à jour la salle';
    })
    .catch(e => console.error('Erreur:', e));
}

function updateRoom(event, id) {
    event.preventDefault();
    const msg = document.getElementById('room-message');
    
    const data = {
        name: document.getElementById('room-name').value,
        capacity: parseInt(document.getElementById('room-capacity').value),
        type: document.getElementById('room-type').value
    };
    
    fetch(`${API_URL}?route=rooms&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        showMessage(msg, res.message || 'Salle mise à jour', 'success');
        document.getElementById('room-form').reset();
        document.getElementById('room-form').onsubmit = addRoom;
        document.getElementById('room-form').querySelector('button').textContent = 'Ajouter une salle';
        loadRooms();
    })
    .catch(e => showMessage(msg, 'Erreur: ' + e.message, 'error'));
}

function deleteRoom(id) {
    if (!confirm('Êtes-vous sûr? Les séances liées seront conservées (soft delete)')) return;
    
    fetch(`${API_URL}?route=rooms&id=${id}`, { method: 'DELETE' })
    .then(r => r.json())
    .then(res => {
        alert(res.message || 'Salle supprimée');
        loadRooms();
    })
    .catch(e => alert('Erreur: ' + e.message));
}

function loadScreeningRooms() {
    fetch(`${API_URL}?route=rooms`)
    .then(r => r.json())
    .then(rooms => {
        const select = document.getElementById('screening-room');
        select.innerHTML = '<option value="">-- Sélectionner une salle --</option>' + 
            rooms.map(r => `<option value="${r.id}">${r.name} (${r.type})</option>`).join('');
    });
}

// SCREENINGS

function addScreening(event) {
    event.preventDefault();
    const msg = document.getElementById('screening-message');
    
    const startInput = document.getElementById('screening-start').value;
    if (!startInput) {
        showMessage(msg, 'Date et heure requises', 'error');
        return;
    }
    
    const data = {
        movie_id: parseInt(document.getElementById('screening-movie').value),
        room_id: parseInt(document.getElementById('screening-room').value),
        start_time: startInput.replace('T', ' ') + ':00'
    };
    
    if (!data.movie_id || !data.room_id) {
        showMessage(msg, 'Film et salle requis', 'error');
        return;
    }
    
    fetch(`${API_URL}?route=screenings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        showMessage(msg, res.message || 'Séance créée', res.message ? 'success' : 'error');
        if (res.message && res.message.includes('créé')) {
            document.getElementById('screening-form').reset();
            loadScreenings();
        }
    })
    .catch(e => showMessage(msg, 'Erreur: ' + e.message, 'error'));
}

function loadScreenings() {
    fetch(`${API_URL}?route=screenings`)
    .then(r => r.json())
    .then(screenings => {
        const list = document.getElementById('screenings-list');
        if (!Array.isArray(screenings) || screenings.length === 0) {
            list.innerHTML = '<p>Aucune séance trouvée</p>';
            return;
        }
        list.innerHTML = screenings.map(s => {
            const startDate = new Date(s.start_time).toLocaleString('fr-FR');
            const endDate = new Date(s.end_time).toLocaleString('fr-FR');
            return `
                <div class="list-item">
                    <div class="list-item-content">
                        <strong>Séance #${s.id}</strong><br>
                        Film: ${s.movie_id} | Salle: ${s.room_id}<br>
                        <small>${startDate} → ${endDate}</small>
                    </div>
                    <div class="list-item-actions">
                        <button class="btn-edit" onclick="editScreening(${s.id})">Modifier</button>
                        <button class="btn-delete" onclick="deleteScreening(${s.id})">Supprimer</button>
                    </div>
                </div>
            `;
        }).join('');
    })
    .catch(e => console.error('Erreur chargement séances:', e));
}

function editScreening(id) {
    fetch(`${API_URL}?route=screenings&id=${id}`)
    .then(r => r.json())
    .then(s => {
        document.getElementById('screening-movie').value = s.movie_id;
        document.getElementById('screening-room').value = s.room_id;
        const dt = new Date(s.start_time);
        const isoStr = dt.toISOString().slice(0, 16);
        document.getElementById('screening-start').value = isoStr;
        
        const form = document.getElementById('screening-form');
        form.onsubmit = (e) => updateScreening(e, id);
        form.querySelector('button').textContent = 'Mettre à jour la séance';
    })
    .catch(e => console.error('Erreur:', e));
}

function updateScreening(event, id) {
    event.preventDefault();
    const msg = document.getElementById('screening-message');
    
    const startInput = document.getElementById('screening-start').value;
    const data = {
        movie_id: parseInt(document.getElementById('screening-movie').value),
        room_id: parseInt(document.getElementById('screening-room').value),
        start_time: startInput.replace('T', ' ') + ':00'
    };
    
    fetch(`${API_URL}?route=screenings&id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(res => {
        showMessage(msg, res.message || 'Séance mise à jour', 'success');
        document.getElementById('screening-form').reset();
        document.getElementById('screening-form').onsubmit = addScreening;
        document.getElementById('screening-form').querySelector('button').textContent = 'Créer une séance';
        loadScreenings();
    })
    .catch(e => showMessage(msg, 'Erreur: ' + e.message, 'error'));
}

function deleteScreening(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette séance?')) return;
    
    fetch(`${API_URL}?route=screenings&id=${id}`, { method: 'DELETE' })
    .then(r => r.json())
    .then(res => {
        alert(res.message || 'Séance supprimée');
        loadScreenings();
    })
    .catch(e => alert('Erreur: ' + e.message));
}

// UTILITIES

function showMessage(elem, text, type) {
    elem.textContent = text;
    elem.className = 'message ' + type;
    setTimeout(() => { elem.className = 'message'; }, 5000);
}

// INIT

window.addEventListener('DOMContentLoaded', () => {
    loadMovies();
    loadScreeningMovies();
    loadScreeningRooms();
});