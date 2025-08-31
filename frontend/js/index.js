import { login, register, logout, getSession } from "./api.js";
import { getForm, logResponse } from "./utils.js";

const USERID_KEY = "userid";
const USERNAME_KEY = "username";
const USERNICKNAME_KEY = "usernick";

// adicionando listener no botão de LOGIN
document.querySelector("#login-form").addEventListener("submit", async (e) => {
    e.preventDefault(); // evita reload da página
    
    // pego dados do formulário e faço loginssss
    const formData = getForm('#login-form');
    const user = formData.get('user');
    const password = formData.get('password');

    const response = await login(user, password);
    logResponse(response);

    if(response || response.code == 'OK') {
        document.querySelector('#user-nickname').textContent = response.data.nickname
    }

});

// adicionando listener no botão de REGISTRAR
document.querySelector("#register-form").addEventListener("submit", async (e) => {
    e.preventDefault(); // evita reload da página
    
    // pego dados do formulário e faço loginssss
    const formData = getForm('#register-form');
    const username = formData.get('username');
    const nickname = formData.get('nickname');
    const email = formData.get('email');
    const password = formData.get('password');
    const birthdate = formData.get('birthdate');

    const response = await register(username, nickname, email, password, birthdate);
    logResponse(response);

});

// adicionando listener no botão de SAIR
document.querySelector("#logout-button").addEventListener("click", async (e) => {
    e.preventDefault(); // evita reload da página

    const response = await logout();
    logResponse(response);

    
    if(response || response.code == 'OK') {
        document.querySelector('#user-nickname').textContent = ''
    }
});

// tentando carregar sessão quando carregar a página
document.addEventListener('DOMContentLoaded', async () => {
    
    const session_response = await getSession();
    if(session_response && session_response.code == "OK") document.querySelector("#user-nickname").textContent = session_response.data.nickname

})