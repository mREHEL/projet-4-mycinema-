const API_URL = 'http://localhost:8080/index.php';
const q = id => document.getElementById(id);
const jsonFetch = (route, opts={}) => {
  const url = `${API_URL}?route=${route}` + (opts.query || '');
  const init = { headers: {'Content-Type':'application/json'}, ...opts };
  return fetch(url, init).then(r => r.json());
};
function showMessage(el, text, type='') {
  el.textContent = text; el.className = 'message ' + type;
  setTimeout(()=> el.className = 'message', 5000);
}
/* MOVIES */
function loadMovies(page=1) {
  jsonFetch('movies', { query:`&page=${page}&per_page=5` })
    .then(list => {
      const out = q('movies-list');
      if (!Array.isArray(list) || !list.length) { out.innerHTML = '<p>Aucun film trouvé</p>'; return; }
      out.innerHTML = list.map(m => `
        <div class="list-item">
          <div class="list-item-content">
            <strong>${m.title}</strong> (${m.duration} min)
            <p>${m.description||''}</p>
            <small>${m.genre||''} • ${m.director||''} (${m.release_year||'?'})</small>
          </div>
          <div class="list-item-actions">
            <button class="btn-edit" onclick="editMovie(${m.id})">Modifier</button>
            <button class="btn-delete" onclick="deleteMovie(${m.id})">Supprimer</button>
          </div>
        </div>`).join('');
    })
    .catch(e=>console.error(e));
}

