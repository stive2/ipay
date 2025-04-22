// Fonction pour authentifier l'utilisateur
async function loginUser(email) { // password
    const url = 'http://127.0.0.1:82/api/agent/login2'; // URL de  l'API

    await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            email: email,
            // password: password,
        })
    }).then(response => response.json())
        .then(data => {
        // Stocker le token dans le localStorage
        localStorage.setItem('auth_token', data.data.token);
        localStorage.setItem('auth_id', data.data.agent.id);
        console.log('Login successful! Token stored.');
    }).catch(error => {
        console.error('Error:', error);
    });
}
