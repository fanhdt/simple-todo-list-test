const form = document.getElementById("todo-form");
const input = document.getElementById("todo-input");
const list = document.getElementById("todo-list");
const statusMessage = document.getElementById("status-message");

function showStatus(message, type = "error") {
  statusMessage.textContent = message;
  statusMessage.hidden = false;
  statusMessage.className = `status-message ${type === "info" ? "info" : ""}`.trim();
}

function hideStatus() {
  statusMessage.hidden = true;
}

function renderTodos(todos) {
  list.innerHTML = "";

  if (todos.length === 0) {
    const empty = document.createElement("li");
    empty.className = "empty-state";
    empty.textContent = "Belum ada todo. Tambahkan satu di atas.";
    list.appendChild(empty);
    return;
  }

  for (const todo of todos) {
    list.appendChild(renderTodoItem(todo));
  }
}

function renderTodoItem(todo) {
  const item = document.createElement("li");
  item.className = `todo-item${todo.completed ? " completed" : ""}`;
  item.dataset.id = String(todo.id);

  const checkbox = document.createElement("input");
  checkbox.type = "checkbox";
  checkbox.checked = todo.completed;
  checkbox.addEventListener("change", () => handleToggleCompleted(todo.id, checkbox.checked));

  const title = document.createElement("span");
  title.className = "todo-title";
  title.textContent = todo.title;

  const deleteBtn = document.createElement("button");
  deleteBtn.type = "button";
  deleteBtn.className = "delete-btn";
  deleteBtn.textContent = "Hapus";
  deleteBtn.addEventListener("click", () => handleDelete(todo.id));

  item.append(checkbox, title, deleteBtn);
  return item;
}

async function loadTodos() {
  showStatus("Memuat data...", "info");
  try {
    const todos = await getTodos();
    hideStatus();
    renderTodos(todos);
  } catch (err) {
    showStatus("Gagal mengambil data Todo.");
  }
}

async function handleCreate(event) {
  event.preventDefault();

  const title = input.value.trim();
  if (title === "") {
    return;
  }

  const submitBtn = form.querySelector("button");
  submitBtn.disabled = true;

  try {
    await createTodo(title);
    input.value = "";
    hideStatus();
    await loadTodos();
  } catch (err) {
    showStatus("Todo gagal dibuat.");
  } finally {
    submitBtn.disabled = false;
  }
}

async function handleToggleCompleted(id, completed) {
  try {
    await updateTodo(id, { completed });
    hideStatus();
    await loadTodos();
  } catch (err) {
    showStatus("Todo gagal diubah.");
  }
}

async function handleDelete(id) {
  try {
    await deleteTodo(id);
    hideStatus();
    await loadTodos();
  } catch (err) {
    showStatus("Todo gagal dihapus.");
  }
}

form.addEventListener("submit", handleCreate);

loadTodos();
