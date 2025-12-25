const formReg = document.querySelector('.registration__form');

formReg.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Данные с формы
	const nickname = document.getElementById('nickname').value.trim();
    const email = document.getElementById('emailReg').value.trim();
    const password = document.getElementById('passwordReg').value;
    const passwordRepeat = document.getElementById('passwordRegCopy').value;

    // 🔹 UX-проверки (НЕ безопасность)
	
	    if (!nickname) {
        alert('Введите никнейм');
        return;
    }

    if (nickname.length < 3 || nickname.length > 32) {
        alert('Никнейм должен быть от 3 до 32 символов');
        return;
    }
	
    if (!email) {
        alert('Введите email');
        return;
    }

    if (password.length < 6 || password.length > 64) {
        alert('Пароль должен быть от 6 до 64 символов');
        return;
    }

    if (password !== passwordRepeat) {
        alert('Пароли не совпадают');
        return;
    }

    try {
        // 1️⃣ Получаем CSRF
        const csrfRes = await fetch('/api/csrf.php', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });
        const { csrf } = await csrfRes.json();

        // 2️⃣ Отправка данных на бэкенд
        const res = await fetch('/api/register.php', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
				nickname,
                email,
                password,
                password_repeat: passwordRepeat,
                csrf
            })
        });

        const data = await res.json();

        if (res.ok && data.status === 'ok') {
            alert('Регистрация успешна! Вы автоматически вошли.');
            // Закрыть модалку
            document.getElementById('modalReg').style.display = 'none';
			
            if (typeof window.updateAuthState === 'function') {
                window.updateAuthState();
                window.updateUserState();
            }
			
        } else {
            alert(data.message || 'Ошибка регистрации');
        }

    } catch (err) {
        console.error(err);
        alert('Ошибка сети или сервера');
    }
});