import { apiPost } from "./api.js";
import { getForm } from "./utils.js";

async function login() {
    // procurando formulario
    const form_data = getForm("#login-form")

    // mandando pra rota de login e esperando resposta
    const response = await apiPost("login.php", {
        "user" : form_data.get("user"), 
        "password" : form_data.get("password")
    });

    if(response) {
        console.log(`Resposta recebida.\n${JSON.stringify(response, null, 2)}`);
    }

}

// adicionando listener na ação submit
document.querySelector("#login-form").addEventListener("submit", (e) => {
    e.preventDefault(); // evita reload da página
    login();
});

async function register() {
    // procurando formulario
    const form_data = getForm("#register-form")

    // pegando dados e mandando pro servidor registrar
    const userdata = {
        "username" : form_data.get("username"),
        "nickname" : form_data.get("nickname"),
        "email" : form_data.get("email"),
        "password" : form_data.get("password"),
        "birthdate" : form_data.get("birthdate")
    }

    const response = await apiPost("register.php", userdata)

    if(response) {
        console.log(`Resposta recebida.\n${JSON.stringify(response, null, 2)}`)
    }
}

// adicionando listener na ação submit
document.querySelector("#register-form").addEventListener("submit", (e) => {
    e.preventDefault(); // evita reload da página
    register();
});