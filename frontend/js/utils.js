function getForm(selector) {
    
    // pego o formulário baseado no id e retorno o objeto FormData
    const form = document.querySelector(selector)
    return new FormData(form)
}

export { getForm }