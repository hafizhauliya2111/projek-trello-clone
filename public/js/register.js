let csrfToken = '';

async function fetchCsrfToken() {
    const response = await fetch('/trello-clone/api/csrf.php');
    const data = await response.json();
    csrfToken = data.csrf_token;
}

fetchCsrfToken();

const form = document.getElementById('register-form');

form.addEventListener('submit', async function (event) {
    event.preventDefault();

    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    const response = await fetch('/trello-clone/api/register.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ username, email, password, csrf_token: csrfToken })
});

const result = await response.json();
const errorContainer = document.getElementById('error-container');

if (result.success) {
    window.location.href = 'login.html';
} else {
    errorContainer.innerHTML = result.errors
        .map(function (msg) {
            return '<p style="color: red;">' + msg + '</p>';
        })
        .join('');
}
});

