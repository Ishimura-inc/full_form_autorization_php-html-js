const nicknameEl = document.getElementById('nick');

async function updateUserState() {
    try {
        const res = await fetch('/api/users/me/index.php', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        // Любой не-200 → гость
        if (!res.ok) {
            nicknameEl.textContent = 'Гость';
            return;
        }

        const text = await res.text();

        // Пустой ответ → гость
        if (!text) {
            nicknameEl.textContent = 'Гость';
            return;
        }

        let data;
        try {
            data = JSON.parse(text);
        } catch {
            console.error('Invalid JSON:', text);
            nicknameEl.textContent = 'Гость';
            return;
        }

        // Проверка статуса и авторизации
        if (data.status === 'ok' && data.auth === true) {
            nicknameEl.textContent = data.data.nickname;
        } else {
            nicknameEl.textContent = 'Гость';
        }

    } catch (err) {
        console.error('updateUserState:', err);
        nicknameEl.textContent = 'Гость';
    }
}

document.addEventListener('DOMContentLoaded', updateUserState);
window.updateUserState = updateUserState;
