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
            console.error("Resposta não é JSON:", text);
            return null;
        }

    } catch (err) {
        console.error("Erro GET:", err);
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
            console.error("Resposta não é JSON:", text);
            return null;
        }

    } catch (err) {
        console.error("Erro POST:", err);
        return null;
    }
}

export { apiGet, apiPost };