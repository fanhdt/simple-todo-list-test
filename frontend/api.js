/**
 * api.js
 *
 * Satu-satunya file yang berkomunikasi langsung dengan backend.
 * Semua bagian frontend lain memanggil fungsi-fungsi di sini,
 * bukan memanggil fetch() secara langsung.
 *
 * PENTING: ganti nilai API_URL di bawah ini sesuai kebutuhan.
 *
 * - Saat development lokal (via Docker): "http://localhost:8000"
 * - Setelah backend dideploy ke Render, ganti menjadi:
 *   "https://todo-api-xxxx.onrender.com"
 *   (gunakan URL asli yang diberikan Render setelah deployment berhasil)
 */
const API_URL = "https://simple-todo-list-test-gu5j.onrender.com";

/**
 * Helper internal untuk memproses response fetch.
 * Melempar Error dengan pesan dari backend jika response tidak OK.
 */
async function handleResponse(response) {
  // DELETE mengembalikan 204 No Content, tidak ada body untuk di-parse
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

/**
 * Mengambil seluruh todo dari backend.
 */
async function getTodos() {
  const response = await fetch(`${API_URL}/api/todos`);
  return handleResponse(response);
}

/**
 * Membuat todo baru.
 */
async function createTodo(title) {
  const response = await fetch(`${API_URL}/api/todos`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ title }),
  });
  return handleResponse(response);
}

/**
 * Mengubah sebagian data todo (title dan/atau completed).
 */
async function updateTodo(id, changes) {
  const response = await fetch(`${API_URL}/api/todos/${id}`, {
    method: "PATCH",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(changes),
  });
  return handleResponse(response);
}

/**
 * Menghapus todo berdasarkan id.
 */
async function deleteTodo(id) {
  const response = await fetch(`${API_URL}/api/todos/${id}`, {
    method: "DELETE",
  });
  return handleResponse(response);
}
