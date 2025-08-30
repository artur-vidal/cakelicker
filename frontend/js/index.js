import { apiPost } from "./api.js";
import { getForm } from "./utils.js";

const USERID_KEY = "userid";
const USERNAME_KEY = "username";
const USERNICKNAME_KEY = "usernick";

async function login() {
    // procurando formulario
    const form_data = getForm("#login-form");

    // mandando pra rota de login e esperando resposta
    const response = await apiPost("/login.php", {
        "user" : form_data.get("user"), 
        "password" : form_data.get("password")
    });

    if(response) {
        // console.log(`Resposta recebida.\n${JSON.stringify(response, null, 2)}`);
        
        // guardando dados do usuário recebidos se estiver tudo ok
        if(response.code == "OK") {
            document.querySelector("#user-nickname").textContent = response.data.nickname
        }
    }

}

// adicionando listener na ação submit
document.querySelector("#login-form").addEventListener("submit", (e) => {
    e.preventDefault(); // evita reload da página
    login();
});

async function register() {
    // procurando formulario
    const form_data = getForm("#register-form");

    // pegando dados e mandando pro servidor registrar
    const userdata = {
        "username" : form_data.get("username"),
        "nickname" : form_data.get("nickname"),
        "email" : form_data.get("email"),
        "password" : form_data.get("password"),
        "birthdate" : form_data.get("birthdate")
    };

    const response = apiPost("/register.php", userdata);

    if(response) {
        console.log(`Resposta recebida.\n${JSON.stringify(response, null, 2)}`);
    }
}

// adicionando listener na ação submit
document.querySelector("#register-form").addEventListener("submit", (e) => {
    e.preventDefault(); // evita reload da página
    register();
});

// manda pro PHP remover a sessão toda
async function logout() {
    const response = await apiPost('/remove_session.php');

    if(response && response.code == "OK") {
        document.querySelector("#user-nickname").textContent = "";
    }
}

// adicionando listener na ação submit
document.querySelector("#logout-button").addEventListener("click", (e) => {
    e.preventDefault(); // evita reload da página
    logout();
});

// tentando carregar sessão quando carregar a página
document.addEventListener('DOMContentLoaded', async () => {
    
    const session_response = await apiPost("/load_session.php");
    if(session_response) document.querySelector("#user-nickname").textContent = session_response.data.nickname

})