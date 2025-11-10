const API_BASE = './api';
const todoList = document.getElementById('todoList');
const newTaskInput = document.getElementById('newTask');
const newDescInput = document.getElementById('newDesc');
const addBtn = document.getElementById('addBtn');

loadTodos();

async function loadTodos() {
  const res = await fetch(`${API_BASE}/getTodos.php`);
  const todos = await res.json();

  todoList.innerHTML = '';
  todos.forEach(todo => {
    const li = document.createElement('li');
    li.className = todo.done == 1 ? 'done' : '';

    const text = document.createElement('div');
    text.innerHTML = `<strong>${todo.task}</strong><br><small>${todo.description || ''}</small>`;
    text.addEventListener('click', () => toggleDone(todo.id, !todo.done));

    const del = document.createElement('button');
    del.textContent = 'x';
    del.className = 'delete';
    del.addEventListener('click', () => deleteTodo(todo.id));

    li.appendChild(text);
    li.appendChild(del);
    todoList.appendChild(li);
  });
}

addBtn.addEventListener('click', async () => {
  const task = newTaskInput.value.trim();
  const description = newDescInput.value.trim();
  if (task === '') return alert('Title cannot be empty!');

  await fetch(`${API_BASE}/addTodo.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ task, description }),
  });

  newTaskInput.value = '';
  newDescInput.value = '';
  loadTodos();
});

async function toggleDone(id, done) {
  await fetch(`${API_BASE}/updateTodo.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, done }),
  });
  loadTodos();
}

async function deleteTodo(id) {
  await fetch(`${API_BASE}/deleteTodo.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id }),
  });
  loadTodos();
}
