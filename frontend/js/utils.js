export function getForm(selector) {
    
    // pego o formulário baseado no id e retorno o objeto FormData
    const form = document.querySelector(selector);
    return new FormData(form);
}

export function logResponse(responseJson) {

    // simplesmente dou um log bonito
    console.log(JSON.stringify(responseJson, null, 2));

}

function getCookieByName(cookieName) {
    const cookies = document.cookie;
    const semicolonSplit = cookies.split("; "); // lista de cookies, ainda em 'chave=valor'
    
    for(let i = 0; i < semicolonSplit.length; i++) {
        let curCookie = semicolonSplit[i].split("=");
        if(curCookie[0] == cookieName) {
            return curCookie[1];
        }
    }

    return null;
}