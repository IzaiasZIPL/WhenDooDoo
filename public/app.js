// app.js — frontend to consume api/users.php and api/todos.php
const USERS_API = '/api/users.php';
const TODOS_API = '/api/todos.php';

const userSelect = document.getElementById('userSelect');
const refreshUsersBtn = document.getElementById('refreshUsers');
const createUserForm = document.getElementById('createUserForm');
const currentUserLabel = document.getElementById('currentUserLabel');
const createTodoForm = document.getElementById('createTodoForm');
const todosDiv = document.getElementById('todos');
const statusDiv = document.getElementById('status');

let currentUserId = null;

// small helper for errors
function showStatus(msg, isError = true, ms = 4000) {
  statusDiv.textContent = msg;
  statusDiv.style.color = isError ? '#cc0000' : '#0b6623';
  if (ms) setTimeout(() => { if (statusDiv.textContent === msg) statusDiv.textContent = ''; }, ms);
}

// fetch users and populate select
async function fetchUsers() {
  try {
    const res = await fetch(USERS_API);
    if (!res.ok) throw new Error('Failed to fetch users');
    const users = await res.json();
    userSelect.innerHTML = '<option value="">— Select user —</option>';
    users.forEach(u => {
      const opt = document.createElement('option');
      opt.value = u.id;
      opt.textContent = u.name ? `${u.name} (${u.id})` : `User ${u.id}`;
      userSelect.appendChild(opt);
    });
  } catch (err) {
    showStatus('Could not load users: ' + err.message);
  }
}

// create user
createUserForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const form = e.target;
  const name = form.name.value.trim();
  const email = form.email.value.trim();
  if (!name) return showStatus('Name required');

  try {
    const res = await fetch(USERS_API, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ name, email })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Create failed');
    form.reset();
    await fetchUsers();
    showStatus('User created', false);
  } catch (err) {
    showStatus('Create user error: ' + err.message);
  }
});

// when user selected
userSelect.addEventListener('change', async (e) => {
  const val = e.target.value;
  if (!val) {
    currentUserId = null;
    currentUserLabel.textContent = 'No user selected';
    todosDiv.innerHTML = '';
    return;
  }
  currentUserId = parseInt(val, 10);
  currentUserLabel.textContent = `User #${currentUserId}`;
  await fetchAndRenderTodos();
});

// refresh users button
refreshUsersBtn.addEventListener('click', async (e) => {
  await fetchUsers();
});

// fetch todos for current user
async function fetchAndRenderTodos() {
  if (!currentUserId) return;
  todosDiv.innerHTML = '<div style="padding:8px;color:#666">Loading...</div>';
  try {
    const res = await fetch(`${TODOS_API}?user_id=${currentUserId}`);
    if (!res.ok) throw new Error('Failed to fetch todos');
    const todos = await res.json();
    renderTodos(todos);
  } catch (err) {
    todosDiv.innerHTML = '';
    showStatus('Could not load todos: ' + err.message);
  }
}

// render todos
function renderTodos(todos) {
  if (!Array.isArray(todos) || todos.length === 0) {
    todosDiv.innerHTML = '<div style="padding:8px;color:#666">No todos yet.</div>';
    return;
  }
  todosDiv.innerHTML = '';
  todos.forEach(t => {
    const item = document.createElement('div');
    item.className = 'todo card';
    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.checked = !!Number(t.is_done);
    checkbox.addEventListener('change', () => toggleDone(t.id, checkbox.checked));

    const text = document.createElement('div');
    text.style.minWidth = '0';
    const title = document.createElement('div');
    title.className = 'title' + (checkbox.checked ? ' done' : '');
    title.textContent = t.title;
    const notes = document.createElement('div');
    notes.className = 'notes';
    notes.textContent = t.notes || '';
    text.appendChild(title);
    if (t.notes) text.appendChild(notes);
    if (t.due_at) {
      const meta = document.createElement('div');
      meta.className = 'meta';
      meta.textContent = 'Due: ' + t.due_at;
      text.appendChild(meta);
    }

    const actions = document.createElement('div');
    actions.className = 'todo-actions';
    const editBtn = document.createElement('button');
    editBtn.textContent = 'Edit';
    editBtn.className = 'small ghost';
    editBtn.addEventListener('click', () => editTodoPrompt(t));
    const delBtn = document.createElement('button');
    delBtn.textContent = 'Delete';
    delBtn.className = 'small';
    delBtn.addEventListener('click', () => deleteTodo(t.id));
    actions.appendChild(editBtn);
    actions.appendChild(delBtn);

    item.appendChild(checkbox);
    item.appendChild(text);
    item.appendChild(actions);
    todosDiv.appendChild(item);
  });
}

// create todo
createTodoForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (!currentUserId) return showStatus('Select a user first');

  const form = e.target;
  const title = form.title.value.trim();
  const due_at = form.due_at.value.trim() || null;
  if (!title) return showStatus('Title required');

  try {
    const res = await fetch(TODOS_API, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ user_id: currentUserId, title, due_at })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Create failed');
    form.reset();
    await fetchAndRenderTodos();
    showStatus('Todo created', false);
  } catch (err) {
    showStatus('Create todo error: ' + err.message);
  }
});

// toggle done
async function toggleDone(id, done) {
  try {
    const res = await fetch(`${TODOS_API}?id=${id}`, {
      method: 'PUT',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ is_done: done ? 1 : 0 })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Update failed');
    await fetchAndRenderTodos();
  } catch (err) {
    showStatus('Toggle error: ' + err.message);
  }
}

// edit via prompt (simple)
async function editTodoPrompt(todo) {
  const newTitle = prompt('Edit title', todo.title);
  if (newTitle === null) return; // cancelled
  const newNotes = prompt('Edit notes (leave blank to keep)', todo.notes || '');
  const newDue = prompt('Due (YYYY-MM-DD HH:MM) — leave blank to clear', todo.due_at || '');
  try {
    const payload = { title: newTitle };
    if (newNotes !== null) payload.notes = newNotes;
    if (newDue !== null) payload.due_at = newDue === '' ? null : newDue;
    const res = await fetch(`${TODOS_API}?id=${todo.id}`, {
      method: 'PUT',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Update failed');
    await fetchAndRenderTodos();
    showStatus('Todo updated', false);
  } catch (err) {
    showStatus('Edit error: ' + err.message);
  }
}

// delete
async function deleteTodo(id) {
  if (!confirm('Delete this todo?')) return;
  try {
    const res = await fetch(`${TODOS_API}?id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Delete failed');
    await fetchAndRenderTodos();
    showStatus('Todo deleted', false);
  } catch (err) {
    showStatus('Delete error: ' + err.message);
  }
}

// init
(async function init() {
  await fetchUsers();
  // optionally auto-select first user
  if (userSelect.options.length > 1) {
    userSelect.selectedIndex = 1;
    userSelect.dispatchEvent(new Event('change'));
  }
})();
