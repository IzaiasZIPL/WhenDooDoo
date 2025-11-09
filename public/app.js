// public/app.js
const API = '/api/todos.php';

const titleEl = document.getElementById('title');
const notesEl = document.getElementById('notes');
const createBtn = document.getElementById('createBtn');
const listEl = document.getElementById('list');
const clearAllBtn = document.getElementById('clearAll');

async function fetchTodos() {
  const res = await fetch(API);
  const json = await res.json();
  return json.todos || [];
}

function renderTodos(todos) {
  listEl.innerHTML = '';
  if (todos.length === 0) {
    listEl.innerHTML = '<p>No todos yet.</p>';
    return;
  }
  todos.forEach(t => {
    const div = document.createElement('div');
    div.className = 'todo';
    div.innerHTML = `
      <div class="left">
        <input type="checkbox" ${t.is_done ? 'checked' : ''} data-id="${t.id}" class="toggle" />
        <div>
          <div class="${t.is_done ? 'done' : ''}">${escapeHtml(t.title)}</div>
          <div style="font-size:0.9rem;color:#666">${t.notes ? escapeHtml(t.notes) : ''}</div>
        </div>
      </div>
      <div>
        <button data-id="${t.id}" class="edit">Edit</button>
        <button data-id="${t.id}" class="delete">Delete</button>
      </div>
    `;
    listEl.appendChild(div);
  });
}

function escapeHtml(text) {
  return text.replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m]));
}

async function reload() {
  const todos = await fetchTodos();
  renderTodos(todos);
}

createBtn.addEventListener('click', async () => {
  const title = titleEl.value.trim();
  const notes = notesEl.value.trim();
  if (!title) return alert('Title required');
  await fetch(API, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ title, notes })
  });
  titleEl.value = '';
  notesEl.value = '';
  reload();
});

listEl.addEventListener('click', async (e) => {
  if (e.target.matches('.delete')) {
    const id = e.target.dataset.id;
    if (!confirm('Delete todo?')) return;
    await fetch(`${API}?id=${id}`, { method: 'DELETE' });
    reload();
  } else if (e.target.matches('.edit')) {
    const id = e.target.dataset.id;
    const newTitle = prompt('New title?');
    if (newTitle === null) return;
    await fetch(`${API}?id=${id}`, {
      method: 'PUT',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ title: newTitle })
    });
    reload();
  } else if (e.target.matches('.toggle')) {
    const id = e.target.dataset.id;
    const is_done = e.target.checked ? 1 : 0;
    await fetch(`${API}?id=${id}`, {
      method: 'PUT',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ is_done })
    });
    reload();
  }
});

clearAllBtn.addEventListener('click', async () => {
  if (!confirm('Delete ALL todos for this session?')) return;
  await fetch(API, { method: 'DELETE' });
  reload();
});

reload();
