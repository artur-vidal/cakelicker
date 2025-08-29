const API_URL = "../backend/routes/";
async function apiGet(endpoint) {
    try {
        const res = await fetch(API_URL + endpoint, {
            method: "GET",
            headers: { "Content-Type": "application/json" }
        });
        return await res.json();
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
        return await res.json();
    } catch (err) {
        console.error("Erro POST:", err);
        return null;
    }
}

export { apiGet, apiPost };