function submitMovieHandler(e, id) {
  e.preventDefault();
  const msg = q('movie-message');
  const data = {
    title: q('movie-title').value,
    description: q('movie-description').value,
    duration: parseInt(q('movie-duration').value) || 0,
    release_year: parseInt(q('movie-year').value) || null,
    genre: q('movie-genre').value,
    director: q('movie-director').value
  };
  const method = id ? 'PUT' : 'POST';
  const urlQuery = id ? `&id=${id}` : '';
  fetch(`${API_URL}?route=movies${urlQuery}`, {
    method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(data)
  }).then(r=>r.json()).then(res=>{
    showMessage(msg, res.message || (id ? 'Film mis à jour' : 'Film ajouté'), 'success');
    q('movie-form').reset();
    q('movie-form').onsubmit = addMovie;
    q('movie-form').querySelector('button').textContent = 'Ajouter un film';
    loadMovies(); loadScreeningMovies();
  }).catch(err=>showMessage(msg,'Erreur: '+err.message,'error'));
}
function addMovie(e){ submitMovieHandler(e, 0); }
function editMovie(id){
  jsonFetch('movies', { query:`&id=${id}` })
    .then(m=>{
      q('movie-title').value = m.title;
      q('movie-description').value = m.description||'';
      q('movie-duration').value = m.duration;
      q('movie-year').value = m.release_year||'';
      q('movie-genre').value = m.genre||'';
      q('movie-director').value = m.director||'';
      const form = q('movie-form'); form.onsubmit = ev => submitMovieHandler(ev, id);
      form.querySelector('button').textContent = 'Mettre à jour le film';
    });
}
function deleteMovie(id){
  if (!confirm('Supprimer ce film ?')) return;
  fetch(`${API_URL}?route=movies&id=${id}`, { method:'DELETE' })
    .then(r=>r.json()).then(res=>{ alert(res.message||'Film supprimé'); loadMovies(); })
    .catch(e=>alert('Erreur: '+e.message));
}
function loadScreeningMovies(){
  jsonFetch('movies').then(list=>{
    q('screening-movie').innerHTML = '<option value="">-- Sélectionner un film --</option>' +
      list.map(m => `<option value="${m.id}">${m.title} (${m.duration}min)</option>`).join('');
  });
}
/* ROOMS */
function loadRooms(){
  jsonFetch('rooms').then(list=>{
    const out = q('rooms-list');
    if (!Array.isArray(list) || !list.length){ out.innerHTML='<p>Aucune salle trouvée</p>'; return; }
    out.innerHTML = list.map(r=>`
      <div class="list-item">
        <div class="list-item-content"><strong>${r.name}</strong> - ${r.capacity} places (${r.type})</div>
        <div class="list-item-actions">
          <button class="btn-edit" onclick="editRoom(${r.id})">Modifier</button>
          <button class="btn-delete" onclick="deleteRoom(${r.id})">Supprimer</button>
        </div>
      </div>`).join('');
  }).catch(e=>console.error(e));
}
function submitRoomHandler(e, id){
  e.preventDefault();
  const msg = q('room-message');
  const data = { name:q('room-name').value, capacity:parseInt(q('room-capacity').value)||0, type:q('room-type').value };
  const method = id ? 'PUT' : 'POST';
  const urlQuery = id ? `&id=${id}` : '';
  fetch(`${API_URL}?route=rooms${urlQuery}`, { method, headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)})
    .then(r=>r.json()).then(res=>{
      showMessage(msg, res.message||'OK','success'); q('room-form').reset();
      q('room-form').onsubmit = addRoom; q('room-form').querySelector('button').textContent='Ajouter une salle';
      loadRooms(); loadScreeningRooms();
    }).catch(err=>showMessage(msg,'Erreur: '+err.message,'error'));
}
function addRoom(e){ submitRoomHandler(e,0); }
function editRoom(id){ jsonFetch('rooms', { query:`&id=${id}`}).then(r=>{
  q('room-name').value=r.name; q('room-capacity').value=r.capacity; q('room-type').value=r.type;
  const f=q('room-form'); f.onsubmit = ev=> submitRoomHandler(ev, id); f.querySelector('button').textContent='Mettre à jour la salle';
}); }
function deleteRoom(id){ if(!confirm('Êtes-vous sûr?')) return; fetch(`${API_URL}?route=rooms&id=${id}`,{method:'DELETE'}).then(r=>r.json()).then(res=>{alert(res.message||'Salle supprimée'); loadRooms();}); }
function loadScreeningRooms(){ jsonFetch('rooms').then(list=>{ q('screening-room').innerHTML = '<option value=\"\">-- Sélectionner une salle --</option>' + list.map(r=>`<option value=\"${r.id}\">${r.name} (${r.type})</option>`).join(''); }); }
/* SCREENINGS */
function loadScreenings(){
  jsonFetch('screenings').then(list=>{
    const out = q('screenings-list');
    if(!Array.isArray(list)||!list.length){ out.innerHTML='<p>Aucune séance trouvée</p>'; return; }
    out.innerHTML = list.map(s=>{
      const start = new Date(s.start_time).toLocaleString('fr-FR');
      const end = new Date(s.end_time).toLocaleString('fr-FR');
      return `<div class="list-item"><div class="list-item-content"><strong>Séance #${s.id}</strong><br>Film: ${s.movie_id} | Salle: ${s.room_id}<br><small>${start} → ${end}</small></div><div class="list-item-actions"><button class="btn-edit" onclick="editScreening(${s.id})">Modifier</button><button class="btn-delete" onclick="deleteScreening(${s.id})">Supprimer</button></div></div>`;
    }).join('');
  });
}
function submitScreeningHandler(e, id){
  e.preventDefault();
  const msg = q('screening-message');
  const start = q('screening-start').value;
  if(!start){ showMessage(msg,'Date et heure requises','error'); return; }
  const data = { movie_id:parseInt(q('screening-movie').value), room_id:parseInt(q('screening-room').value), start_time: start.replace('T',' ') + ':00' };
  const method = id ? 'PUT' : 'POST';
  const urlQuery = id ? `&id=${id}` : '';
  fetch(`${API_URL}?route=screenings${urlQuery}`, { method, headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)})
    .then(r=>r.json()).then(res=>{
      showMessage(msg, res.message||'OK','success'); q('screening-form').reset();
      q('screening-form').onsubmit = addScreening; q('screening-form').querySelector('button').textContent='Créer une séance';
      loadScreenings();
    }).catch(err=>showMessage(msg,'Erreur: '+err.message,'error'));
}
function addScreening(e){ submitScreeningHandler(e,0); }
function editScreening(id){ jsonFetch('screenings', { query:`&id=${id}`}).then(s=>{
  q('screening-movie').value=s.movie_id; q('screening-room').value=s.room_id;
  q('screening-start').value = new Date(s.start_time).toISOString().slice(0,16);
  const f=q('screening-form'); f.onsubmit = ev => submitScreeningHandler(ev, id); f.querySelector('button').textContent='Mettre à jour la séance';
}); }
function deleteScreening(id){ if(!confirm('Supprimer ?')) return; fetch(`${API_URL}?route=screenings&id=${id}`,{method:'DELETE'}).then(r=>r.json()).then(()=>loadScreenings()); }
/* INIT */
window.addEventListener('DOMContentLoaded', ()=>{
  q('movie-form').onsubmit = addMovie;
  q('room-form').onsubmit = addRoom;
  q('screening-form').onsubmit = addScreening;
  loadMovies(); loadScreeningMovies(); loadScreeningRooms(); loadRooms(); loadScreenings();
});