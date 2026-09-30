const list = document.getElementById("todo-list");
const statusMessage = document.getElementById("status-message");
const todoCount = document.getElementById("todo-count");
const emptyState = document.getElementById("empty-state");
const refreshButton = document.getElementById("refresh-button");

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

todoCount.textContent = `${todos.length} todo`;

if (todos.length === 0) {
emptyState.hidden = false;
return;
}

emptyState.hidden = true;

for (const todo of todos) {
list.appendChild(renderTodoItem(todo));
}
}

function renderTodoItem(todo) {
const item = document.createElement("li");
item.className = `todo-item${todo.completed ? " completed" : ""}`;
item.dataset.id = String(todo.id);

const check = document.createElement("div");
check.className = "todo-check";

if (todo.completed) {
check.textContent = "✓";
}

const content = document.createElement("div");
content.className = "todo-content";

const title = document.createElement("div");
title.className = "todo-title";
title.textContent = todo.title;

const meta = document.createElement("div");
meta.className = "todo-meta";
meta.textContent = todo.completed
? "Selesai"
: "Belum selesai";

content.append(title, meta);
item.append(check, content);

return item;
}

async function loadTodos() {
showStatus("Memuat data...", "info");

try {
const todos = await getTodos();


hideStatus();
renderTodos(todos);


} catch (err) {
console.error("Gagal mengambil todo:", err);


showStatus("Gagal mengambil data Todo.");

todoCount.textContent = "Gagal memuat data";
list.innerHTML = "";
emptyState.hidden = true;


}
}

refreshButton.addEventListener("click", loadTodos);

loadTodos();
