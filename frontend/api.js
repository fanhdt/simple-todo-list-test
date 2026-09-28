const API_URL = "https://simple-todo-list-test-gu5j.onrender.com";


async function handleResponse(response) {
  if (response.status === 204) {
    return null;
  }

  const result = await response.json();

  if (!response.ok) {
    const message = result?.error?.message || "Terjadi kesalahan pada server.";
    throw new Error(message);
  }

  return result.data;
}

// ambil seluruh data todo
async function getTodos() {
  const response = await fetch(`${API_URL}/api/todos`);
  return handleResponse(response);
}

// buat todo baru
async function createTodo(title) {
  const response = await fetch(`${API_URL}/api/todos`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ title }),
  });
  return handleResponse(response);
}

// ubah sebagian data todo (update)
async function updateTodo(id, changes) {
  const response = await fetch(`${API_URL}/api/todos/${id}`, {
    method: "PATCH",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(changes),
  });
  return handleResponse(response);
}

// Hapus todo
async function deleteTodo(id) {
  const response = await fetch(`${API_URL}/api/todos/${id}`, {
    method: "DELETE",
  });
  return handleResponse(response);
}
