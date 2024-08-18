(function(){
    const tagsInput = document.querySelector('#tags_input')

    if(tagsInput){

        const tagsDiv = document.querySelector('#tags')
        const tagsInputHidden = document.querySelector('[name="tags"]');
        
        let tags = [];

        //recuperar del input oculto
        if(tagsInputHidden.value !== ''){
            tags = tagsInputHidden.value.split(',');
            mostrarTags();
        }

        //escuchar los cambios en el input
        tagsInput.addEventListener('keypress', guardarTag)

        function guardarTag(e){

            if(e.keyCode === 44){
                
                if(e.target.value.trim() === '' || e.target.value.value < 1){
                    return
                }
                e.preventDefault()
                tags = [...tags, e.target.value.trim()]
                tagsInput.value = '';
                mostrarTags()
            }
        }
        //mostrar en formulario el div con las areas de experiencia 
        function mostrarTags(){
            //Esto se hace con DOM Scripting y no con InnerHTML porque no podes agregar eventos 
            //genero los tags
            tagsDiv.textContent = '';
            tags.forEach(tag =>{
                const etiqueta = document.createElement('LI');
                etiqueta.classList.add('formulario__tag')
                etiqueta.textContent = tag;
                etiqueta.ondblclick = eliminarTag;
                tagsDiv.appendChild(etiqueta) 
            })

            actualizarInputHidden();
        }
        //borro los tags y actualizo el inputHidden
        function eliminarTag(e){
            e.target.remove()

            tags = tags.filter(tag => tag !== e.target.textContent)
            actualizarInputHidden();
        }

        function actualizarInputHidden(){
            tagsInputHidden.value = tags.toString();
        }
    }

})();