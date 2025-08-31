const API_URL = "../backend/routes";

async function apiGet(endpoint) {
    try {
        const res = await fetch(API_URL + endpoint, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });

        const text = await res.text(); // pegando resposta como texto primeiro

        // tento transformar o texto em JSON, se não der, eu aviso
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("Resposta não é JSON:\n", text);
            return null;
        }

    } catch (err) {
        console.error("Erro GET: ", err);
        return null;
    }
}

async function apiPost(endpoint, data) {
    try {
        const res = await fetch(API_URL + endpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });

        const text = await res.text(); // pegando resposta como texto primeiro

        // tento transformar o texto em JSON, se não der, eu aviso
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("Resposta não é JSON:\n", text);
            return null;
        }

    } catch (err) {
        console.error("Erro POST: ", err);
        return null;
    }
}

async function apiDelete(endpoint, data) {
    try {
        const res = await fetch(API_URL + endpoint, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });

        const text = await res.text(); // pegando resposta como texto primeiro

        // tento transformar o texto em JSON, se não der, eu aviso
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error("Resposta não é JSON:\n", text);
            return null;
        }

    } catch (err) {
        console.error("Erro DELETE: ", err);
        return null;
    }
}

export async function login(user, password) {

    // mandando pra rota de login e esperando resposta
    return apiPost("/login.php", {
        "user" : user, 
        "password" : password
    });
}

export async function logout() {
    return apiDelete('/session.php');
}

export async function getSession() {
    return apiGet('/session.php')
}

export async function register(username, nickname, email, password, birthdate) {

    // pegando dados e mandando pro servidor registrar
    return apiPost("/register.php", {
        "username" : username,
        "nickname" : nickname,
        "email" : email,
        "password" : password,
        "birthdate" : birthdate
    });
}