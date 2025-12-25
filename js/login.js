// login.js
const formLogin = document.querySelector('.authorization__form');

formLogin.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPassword').value;

    try {
        // 1️⃣ Получаем CSRF
        const csrfRes = await fetch('/api/csrf.php', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        const { csrf } = await csrfRes.json();

        // 2️⃣ Логин
        const res = await fetch('/api/login.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password, csrf })
        });

        const data = await res.json();

        if (res.ok && data.status === 'ok') {
            document.getElementById('modal').style.display = 'none';
            alert('Вы успешно вошли!');
            window.updateAuthState();
            window.updateUserState();
        } else {
            alert(data.message || 'Ошибка входа');
        }

    } catch (err) {
        console.error(err);
        alert('Ошибка сети или сервера');
    }
});
